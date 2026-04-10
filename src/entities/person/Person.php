<?php

namespace Besnovatyj\Person\entities\person;

use DateTimeImmutable;
use Exception;
use Besnovatyj\Meta\MetaBehavior;
use common\components\dispatcher\AggregateRoot;
use Besnovatyj\Meta\Meta;
use common\components\dispatcher\EventTrait;
use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\entities\person\queries\PersonQuery;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
/**
 * @property integer $id
 * @property integer $category_id           // Категория, раздел
 * @property string $name                   // ФИО
 * @property string $birthday            // Дата рождения
 * @property string $description    // Основная информация
 * @property integer $main_photo_id         // Главная фотография
 * @property integer $status                // Статус
 * @property integer $created_at            // Дата создания записи
 *
 * @property Meta $meta
 * @property Category $category
 * @property Photo $mainPhoto
 * @property Photo[] $photos
 * @property PersonVideo[] $videos
 */
class Person extends ActiveRecord implements AggregateRoot
{
    use EventTrait;

    public const int STATUS_DRAFT = 0;
    public const int STATUS_ACTIVE = 1;
    public const int STATUS_PENDING_DELETE = 2;

    public $meta;

    public static function create(
        $categoryId,
        $name,
        $birthday,
        $description,
        Meta $meta,
    ): self
    {
        $person = new static();
        $person->category_id = $categoryId;
        $person->name = $name;
        $person->birthday = $birthday;
        $person->description = $description;
        $person->meta = $meta;
        $person->status = self::STATUS_DRAFT;
        $person->created_at = time();
        return $person;
    }

    public function edit(
        $name,
        $birthday,
        $description,
        Meta $meta,
    ): void
    {
        $this->name = $name;
        $this->birthday = $birthday;
        $this->description = $description;
        $this->meta = $meta;
    }

    public function changeMainCategory($categoryId): void
    {
        $this->category_id = $categoryId;
    }

    public function activate(): void
    {
        if ($this->isActive()) {
            throw new DomainException('Already active.');
        }
        $this->status = self::STATUS_ACTIVE;
    }

    public function draft(): void
    {
        if ($this->isDraft()) {
            throw new DomainException('Already draft.');
        }
        $this->status = self::STATUS_DRAFT;
    }

    public function markForDeletion(): void
    {
        if ($this->isPendingDelete()) {
            throw new DomainException('Already marked for deletion.');
        }
        $this->status = self::STATUS_PENDING_DELETE;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPendingDelete(): bool
    {
        return $this->status === self::STATUS_PENDING_DELETE;
    }

    public function getSeoTitle(): string
    {
        return $this->meta->title ?: $this->name;
    }

    /**
     * @throws Exception
     */
    public function getAge(): string
    {
        $birthday = new DateTimeImmutable($this->birthday ?? 'now');
        $now = new DateTimeImmutable('now');
        $years = (int)$now->diff($birthday)->format('%y');
        return $years . ' ' . $this->num2word($years, ['год', 'года', 'лет']);
    }

    protected function num2word($num, $words)
    {
//        https://ru.stackoverflow.com/questions/89458/
        $num %= 100;
        if ($num > 19) {
            $num %= 10;
        }
        switch ($num) {
            case 1:
            {
                return ($words[0]);
            }
            case 2:
            case 3:
            case 4:
            {
                return ($words[1]);
            }
            default:
            {
                return ($words[2]);
            }
        }
    }

    // Photos

    public function setMainPhoto(?int $id): void
    {
        $this->main_photo_id = $id;
    }

    ##########################

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getPhotos(): ActiveQuery
    {
        return $this->hasMany(Photo::class, ['person_id' => 'id'])->orderBy('sort');
    }

    public function getMainPhoto(): ActiveQuery
    {
        return $this->hasOne(Photo::class, ['id' => 'main_photo_id']);
    }

    public function getVideos(): ActiveQuery
    {
        return $this->hasMany(PersonVideo::class, ['person_id' => 'id'])->orderBy('sort');
    }

    ##########################

    public static function tableName(): string
    {
        return '{{%person_persons}}';
    }

    public function behaviors(): array
    {
        return [
            MetaBehavior::class,
        ];
    }

    public function transactions(): array
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public function beforeDelete(): bool
    {
        if (parent::beforeDelete()) {
            foreach ($this->photos as $photo) {
                $photo->delete();
            }
            foreach ($this->videos as $video) {
                $video->delete();
            }
            return true;
        }
        return false;
    }

//    public function afterSave($insert, $changedAttributes): void
//    {
//        $related = $this->getRelatedRecords();
//        parent::afterSave($insert, $changedAttributes);
//        if (array_key_exists('mainPhoto', $related)) {
//            $this->updateAttributes(['main_photo_id' => $related['mainPhoto'] ? $related['mainPhoto']->id : null]);
//        }
//    }

    public function attributeLabels(): array
    {
        return [
            'category_id' => 'Категория, раздел',
            'name' => 'ФИО',
            'birthday' => 'Дата рождения',
            'status' => 'Статус',
            'created_at' => 'Дата добавления',
            'age' => 'Возраст',
        ];
    }

    public static function find(): PersonQuery
    {
        return new PersonQuery(static::class);
    }
}
