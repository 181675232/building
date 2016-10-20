<?php
namespace Admin\Controller;
use Think\Controller;

class ProductController extends Controller {

    //加载产品页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取产品列表
    public function getList() {
        if (IS_AJAX) {
            $Product = D('Product');
            $this->ajaxReturn($Product->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                                                I('post.keywords'), I('post.date'), I('post.date_from'), I('post.date_to'), I('post.type'), I('post.alarm')));
        } else {
            $this->error('非法操作！');
        }
    }

    //添加产品
    public function register() {
        if (IS_AJAX) {
            $Product = D('Product');
            echo $Product->register(I('post.name'), I('post.sn'), I('post.type'), I('post.pro_price'), I('post.sell_price'),
                                    I('post.unit'), I('post.inventory_alarm'), I('post.details'));
        } else {
            $this->error('非法操作！');
        }
    }

    //删除产品
    public function remove() {
        if (IS_AJAX) {
            $Product = D('Product');
            echo $Product->remove(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

    //获取产品详情
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