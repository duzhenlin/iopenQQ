<?php
/**
 * Created by PhpStorm.
 * User: duzhenlin
 * Date: 2018/6/27
 * Time: 16:29
 */

namespace IopenQQ\Article;


use IopenQQ\Core\AbstractAPI;
use IopenQQ\Core\App;
use Pimple\Container;

/**
 * Class Article
 * @property  App $container
 * @package IopenQQ\Article
 */
class Article extends AbstractAPI
{
    /**
     *
     */
    const AUTH_PUB_PIC_URL = 'https://api.om.qq.com/article/authpubpic';
    /**
     *
     */
    const AUTH_PUB_VID_URL = 'http://api.om.qq.com/article/authpubvid';
    /**
     *
     */
    const INFO_AUTH_URL = 'https://api.om.qq.com/transaction/infoauth';
    /**
     *
     */
    const AUTH_LIST = 'https://api.om.qq.com/article/authlist';

    /**
     * @var Container
     */
    protected $container;
    /**
     * @var false|mixed
     */
    protected $access_token;
    /**
     * @var
     */
    protected $client_id;

    /**
     * Article constructor.
     * @param Container $pimple
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    public function __construct(Container $pimple)
    {
        $this->container = $pimple;
        $this->client_id = $pimple['config']['client_id'];
        $this->access_token = $this->container->oauth_access_token->getToken($this->client_id);
    }

    /**
     * 获取授权用户文章列表
     * @param $openid
     * @param $page
     * @param $limit
     * @return bool|\IopenQQ\Core\Collection
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    public function authList($openid, $page, $limit)
    {
        $params = [
            'access_token' => $this->access_token,
            'openid' => $openid,
            'page' => $page,
            'limit' => $limit
        ];
        $result = $this->parseJSON('get', [self::AUTH_LIST, $params]);
        return !$result['code'] ? $result : false;
    }

    /**
     * 获取事务信息
     * @param $openid
     * @param $transaction_id
     * @return bool|\IopenQQ\Core\Collection
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    public function transactionInfo($openid, $transaction_id)
    {
        $params = [
            'access_token' => $this->access_token,
            'openid' => $openid,
            'transaction_id' => $transaction_id
        ];
        $result = $this->parseJSON('get', [self::INFO_AUTH_URL, $params]);
        return !$result['code'] ? $result : false;
    }

    /**
     * 发表视频
     * @param $path
     * @param $openid
     * @param $data
     * @return \IopenQQ\Core\Collection
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    public function authPubVic($path, $openid, $data)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new \InvalidArgumentException("文件不存在或者不可读： '$path'");
        }

        $params = [
            'access_token' => $this->access_token,
            'openid' => $openid,
            'title' => $data['title'],
            'tags' => $data['tags'],
            'cat' => $data['cat'],
            'md5' => $data['md5'],
            'desc' => $data['desc'],
        ];
        if ($data['apply']) {
            $params['apply'] = $data['apply'];
        }
        return $this->parseJSON('upload', [self::AUTH_PUB_VID_URL, ['media' => $path], $params]);
    }

    /**
     * 发送图文
     * @param $openid
     * @param $data
     * @return bool|\IopenQQ\Core\Collection
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    public function authPubPic($openid, $data)
    {

        $params = [
            'access_token' => $this->access_token,
            'openid' => $openid,
            'title' => $data['title'],
            'content' => $data['content'],
            'cover_pic' => $data['cover_pic'],
        ];
        if ($data['cover_type']) {
            $params['cover_type'] = $data['cover_type'];
        }
        if ($data['tag']) {
            $params['tag'] = $data['tag'];
        }
        if ($data['category']) {
            $params['category'] = $data['category'];
        }
        if ($data['apply']) {
            $params['apply'] = $data['apply'];
        }
        if ($data['original_platform']) {
            $params['original_platform'] = $data['original_platform'];
        }
        if ($data['original_url']) {
            $params['original_url'] = $data['original_url'];
        }
        if ($data['original_author']) {
            $params['original_author'] = $data['original_author'];
        }
        $result = $this->parseJSON('post', [self::AUTH_PUB_PIC_URL, $params]);
        return !$result['code'] ? $result : false;
    }
}