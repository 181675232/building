<?php
namespace Api\Controller;
use Think\Controller;
 
class CommonController extends Controller { 
	protected  function _initialize(){
		//utf-8编码
		header("Content-Type:application/json; charset=utf-8");
		// 指定允许其他域名访问
		header('Access-Control-Allow-Origin:*');
		// 响应类型
		//header('Access-Control-Allow-Methods:POST');
		header('Access-Control-Allow-Methods:GET, POST, OPTIONS');
		// 响应头设置
		header('Access-Control-Allow-Headers:x-requested-with,content-type,JS-App-Key,JS-Nonce,JS-Timestamp,JS-Signature');
		//接口签名认证
		if (ACTION_NAME != 'alipay_notify_url'){
		// 		$header = getallheaders();
		// 		if (($header['JS-Timestamp']+600000)>(time().'000')){
		// 			if ((sha1($header['JS-App-Key'].$header['JS-Nonce'].$header['JS-Timestamp'])) != ($header['JS-Signature'])){
		// 				json('400','签名不合法'); 
		// 			}
		// 		}else {
		// 			json('400','签名失效');
		// 		}
		}
	}
}