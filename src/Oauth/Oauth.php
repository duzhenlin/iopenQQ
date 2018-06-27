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
    /**
     *
     */
    const GET_CODE_URL = 'https://auth.om.qq.com/omoauth2/authorize?';
    /**
     *
     */
    const GET_TOKEN_URL = 'https://auth.om.qq.com/omoauth2/accesstoken?';
    /**
     *
     */
    const GET_REFRESH_TOKEN_URL = 'https://auth.om.qq.com/omoauth2/refreshtoken?';
    /**
     *
     */
    const GET_USER_INFO_URL = 'https://api.om.qq.com/media/basicinfoauth?';

    /**
     * @var
     */
    protected $client_id;
    /**
     * @var
     */
    protected $client_secret;
    /**
     * @var
     */
    protected $redirect_uri;
    /**
     * @var
     */
    protected $oauth_access_token;

    /**
     * Oauth constructor.
     * @param $client_id
     * @param $client_secret
     * @param $redirect_uri
     * @param $oauth_access_token
     */
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

        ];
        if ($state) {
            $params['state'] = $state;
        }
        return self::GET_CODE_URL . http_build_query($params);
    }


    /**
     * 通过code换取第三方授权access_token
     * @param $code
     * @return bool|Collection
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    public function getOauthAccessToken($code)
    {
        $params = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $result = $this->parseJSON('post', [self::GET_TOKEN_URL, $params]);

        if (!$result['code']) {
            $data = [
                'access_token' => $result['data']['access_token'],
                'openid' => $result['data']['openid'],

                'refresh_token' => $result['data']['refresh_token'],
            ];
            @$data['scope'] = $result['data']['scope'];
            $this->oauth_access_token->cacheToken($this->client_id, $result);
        }
        return !$result['code'] ? new Collection($data) : false;
    }

    /**
     * 获取用户信息
     * @param $openid
     * @return bool|Collection
     * @throws \IopenQQ\Core\Exceptions\HttpException
     */
    public function getOauthUserInfo($openid)
    {
        $access_token = $this->oauth_access_token->getToken($this->client_id);
        $params = [
            'access_token' => $access_token,
            'openid' => $openid,
        ];
        $result = $this->parseJSON('post', [self::GET_USER_INFO_URL, $params]);
        !$result['code'] ? $result->set('openid', $openid) : false;
        return !$result['code'] ? $result : false;
    }
}