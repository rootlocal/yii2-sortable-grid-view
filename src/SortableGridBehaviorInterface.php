<?php

namespace rootlocal\widgets\sortable;

use Throwable;
use yii\base\InvalidConfigException;

/**
 * Interface SortableGridBehaviorInterface
 *
 * @package rootlocal\widgets\sortable
 */
interface SortableGridBehaviorInterface
{
    /**
     * Implementation sorting
     *
     * @param array $items
     * @return void
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function gridSort(array $items = []);

    /**
     * Getting Database field name for row sorting default value: sort_order
     *
     * @return string
     */
    public function getSortableAttribute(): string;
}