<?php

namespace Besnovatyj\Person\forms\frontend\search;

use Besnovatyj\Person\entities\Category;
use common\treeModule\TreeQueryScope;
use yii\base\Model;

class SearchForm extends Model
{
    public ?string $text = null;
    public ?int $category = null;
    public ?int $age_from = null;
    public ?int $age_to = null;

    public function rules(): array
    {
        return [
            [['text'], 'string'],
            [['category', 'age_from', 'age_to'], 'integer'],
        ];
    }

    public function categoriesList(): array
    {
        $scope = new TreeQueryScope(Category::class);
        return $scope->dropdownTree();
    }

    public function formName(): string
    {
        return '';
    }

    public function attributeLabels(): array
    {
        return [
            'text' => 'Общий поиск',
            'category' => 'Категория',
            'age_from' => 'От',
            'age_to' => 'До',
        ];
    }

}
