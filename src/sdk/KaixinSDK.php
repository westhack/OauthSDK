<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class KaixinSDK
 * @package OauthSDK\sdk
 */
class KaixinSDK extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'http://api.kaixin001.com/oauth2/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://api.kaixin001.com/oauth2/access_token';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://api.kaixin001.com/';
	
	/**
	 * 组装接口调用参数 并调用接口
	 * @param  string $api    开心网API
	 * @param  string $param  调用API的额外参数
	 * @param  string $method HTTP请求方法 默认为GET
	 * @param bool $multi
	 * @return json
	 * @throws \Exception
	 */
	public function call($api, $param = '', $method = 'GET', $multi = false){		
		/* 开心网调用公共参数 */
		$params = array(
			'access_token' => $this->token['access_token'],
		);
		
		$data = $this->http($this->url($api, '.json'), $this->param($params, $param), $method);
		return json_decode($data, true);
	}

	/**
	 * 解析access_token方法请求后的返回值
	 * @param $result 获取access_token的方法的返回值
	 * @param $extend
	 * @return mixed
	 * @throws \Exception
	 */
	protected function parseToken($result, $extend){
		$data = json_decode($result, true);
		if($data['access_token'] && $data['expires_in'] && $data['refresh_token']){
			$this->token    = $data;
			$data['openid'] = $this->openid();
			return $data;
		} else
			throw new \Exception("获取开心网ACCESS_TOKEN出错：{$data['error']}");
	}

	/**
	 * 获取当前授权应用的openid
	 * @return mixed
	 * @throws \Exception
	 */
	public function openid(){
		if(isset($this->token['openid']))
			return $this->token['openid'];
		
		$data = $this->call('users/me');
		if(!empty($data['uid']))
			return $data['uid'];
		else
			throw new \Exception('没有获取到开心网用户ID！');
	}
	
}