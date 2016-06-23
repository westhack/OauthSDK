<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class DiandianSDK
 * @package OauthSDK\sdk
 */
class DiandianSDK extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://api.diandian.com/oauth/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://api.diandian.com/oauth/token';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://api.diandian.com/v1/';
	
	/**
	 * 组装接口调用参数 并调用接口
	 * @param  string $api    点点网API
	 * @param  string $param  调用API的额外参数
	 * @param  string $method HTTP请求方法 默认为GET
	 * @param bool $multi
	 * @return json
	 * @throws \Exception
	 */
	public function call($api, $param = '', $method = 'GET', $multi = false){		
		/* 点点网调用公共参数 */
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
		if($data['access_token'] && $data['expires_in'] && $data['token_type'] && $data['uid']){
			$data['openid'] = $data['uid'];
			unset($data['uid']);
			return $data;
		} else
			throw new \Exception("获取点点网ACCESS_TOKEN出错：{$data['error']}");
	}

	/**
	 * 获取当前授权应用的openid
	 * @return string
	 * @throws \Exception
	 */
	public function openid(){
		$data = $this->token;
		if(isset($data['openid']))
			return $data['openid'];
		else
			throw new \Exception('没有获取到点点网用户ID！');
	}
}