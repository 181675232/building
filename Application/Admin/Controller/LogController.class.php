<?php
namespace Admin\Controller;
use Think\Controller;

class LogController extends Controller {

    //加载入库页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取日志列表
    public function getList() {
        if (IS_AJAX) {
            $Log = D('Log');
            $this->ajaxReturn($Log->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                                              I('post.keywords'), I('post.date'), I('post.date_from'), I('post.date_to')));
        } else {
            $this->error('非法操作！');
        }
    }

}