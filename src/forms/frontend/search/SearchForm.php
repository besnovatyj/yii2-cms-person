<?php

namespace Besnovatyj\Person\forms\frontend\search;

use Besnovatyj\Forms\BaseForm;
use Besnovatyj\Person\entities\Category;
use common\treeModule\TreeQueryScope;
use yii\db\ActiveQuery;

class SearchForm extends BaseForm
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
        return $scope->dropdownTree(
            nameAttribute: 'name',
            filter: function (ActiveQuery $query) {
                $query->active();
            },
            indent: '-'
        );
    }

    public function formName(): string
    {
        return '';
    }

    public function attributeLabels(): array
    {
        return [
            'text' => 'Имя',
            'category' => 'Категория',
            'age_from' => 'От',
            'age_to' => 'До',
        ];
    }

}
