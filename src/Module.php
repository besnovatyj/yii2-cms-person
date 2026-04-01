<?php

namespace Besnovatyj\Person;

use common\components\module\BaseModule;
use Yii;

class Module extends BaseModule
{
    public const bool EDITABLE = true;

    public function init(): void
    {
        parent::init();
        if (Yii::$app->id === 'app-frontend') {
            $this->controllerNamespace = 'Besnovatyj\\' . $this->id . '\controllers\frontend';
        }
    }

    public static function getContainerConfig(): array
    {
        return require __DIR__ . '/config/container.php';
    }

    public static function getAdminMenu(): array
    {
        return require __DIR__ . '/config/adminMenu.php';
    }

    public static function getConfig(): array
    {
        return require __DIR__ . '/config/config.php';
    }

    public static function getOptions(): array
    {
        return require __DIR__ . '/config/options.php';
    }

    public static function getDependencies(): array
    {
        return require __DIR__ . '/config/dependencies.php';
    }

}
