<?php

declare(strict_types=1);

use Besnovatyj\Meta\Meta;
use Besnovatyj\Person\entities\Category;
use Besnovatyj\Person\repositories\PersonRepository;
use Besnovatyj\Person\services\manage\PersonVideoService;
use Besnovatyj\Person\thumbnails\VideoFactory;
use common\treeModule\entities\Node;
use common\treeModule\forms\TreeNodeFormInterface;
use common\treeModule\TreeManager;
use common\treeModule\TreeQueryScope;

/**
 * Конфигурация DI контейнера для модуля Person
 */
return function (\yii\di\Container $container): void {

    // TreeManager для категорий Person
    $container->setSingleton('person.tree.manager', function () {
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
    });

    // TreeQueryScope для чтения дерева категорий Person
    $container->setSingleton('person.tree.scope', fn() => new TreeQueryScope(Category::class));

    // Фабрика видео-данных
    $container->setSingleton(VideoFactory::class, fn() => new VideoFactory());


    // Сервис управления видеороликами
    $container->setSingleton(PersonVideoService::class, fn() => new PersonVideoService(
        $container->get(VideoFactory::class),
        $container->get(PersonRepository::class),
    ));
};
