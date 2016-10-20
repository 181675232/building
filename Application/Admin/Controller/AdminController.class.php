<?php
namespace Admin\Controller;
use Think\Controller;

class UserController extends Controller {

    //加载职位页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取帐号列表
    public function getList() {
        if (IS_AJAX) {
            $User = D('User');
            $this->ajaxReturn($User->getList(I('post.page'), I('post.rows'), I('post.order'),
                                             I('post.sort'), I('post.keywords'),
                                             I('post.date'), I('post.date_from'),
                                             I('post.date_to'), I('post.state')));
        } else {
            $this->error('非法操作！');
        }
    }

    //添加帐号
    public function register() {
        if (IS_AJAX) {
            $User = D('User');
            echo $User->register(I('post.accounts'), I('post.password'), I('post.email'), I('post.uid'), I('post.name'));
        } else {
            $this->error('非法操作！');
        }
    }

    //删除帐号
    public function remove() {
        if (IS_AJAX) {
            $User = D('User');
            echo $User->remove(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

    //获取一条帐号信息
    public function getUser() {
        if (IS_AJAX) {
            $User = D('User');
            $this->ajaxReturn($User->getUser(I('post.id')));
        } else {
            $this->error('非法操作！');
        }
    }

    //修改帐号
    public function update() {
        if (IS_AJAX) {
            $User = D('User');
            echo $User->update(I('post.id'), I('post.password'), I('post.email'),
                               I('post.state'), I('post.name'), I('post.uid'), I('post.user_name'));
        } else {
            $this->error('非法操作！');
        }
    }

    //帐号审核设置
    public function state() {
        if (IS_AJAX) {
            $User = D('User');
            echo $User->state(I('post.id'), I('post.state'));
        } else {
            $this->error('非法操作！');
        }
    }

    //获取帐号详情
    public function getDetails() {
        if (IS_AJAX) {
            $User = D('User');
            $this->assign('object', $User->getDetails(I('get.id')));
            $this->display('details');
        } else {
            $this->error('非法操作！');
        }
    }
}