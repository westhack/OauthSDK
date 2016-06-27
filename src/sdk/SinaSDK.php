<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class SinaSDK
 * @package OauthSDK\sdk
 */
class SinaSDK extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://api.weibo.com/oauth2/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://api.weibo.com/oauth2/access_token';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://api.weibo.com/2/';
	
	/**
	 * 组装接口调用参数 并调用接口
	 * @param  string $api    微博API
	 * @param  string $param  调用API的额外参数
	 * @param  string $method HTTP请求方法 默认为GET
	 * @param bool $multi
	 * @return mixed
	 * @throws \Exception
	 */
	public function call($api, $param = '', $method = 'GET', $multi = false){		
		/* 新浪微博调用公共参数 */
		$params = array(
			'access_token' => $this->token['access_token'],
		);
		
		$vars = $this->param($params, $param);
		$data = $this->http($this->url($api, '.json'), $vars, $method, array(), $multi);
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
		if($data['access_token'] && $data['expires_in'] && $data['remind_in'] && $data['uid']){
			$data['openid'] = $data['uid'];
			unset($data['uid']);
			return $data;
		} else
			throw new \Exception("获取新浪微博ACCESS_TOKEN出错：{$data['error']}");
	}

	/**
	 * 获取当前授权应用的openid
	 * @return mixed
	 * @throws \Exception
	 */
	public function openid(){
		$data = $this->token;
		if(isset($data['openid']))
			return $data['openid'];
		else
			throw new \Exception('没有获取到新浪微博用户ID！');
	}


	/**
	 * 获取用户信息
	 * @return array||bool
	 * @throws \Exception
	 */
	public function getUserInfo(){
		$params['uid'] = $this->openid();
		$response = $this->call('users/show',$params);
		if ($response['ret'] == 0) {
			$userInfo['openid'] = $this->openid();
			$userInfo['username'] = $response['screen_name'];
			$userInfo['avatar'] = $response['profile_image_url'];
			$userInfo['sex'] = $response['gender']=='m'?1:2;
			return $userInfo;
		} else {
			return false;
		}
	}
	
}