<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class RenrenSDK
 * @package OauthSDK\sdk
 */
class RenrenSDK extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://graph.renren.com/oauth/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://graph.renren.com/oauth/token';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'http://api.renren.com/restserver.do';

	/**
	 * 组装接口调用参数 并调用接口
	 * @param  string $api    微博API
	 * @param  string $param  调用API的额外参数
	 * @param  string $method HTTP请求方法 默认为GET
	 * @param bool $multi
	 * @return json
	 * @throws \Exception
	 */
	public function call($api, $param = '', $method = 'POST', $multi = false){
		/* 人人网调用公共参数 */
		$params = array(
			'method'       => $api,
			'access_token' => $this->token['access_token'],
			'v'            => '1.0',
			'format'       => 'json',
		);
		
		$data = $this->http($this->url(''), $this->param($params, $param), $method);
		return json_decode($data, true);
	}
	
	/**
	 * 合并默认参数和额外参数
	 * @param array $params  默认参数
	 * @param array/string $param 额外参数
	 * @return array:
	 */
	protected function param($params, $param){
		$params = parent::param($params, $param);
		
		/* 签名 */
		ksort($params);
		$param = array();
		foreach ($params as $key => $value){
			$param[] = "{$key}={$value}";
		}
		$sign = implode('', $param).$this->AppSecret;
		$params['sig'] = md5($sign);

		return $params;
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
		if($data['access_token'] && $data['expires_in'] && $data['refresh_token'] && $data['user']['id']){
			$data['openid'] = $data['user']['id'];
			unset($data['user']);
			return $data;
		} else
			throw new \Exception("获取人人网ACCESS_TOKEN出错：{$data['error_description']}");
	}

	/**
	 * 获取当前授权应用的openid
	 * @return string
	 * @throws \Exception
	 */
	public function openid(){
		$data = $this->token;
		if(!empty($data['openid']))
			return $data['openid'];
		else
			throw new \Exception('没有获取到人人网用户ID！');
	}
}