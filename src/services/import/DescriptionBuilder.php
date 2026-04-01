<?php

declare(strict_types=1);

namespace Besnovatyj\Person\services\import;

/**
 * Сборщик поля description из данных старого модуля OldPerson.
 *
 * Порядок и заголовки блоков легко изменяются через FIELD_MAP.
 * Пустые поля пропускаются.
 */
class DescriptionBuilder
{
    /**
     * Маппинг: заголовок блока => имя поля в старой таблице old_person_persons.
     *
     * Для изменения порядка или заголовков — просто переставьте/переименуйте элементы.
     */
    private const array FIELD_MAP = [
        'Общая информация' => 'general_information',
        'Звания' => 'titles',
        'Театр' => 'theatre',
        'Образование' => 'education',
        'Фильмография' => 'filmography',
        'Навыки' => 'additional_information',
    ];

    /**
     * Собирает description из атрибутов старой персоны и её характеристик.
     *
     * @param array $oldAttributes Атрибуты записи old_person_persons
     * @param array $characteristics Массив ['name' => string, 'value' => string]
     * @return string Готовое описание (plain text)
     */
    public function build(array $oldAttributes, array $characteristics): string
    {
        $blocks = [];

        foreach (self::FIELD_MAP as $title => $field) {
            $value = trim((string)($oldAttributes[$field] ?? ''));
            if ($value !== '') {
                $blocks[] = '<div class="mb-2"><strong>' . $title . "</strong><br>" . $value . '</div>';
            }
        }

        $charLines = [];
        foreach ($characteristics as $item) {
            $value = trim((string)($item['value'] ?? ''));
            if ($value !== '' && $value !== '0' && (!str_contains($item['name'], 'Треб-я проверка'))) {
                $charLines[] = $item['name'] . ': ' . $value;
            }
        }

        if ($charLines !== []) {
            $blocks[] = '<div class="mb-2"><strong>Прочее</strong><br>' . implode("<br>", $charLines) . '</div>';
        }

        $blocks = array_map(function ($str) {
            return '<div>' . $str . '</div>';
        }, $blocks);

        return implode("\n\n", $blocks);
    }
}
