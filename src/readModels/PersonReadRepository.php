<?php

namespace Besnovatyj\Person\readModels;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\forms\frontend\search\SearchForm;
use common\treeModule\TreeQueryScope;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\ActiveQuery;

class PersonReadRepository
{
    private TreeQueryScope $treeScope;

    public function __construct()
    {
        $this->treeScope = new TreeQueryScope(Category::class);
    }

    /**
     * @throws Exception
     */
    public function countByAge(int $ageItem): int
    {
        $query = Person::find()->active();

        if ($ageItem >= 0) {
            // 'today' без компонента времени — результат не зависит от секунды выполнения
            $today = new DateTimeImmutable('today');
            // Не моложе $ageItem лет: уже отпраздновали $ageItem-й день рождения
            $query->andWhere(['<=', 'birthday', $today->sub(new DateInterval("P{$ageItem}Y"))->format('Y-m-d')]);
            // Не старше $ageItem лет: ещё не отпраздновали ($ageItem+1)-й день рождения.
            // Строгое '>' исключает именинников, которым сегодня исполняется $ageItem+1
            $query->andWhere(['>', 'birthday', $today->sub(new DateInterval('P' . ($ageItem + 1) . 'Y'))->format('Y-m-d')]);
        }

        return $query->count();
    }

    public function getAll(): DataProviderInterface
    {
        // Индексная страница, пока не реализована
        $query = Person::find()->alias('p')->active('p')->with('mainPhoto');
        return $this->getProvider($query);
    }

    public function getAllByCategory(Category $category): DataProviderInterface
    {
        $query = Person::find()->alias('p')->active('p')->with('mainPhoto', 'category');
        $ids = $this->treeScope->descendantIds($category, andSelf: true);
        $query->andWhere(['p.category_id' => $ids]);
        $query->groupBy('p.id');
        return $this->getProvider($query);
    }

    public function find($id): ?Person
    {
        // Конкретный актёр
        // andWhere( person.category=active )
        $query = Person::find()->alias('p')->active('p');
        $query->joinWith(['category c'], false);
        $query->andWhere(['and', ['p.id' => $id], ['c.status' => Category::STATUS_ACTIVE]]);
        $query->groupBy('p.id');
        /** @var $person Person */
        $person = $query->one();
        return $person;
//        return Person::find()->alias('p')->active('p')->andWhere(['id' => $id])->one();
    }

    private function getProvider(ActiveQuery $query): ActiveDataProvider
    {
        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['birthday' => SORT_ASC],
                'attributes' => [
                    'birthday' => [
                        'asc' => ['p.birthday' => SORT_ASC],
                        'desc' => ['p.birthday' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSizeLimit' => [15, 100],
                'pageSize' => 30
            ]
        ]);
    }

    /**
     * @throws Exception
     */
    public function search(SearchForm $form): DataProviderInterface
    {
        // Страница поиска
        $query = Person::find()->alias('p')->active('p')->with('mainPhoto', 'category');

        if ($form->category) {
            $category = Category::find()->active()->andWhere(['id' => $form->category])->one();
            if ($category) {
                $ids = $this->treeScope->descendantIds($category, andSelf: true);
                $query->andWhere(['p.category_id' => $ids]);
            } else {
                $query->andWhere(['p.id' => 0]);
            }
        }
        $today = new DateTimeImmutable('today');
        if ($form->age_from) {
            // Не моложе age_from лет
            $query->andWhere(['<=', 'birthday', $today->sub(new DateInterval('P' . $form->age_from . 'Y'))->format('Y-m-d')]);
        }
        if ($form->age_to) {
            // Не старше age_to лет; строгое '>' исключает именинников (age_to + 1)
            $query->andWhere(['>', 'birthday', $today->sub(new DateInterval('P' . ($form->age_to + 1) . 'Y'))->format('Y-m-d')]);
        }

        if (!empty($form->text)) {
            $query->andWhere(['or',
                ['like', 'p.name', $form->text],
//                ['like', 'p.description', $form->text],
            ]);
        }

        // andWhere( person.category=active )
        $query->joinWith(['category c'], false);
        $query->andWhere(['c.status' => Category::STATUS_ACTIVE]);

        $query->groupBy('p.id'); // При join надо группировать, чтобы не было повторов

//        var_dump($query->prepare(\Yii::$app->db->queryBuilder)->createCommand()->rawSql); exit();

        return $this->getProvider($query);
    }
}
