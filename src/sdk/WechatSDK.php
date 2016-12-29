<?php
namespace OauthSDK\sdk;

use OauthSDK\Oauth;

/**
 * Class WechatSDK
 * @package OauthSDK\sdk
 */
class WechatSDK extends Oauth{


	/**
	 * 获取requestCode的api接口
	 * @var string
	 */
	protected $getRequestCodeURL = 'https://open.weixin.qq.com/connect/qrconnect';

	/**
	 * 获取access_token的api接口
	 * @var string
	 */
	protected $getAccessTokenURL = 'https://api.weixin.qq.com/sns/oauth2/access_token';
	
	/**
	 * 获取request_code的额外参数,可在配置中修改 URL查询字符串格式
	 * @var srting
	 */
	protected $authorize = 'scope=snsapi_login';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = 'https://api.weixin.qq.com/';



    /**
     * 是否微信打开
     * @return bool
     */
    private function isWechat(){
        if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
        }
        return false;
    }

    /**
     * 获取requestCode的api接口
     * @return string
     */
    public function _getRequestCodeURL(){
        if($this->isWechat()){
            $this->getRequestCodeURL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
        }else{
            $this->getRequestCodeURL = 'https://open.weixin.qq.com/connect/qrconnect';
        }
        return $this->getRequestCodeURL;
    }
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
		/* 微信调用公共参数 */
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
	 * 请求code
	 */
	public function getRequestCodeURL(){
		//Oauth 标准参数
		$params = array(
			'appid'     => $this->appKey,
			'redirect_uri'  => $this->callback,
			'response_type' => $this->responseType,
		);

		//获取额外参数
		if($this->authorize){
			parse_str($this->authorize, $_param);
			if(is_array($_param)){
				$params = array_merge($params, $_param);
			} else {
				throw new \Exception('AUTHORIZE配置不正确！');
			}
		}
		return $this->_getRequestCodeURL() . '?' . http_build_query($params);
	}

	/**
	 * 获取access_token
	 * @param $code 上一步请求到的code
	 * @param null $extend
	 * @return array|null
	 * @throws \Exception
	 */
	public function getAccessToken($code, $extend = null){
		$params = array(
			'appid'     => $this->appKey,
			'secret' => $this->appSecret,
			'grant_type'    => $this->grantType,
			'code'          => $code,
			'redirect_uri'  => $this->callback,
		);

		$data = $this->http($this->getAccessTokenURL, $params, 'POST');
		$this->token = $this->parseToken($data, $extend);
		return $this->token;
	}

	/**
	 * 解析access_token方法请求后的返回值
	 * @param $result 获取access_token的方法的返回值
	 * @param $extend
	 * @return mixed
	 * @throws \Exception
	 */
	protected function parseToken($result, $extend){
		$data = json_decode($result,true);
		if($data['access_token'] && $data['expires_in'] && $data['refresh_token']&&$data['unionid']){
			$this->token    = $data;
			$data['openid'] = $this->openid();
			return $data;
		} else
			throw new \Exception("获取微信 ACCESS_TOKEN 出错：{$result}");
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
			throw new \Exception("获取微信用户openid出错！");
	}

	/**
	 * 获取用户信息
	 * @return array||bool
	 * @throws \Exception
	 */
	public function getUserInfo(){
		$params['lang'] = 'zh_CN';
		$response  = $this->call('sns/userinfo',$params);
		if($response['openid']){
			$data['openid'] = $response['openid'];
			$data['username'] = $response['nickname'];
			$data['avatar'] = $response['headimgurl'];
			$data['sex'] = $response['sex'];
			return $data;
		}else{
			return false;
		}
	}
}