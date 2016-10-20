<?php
namespace Admin\Controller;
use Think\Controller;

class InlibController extends Controller {

    //加载入库页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //添加入库产品
    public function register() {
        if (IS_AJAX) {
            $Inlib = D('Inlib');
            echo $Inlib->register(I('post.pid'), I('post.sid'), I('post.sn'), I('post.product'), I('post.staff'),
                                  I('post.pro_price'), I('post.unit'), I('post.number'), I('post.mode'), I('post.mode_explain'), I('post.discount'));
        } else {
            $this->error('非法操作！');
        }
    }

    //获取入库产品列表
    public function getList() {
        if (IS_AJAX) {
            $Inlib = D('Inlib');
            $this->ajaxReturn($Inlib->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                                              I('post.keywords'), I('post.date'), I('post.date_from'), I('post.date_to')));
        } else {
            $this->error('非法操作！');
        }
    }

    //获取产品详情
    public function getDetails() {
        if (IS_AJAX) {
            $Inlib = D('Inlib');
            $this->assign('object', $Inlib->getDetails(I('get.id')));
            $this->display('details');
        } else {
            $this->error('非法操作！');
        }
    }

}