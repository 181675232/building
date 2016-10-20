<?php
namespace Admin\Controller;
use Think\Controller;

class PostController extends Controller {

    //加载职位页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取职位列表
    public function getList() {
        if (IS_AJAX) {
            $Post = D('Post');
            $this->ajaxReturn($Post->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                                             I('post.name'), I('post.date'), I('post.date_from'), I('post.date_to')));
        } else {
            $this->error('非法操作！');
        }
    }

    //获取所有职位
    public function getListAll() {
        if (IS_AJAX) {
            $Post = D('Post');
            $this->ajaxReturn($Post->getListAll());
        } else {
            $this->error('非法操作！');
        }
    }

    //添加职位
    public function register() {
        if (IS_AJAX) {
            $Post = D('Post');
            echo $Post->register(I('post.name'));
        } else {
            $this->error('非法操作！');
        }
    }

    //获取一条职位信息
    public function getPost() {
        if (IS_AJAX) {
            $Post = D('Post');
            $this->ajaxReturn($Post->getPost(I('post.id')));
        } else {
            $this->error('非法操作！');
        }
    }

    //修改职位
    public function update() {
        if (IS_AJAX) {
            $Post = D('Post');
            echo $Post->update(I('post.id'), I('post.name'));
        } else {
            $this->error('非法操作！');
        }
    }

    //删除职位
    public function remove() {
        if (IS_AJAX) {
            $Post = D('Post');
            echo $Post->remove(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

}