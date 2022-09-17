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
     * @return array|string[]
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function run(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Json::decode(Yii::$app->request->getRawBody());
        $result = [];

        if (!$this->_model->hasMethod('gridSort') || !$this->_model->hasMethod('gridSortUpOrDownButton')) {
            throw new InvalidConfigException('Not found right `SortableGridBehavior` behavior Model class.');
        }

        if (!array_key_exists('action', $request)) {
            return ['status' => 'error', 'message' => 'parameter "action" not requested'];
        }

        switch ($request['action']) {
            case 'up':
            case 'down':
                $result = $this->getModel()->gridSortUpOrDownButton($request['action'], $request['id']);
                break;

            case 'sortable':
                if (array_key_exists('items', $request) && !empty($request['items'])) {
                    $result = $this->getModel()->gridSort($request['items']);
                } else {
                    return ['status' => 'error', 'message' => 'parameter "items" not requested'];
                }
                break;
        }

        return ['status' => empty($result) ? 'error' : 'success', 'result' => Json::htmlEncode($result), 'action' => $request['action']];
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