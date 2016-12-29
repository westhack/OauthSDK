<?php
namespace OauthSDK;
/**
 * Class Oauth
 * @package OauthSDK
 */
abstract class Oauth{

	protected static $instance ;
	/**
	 * 配置
	 * @var array
	 */
	protected $config = array();
	/**
	 * oauth版本
	 * @var string
	 */
	protected $version = '2.0';
	
	/**
	 * 申请应用时分配的app_key
	 * @var string
	 */
	protected $appKey = '';
	
	/**
	 * 申请应用时分配的 app_secret
	 * @var string
	 */
	protected $appSecret = '';
	
	/**
	 * 授权类型 response_type 目前只能为code
	 * @var string
	 */
	protected $responseType = 'code';
	
	/**
	 * grant_type 目前只能为 authorization_code
	 * @var string 
	 */
	protected $grantType = 'authorization_code';
	
	/**
	 * 回调页面URL  可以通过配置文件配置
	 * @var string
	 */
	protected $callback = '';
	
	/**
	 * 获取request_code的额外参数 URL查询字符串格式
	 * @var srting
	 */
	protected $authorize = '';
	
	/**
	 * 获取request_code请求的URL
	 * @var string
	 */
	protected $getRequestCodeURL = '';
	
	/**
	 * 获取access_token请求的URL
	 * @var string
	 */
	protected $getAccessTokenURL = '';

	/**
	 * API根路径
	 * @var string
	 */
	protected $apiBase = '';
	
	/**
	 * 授权后获取到的TOKEN信息
	 * @var array
	 */
	protected $token = null;

	/**
	 * 调用接口类型
	 * @var string
	 */
	private $type = '';

	/**
	 * 构造方法，配置应用信息
	 * Oauth constructor.
	 * @param null $type
	 * @param array $config
	 * @param null $token
	 */
	public function __construct($type=null, $config=array(),$token = null){
		//设置SDK类型
		$this->type = strtoupper($type);
		//获取应用配置
		$this->config = $config[$this->type];
		if(empty($this->config['APP_KEY']) || empty($this->config['APP_SECRET'])){
			throw new \Exception('请配置您申请的APP_KEY和APP_SECRET');
		} else {
			$this->appKey    = $this->config['APP_KEY'];
			$this->appSecret = $this->config['APP_SECRET'];
			$this->callback = $this->config['CALLBACK'];
			$this->token     = $token; //设置获取到的TOKEN
		}
	}

	/**
	 * 取得Oauth实例
	 * @param $type
	 * @param array $config
	 * @param null $token
	 * @return mixed
	 * @throws \Exception
	 */
    public static function getInstance($type, $config =array(),$token = null) {
    	$name = '\\OauthSDK\\sdk\\'.ucfirst(strtolower($type)) . 'SDK';
		if(self::$instance[$name]){
			return self::$instance[$name];
		}
    	if (class_exists($name)) {
    		return self::$instance[$name] = new $name($type, $config,$token);
    	} else {
			throw new \Exception('无法找到类:' . $name);
    	}
    }

	/**
	 * 初始化配置
	 */
	private function config(){
		$config = $this->config;
		if(!empty($config['AUTHORIZE']))
			$this->authorize = $config['AUTHORIZE'];
		if(!empty($config['CALLBACK']))
			$this->callback = $config['CALLBACK'];
		else
			throw new \Exception('请配置回调页面地址');
	}
	
	/**
	 * 请求code 
	 */
	public function getRequestCodeURL(){
		$this->config();
		//Oauth 标准参数
		$params = array(
			'client_id'     => $this->appKey,
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
		return $this->getRequestCodeURL . '?' . http_build_query($params);
	}

	/**
	 * 获取access_token
	 * @param $code 上一步请求到的code
	 * @param null $extend
	 * @return array|null
	 * @throws \Exception
	 */
	public function getAccessToken($code, $extend = null){
		$this->config();
		$params = array(
				'client_id'     => $this->appKey,
				'client_secret' => $this->appSecret,
				'grant_type'    => $this->grantType,
				'code'          => $code,
				'redirect_uri'  => $this->callback,
		);

		$data = $this->http($this->getAccessTokenURL, $params, 'POST');
		$this->token = $this->parseToken($data, $extend);
		return $this->token;
	}

	/**
	 * 合并默认参数和额外参数
	 * @param array $params  默认参数
	 * @param array/string $param 额外参数
	 * @return array:
	 */
	protected function param($params, $param){
		if(is_string($param))
			parse_str($param, $param);
		return array_merge($params, $param);
	}

	/**
	 * 获取指定API请求的URL
	 * @param  string $api API名称
	 * @param  string $fix api后缀
	 * @return string      请求的完整URL
	 */
	protected function url($api, $fix = ''){
		return $this->apiBase . $api . $fix;
	}
	

	/**
	 * HTTP请求
	 * @param $url 请求URL
	 * @param $params 请求参数
	 * @param string $method 请求方法GET/POST
	 * @param array $header 请求头部
	 * @param bool $multi
	 * @return array  $data 响应数据
	 * @throws \Exception
	 */
	protected function http($url, $params, $method = 'GET', $header = array(), $multi = false){
		$opts = array(
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER     => $header
		);

		/* 根据请求类型设置特定参数 */
		switch(strtoupper($method)){
			case 'GET':
				$opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
				break;
			case 'POST':
				//判断是否传输文件
				$params = $multi ? $params : http_build_query($params);
				$opts[CURLOPT_URL] = $url;
				$opts[CURLOPT_POST] = 1;
				$opts[CURLOPT_POSTFIELDS] = $params;
				break;
			default:
				throw new \Exception('不支持的请求方式！');
		}
		
		/* 初始化并执行curl请求 */
		$ch = curl_init();
		curl_setopt_array($ch, $opts);
		$data  = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if($error) throw new \Exception('请求发生错误：' . $error);
		return  $data;
	}
	/**
	 * 获取客户端IP地址
	 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
	 * @return mixed
	 */
	public function getUserIp($type = 0) {
		$type       =  $type ? 1 : 0;
		static $ip  =   NULL;
		if ($ip !== NULL) return $ip[$type];
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos    =   array_search('unknown',$arr);
			if(false !== $pos) unset($arr[$pos]);
			$ip     =   trim($arr[0]);
		}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip     =   $_SERVER['HTTP_CLIENT_IP'];
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip     =   $_SERVER['REMOTE_ADDR'];
		}
		// IP地址合法验证
		$long = sprintf("%u",ip2long($ip));
		$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}
	/**
	 * 抽象方法，在SNSSDK中实现
	 * 组装接口调用参数 并调用接口
	 * @param $api
	 * @param string $param
	 * @param string $method
	 * @param bool $multi
	 * @return mixed
	 */
	abstract protected function call($api, $param = '', $method = 'GET', $multi = false);
	
	/**
	 * 抽象方法，在SNSSDK中实现
	 * 解析access_token方法请求后的返回值
	 * @param $result
	 * @param $extend
	 * @return mixed
	 */
	abstract protected function parseToken($result, $extend);
	
	/**
	 * 抽象方法，在SNSSDK中实现
	 * 获取当前授权用户的SNS标识
	 */
	abstract public function openid();

    /**
     * 抽象方法，在SNSSDK中实现
     * 获取用户信息
     */
	abstract public function getUserInfo();
}