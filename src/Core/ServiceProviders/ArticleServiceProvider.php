<?php

namespace IopenQQ\Core\ServiceProviders;

use IopenQQ\Article\Article;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ArticleServiceProvider
 * @package IopenQQ\Core\ServiceProviders
 */
class ArticleServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple)
    {
        $pimple['article'] = function () use ($pimple) {
            return new Article($pimple);
        };
    }
}