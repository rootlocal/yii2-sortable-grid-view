<?php

namespace rootlocal\widgets\sortable;


use yii\base\Widget;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;

/**
 * Class SortableWidget Sortable version of Yii2 Sortable widget.
 *
 * @property-read string $hash Hash variable to store the plugin {@see SortableGridView::getHash()}
 * @property-read void $jsOptions Getting JS object options {@see SortableGridView::getJsOptions()}
 *
 * @package rootlocal\widgets\sortable
 */
class SortableWidget extends Widget
{
    /** @var string|array Sort action */
    public $sortableAction = ['sort'];
    /** @var string Css JQuery Selector Default .grid-view */
    public string $selector = '.grid-view';
    /** @var string string The name of the jQuery plugin to use for this widget. */
    public const PLUGIN_NAME = 'sortable_grid_view';

    /** @var string|null JS object options */
    private ?string $_jsOptions = null;
    /** @var string|null */
    private ?string $_hash = null;


    /**
     * {@inheritDoc}
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
    protected function registerAssets(View $view): void
    {
        SortableAsset::register($view);
    }

    /**
     * Registers the needed client script and options.
     *
     * @param View $view
     * @return void
     */
    public function registerClientScripts(View $view): void
    {
        $this->hashPluginOptions($view);
        $js = sprintf('jQuery("%s").%s(%s);', $this->selector, self::PLUGIN_NAME, $this->getHash());
        $view->registerJs(new JsExpression($js));
    }

    /**
     * Getting JS object options
     *
     * @return string
     */
    public function getJsOptions(): string
    {
        if ($this->_jsOptions === null) {
            $json = Json::htmlEncode(['action' => $this->sortableAction]);
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
            $this->_hash = $this::PLUGIN_NAME . '_' . hash('crc32', $this->id . $this->getJsOptions());
        }

        return $this->_hash;
    }

    /**
     * Register JS variable $this::PLUGIN_NAME
     *
     * @param $view View
     */
    protected function hashPluginOptions(View $view)
    {
        $js = sprintf('var %s = %s;', $this->hash, $this->getJsOptions());
        $view->registerJs(new JsExpression($js), $view::POS_HEAD);
    }
}