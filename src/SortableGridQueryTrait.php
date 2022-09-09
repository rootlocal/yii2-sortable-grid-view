<?php

namespace rootlocal\widgets\sortable;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;

/**
 * Trait SortableGridQueryTrait
 * Trait for Sortable Grid Query class Model
 *
 * @property ActiveRecord $modelClass
 *
 * @package rootlocal\widgets\sortable
 */
trait SortableGridQueryTrait
{
    /**
     * Order Sorting by 'sortableAttribute' {@see SortableGridBehaviorInterface::getSortableAttribute()}
     *
     * @return ActiveQuery
     */
    public function sortByOrder(): ActiveQuery
    {
        $modelClass = $this->modelClass;
        /* @var $model SortableGridBehaviorInterface|ActiveRecordInterface */
        $model = $modelClass::instance();

        return $this->orderBy([$model::tableName() . '.' . $model->getSortableAttribute() => SORT_ASC]);
    }

}