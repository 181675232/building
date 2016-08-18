<?php
namespace Admin\Controller;

class BannerController extends CommonController {
	
	public function index(){
		$banner = M('banner');
		$data = $banner->select();
		$this->assign('data_img',$data);
		$this->display();
	}
	
	public function edit(){		
		$banner = M('banner');
		if (IS_POST){		
			$data = $_POST;
			$banner->where('1')->delete();		
			for($i=0;$i<count($data['user_simg']);$i++){
				$data_img['simg'] = $data['user_simg'][$i];
				$data_img['title'] = $data['user_desc'][$i];
				$data_img['addtime'] = time();
				$banner->add($data_img);
			}
			$this->redirect("/Admin/Banner");	
		}
	}	
} 