<?php

namespace rootlocal\widgets\sortable;

use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

/**
 * Class Bootstrap
 *
 * @author Alexander Zakharov <sys@eml.ru>
 * @package rootlocal\widgets\sortable
 */
class Bootstrap implements BootstrapInterface
{

    /**
     * {@inheritDoc}
     */
    public function bootstrap($app)
    {
        // add module I18N category
        if (!isset($app->i18n->translations['rootlocal-sort'])) {
            $app->i18n->translations['rootlocal-sort'] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => __DIR__ . '/messages',
            ];
        }
    }
}