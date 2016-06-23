<?php
include_once 'config.php';
$type = $_GET['type'];
$sns = Oauth::getInstance($type,$config);
//跳转到授权页面
header('Location: ' . $sns->getRequestCodeURL());