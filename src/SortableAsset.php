<?php

namespace rootlocal\widgets\sortable;

use yii\jui\JuiAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\YiiAsset;

/**
 * Class SortableAsset
 *
 * @package rootlocal\widgets\sortable
 */
class SortableAsset extends AssetBundle
{
    /** @var string[] */
    public $js = ['js/jquery.sortable.gridview.js'];
    /** @var string[] */
    public $css = ['css/sortable_gridview.css'];
    /** @var string[] */
    public $depends = [
        YiiAsset::class,
        JqueryAsset::class,
        JuiAsset::class
    ];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
    }
}