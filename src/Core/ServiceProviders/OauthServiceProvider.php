<?php

namespace IopenQQ\Core\ServiceProviders;

use IopenQQ\Oauth\AccessToken;
use IopenQQ\Oauth\Oauth;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OauthServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['oauth'] = function () use ($pimple) {
            return new Oauth(
                $pimple['config']['client_id'],
                $pimple['config']['client_secret'],
                $pimple['config']['redirect_uri'],
                $pimple['oauth_access_token']
            );
        };

        $pimple['oauth_access_token'] = function () use ($pimple) {
            return new AccessToken($pimple);
        };
    }

}