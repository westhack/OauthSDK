<?php
include_once 'config.php';

$type = $_GET['type'];
$code = $_GET['code'];

(empty($type) || empty($code)) && exit('参数错误');
$sns = Oauth::getInstance($type,$config);
$tokenArr = $sns->getAccessToken($code, $extend);

$openid = $tokenArr['openid'];
$token = $tokenArr['access_token'];
//获取当前登录用户信息
if ($openid) {
    $userinfo = $userInfo = $sns->getUserInfo();
    exit( 'SUCCESS');
} else {
    exit('系统出错;请稍后再试！');
}