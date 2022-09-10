# YII2 sortable GridView column Widget

[![Latest Stable Version](https://img.shields.io/packagist/v/rootlocal/yii2-sortable-grid-view.svg)](https://packagist.org/packages/rootlocal/yii2-sortable-grid-view)

* [Source code](https://github.com/rootlocal/yii2-sortable-grid-view)
* [API Documentation](https://rootlocal.github.io/yii2-sortable-grid-view-docs/api/)

## install

~~~shell
composer require rootlocal/yii2-sortable-grid-view
~~~

or added composer.json:

~~~
    "rootlocal/yii2-sortable-grid-view": "~2.0"
~~~

### Model Behavior

~~~php
<?php

namespace common\models;

use rootlocal\widgets\sortable\SortableGridBehavior;

class Book extends ActiveRecord
{
// ...

    /**
     * {@inheritDoc}
     */
    public function behaviors(): array
    {
        return [
            'sort' => ['class' => SortableGridBehavior::class],
            // ...
        ];
    }
}
~~~

### Query

~~~php
<?php

namespace common\models;

use rootlocal\widgets\sortable\SortableGridQueryTrait;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Book]].
 *
 * @see Book
 * @mixin SortableGridQueryTrait
 */
class BookQuery extends ActiveQuery
{
    use SortableGridQueryTrait;
    
    // ...
}
~~~

### Sorting
~~~php
<?php

$query = Book->find()->sortByOrder();
~~~

## Example

~~~php
<?php

use common\models\BookSearch;
use rootlocal\widgets\sortable\SortableGridColumnWidget;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\web\View;

/**
 * @var View $this
 * @var BookSearch $searchModel
 * @var ActiveDataProvider $dataProvider
 */

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        
        ['class' => SortableGridColumnWidget::class],
        
        // ...

    ]]) ?>
~~~

## Extended config

~~~php
<?php

use common\models\BookSearch;
use rootlocal\widgets\sortable\SortableGridColumnWidget;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\web\View;
use yii\helpers\Url;

/**
 * @var View $this
 * @var BookSearch $searchModel
 * @var ActiveDataProvider $dataProvider
 */

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        
        [
            'class' => SortableGridColumnWidget::class,
            'template' => '{sort} {test}',
            'sortableWidgetOptions' => [
                // Sort action
                'sortableAction' => Url::to(['/book/sort'])
            ],
            
            'buttons' => [
                'test' => fn(BookSearch $model, $key) => '<i class="fa fa-address-book"></i>',
            ],
            
            'visibleButtons' => [
                'test' => fn(BookSearch $model) => $model->status === $model::STATUS_TEST10,
            ],
        ],
        
        // ...

    ]]) ?>
~~~