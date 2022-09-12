<?php

namespace rootlocal\widgets\sortable;

use Exception;
use Throwable;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Action for sortable Yii2 GridView widget.
 *
 * example:
 *
 * ```php
 * public function actions()
 * {
 *    return [
 *       'sort' => [
 *          'class' => SortableGridAction::class,
 *          'model' => Model::class,
 *       ],
 *   ];
 * }
 * ```
 *
 * @property ActiveRecordInterface|array|string $model
 *
 * @package rootlocal\widgets\sortable
 */
class SortableGridAction extends Action
{
    /** @var SortableGridBehaviorInterface|ActiveRecord|null */
    private $_model;


    /**
     * @return array|string[]
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function run(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Json::decode(Yii::$app->request->getRawBody());

        if (!$this->_model->hasMethod('gridSort')) {
            throw new InvalidConfigException('Not found right `SortableGridBehavior` behavior Model class.');
        }

        if (array_key_exists('id', $request) && array_key_exists('action', $request)) {
            $sortableAttribute = $this->getModel()->sortableAttribute;
            $tableColumn = $this->getModel()::tableName() . '.' . $sortableAttribute;
            $owner = $this->getModel()::findOne((int)$request['id']);
            $target = null;

            switch ($request['action']) {
                case 'up';
                    $target = $this->getModel()::find()
                        ->andWhere($tableColumn . ' < :sort', [':sort' => $owner->{$sortableAttribute}])
                        ->orderBy(['sort_order' => SORT_DESC])
                        ->one();
                    break;
                case 'down':
                    $target = $this->getModel()::find()
                        ->andWhere($tableColumn . ' > :sort', [':sort' => $owner->{$sortableAttribute}])
                        ->orderBy(['sort_order' => SORT_ASC])
                        ->one();
                    break;
            }

            if ($target === null) {
                throw new BadRequestHttpException('Can\'t find target model');
            }

            $transaction = $this->getModel()->getDb()->beginTransaction();

            try {
                $ownerSortId = $owner->{$sortableAttribute};
                $targetSortId = $target->{$sortableAttribute};
                $owner->{$sortableAttribute} = $targetSortId;
                $target->{$sortableAttribute} = $ownerSortId;

                if ($owner->save(false) && $target->save(false)) {
                    $transaction->commit();

                    return [
                        'status' => 'success',
                        'action' => $request['action'],
                        'id' => $request['id'],
                        'owner' => [
                            'pk' => $owner->getPrimaryKey(),
                            'sort_id' => $owner->{$sortableAttribute},
                        ],
                        'target' => [
                            'pk' => $target->getPrimaryKey(),
                            'sort_id' => $target->{$sortableAttribute},
                        ],
                    ];

                } else {
                    $transaction->rollBack();
                    return ['status' => 'error', 'message' => 'RollBack, models not Saved'];
                }

            } catch (Exception|Throwable $e) {
                $transaction->rollBack();
                Yii::error($e->getMessage(), self::class);
                throw $e;
            }

        }

        if (array_key_exists('items', $request)) {
            $this->getModel()->gridSort($request['items']);
            return ['status' => 'success'];
        }

        throw new BadRequestHttpException('Don\'t received POST param `items`. ');
    }

    /**
     * @return SortableGridBehaviorInterface|ActiveRecord
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param string|ActiveRecord|null $model
     * @throws InvalidConfigException
     */
    public function setModel($model): void
    {
        if (is_string($model)) {
            /** @var SortableGridBehaviorInterface $obj */
            $obj = Yii::createObject(['class' => $model]);
            $this->_model = $obj;
            return;
        }

        if (is_array($model) && array_key_exists('class', $model)) {
            /** @var SortableGridBehaviorInterface $obj */
            $obj = Yii::createObject($model);
            $this->model = $obj;
            return;
        }

        if ($model instanceof ActiveRecord) {
            /** @var SortableGridBehaviorInterface $obj */
            $obj = $model;
            $this->model = $obj;
            return;
        }

        throw new  InvalidConfigException('No valid "model" attribute');
    }

}