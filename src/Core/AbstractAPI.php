<?php
/**
 * Created by PhpStorm.
 * User: duzhenlin
 * Date: 2018/4/19
 * Time: 13:56
 */

namespace IopenQQ\Core;


use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use IopenQQ\Core\Exceptions\HttpException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AbstractAPI
 * @package IMap\Core
 */
abstract class  AbstractAPI
{
    /**
     * @var
     */
    private $_http;
    /**
     * @var
     */
    protected $cache;


    /**
     * @return Cache|FilesystemCache
     */
    protected function getCacheHandler()
    {
        if (!$this->cache instanceof Cache) {
            $this->cache = new FilesystemCache(sys_get_temp_dir());
        }
        return $this->cache;
    }



    /**
     * @return Http
     */
    protected function getHttp()
    {
        if (!$this->_http) {
            $this->_http = new Http();
        }
        return $this->_http;
    }

    /**
     * @param $method
     * @param array $args
     * @return Collection
     * @throws HttpException
     */
    public function parseJSON($method, array $args)
    {
        $http = $this->getHttp();

        $contents = $http->parseJSON(call_user_func_array([$http, $method], $args));

        $this->checkAndThrow($contents);


        return new Collection($contents);
    }

    /**
     * @param array $contents
     * @throws HttpException
     */
    protected function checkAndThrow(array $contents)
    {
        if (isset($contents['code']) && 0 != $contents['code']) {
            if (empty($contents['msg'])) {
                $contents['msg'] = 'Unknown';
            }
            throw new HttpException($contents['msg'], $contents['code']);
        }
    }

    protected function registerHttpMiddlewares()
    {
        // retry
        $this->_http->addMiddleware($this->retryMiddleware());
        // access token
        $this->_http->addMiddleware($this->accessTokenMiddleware());
    }

    /**
     * @return callable
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            // Limit the number of retries to 2
            if ($retries <= 2 && $response && $body = $response->getBody()) {
                // Retry on server errors
                if (stripos($body, 'code') && (stripos($body, '40015') || stripos($body, '41009'))) {
                    $field = $this->accessToken->getQueryName();
                    $token = $this->accessToken->getToken(true);

                    $request->withUri($newUri = Uri::withQueryValue($request->getUri(), $field, $token));
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * @return \Closure
     */
    protected function accessTokenMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if (!$this->accessToken) {
                    return $handler($request, $options);
                }

                $field = $this->accessToken->getQueryName();
                $token = $this->accessToken->getToken();

                $request = $request->withUri(Uri::withQueryValue($request->getUri(), $field, $token));

                return $handler($request, $options);
            };
        };
    }
}