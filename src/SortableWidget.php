<?php

namespace rootlocal\widgets\sortable;


use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/**
 * Class SortableWidget Sortable JQuery Widget
 *
 * Documentation Jquery js: [api.jqueryui.com](https://api.jqueryui.com/sortable/)
 *
 * @property-read string $hash Hash variable to store the plugin [[SortableWidget::getHash]]
 * @property-read void $jsOptions JS object options [[SortableWidget::getJsOptions]]
 *
 * @package rootlocal\widgets\sortable
 */
class SortableWidget extends Widget
{
    /**
     * String The name of the jQuery plugin to use for this widget.
     *
     * @var string
     */
    public const PLUGIN_NAME = 'sortable_grid_view';

    /** @var string|array Controller Sorted action see [[SortableGridAction]] */
    public $sortableAction = ['sort'];
    /** @var string Css JQuery Selector Default <code>.grid-view</code> */
    public string $selector = '.grid-view';
    /**
     * @var array Options for js plugin
     * <hr>
     * Documentation Jquery js: [api.jqueryui.com](https://api.jqueryui.com/sortable/)
     * <br>
     * **action** `'sort'` Адрес url экшена котроллера на который отправляется запрос<br>
     *
     * **axis** `'y'` Позволяет задать ось, по которой можно перетаскивать элемент. Возможные значения:<br>
     * `'x'` элемент можно будет перетаскивать только по горизонтали<br>
     * `'y'` элемент можно будет перетаскивать только по вертикали<br>
     *
     * **cursor** `'move'` Позволяет задать вид курсора мыши во время перетаскивания.<br>
     *
     * **opacity** `'0.9'` Устанавливает прозрачность элемента помощника<br>
     * (элемент, который отображается во время перетаскивания).<br>
     *
     * **delay** `0` Устанавливает задержку в миллисекундах перед тем, как элемент<br>
     * начнет перетаскиваться (может использоваться для предотвращения перетаскивания<br>
     * при случайном щелчке на элементе).<br>
     *
     * **items** `tr` Указывает какие элементы в группе могут быть отсортированы.<br>
     * Значение  `'> *'` - все элементы в выбранной группе<br>
     *
     * **handle** `'.sortable-column-btn'` Указывает элемент, при щелчке на который начнется перетаскивание.<br>
     */
    public array $options = [];

    /** @var string|null JS object options [[SortableWidget::getJsOptions]] */
    private ?string $_jsOptions = null;
    /** @var string|null Hash variable to store the plugin [[SortableWidget::getHash]] */
    private ?string $_hash = null;


    /**
     * Initializes the object.
     * This method is called at the end of the constructor.
     * The default implementation will trigger an [[EVENT_INIT]] event.
     */
    public function init()
    {
        parent::init();
        $this->sortableAction = Url::to($this->sortableAction);
        $view = $this->getView();
        $this->registerAssets($view);
        $this->registerClientScripts($view);
    }

    /**
     * Register assets
     *
     * @param View $view
     * @return void
     */
    private function registerAssets(View $view): void
    {
        SortableAsset::register($view);
    }

    /**
     * Registers the needed client script and options.
     *
     * @param View $view
     * @return void
     */
    private function registerClientScripts(View $view): void
    {
        $this->hashPluginOptions($view);
        $js = sprintf('jQuery("%s").%s(%s);',
            $this->selector, self::PLUGIN_NAME, $this->getHash());
        $view->registerJs(new JsExpression($js));
    }

    /**
     * Getting JS options
     *
     * @return string
     */
    public function getJsOptions(): string
    {
        if ($this->_jsOptions === null) {

            $defaultOptions = [
                'action' => $this->sortableAction,
            ];

            $options = ArrayHelper::merge($defaultOptions, $this->options);
            $json = Json::htmlEncode($options);
            $this->_jsOptions = new JsExpression($json);
        }

        return $this->_jsOptions;
    }

    /**
     * Generates a hashed variable to store the plugin
     *
     * @return string
     */
    public function getHash(): string
    {
        if (empty($this->_hash)) {
            $this->_hash = $this::PLUGIN_NAME . '_'
                . hash('crc32', $this->id . $this->getJsOptions());
        }

        return $this->_hash;
    }

    /**
     * Register JS variable for [[SortableWidget::PLUGIN_NAME]]
     *
     * @param $view View
     */
    private function hashPluginOptions(View $view)
    {
        $js = sprintf('var %s = %s;', $this->hash, $this->getJsOptions());
        $view->registerJs(new JsExpression($js), $view::POS_HEAD);
    }
}