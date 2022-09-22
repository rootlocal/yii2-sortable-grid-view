<?php

namespace rootlocal\widgets\sortable;

use yii\base\InvalidConfigException;

/**
 * Interface SortableGridBehaviorInterface
 *
 * @package rootlocal\widgets\sortable
 */
interface SortableGridBehaviorInterface
{
    /**
     * Сортировка строк таблицы перетаскиванием "Drag-and-drop"
     *
     * @param array $items ['old_primary_key' => 'new_primary_key']
     * @return array New values attributes (new sorted values)
     * [['id' => `primary_key_value`, 'sort_id' => `sort_value`]]
     *
     * @throws InvalidConfigException
     */
    public function gridSort(array $items = []): array;

    /**
     * Сортировка строк таблицы нажатием на кнопки up или down
     *
     * @param string $button String name "up" or "down" action button
     * @param int $id Primary Key value Model
     * @return array new values models New values attributes (new sorted values)
     * [['id' => `primary_key_value`, 'sort_id' => `sort_value`]]
     *
     * @throws InvalidConfigException
     */
    public function gridSortUpOrDownButton(string $button, int $id): array;

    /**
     * Getting Database field name for row sorting default value: sort_order
     *
     * @return string
     */
    public function getSortableAttribute(): string;
}
