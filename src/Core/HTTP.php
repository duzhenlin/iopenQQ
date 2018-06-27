<?php
/**
 * Created by PhpStorm.
 * User: duzhenlin
 * Date: 2018/4/19
 * Time: 13:44
 */

namespace IopenQQ\Core;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\HandlerStack;
use IopenQQ\Core\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HTTP
 * @package IMap\Core
 */
class HTTP
{

    /**
     * @var
     */
    protected $client;
    /**
     * @var array
     */
    protected $middlewares = [];
    /**
     * @var array
     */
    protected static $defaults = [];

    /**
     * @param array $defaults
     */
    public static function setDefaultOptions($defaults = [])
    {
        self::$defaults = $defaults;
    }

    /**
     * Return current guzzle default settings.
     *
     * @return array
     */
    public static function getDefaultOptions()
    {
        return self::$defaults;
    }


    /**
     * GET request.
     * @param $url
     * @param array $options
     * @return mixed|ResponseInterface
     */
    public function get($url, array $options = [])
    {

        return $this->request($url, 'GET', ['query' => $options]);
    }

    /**
     * POST request.
     * @param $url
     * @param array $options
     * @return mixed|ResponseInterface
     */
    public function post($url, $options = [])
    {
        $key = is_array($options) ? 'form_params' : 'body';

        return $this->request($url, 'POST', [$key => $options]);
    }

    /**
     * JSON request.
     * @param $url
     * @param array $options
     * @param int $encodeOption
     * @return mixed|ResponseInterface
     */
    public function json($url, $options = [], $encodeOption = JSON_UNESCAPED_UNICODE)
    {
        is_array($options) && $options = json_encode($options, $encodeOption);

        return $this->request($url, 'POST', ['body' => $options, 'headers' => ['content-type' => 'application/json']]);
    }


    /**
     * Upload file.
     * @param $url
     * @param array $files
     * @param array $form
     * @param array $queries
     * @return mixed|ResponseInterface
     */
    public function upload($url, array $files = [], array $form = [], array $queries = [])
    {
        $multipart = [];

        foreach ($files as $name => $path) {
            $multipart[] = [
                'name' => $name,
                'contents' => fopen($path, 'r'),
            ];
        }

        foreach ($form as $name => $contents) {
            $multipart[] = compact('name', 'contents');
        }

        return $this->request($url, 'POST', ['query' => $queries, 'multipart' => $multipart]);
    }

    /**
     * Set GuzzleHttp\Client.
     *
     * @param  \GuzzleHttp\Client $client
     * @return Http
     */
    public function setClient(HttpClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Return GuzzleHttp\Client instance.
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        if (!($this->client instanceof HttpClient)) {
            $this->client = new HttpClient();
        }

        return $this->client;
    }

    /**
     * Add a middleware.
     *
     * @param  callable $middleware
     * @return $this
     */
    public function addMiddleware(callable $middleware)
    {
        array_push($this->middlewares, $middleware);

        return $this;
    }

    /**
     * Return all middlewares.
     *
     * @return array
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }


    /**
     * Make a request.
     * @param $url
     * @param string $method
     * @param array $options
     * @return mixed|ResponseInterface
     */
    public function request($url, $method = 'GET', $options = [])
    {

        $method = strtoupper($method);

        $options = array_merge(self::$defaults, $options);

        $options['handler'] = $this->getHandler();

        $response = $this->getClient()->request($method, $url, $options);

        return $response;
    }


    /**
     * @param $body
     * @return bool|mixed
     * @throws HttpException
     */
    public function parseJSON($body)
    {
        if ($body instanceof ResponseInterface) {
            $body = $body->getBody();
        }

        // XXX: json maybe contains special chars. So, let's FUCK the WeChat API developers ...
        $body = $this->fuckTheWeChatInvalidJSON($body);

        if (empty($body)) {
            return false;
        }

        $contents = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new HttpException('Failed to parse JSON: ' . json_last_error_msg());
        }

        return $contents;
    }

    /**
     * Filter the invalid JSON string.
     *
     * @param  \Psr\Http\Message\StreamInterface|string $invalidJSON
     * @return string
     */
    protected function fuckTheWeChatInvalidJSON($invalidJSON)
    {
        return preg_replace("/\p{Cc}/u", '', trim($invalidJSON));
    }

    /**
     * Build a handler.
     *
     * @return HandlerStack
     */
    protected function getHandler()
    {
        $stack = HandlerStack::create();

        foreach ($this->middlewares as $middleware) {
            $stack->push($middleware);
        }

        return $stack;
    }
}