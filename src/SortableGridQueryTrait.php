<?php

namespace rootlocal\widgets\sortable;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;

/**
 * Trait SortableGridQueryTrait
 *
 * Trait for Sortable Grid Query class Model
 *
 * **Example:**
 * - Query class:
 *
 * ```php
 *      class BookQuery extends ActiveQuery
 *      {
 *          use SortableGridQueryTrait;
 *          // ...
 *       }
 * ```
 * - Model class:
 *
 * ```php
 *      class Book extends ActiveRecord
 *      {
 *          // ...
 *          public static function find(): BookQuery
 *          {
 *              return new BookQuery(get_called_class());
 *          }
 *          // ...
 *      }
 * ```
 * - Sorting:
 *
 * ```php
 *      $query = Book::find()->sortByOrder();
 * ```
 *
 *
 * @property ActiveRecord $modelClass
 *
 * @package rootlocal\widgets\sortable
 */
trait SortableGridQueryTrait
{
    /**
     * Order Sorting by 'sortableAttribute' [[SortableGridBehaviorInterface::getSortableAttribute]]
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