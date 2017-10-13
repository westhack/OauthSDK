# OauthSDK
QQ、微信、微博、第三方登录SDK

## 安装

使用 Composer 安装:

```
composer require fakerpp/oauthsdk
```

### 如何使用

```html
html
    <a href="login.php?client=qq">QQ登录</a>
    <a href="login.php?client=wechat">微信登录</a>
    <a href="login.php?client=sina">新浪登录</a>
```
```php
use OauthSDK\Oauth;

$config =  array(
    //腾讯QQ登录配置
    'QQ' => array(
        'APP_KEY' => '123456', //应用注册成功后分配的 APP ID
        'APP_SECRET' => '9cc9ac2fb17d010104d8a58dbebb4d3a', //应用注册成功后分配的KEY
        'CALLBACK' =>  'http://www.example.com/callback.php?client=qq',//回调URL
        'SCOPE' =>  'get_user_info',//应用授权作用域
    ),
    //新浪微博配置
    'SINA' => array(
        'APP_KEY' => '123456', //应用注册成功后分配的 APP ID
        'APP_SECRET' => '9cc9ac2fb17d010104d8a58dbebb4d3a', //应用注册成功后分配的KEY
        'CALLBACK' => 'http://www.example.com/callback.php?client=sina',//回调URL
        'SCOPE' => 'all',//应用授权作用域
    ),
    //腾讯微信配置
    'WECHAT' => array(
        'APP_KEY' => '123456', //应用注册成功后分配的 APP ID
        'APP_SECRET' => '9cc9ac2fb17d010104d8a58dbebb4d3a', //应用注册成功后分配的KEY
        'CALLBACK' => 'http://www.example.com/callback.php?client=wechat',//回调URL
        'SCOPE' => 'snsapi_userinfo',//应用授权作用域
    )
);
//login
$client = $_GET['client'];
$sns = Oauth::getInstance($client,$config);
//跳转到授权页面
header('Location: ' . $sns->getRequestCodeURL());


//callback
$client = $_GET['client'];
$code = $_GET['code'];

(empty($client) || empty($code)) && exit('参数错误');
$sns = Oauth::getInstance($client,$config);
$tokenArr = $sns->getAccessToken($code);

$openid = $tokenArr['openid'];
$token = $tokenArr['access_token'];
//获取当前登录用户信息
if ($openid) {
    $userinfo  = $sns->getUserInfo();
    exit( 'SUCCESS');
} else {
    exit('系统出错;请稍后再试！');
}
```


# License

MIT
