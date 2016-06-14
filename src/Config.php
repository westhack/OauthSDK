<?php
namespace OauthSDK;

//$SITE_URL = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME']."/";
//define('URL_CALLBACK', "" . $SITE_URL . "Index/callback?type=");
class Config
{
	public static $configs =  array(

	//腾讯QQ登录配置
	'QQ' => array(
		'APP_KEY' => '101205983', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '9380197af5efd5c47bc561323047ccec', //应用注册成功后分配的KEY
		'CALLBACK' =>  'qq',
	),
	//新浪微博配置
	'SINA' => array(
		'APP_KEY' => '120967331', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '8aa15f65593eaf9e787baec45a801296', //应用注册成功后分配的KEY
		'CALLBACK' => 'sina',
	),
	//人人网配置
	'RENREN' => array(
		'APP_KEY' => '', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '', //应用注册成功后分配的KEY
		'CALLBACK' =>  'renren',
	)
);
	public static function get($type) {
		if (isset(self::$configs[$type])) {
			return self::$configs[$type];
		}else {
			return false;
		}
	}
}

?>