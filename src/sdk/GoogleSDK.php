<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class GoogleSDK
 * @package OauthSDK\sdk
 */
class GoogleSDK extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://accounts.google.com/o/oauth2/auth';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://accounts.google.com/o/oauth2/token';

	/**
	 * 获取request_code的额外参数 URL查询字符串格式
	 * @var srting
	 */
	protected $authorize = 'scope=https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://www.googleapis.com/oauth2/v1/';

	/**
	 * 组装接口调用参数 并调用接口
	 * @param  string $api    微博API
	 * @param  string $param  调用API的额外参数
	 * @param  string $method HTTP请求方法 默认为GETd
	 * @param bool $multi
	 * @return json
	 * @throws \Exception
	 */
	public function call($api, $param = '', $method = 'GET', $multi = false){
		/*  Google 调用公共参数 */
		$params = array();
		$header = array("Authorization: Bearer {$this->token['access_token']}");

		$data = $this->http($this->url($api), $this->param($params, $param), $method, $header);
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
		if($data['access_token'] && $data['token_type'] && $data['expires_in']){
			$this->token = $data;
			$data['openid'] = $this->openid();
			return $data;
		} else
			throw new \Exception("获取 Google ACCESS_TOKEN出错：未知错误");
	}
	
	/**
	 * 获取当前授权应用的openid
	 * @return mixed
	 * @throws \Exception
	 */
	public function openid(){
		if(isset($this->token['openid']))
			return $this->token['openid'];
		
		$data = $this->call('userinfo');
		if(!empty($data['id']))
			return $data['id'];
		else
			throw new \Exception('没有获取到 Google 用户ID！');
	}

    /**
     * 获取当前授权应用的openid
     * @return mixed
     * @throws \Exception
     */
    public function getUserInfo(){
        $response = $this->call('userinfo');
        if($response['openid']){
            $data['openid'] = $response['id'];
            $data['username'] = $response['name'];
            $data['avatar'] = $response['picture'];
            $data['sex'] = $response['gender']=='male'?1:2;
            return $data;
        }else{
            return false;
        }
    }
}