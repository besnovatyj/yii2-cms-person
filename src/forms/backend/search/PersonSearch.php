<?php

namespace Besnovatyj\Person\forms\backend\search;

use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\helpers\PersonHelper;
use common\treeModule\TreeQueryScope;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PersonSearch extends Model
{
    public ?string $id = null;
    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?string $name = null;
    public ?string $category_id = null;
    public ?string $status = null;

    public function rules(): array
    {
        return [
            [['id', 'category_id', 'status'], 'integer'],
            [['name',], 'safe'],
            [['date_from', 'date_to'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Person::find()->with('mainPhoto', 'category');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['birthday' => SORT_ASC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'category_id' => $this->category_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        $query
            ->andFilterWhere(['>=', 'birthday', $this->date_from ?: null])
            ->andFilterWhere(['<=', 'birthday', $this->date_to ?: null]);

        return $dataProvider;
    }

    public function categoriesList(): array
    {
        $scope = new TreeQueryScope(Category::class);
        return $scope->dropdownTree();
    }


    public function statusList(): array
    {
        return PersonHelper::statusList();
    }
}
