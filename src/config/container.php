<?php

declare(strict_types=1);

use Besnovatyj\Meta\Meta;
use Besnovatyj\Person\entities\Category;
use common\treeModule\entities\Node;
use common\treeModule\forms\TreeNodeFormInterface;
use common\treeModule\TreeManager;
use common\treeModule\TreeQueryScope;

/**
 * Конфигурация DI контейнера для модуля Person
 */
return [
    'singletons' => [

        // TreeManager для категорий Person
        'person.tree.manager' => function () {
            return new TreeManager(
                modelClass: Category::class,
                entityFactory: function (TreeNodeFormInterface $form): Category {
                    return Category::create(
                        $form->name,
                        $form->slug,
                        $form->description,
                        new Meta(
                            $form->meta->title,
                            $form->meta->description,
                            $form->meta->keywords,
                        ),
                    );
                },
                entityUpdater: function (Node $node, TreeNodeFormInterface $form): Node {
                    /** @var Category $node */
                    $node->edit(
                        $form->name,
                        $form->slug,
                        $form->description,
                        new Meta(
                            $form->meta->title,
                            $form->meta->description,
                            $form->meta->keywords,
                        ),
                    );
                    return $node;
                },
            );
        },

        // TreeQueryScope для чтения дерева категорий Person
        'person.tree.scope' => function () {
            return new TreeQueryScope(Category::class);
        },

    ],
];
