<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Person\forms\backend\search;

use Besnovatyj\Person\helpers\CategoryHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use Besnovatyj\Person\entities\Category;

class CategorySearch extends Model
{
    public int|string|null $id = null;
    public int|string|null $name = null;
    public int|string|null $slug = null;
    public int|string|null $status = null;
    public int|string|null $title = null;

    public function rules(): array
    {
        return [
            [['id', 'status',], 'integer'],
            [['name', 'slug', 'title'], 'safe'],
        ];
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Category::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['lft' => SORT_ASC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
        ]);

        $query
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }

    public function statusList(): array
    {
        return CategoryHelper::statusList();
    }
}
