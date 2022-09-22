<?php

namespace rootlocal\widgets\sortable;

use Exception;
use Throwable;
use Yii;
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
 * @package rootlocal\widgets\sortable
 */
class SortableGridBehavior extends Behavior implements SortableGridBehaviorInterface
{
    /** @var Closure|mixed|null Callable function for query */
    public $scope;
    /** @var Closure|mixed|null Callable function for after before insert sorting */
    public $afterGridSort;
    /** @var string Database field name for row sorting default value: sort_order */
    public string $sortableAttribute = 'sort_order';


    /**
     * Declares event handlers for the [[owner]]'s events.
     *
     * @return array events (array keys) and the corresponding event handler methods (array values).
     */
    public function events(): array
    {
        return [BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert'];
    }

    /**
     * {@inheritDoc}
     */
    public function gridSortUpOrDownButton(string $button, int $id): array
    {
        $model = $this->loadModel();
        $primaryKey = $this->primaryKey();

        $owner = $model->find()
            ->select([$this->getSortableAttribute(), $primaryKey])
            ->where([$primaryKey => $id])->one();

        $target = $model::find()->select([$this->getSortableAttribute(), $primaryKey]);

        if ($button === 'up') {
            $target = $target->andWhere($model::tableName() . '.' . $this->sortableAttribute . ' < :sort', [
                ':sort' => $owner->{$this->sortableAttribute}
            ])->orderBy([$this->sortableAttribute => SORT_DESC])->one();
        } else {
            $target = $target->andWhere($model::tableName() . '.' . $this->sortableAttribute . ' > :sort', [
                ':sort' => $owner->{$this->sortableAttribute}
            ])->orderBy([$this->sortableAttribute => SORT_ASC])->one();
        }

        if ($target === null) {
            $model->addError($this->sortableAttribute, 'Move error: Destination Not Found');
            return [];
        }

        $transaction = $model->getDb()->beginTransaction();

        try {
            $ownerSortId = $owner->{$this->sortableAttribute};
            $targetSortId = $target->{$this->sortableAttribute};
            $owner->{$this->sortableAttribute} = $targetSortId;
            $target->{$this->sortableAttribute} = $ownerSortId;

            if ($owner->save(false)
                && $target->save(false)) {
                $transaction->commit();

                return [
                    0 => ['id' => $owner->getPrimaryKey(), 'sort_id' => $owner->{$this->sortableAttribute}],
                    1 => ['id' => $target->getPrimaryKey(), 'sort_id' => $target->{$this->sortableAttribute}],
                ];
            }

            $transaction->rollBack();
        } catch (Exception|Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), self::class);
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function gridSort(array $items = []): array
    {
        $model = $this->loadModel();
        $primaryKey = $this->primaryKey();
        /** @var int[] $newOrder */
        $newOrder = [];
        /** @var ActiveRecord[] $models */
        $models = [];

        foreach ($items as $old => $new) {
            $models[$new] = $model::find()
                ->select([$this->getSortableAttribute(), $primaryKey])
                ->where([$primaryKey => $new])->one();

            $newOrder[$old] =
                !empty($models[$new]->{$this->getSortableAttribute()})
                    ? $models[$new]->{$this->getSortableAttribute()} : $new;
        }

        $transaction = $model::getDb()->beginTransaction();
        $result = [];

        try {
            foreach ($newOrder as $modelId => $orderValue) {
                $models[$modelId]->updateAttributes([$this->getSortableAttribute() => $orderValue]);
                $result[] = ['id' => $modelId, 'sort_id' => $orderValue];
            }

            $transaction->commit();
        } catch (Exception|Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), self::class);
            return [];
        }

        if (is_callable($this->afterGridSort)) {
            call_user_func($this->afterGridSort, $model);
        }

        return $result;
    }

    /**
     * ModelEvent an event that is triggered before inserting a record.
     *
     * @return void
     * @throws InvalidConfigException
     */
    public function beforeInsert(): void
    {
        $model = $this->loadModel();

        if (empty($model->{$this->getSortableAttribute()})) {
            $query = $model::find();

            if (is_callable($this->scope)) {
                call_user_func($this->scope, $query);
            }

            /* Override model alias if defined in the model's class */
            $query->from([$model::tableName() => $model::tableName()]);
            $maxOrder = $query->max('{{%' . trim($model::tableName(), '{}%')
                . '}}.[[' . $this->getSortableAttribute() . ']]');
            $model->{$this->getSortableAttribute()} = $maxOrder + 1;
        }
    }

    /**
     * Getting Database field name for row sorting default value: sort_order
     *
     * @return string
     */
    public function getSortableAttribute(): string
    {
        return $this->sortableAttribute;
    }

    /**
     * @return  ActiveRecord
     * @throws InvalidConfigException
     */
    private function loadModel(): ActiveRecord
    {
        /** @var ActiveRecord $model */
        $model = $this->owner;

        if (!$model->hasAttribute($this->getSortableAttribute())) {
            throw new InvalidConfigException(sprintf(
                'Model does not have sortable attribute: "%s"',
                $this->getSortableAttribute()
            ));
        }

        return $model;
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    private function primaryKey(): string
    {
        $model = $this->loadModel();
        /** @var ?string $primaryKey */
        $primaryKey = !empty($model::primaryKey()) && is_array($model::primaryKey()) ? $model::primaryKey()[0] : null;

        if ($primaryKey === null) {
            throw new InvalidConfigException("Model does not have primaryKey");
        }

        return $primaryKey;
    }
}
