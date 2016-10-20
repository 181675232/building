<?php
namespace Admin\Controller;
use Think\Controller;

class ClientController extends Controller {

    //加载客户页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取客户列表
    public function getList() {
        if (IS_AJAX) {
            $Client = D('Client');
            $this->ajaxReturn($Client->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                                                I('post.keywords'), I('post.date'), I('post.date_from'), I('post.date_to')));
        } else {
            $this->error('非法操作！');
        }
    }
	
    //添加客户信息
    public function register() {
        if (IS_AJAX) {
            $Client = D('Client');
            echo $Client->register(I('post.company'), I('post.name'), I('post.tel'), I('post.source'));
        } else {
            $this->error('非法操作！');
        }
    }
	
	//删除客户 
    public function remove() {
        if (IS_AJAX) {
            $Client = D('Client');
            echo $Client->remove(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }
	
    //获取一条客户信息
    public function getClient() {
        if (IS_AJAX) {
            $Client = D('Client');
            $this->ajaxReturn($Client->getPost(I('post.id')));
        } else {
            $this->error('非法操作！');
        }
    }

    //修改客户信息
    public function update() {
        if (IS_AJAX) {
            $Client = D('Client');
            echo $Client->update(I('post.id'), I('post.name'), I('post.company'), I('post.tel'), I('post.source'));
        } else {
            $this->error('非法操作！');
        }
    }
	
    //获取客户详情
    public function getDetails() {
        if (IS_AJAX) {
            $Client = D('Client');
            $this->assign('object', $Client->getDetails(I('get.id')));
            $this->display('details');
        } else {
            $this->error('非法操作！');
        }
    }

}