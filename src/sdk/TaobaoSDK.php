<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

class TaobaoSDK extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://oauth.taobao.com/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://oauth.taobao.com/token';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://eco.taobao.com/router/rest';
	
	/**
	 * 组装接口调用参数 并调用接口
	 * @param  string $api    接口API
	 * @param  string $param  调用API的额外参数
	 * @param  string $method HTTP请求方法 默认为GET
	 * @param bool $multi
	 * @return json
	 * @throws \Exception
	 */
	public function call($api, $param = '', $method = 'GET', $multi = false){		
		/* 淘宝网调用公共参数 */
		$params = array(
			'method'       => $api,
			'access_token' => $this->token['access_token'],
			'format'       => 'json',
			'v'            => '2.0',
		);
		$data = $this->http($this->url(''), $this->param($params, $param), $method);
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
		if($data['access_token'] && $data['expires_in'] && $data['taobao_user_id']){
			$data['openid'] = $data['taobao_user_id'];
			unset($data['taobao_user_id']);
			return $data;
		} else
			throw new \Exception("获取淘宝网ACCESS_TOKEN出错：{$data['error']}");
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
			throw new \Exception('没有获取到淘宝网用户ID！');
	}

    /**
     * 获取用户信息
     * @return array||bool
     * @throws \Exception
     */
    public function getUserInfo(){
        $params['fields'] = 'nick,avatar,sex';
        $response  = $this->call('taobao.user.buyer.get',$params);
        if($response['openid']){
            $data['openid'] = $this->openid();
            $data['username'] = $response['nick'];
            $data['avatar'] = $response['avatar'];
            $data['sex'] = $response['sex']=='m'?1:2;
            return $data;
        }else{
            return false;
        }
    }
	
}