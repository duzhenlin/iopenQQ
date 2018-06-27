<?php

namespace IopenQQ\Oauth;

use IopenQQ\Core\AbstractAPI;
use Pimple\Container;

/**
 * Class AccessToken
 * @package IopenQQ\Oauth
 */
class AccessToken extends AbstractAPI
{
    /**
     *
     */
    const ACCESS_TOKEN_URL = 'https://auth.om.qq.com/omoauth2/refreshtoken?';

    /**
     * @var Container
     */
    protected $container;
    /**
     * @var
     */
    protected $client_id;
    /**
     * @var string
     */
    protected $accessTokenCacheKey = 'Dzl.IOpenQQ.oauth_access_token.';
    /**
     * @var string
     */
    protected $refreshTokenCacheKey = 'Dzl.IOpenQQ.oauth_refresh_token.';

    /**
     * AccessToken constructor.
     * @param Container $pimple
     */
    public function __construct(Container $pimple)
    {
        $this->container = $pimple;
        $this->client_id = $this->container['config']['client_id'];
        $this->cache = $this->container->cache;
    }

    /**
     * 缓存access token
     * @param string $client_id
     * @param array|\IopenQQ\Core\Collection $data access token 信息
     */
    public function cacheToken($client_id, $data)
    {
        $access_token = $data['data']['access_token'];
        $expires_in = $data['data']['expires_in'];
        $refresh_token = $data['data']['refresh_token'];
        $this->getCacheHandler()->save($this->accessTokenCacheKey . $client_id, $access_token, $expires_in - 1500);
        $this->getCacheHandler()->save($this->refreshTokenCacheKey . $client_id, $refresh_token, 0);
    }


    /**
     * 获取access_token
     * @param $client_id
     * @param bool $forceRefresh
     * @return false|mixed
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    public function getToken($client_id, $forceRefresh = false)
    {
        $access_token = $this->getCacheHandler()->fetch($this->accessTokenCacheKey . $client_id);
        if (!$access_token || $forceRefresh) {
            $access_token = $this->getAccessToken($client_id);
        }
        return $access_token;
    }

    /**
     * 刷新获取access_token
     * @param $openid
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    protected function getAccessToken($openid)
    {

        $params = [
            'openid' => $openid,
            'client_id' => $this->client_id,
            'refresh_token' => $this->getCacheHandler()->fetch($this->refreshTokenCacheKey . $this->client_id),
            'grant_type' => 'refreshtoken',
        ];
        $token = $this->parseJSON('get', [self::ACCESS_TOKEN_URL, $params]);
        if (!$token['code']) {
            $this->cacheToken($this->client_id, $token);
        }
    }
}