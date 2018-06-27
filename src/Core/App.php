<?php
/**
 * Created by PhpStorm.
 * User: duzhenlin
 * Date: 2018/4/19
 * Time: 12:04
 */

namespace IopenQQ\Core;


use Doctrine\Common\Cache\Cache as CacheInterface;
use Doctrine\Common\Cache\FilesystemCache;
use Pimple\Container;
use Symfony\Component\HttpFoundation\Request;
/**
 * Class App
 * @property  \IopenQQ\Oauth\Oauth $oauth
 * @property  \IopenQQ\Article\Article $article
 * @property  \IopenQQ\Oauth\AccessToken $oauth_access_token
 * @package IopenQQ\Core
 */
class App extends Container
{
    /**
     * @var array
     */
    protected static $valid_config_key = [
        'client_id',
        'client_secret',
        'redirect_uri',
        'cache',
        'cache_dir',
    ];
    /**
     * @var array
     */
    protected $providers = [
        ServiceProviders\OauthServiceProvider::class,
        ServiceProviders\ArticleServiceProvider::class,
    ];

    /**
     * App constructor.
     * @param $config
     */
    public function __construct($config)
    {
        parent::__construct();
        $config = $this->filterConfig($config);
        $this['config'] = function () use ($config) {
            return new Config($config);
        };

        if ($this['config']['debug']) {
            error_reporting(E_ALL);
        }
        $this->registerProviders();
        $this->registerBase();
        Http::setDefaultOptions($this['config']->get('guzzle', ['timeout' => 5.0]));
    }

    /**
     * 注册基本服务
     */
    protected function registerBase()
    {
        $this['request'] = function () {
            return Request::createFromGlobals();
        };
        if (!empty($this['config']['cache']) && $this['config']['cache'] instanceof CacheInterface) {
            $this['cache'] = $this['config']['cache'];
        } else {
            $this['cache'] = function () {
                return new FilesystemCache($this['config']->get('cache_dir', sys_get_temp_dir()));
            };
        }
    }


    /**
     *  注册服务
     */
    protected function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    /**
     * 过滤配置
     * @param $config
     * @return mixed
     */
    protected function filterConfig($config)
    {
        foreach ($config as $key => $val) {
            if (!in_array($key, self::$valid_config_key)) {
                unset($config[$key]);
            }
        }
        return $config;
    }

    /**
     * @param $provider
     * @return $this
     */
    public function addProvider($provider)
    {
        array_push($this->providers, $provider);

        return $this;
    }

    /**
     * @param array $providers
     */
    public function setProviders(array $providers)
    {
        $this->providers = [];

        foreach ($providers as $provider) {
            $this->addProvider($provider);
        }
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * @param $id
     * @param $value
     */
    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }
}