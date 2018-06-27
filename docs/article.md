## 文章发布

图文发布：
~~~
$data = [
    'title' => '暖心小学生！ 青岛雨中“小雷锋”引网友点赞',
    'content' => '青岛公交集团李沧巴士在了解这一情况后，立即根据网友提供信息，调取了9路线1419号车的监控，据监控显示，6月26日早高峰，雨水断断续续时大时小，发车后有乘客打开车窗通风，但因没有及时关闭，导致车厢内的座位上也溅上了雨水。面对有积水的座位，大多乘客没有去坐，选择了站立，但在车上一名小暖男的举动，温暖了整个车厢。',
    'cover_pic' => 'https://mats.gtimg.com/om/omopen_1.0/images//open_logo_header.v2.png',
];
$app->article->authPubPic($openid, $data)
~~~

发表视频：

~~~

$data = [
    'title' => 'title',
    'tags' => 'tags',
    'cat' => 'cat',
    'md5' => 'md5',
    'desc' => 'desc',
];
$path = 是视频文件路径
$app->article->authPubVic($path, $openid, $data);
~~~

获取授权用户文章列表：

~~~
$app->article->authList($openid, $page, $limit);
~~~

获取事务信息:

~~~
$app->article->transactionInfo($openid, $transaction_id);
~~~


