<?php

/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Person;

use Besnovatyj\Kernel\module\CmsModule;
use Besnovatyj\Contracts\dashboard\DashboardWidgetDescriptor;
use Besnovatyj\Contracts\dashboard\ProvidesDashboardWidgets;
use Besnovatyj\Contracts\module\DeclaresModule;
use Besnovatyj\Contracts\module\ProvidesAdminMenu;
use Besnovatyj\Contracts\module\ProvidesDirectories;
use Besnovatyj\Contracts\module\ProvidesMigrations;
use Besnovatyj\Contracts\module\ProvidesOptions;
use Besnovatyj\Person\widgets\dashboard\PersonsCountTile;
use Yii;

class Module extends CmsModule implements
    DeclaresModule, ProvidesAdminMenu,
    ProvidesDirectories, ProvidesMigrations,
    ProvidesOptions, ProvidesDashboardWidgets
{
    public const bool EDITABLE = true;
    public const string VERSION = '1.0.0';
    public const string MODULE_ID = 'Person';
    public static function moduleId(): string { return self::MODULE_ID; }
    public static function moduleVersion(): string { return self::VERSION; }
    public static function isEditable(): bool { return self::EDITABLE; }
    public static function adminMenu(): array { return require __DIR__.'/config/adminMenu.php'; }
    public static function moduleConfig(): array { return require __DIR__.'/config/config.php'; }
    public static function options(): array { return require __DIR__.'/config/options.php'; }
    public static function migrationPath(): string { return __DIR__.'/migrations'; }
    public static function migrationNamespace(): ?string { return __NAMESPACE__.'\\migrations'; }
    public static function directories(): array { return ['@static/origin/Person','@static/cache/Person'];}

    /** @return DashboardWidgetDescriptor[] */
    public static function dashboardWidgets(): array
    {
        return [
            new DashboardWidgetDescriptor(
                id: self::MODULE_ID . '.personsCount',
                title: 'Актёры',
                tileClass: PersonsCountTile::class,
                iconClass: 'bi bi-person-badge',
                priority: 210,
            ),
        ];
    }

}
