##授权
获取授权url，$state 为可选参数
~~~
$app->oauth->getOauthRedirect($state);

~~~

根据code获取openid，根据openid获取用户信息
~~~
  $res = $app->oauth->getOauthAccessToken($code);
  $userInfo = $app->oauth->getOauthUserinfo($res['openid']);
~~~