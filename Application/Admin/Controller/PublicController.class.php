<?php
namespace Admin\Controller;
use Api\Controller\IndexController;
use Think\Controller;


class PublicController extends Controller {
//	验证码
	public function scode(){			
		$Verify = new \Think\Verify();
		$Verify->entry();
	}
//	登陆
	public function admin(){
		if (IS_POST){
			$admin = M("Admin");
			if ($admin->create()){
				$data['name'] = $_POST['name'];
				$data = $admin->field('password,name')->where($data)->find();
				if (!$data){
					$this->error('用户名不存在！');
				}else {
					if (md5($_POST['password']) != $data['password']){
						$this->error('密码输入错误！');
					}else {
						$user = $admin->where($data)->find();
						if ($user['state'] !=1 && $user['name'] != C('RBAC_SUPERADMIN')){
							$this->error('您的账号已被冻结！');
						}
						session(C('USER_AUTH_KEY'),$user['id']);
						session('username',$user['name']);
						session('userip',$user['ip']);
						session('usertime',$user['addtime']);
						session('level',$user['level']);
						if ($user['level'] != 0){
							if ($user['provinceid']){
								session('provinceid',$user['provinceid']);
							}
							if ($user['cityid']){
								session('cityid',$user['cityid']);
							}
							if ($user['areaid']){
								session('areaid',$user['areaid']);
							}
						}
						
						if($_SESSION['username']==C('RBAC_SUPERADMIN')) {
							$_SESSION[C('ADMIN_AUTH_KEY')] =	true;
						}
						$rbac = new \Org\Util\Rbac();
						$rbac::saveAccessList();			
						$data1['id'] = $user['id'];
						$data1['ip'] = $_SERVER['REMOTE_ADDR'];
						$data1['addtime'] = time();
						$admin->save($data1);
						$this->redirect('/Admin');
					
					}
				}
			}else {
				$this->error($admin->getError());
			}
		}
		$this->display('Index_login');
	}
//	登陆ajax
	public function login(){
		if (!check_code($_POST['code'])){
			echo '验证码输入有误！';
			exit;
		}
		$admin = M('Admin');
		$data['name'] = $_POST['name'];
		$data = $admin->field('password')->where($data)->find();
		if (!$data){
			echo '用户名不存在！';
			exit;
		}else {
			if (md5($_POST['password']) != $data['password']){
				echo '密码输入错误！';
				exit;
			}else {
				echo 1;
				exit;
			}
		}
	}
//	退出登录
	public function logout(){
		session(null); 
		$this->redirect('/Admin');

	}
//	上传图片
	function upload(){
		header("Content-Type:text/html; charset=utf-8");
		$upload = new \Think\Upload();// 实例化上传类
		$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->rootPath  =     './Public/upfile/'; // 设置附件上传根目录
		$upload->savePath  =     ''; // 设置附件上传（子）目录
		$upload->saveName = time().'_'.mt_rand(); //文件名
			
		// 上传文件
		$info   =   $upload->upload();
		if(!$info) {// 上传错误提示错误信息
			echo '上传失败！';
		}else{// 上传成功
			
			$data['status'] = 1;
			$data['msg'] = '文件上传成功！';
			$data['name'] = $info['Filedata']['name'];
			$data['path'] = '/Public/upfile/'.$info['Filedata']['savepath'].$info['Filedata']['savename'];
			$data['size'] = $info['Filedata']['size'];
			$data['ext'] = $info['Filedata']['ext'];
			if (!empty($_GET['IsThumbnail'])){
				$data['thumb'] = '/Public/upfile/'.$info['Filedata']['savepath'].'thumb_'.$info['Filedata']['savename'];
				$image = new \Think\Image();
				$image->open('.'.$data['path']);
				// 生成一个居中裁剪为150*150的缩略图并保存为thumb.jpg
				$image->thumb(150, 150,\Think\Image::IMAGE_THUMB_CENTER)->save('.'.$data['thumb']);
			}
			echo json_encode($data);
			exit;
		}
	}

    //	上传excel
    function upload_excel(){
        header("Content-Type:text/html; charset=utf-8");
        $upload = new \Think\Upload();// 实例化上传类
        $upload->exts      =     array('xls', 'xlsx');// 设置附件上传类型
        $upload->rootPath  =     './Public/upfile/excel/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        $upload->saveName = time().'_'.mt_rand(); //文件名

        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            echo '上传失败！';
        }else{// 上传成功
            $data['status'] = 1;
            $data['msg'] = '文件上传成功！';
            $data['name'] = $info['Filedata']['name'];
            $data['path'] = '/Public/upfile/excel/'.$info['Filedata']['savepath'].$info['Filedata']['savename'];
            $data['size'] = $info['Filedata']['size'];
            $data['ext'] = $info['Filedata']['ext'];
            echo json_encode($data);
            exit;
        }
    }

    //导入Excel
    public function excelimport(){
        if (I('post.')){
            $table = M('task');

            if (file_exists('.'.I('post.file'))){
                $excel = new \Org\Util\Excel();
                $excel->excelimport('.'.I('post.file'),I('post.title'));
                exit;
            }else{
                alertBack('文件不存在！');
            }
        }
        $this->display();
    }






}