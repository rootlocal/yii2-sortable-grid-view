<?php

namespace rootlocal\widgets\sortable;

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
     * @throws InvalidConfigException
     * @throws BadRequestHttpException|Throwable
     */
    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Json::decode(Yii::$app->request->getRawBody());

        if (!array_key_exists('items', $request)) {
            throw new BadRequestHttpException('Don\'t received POST param `items`. ');
        }

        if (!$this->_model->hasMethod('gridSort')) {
            throw new InvalidConfigException('Not found right `SortableGridBehavior` behavior Model class.');
        }

        $this->getModel()->gridSort($request['items']);

        return ['status' => 'success'];
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