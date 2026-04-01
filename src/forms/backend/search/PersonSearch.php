<?php

namespace Besnovatyj\Person\forms\backend\search;

use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\helpers\PersonHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class PersonSearch extends Model
{
    public $id;

    public $birthday;
    public $date_from;
    public $date_to;

    public $name;
    public $category_id;
    public $status;

    public function rules(): array
    {
        return [
            [['id', 'category_id', 'status'], 'integer'],
            [['name',], 'safe'],
//            [['date_from', 'date_to'], 'date', 'format' => 'php:Y-m-d'],
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
            'birthday' => $this->birthday,
            'category_id' => $this->category_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        $query
            ->andFilterWhere(['>=', 'date', $this->date_from ? strtotime($this->date_from) : null])
            ->andFilterWhere(['<=', 'date', $this->date_to ? strtotime($this->date_to) : null]);

        return $dataProvider;
    }

    public function categoriesList(): array
    {
        return ArrayHelper::map(Category::find()->andWhere(['>', 'depth', 0])->orderBy('lft')->asArray()->all(), 'id', function (array $category) {
            return ($category['depth'] > 1 ? str_repeat('-- ', $category['depth'] - 1) . ' ' : '') . $category['name'];
        });
    }

    public function statusList(): array
    {
        return PersonHelper::statusList();
    }
}
