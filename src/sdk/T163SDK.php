<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class T163SDK
 * @package OauthSDK\sdk
 */
class T163SDK extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://api.t.163.com/oauth2/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://api.t.163.com/oauth2/access_token';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://api.t.163.com/';

	/**
	 * 组装接口调用参数 并调用接口
	 * @param  string $api    微博API
	 * @param  string $param  调用API的额外参数
	 * @param  string $method HTTP请求方法 默认为GET
	 * @param bool $multi
	 * @return json
	 * @throws \Exception
	 */
	public function call($api, $param = '', $method = 'GET', $multi = false){
		/* 新浪微博调用公共参数 */
		$params = array(
			'oauth_token' => $this->token['access_token'],
		);
		
		$data = $this->http($this->url($api, '.json'), $this->param($params, $param), $method);
		return json_decode($data, true);
	}

	/**
	 * 解析access_token方法请求后的返回值
	 * @param $result 获取access_token的方法的返回值
	 * @param $extend
	 * @return array
	 * @throws \Exception
	 */
	protected function parseToken($result, $extend){
		$data = json_decode($result, true);
		if($data['uid'] && $data['access_token'] && $data['expires_in'] && $data['refresh_token']){
			$data['openid'] = $data['uid'];
			unset($data['uid']);
			return $data;
		} else
			throw new \Exception("获取网易微博ACCESS_TOKEN出错：{$data['error']}");
	}

	/**
	 * 获取当前授权应用的openid
	 * @return mixed
	 * @throws \Exception
	 */
	public function openid(){
		if(isset($this->token['openid']))
			return $this->token['openid'];
		
		$data = $this->call('users/show');
		if(!empty($data['id']))
			return $data['id'];
		else
			throw new \Exception('没有获取到网易微博用户ID！');
	}
	
}