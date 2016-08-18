<?php
namespace Org\Util;
require_once "./ThinkPHP/Library/Vendor/Alipay/lib/alipay_notify.class.php";
require_once "./ThinkPHP/Library/Vendor/Alipay/lib/alipay_submit.class.php";

class Alipay {
	
	public $alipay_config;
	
	public function __construct(){
		require_once "./ThinkPHP/Library/Vendor/Alipay/alipay.config.php";
		$this->alipay_config=$alipay_config;
	}
	public function alipayapi(){
		
	}
	
	//异步回调
	public function notify(){
		//$alipayNotify = new \AlipayNotify($this->alipay_config);
		//$verify_result = $alipayNotify->verifyNotify();
		$post = I('post.');
		$config = $this->alipay_config;
		$order = M('order');
		if ($post['seller_id'] != $config['partner']){
			return 0;
		}
		$res = $order->where("orderid = '{$post['out_trade_no']}'")->find();
		if (!$post['total_fee']){
			return 0;
		}
		if ($res['price'] != $post['total_fee']){
			return 0;
		}
		return 1;
// 		if ($verify_result){
// 			return 1;
// 		}else {
// 			return 0;
// 		}
	}
	
}
