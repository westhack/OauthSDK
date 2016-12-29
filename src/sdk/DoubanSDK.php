<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class DoubanSDK
 * @package OauthSDK\sdk
 */
class DoubanSDK extends Oauth{
	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://www.douban.com/service/auth2/auth';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://www.douban.com/service/auth2/token';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://api.douban.com/v2/';

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
		/* 豆瓣调用公共参数 */
		$params = array();
		$header = array("Authorization: Bearer {$this->token['access_token']}");
		$data   = $this->http($this->url($api), $this->param($params, $param), $method, $header);
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
		if($data['access_token'] && $data['expires_in'] && $data['refresh_token'] && $data['douban_user_id']){
			$data['openid'] = $data['douban_user_id'];
			unset($data['douban_user_id']);
			return $data;
		} else
			throw new \Exception("获取豆瓣ACCESS_TOKEN出错：{$data['msg']}");
	}

	/**
	 * 获取当前授权应用的openid
	 * @return string
	 * @throws \Exception
	 */
	public function openid(){
		$data = $this->token;
		if(isset($data['douban_user_id']))
			return $data['douban_user_id'];
		else
			throw new \Exception('没有获取到豆瓣用户ID！');
	}

    /**
     * 获取用户信息
     * @return array||bool
     * @throws \Exception
     */
    public function getUserInfo(){
        $response  = $this->call('user/~me"');
        if($response['uid ']){
            $data['openid'] = $response['uid'];
            $data['username'] = $response['name'];
            $data['avatar'] = $response['large_avatar']!=''?$response['large_avatar']:$response['avatar'];
            $data['sex'] = $response['sex']==1?1:2;
            return $data;
        }else{
            return false;
        }
    }
	
}