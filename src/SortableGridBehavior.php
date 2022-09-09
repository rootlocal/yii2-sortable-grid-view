<?php

namespace rootlocal\widgets\sortable;

use Throwable;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

/**
 * Behavior for sortable Yii2 GridView widget.
 *
 * For example:
 *
 * ```php
 * public function behaviors()
 * {
 *    return [
 *       'sort' => [
 *           'class' => SortableGridBehavior::class,
 *           'sortableAttribute' => 'sort_order',
 *           'scope' => function ($query) {
 *              $query->andWhere(['group_id' => $this->group_id]);
 *           },
 *       ],
 *   ];
 * }
 * ```
 *
 * @property string $sortableAttribute Database field name for row sorting default value: sort_order
 *
 * @package rootlocal\widgets\sortable
 */
class SortableGridBehavior extends Behavior implements SortableGridBehaviorInterface
{
    /** @var Closure|mixed Callable function for query */
    public $scope;
    /** @var Closure|mixed Callable function for after before insert sorting */
    public $afterGridSort;

    /** @var string|null Database field name for row sorting default value: sort_order */
    private ?string $_sortableAttribute = null;


    /**
     * {@inheritDoc}
     */
    public function events(): array
    {
        return [BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert'];
    }

    /**
     * @param array $items
     *
     * @return void
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function gridSort(array $items = [])
    {
        if (empty($items)) {
            return;
        }

        /** @var ActiveRecord $model */
        $model = $this->owner;

        if (!$model->hasAttribute($this->sortableAttribute)) {
            throw new InvalidConfigException("Model does not have sortable attribute `{$this->sortableAttribute}`.");
        }

        $newOrder = [];
        $models = [];

        foreach ($items as $old => $new) {
            $models[$new] = $model::findOne($new);
            $newOrder[$old] = $models[$new]->{$this->sortableAttribute} ? $models[$new]->{$this->sortableAttribute} : $new;
        }

        $model::getDb()->transaction(function () use ($models, $newOrder) {

            foreach ($newOrder as $modelId => $orderValue) {
                /** @var ActiveRecord[] $models */
                $models[$modelId]->updateAttributes([$this->sortableAttribute => $orderValue]);
            }

        });

        if (is_callable($this->afterGridSort)) {
            call_user_func($this->afterGridSort, $model);
        }
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function beforeInsert()
    {
        /** @var ActiveRecord $model */
        $model = $this->owner;

        if (!$model->hasAttribute($this->sortableAttribute)) {
            throw new InvalidConfigException("Invalid sortable attribute `{$this->sortableAttribute}`.");
        }

        $query = $model::find();

        if (is_callable($this->scope)) {
            call_user_func($this->scope, $query);
        }

        /* Override model alias if defined in the model's class */
        $query->from([$model::tableName() => $model::tableName()]);
        $maxOrder = $query->max('{{' . trim($model::tableName(), '{}') . '}}.[[' . $this->sortableAttribute . ']]');
        $model->{$this->sortableAttribute} = $maxOrder + 1;
    }

    /**
     * Getting Database field name for row sorting default value: sort_order
     *
     * @return string
     */
    public function getSortableAttribute(): string
    {
        if ($this->_sortableAttribute === null){
            $this->_sortableAttribute = 'sort_order';
        }

        return $this->_sortableAttribute;
    }

    /**
     * Setting Database field name for row sorting
     *
     * @param string $sortableAttribute
     */
    public function setSortableAttribute(string $sortableAttribute): void
    {
        $this->_sortableAttribute = $sortableAttribute;
    }

}