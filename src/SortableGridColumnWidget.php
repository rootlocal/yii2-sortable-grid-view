<?php

namespace rootlocal\widgets\sortable;


use Yii;
use yii\grid\Column;
use yii\helpers\Html;

/**
 * Class SortableGridColumn Sortable Column for Yii2 Grid widget
 *
 * To add an SortableGridColumn to the gridview. Configuration as follows:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => SortableGridColumnWidget::class,
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 *
 * @package rootlocal\widgets\sortable
 */
class SortableGridColumnWidget extends Column
{
    /** @var array [[SortableWidget]] Sortable JQuery Widget */
    public array $sortableWidgetOptions = [];
    /**
     * @var array the HTML attributes for the header cell tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $headerOptions = ['class' => 'sortable-column'];
    /**
     * @var string the template used for composing each cell in the action column.
     *
     * ```php
     * [
     *      'class' => \rootlocal\widgets\sortable\SortableGridColumnWidget::class,
     *      'template' => '{sort}'
     * ],
     * ```
     *
     * @see buttons
     */
    public string $template = '{sort}';
    /**
     * @var array button rendering callbacks. The array keys are the button names (without curly brackets),
     * and the values are the corresponding button rendering callbacks. The callbacks should use the following
     * signature:
     *
     * ```php
     * function ($model, $key) {
     *     // return the button HTML code
     * }
     * ```
     *
     * ```php
     * [
     *     'sort' => function ($model, $key) {
     *         return $model->status === $model::STATUS_SORTING ? Html::tag('span', 'sort') : '';
     *     },
     * ],
     * ```
     */
    public array $buttons = [];
    /**
     * @var array button icons. The array keys are the icon names and the values the corresponding html:
     * ```php
     * [
     *     'sort' => '<i class="fa fa-sort" aria-hidden="true"></i>',
     * ]
     * ```
     * Defaults to [FontAwesome](https://fontawesome.com)
     * @see https://fontawesome.com
     */
    public array $icons = [
        'sort' => '<i class="fa fa-arrows" aria-hidden="true"></i>',
    ];
    /** @var array visibility conditions for each button. The array keys are the button names (without curly brackets),
     * and the values are the boolean true/false or the anonymous function. When the button name is not specified in
     * this array it will be shown by default.
     * The callbacks must use the following signature:
     *
     * ```php
     * function ($model, $key, $index) {
     *     return $model->status === $model::STATUS_SORTING;
     * }
     * ```
     *
     * Or you can pass a boolean value:
     *
     * ```php
     * [
     *     'sort' => \Yii::$app->user->can('sort'),
     * ],
     * ```
     */
    public array $visibleButtons = [];
    /** @var array html options to be applied to the [[initDefaultButton()|default button]]. */
    public array $buttonOptions = [];


    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
        $this->initDefaultButtons();
        $objClass = $this->sortableWidgetOptions;
        $objClass['class'] = SortableWidget::class;
        Yii::createObject($objClass);
    }

    /**
     * Initializes the default button rendering callbacks.
     *
     * @return void
     */
    protected function initDefaultButtons(): void
    {
        $this->initDefaultButton('sort', 'sort', ['class' => 'sortable-column-btn']);
    }

    /**
     * Initializes the default button rendering callback for single button.
     *
     * @param string $name Button name as it's written in template
     * @param string $iconName The part of Bootstrap font awesome class that makes it unique
     * @param array $additionalOptions Array of additional HTML tag options
     */
    protected function initDefaultButton(string $name, string $iconName, array $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && str_contains($this->template, '{' . $name . '}')) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                $title = ucfirst($name);

                if ($name === 'sort') {
                    $title = Yii::t('rootlocal-sort', 'Sorting');
                }

                $options = array_merge(['title' => $title, 'aria-label' => $title], $additionalOptions, $this->buttonOptions);
                $icon = $this->icons[$iconName] ?? Html::tag('i', '', ['class' => "fa fa-$iconName"]);

                return Html::tag('span', $icon, $options);
            };
        }
    }


    /**
     * Renders the data cell content.
     * @param mixed $model the data model
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data model among the models array returned by [[GridView::dataProvider]].
     * @return string the rendering result
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
            $name = $matches[1];

            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure
                    ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                    : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }

            if ($isVisible && isset($this->buttons[$name])) {
                return call_user_func($this->buttons[$name], $model, $key, $this);
            }

            return '';
        }, $this->template);
    }

}