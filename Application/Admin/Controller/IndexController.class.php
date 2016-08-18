<?php
namespace Admin\Controller;

class IndexController extends CommonController {
	
	public function index(){
		$table = M('admin');
		$id = I('session.userid');
		$data = $table->field('t_admin.name,t_role.name as title')
		->join('left join t_role_user on t_role_user.user_id = t_admin.id')
		->join('left join t_role on t_role.id = t_role_user.role_id')
		->where("t_admin.id = $id")->find();		
		if ($data['name'] == 'admin'){
			$res['name'] = '超级管理员';
		}else {
			$res['name'] = $data['title'];
		}
		$this->assign('usergroup',$res);
		$this->display();
	}
	public function center(){
		
		$admin = M('Admin');
		$base = M('Base');
		$data['admin'] = $admin->find(I('session.userid'));
		$base = $base->find(1);
		$data['info'] = array(
				'网站名称'=>$base['title'],
				'操作系统'=>PHP_OS,
				'运行环境'=>$_SERVER["SERVER_SOFTWARE"],
				'PHP运行方式'=>php_sapi_name(),
				'上传附件限制'=>ini_get('upload_max_filesize'),
				'执行时间限制'=>ini_get('max_execution_time').'秒',
				'服务器时间'=>date("Y年n月j日 H:i:s"),
				'北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
				'ThinkPHP版本'=>THINK_VERSION.' [ <a href="http://thinkphp.cn" target="_blank">查看最新版本</a> ]',
				'服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
				'剩余空间'=>round((disk_free_space(".")/(1024*1024)),2).'M',
				'端口号'=>$_SERVER["SERVER_PORT"],
		);		
		$this->assign($data);
		$this->display();
	}

	
} 