<?php
header("Content-type: text/html; charset=utf-8");
$config =  array(
    //腾讯QQ登录配置
    'QQ' => array(
        'APP_KEY' => '123456', //应用注册成功后分配的 APP ID
        'APP_SECRET' => '9cc9ac2fb17d010104d8a58dbebb4d3a', //应用注册成功后分配的KEY
        'CALLBACK' =>  'http://localhost/callback.php?type=qq',//回调URL
    ),
    //新浪微博配置
    'SINA' => array(
        'APP_KEY' => '123456', //应用注册成功后分配的 APP ID
        'APP_SECRET' => '9cc9ac2fb17d010104d8a58dbebb4d3a', //应用注册成功后分配的KEY
        'CALLBACK' => 'http://localhost/callback.php?type=sina',//回调URL
    ),
    //腾讯微信配置
    'WECHAT' => array(
        'APP_KEY' => '123456', //应用注册成功后分配的 APP ID
        'APP_SECRET' => '9cc9ac2fb17d010104d8a58dbebb4d3a', //应用注册成功后分配的KEY
        'CALLBACK' => 'http://localhost/callback.php?type=wechat',//回调URL
    )
);
