<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class BaiduSDK
 * @package OauthSDK\sdk
 */
class BaiduSDK extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://openapi.baidu.com/oauth/2.0/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://openapi.baidu.com/oauth/2.0/token';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://openapi.baidu.com/rest/2.0/';
	
	/**
	 * 组装接口调用参数 并调用接口
	 * @param  string $api    百度API
	 * @param  string $param  调用API的额外参数
	 * @param  string $method HTTP请求方法 默认为GET
	 * @param bool $multi
	 * @return json
	 * @throws \Exception
	 */
	public function call($api, $param = '', $method = 'GET', $multi = false){		
		/* 百度调用公共参数 */
		$params = array(
			'access_token' => $this->token['access_token'],
		);
		
		$data = $this->http($this->url($api), $this->param($params, $param), $method);
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
			throw new \Exception("获取百度ACCESS_TOKEN出错：{$data['error']}");
	}

	/**
	 * 获取当前授权应用的openid
	 * @return mixed
	 * @throws \Exception
	 */
	public function openid(){
		if(isset($this->token['openid']))
			return $this->token['openid'];
		
		$data = $this->call('passport/users/getLoggedInUser');
		if(!empty($data['uid']))
			return $data['uid'];
		else
			throw new \Exception('没有获取到百度用户ID！');
	}


    /**
     * 获取用户信息
     * @return array||bool
     * @throws \Exception
     */
    public function getUserInfo(){
        $response  = $this->call('passport/users/getInfo');
        if($response['userid']){
            $data['openid'] = $response['userid'];
            $data['username'] = $response['username'];
            $data['avatar'] = $response['portrait'];
            $data['sex'] = $response['sex']==1?1:2;
            return $data;
        }else{
            return false;
        }
    }

	
}