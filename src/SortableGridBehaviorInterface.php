<?php

namespace rootlocal\widgets\sortable;

use Throwable;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * Interface SortableGridBehaviorInterface
 *
 * @package rootlocal\widgets\sortable
 */
interface SortableGridBehaviorInterface
{
    /**
     * Sorting Items
     *
     * @param array $items
     * @return bool true if success, false if error
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function gridSort(array $items = []): bool;

    /**
     * Getting Database field name for row sorting default value: sort_order
     *
     * @return string
     */
    public function getSortableAttribute(): string;
}