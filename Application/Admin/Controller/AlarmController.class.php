<?php
namespace Admin\Controller;
use Think\Controller;

class AlarmController extends Controller {

    //加载库存警报产品页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取库存警报产品列表
    public function getList() {
        if (IS_AJAX) {
            $Product = D('Product');
            $this->ajaxReturn($Product->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                                                I('post.keywords'), I('post.date'), I('post.date_from'), I('post.date_to'), I('post.type')));
        } else {
            $this->error('非法操作！');
        }
    }


    //获取库存警报产品详情
    public function getDetails() {
        if (IS_AJAX) {
            $Product = D('Product');
            $this->assign('object', $Product->getDetails(I('get.id')));
            $this->display('details');
        } else {
            $this->error('非法操作！');
        }
    }

}