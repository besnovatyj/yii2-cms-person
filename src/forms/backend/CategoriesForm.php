<?php

namespace Besnovatyj\Person\forms\backend;

use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\TreeManager\Manager\TreeQueryScope;
use yii\base\Model;

class CategoriesForm extends Model
{
    public int $main = 0;

    public function __construct(?Person $person = null, $config = [])
    {
        if ($person) {
            $this->main = $person->category_id;
        }
        parent::__construct($config);
    }

    public function categoriesList(): array
    {
        $scope = new TreeQueryScope(Category::class);
        return $scope->dropdownTree();
    }

    public function rules(): array
    {
        return [
            ['main', 'required'],
            ['main', 'integer'],
        ];
    }
}
