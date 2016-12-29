<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class GithubSDK
 * @package OauthSDK\sdk
 */
class GithubSDK extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://github.com/login/oauth/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://github.com/login/oauth/access_token';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://api.github.com/';

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
		/* Github 调用公共参数 */
		$params = array();
		$header = array("Authorization: bearer {$this->token['access_token']}");

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
		parse_str($result, $data);
		if($data['access_token'] && $data['token_type']){
			$this->token = $data;
			$data['openid'] = $this->openid();
			return $data;
		} else
			throw new \Exception("获取 Github ACCESS_TOKEN出错：未知错误");
	}

	/**
	 * 获取当前授权应用的openid
	 * @return mixed
	 * @throws \Exception
	 */
	public function openid(){
		if(isset($this->token['openid']))
			return $this->token['openid'];
		
		$data = $this->call('user');
		if(!empty($data['id']))
			return $data['id'];
		else
			throw new \Exception('没有获取到 Github 用户ID！');
	}

    /**
     * 获取用户信息
     * @return array||bool
     * @throws \Exception
     */
    public function getUserInfo(){
        $response  = $this->call('user');
        if($response['id']){
            $data['openid'] = $response['userid'];
            $data['username'] = $response['name'];
            $data['avatar'] = $response['avatar_url'];
            $data['sex'] = $response['sex']==1?1:2;
            return $data;
        }else{
            return false;
        }
    }



}