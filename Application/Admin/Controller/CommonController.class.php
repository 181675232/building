<?php
namespace Admin\Controller;
use Think\Controller;

class CommonController extends Controller {
	//Jpush key
	protected $title = 'jollyShop';
	protected $app_key='bdf045e466d05c077ec2dd74';
	protected $master_secret = 'e412050beb8b2dd8036fc9ed';
	
	//oss
	protected $url = 'http://jolly.img-cn-beijing.aliyuncs.com'; //外网 
	protected $url_nei = 'oss-cn-beijing-internal.aliyuncs.com';
	protected $AccessKeyID='0zHXT3orDFaVxFWn';
	protected $AccessKeySecret = 'yjBwj2Om9l4Bu8qSu7yx3XOAcSxFhv';
	
	//融云
	protected $appKey = 'pwe86ga5ede86';
	protected $appSecret = '96EvBT4wxIvCL';
	public function _initialize(){
		header("Content-Type:text/html; charset=utf-8");	
		if (!$_SESSION['userid']){
			$this->redirect('/Admin/Public/admin');
			exit;
		}
		$rbac=new \Org\Util\Rbac();
		//检测是否登录，没有登录就打回设置的网关
		$rbac::checkLogin();
		//检测是否有权限没有权限就做相应的处理

		if(!$rbac::AccessDecision()){
			if (ACTION_NAME == 'delete'){
				echo 1;
				exit;
			}else {
				alertBack('您没有此操作权限！');
			}			
		}
	}
}