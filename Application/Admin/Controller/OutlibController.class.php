<?php
namespace Admin\Controller;
use Think\Controller;

class OutlibController extends Controller {

    //加载入库页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取出库产品列表
    public function getList() {
        if (IS_AJAX) {
            $Outlib = D('Outlib');
            $this->ajaxReturn($Outlib->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                                              I('post.keywords'), I('post.date'), I('post.date_from'), I('post.date_to')));
        } else {
            $this->error('非法操作！');
        }
    }

    //批量出库
    public function deliver() {
        if (IS_AJAX) {
            $Outlib = D('Outlib');
            echo $Outlib->deliver(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

    //批量出库
    public function repeal() {
        if (IS_AJAX) {
            $Outlib = D('Outlib');
            echo $Outlib->repeal(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

}