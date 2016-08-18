<?php
namespace Org\Util;
//极光推送
use JPush\Model as M;
use JPush\JPushClient;
use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;
require_once './vendor/autoload.php';
//极光推送end

/**
 * 极光push实现类
 */
class Jpush{
	private $appkey = '';
	private $secret = '';
	
	public function __construct($appkey,$secret){
		$this->appkey = $appkey;
		$this->secret = $secret;
	}
	
	public function push($jpushid,$title,$content,$array = array()){
		$client = new JPushClient($this->appkey,$this->secret);
		try {
			if ($jpushid == 'all'){
				$result1 = $client->push()
				->setPlatform(M\Platform('android', 'ios'))
				->setAudience(M\all)
				->setNotification(M\notification($content,
						M\android($content,$title, 1,$array),
						M\ios($content, "happy", "+1", true, $array, "Ios8 Category")
				))
				//->setMessage(M\message('Message Content', 'Message Title', 'Message Type', array("key1"=>"value1", "key2"=>"value2")))
			//	->printJSON()
				->send();
			}else {
				$result1 = $client->push()
				->setPlatform(M\Platform('android', 'ios'))
				->setAudience(M\Audience(M\registration_id($jpushid)))
				->setNotification(M\notification($content,
						M\android($content,$title, 1,$array),
						M\ios($content, "happy", "+1", true, $array, "Ios8 Category")
				))
				//->setMessage(M\message('Message Content', 'Message Title', 'Message Type', array("key1"=>"value1", "key2"=>"value2")))
			//	->printJSON()
				->send();
			}
			//     						 			echo 'Push Success.' . $br;
			//     						 			echo 'sendno : ' . $result1->sendno . $br;
			//     						 			echo 'msg_id : ' .$result1->msg_id . $br;
			//     						 			echo 'Response JSON : ' . $result1->json . $br;
		} catch (APIRequestException $e) {
			//json($e->code,'$e->message');
			//     							echo 'Push Fail.' . $br;
			//     				 			echo 'Http Code : ' . $e->httpCode . $br;
			//     				 			echo 'code : ' . $e->code . $br;
			//     				 			echo 'Error Message : ' . $e->message . $br;
			//     				 			echo 'Response JSON : ' . $e->json . $br;
			//     				 			echo 'rateLimitLimit : ' . $e->rateLimitLimit . $br;
			//     				 			echo 'rateLimitRemaining : ' . $e->rateLimitRemaining . $br;
			//     				 			echo 'rateLimitReset : ' . $e->rateLimitReset . $br;
		}catch (APIConnectionException $e) {
			//json($e->code,'$e->message');
// 		    echo 'Push Fail.' . $br;
// 		    echo 'message' . $e->getMessage() . $br;
		}
		
	}
}