<?php

declare(strict_types=1);

namespace Besnovatyj\Person\forms\backend\person;

use Besnovatyj\CompositeForm\CompositeForm;
use Besnovatyj\Meta\MetaForm;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\entities\person\Video;
use Besnovatyj\Person\forms\backend\CategoriesForm;

/**
 * Форма создания/редактирования персоны.
 *
 * Фотографии управляются через AJAX-виджет (besnovatyj/yii2-cms-images)
 * на странице view после создания персоны.
 *
 * @property MetaForm       $meta
 * @property CategoriesForm $categories
 */
class PersonForm extends CompositeForm
{
    public $name;
    public $birthday;
    public $description;
    /** @var Video[] */
    public $videos;

    public function __construct(?Person $person = null, $config = [])
    {
        if ($person) {
            $this->name        = $person->name;
            $this->birthday    = $person->birthday;
            $this->description = $person->description;
            $this->videos      = array_map(static function (Video $video) {
                return $video->srcString;
            }, $person->videos);
            $this->categories  = new CategoriesForm($person);
            $this->meta        = new MetaForm($person->meta);
        } else {
            $this->categories = new CategoriesForm();
            $this->meta       = new MetaForm();
        }
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['birthday'], 'date', 'format' => 'yyyy-mm-dd'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string'],
            ['videos', 'each', 'rule' => ['string']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function internalForms(): array
    {
        return ['meta', 'categories'];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'categories'  => 'Категория',
            'name'        => 'Имя',
            'birthday'    => 'Дата рождения',
            'description' => 'Общая информация',
            'videos'      => 'Видео',
        ];
    }
}
