<?php
namespace Admin\Controller;
use Think\Controller;

class DocumentaryController extends Controller {

    //加载跟单页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取跟单列表
    public function getList() {
        if (IS_AJAX) {
            $Documentary = D('Documentary');
            $this->ajaxReturn($Documentary->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                                                I('post.keywords'), I('post.date'), I('post.date_from'), I('post.date_to'), I('post.neg')));
        } else {
            $this->error('非法操作！');
        }
    }
	
    //添加客户信息
    public function register() {
        if (IS_AJAX) {
            $Documentary = D('Documentary');
            echo $Documentary->register(I('post.title'), I('post.cid'), I('post.sid'), I('post.company'), I('post.d_name'),
                                    I('post.way'), I('post.evolve'), I('post.next_contact'), I('post.remark'));
        } else {
            $this->error('非法操作！');
        }
    }
	
	//删除客户 
    public function remove() {
        if (IS_AJAX) {
            $Documentary = D('Documentary');
            echo $Documentary->remove(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }
	
    //获取一条客户信息
    public function getDocumentary() {
        if (IS_AJAX) {
            $Documentary = D('Documentary');
            $this->ajaxReturn($Documentary->getDocumentary(I('post.id')));
        } else {
            $this->error('非法操作！');
        }
    }

    //修改客户信息
    public function update() {
        if (IS_AJAX) {
            $Documentary = D('Documentary');
            echo $Documentary->update(I('post.id'), I('post.title'), I('post.cid'), I('post.sid'), I('post.company'), I('post.d_name'),
                                 I('post.way'), I('post.evolve'), I('post.next_contact'), I('post.remark'));
        } else {
            $this->error('非法操作！');
        }
    }
	
    //获取客户详情
    public function getDetails() {
        if (IS_AJAX) {
            $Documentary = D('Documentary');
            $this->assign('object', $Documentary->getDetails(I('get.id')));
            $this->display('details');
        } else {
            $this->error('非法操作！');
        }
    }

}