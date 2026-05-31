<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Person\services\manage;

use Besnovatyj\Meta\Meta;
use Besnovatyj\Person\entities\person\Person;
use Besnovatyj\Person\forms\backend\person\PersonForm;
use Besnovatyj\Person\repositories\CategoryRepository;
use Besnovatyj\Person\repositories\PersonRepository;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\db\StaleObjectException;

/**
 * Сервис управления персонами.
 *
 * Отвечает за CRUD персон и видео.
 * Логика загрузки/удаления/сортировки фотографий вынесена в standalone actions
 * через пакет besnovatyj/yii2-cms-images + PersonImageOwner.
 *
 * Метод makePhotoAvatar() оставлен здесь как специфика Person:
 * он устанавливает аватар через отдельный POST-запрос контроллера,
 * а не через стандартный image set-main-image action.
 */
class PersonManageService
{
    private PersonRepository $persons;
    private CategoryRepository $categories;

    public function __construct(
        PersonRepository   $persons,
        CategoryRepository $categories,
    ) {
        $this->persons    = $persons;
        $this->categories = $categories;
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function create(PersonForm $form): Person
    {
        $category = $this->categories->find($form->categories->main);

        $person = Person::create(
            $category->id,
            $form->name,
            $form->birthday,
            $form->description,
            new Meta(
                $form->meta->title,
                $form->meta->description,
                $form->meta->keywords
            ),
        );

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->persons->save($person);
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $person;
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function edit(int $id, PersonForm $form): void
    {
        $person   = $this->persons->get($id);
        $category = $this->categories->find($form->categories->main);

        $person->edit(
            $form->name,
            $form->birthday,
            $form->description,
            new Meta(
                $form->meta->title,
                $form->meta->description,
                $form->meta->keywords
            ),
        );

        $person->changeMainCategory($category->id);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->persons->save($person);
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function activate(int $id): void
    {
        $person = $this->persons->get($id);
        $person->activate();
        $this->persons->save($person);
    }

    /**
     * @throws Exception
     */
    public function draft(int $id): void
    {
        $person = $this->persons->get($id);
        $person->draft();
        $this->persons->save($person);
    }

    /**
     * @throws Exception
     */
    public function markForDeletion(int $id): void
    {
        $person = $this->persons->get($id);
        $person->markForDeletion();
        $this->persons->save($person);
    }

    /**
     * Удаляет все персоны, помеченные к удалению.
     *
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deletePending(): void
    {
        $persons = $this->persons->getAllPendingDelete();
        foreach ($persons as $person) {
            $this->persons->remove($person);
        }
    }

    /**
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function remove(int $id): void
    {
        $person = $this->persons->get($id);
        $this->persons->remove($person);
    }

}
