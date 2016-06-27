<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class QqSDK
 * @package OauthSDK\sdk
 */
class QqSDK extends Oauth{

	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://graph.qq.com/oauth2.0/authorize';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://graph.qq.com/oauth2.0/token';

	/**
	 * 获取request_code的额外参数,可在配置中修改 URL查询字符串格式
	 * @var srting
	 */
	protected $authorize = 'scope=get_user_info,add_share';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://graph.qq.com/';

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
		/* 腾讯QQ调用公共参数 */
		$params = array(
			'oauth_consumer_key' => $this->appKey,
			'access_token'       => $this->token['access_token'],
			'openid'             => $this->openid(),
			'format'             => 'json'
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
		parse_str($result, $data);
		if($data['access_token'] && $data['expires_in']){
			$this->token = $data;
			$data['openid'] = $this->openid();
			$this->openid = $data['openid'];
			return $data;
		} else
			throw new \Exception("获取腾讯QQ ACCESS_TOKEN 出错：{$result}");
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
		elseif($data['access_token']){
			$data = $this->http($this->url('oauth2.0/me'), array('access_token' => $data['access_token']));
			$data = json_decode(trim(substr($data, 9), " );\n"), true);
			if(isset($data['openid']))
				return $data['openid'];
			else
				throw new \Exception("获取用户openid出错：{$data['error_description']}");
		} else {
			throw new \Exception('没有获取到openid！');
		}
	}

	public function getUserInfo(){

		$data = $this->call('user/get_user_info');
		// 将获取到的信息进行整理
		if ($data['ret'] == 0) {
			$userInfo['openid'] = $this->openid();
			$userInfo['username'] = $data['nickname'];
			$userInfo['avatar'] =  $data['figureurl_qq_2']!=''?$data['figureurl_qq_2']:$data['figureurl_qq_1'];
			$userInfo['sex'] = $data['gender']=='男'?1:2;
			// 此处的$userInfo就是需要的用户信息
			return $userInfo;
		} else {
			return false;
		}
	}
}