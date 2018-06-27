<?php

namespace IopenQQ\Oauth;

use IopenQQ\Core\AbstractAPI;
use IopenQQ\Core\Collection;

/**
 * Class Oauth
 * @property  AccessToken $oauth_access_token
 * @package IopenQQ\Oauth
 */
class Oauth extends AbstractAPI
{
    const GET_CODE_URL = 'https://auth.om.qq.com/omoauth2/authorize?';
    const GET_TOKEN_URL = 'https://auth.om.qq.com/omoauth2/accesstoken?';
    const GET_REFRESH_TOKEN_URL = 'https://auth.om.qq.com/omoauth2/refreshtoken?';
    const GET_USER_INFO_URL = 'https://auth.om.qq.com/omoauth2/refreshtoken?';

    protected $client_id;
    protected $client_secret;
    protected $redirect_uri;
    protected $oauth_access_token;

    public function __construct($client_id, $client_secret, $redirect_uri, $oauth_access_token)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
        $this->oauth_access_token = $oauth_access_token;
    }


    /**
     * 获取授权跳转地址
     * @param string $state
     * @return string
     */
    public function getOauthRedirect($state = '')
    {
        $params = [
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'state' => $state,
        ];
        return self::GET_CODE_URL . http_build_query($params);
    }


    /**
     * @param $appid
     * @param $code
     * @return bool|Collection
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    public function getOauthAccessToken($appid, $code)
    {
        $params = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $result = $this->parseJSON('get', [self::GET_TOKEN_URL, $params]);
        if (!$result['code']) {
            $data = [
                'access_token' => $result['data']['access_token'],
                'openid' => $result['data']['openid'],
                'scope' => $result['data']['scope'],
                'refresh_toekn' => $result['data']['refresh_toekn'],
            ];
            $this->oauth_access_token->cacheToken($appid, $result);
        }
        return !$result['code'] ? new Collection($data) : false;
    }

    public function refreshToken($openid)
    {
        $params = [
            'openid' => $openid,
            'client_id' => $this->client_id,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refreshtoken',
        ];
        $result = $this->parseJSON('get', [self::GET_REFRESH_TOKEN_URL, $params]);
        if (!$result['code']) {
            $data = [
                'access_token' => $result['data']['access_token'],
                'openid' => $result['data']['openid'],
                'scope' => $result['data']['scope'],
                'refresh_toekn' => $result['data']['refresh_toekn'],
            ];
            $this->oauth_access_token->cacheToken($appid, $result);
        }
        return !$result['code'] ? new Collection($data) : false;
    }

    /**
     * 获取用户信息
     * @param $openid
     * @param $access_token
     * @return bool|Collection
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    public function getOauthUserinfo($openid, $access_token)
    {
        $params = [
            'access_token' => $access_token,
            'openid' => $openid,
        ];
        $result = $this->parseJSON('get', [self::GET_USER_INFO_URL, $params]);
        return !$result['code'] ? $result : false;
    }
}