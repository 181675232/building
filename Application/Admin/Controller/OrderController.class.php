<?php
namespace Admin\Controller;
use Think\Controller;

class OrderController extends Controller {

    //加载订单页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取订单列表
    public function getList() {
        if (IS_AJAX) {
            $Order = D('Order');
            $this->ajaxReturn($Order->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                                                I('post.keywords'), I('post.date'), I('post.date_from'), I('post.date_to'), I('post.neg')));
        } else {
            $this->error('非法操作！');
        }
    }
	
    //添加订单信息
    public function register() {
        if (IS_AJAX) {
            $Order = D('Order');
            echo $Order->register(I('post.title'), I('post.amount'), I('post.documentary_id'),
                                  I('post.details'), I('post.contract'), I('post.product_outlib'));
        } else {
            $this->error('非法操作！');
        }
    }
	
	//删除订单
    public function remove() {
        if (IS_AJAX) {
            $Order = D('Order');
            echo $Order->remove(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }
	
    //获取一条客户信息
    public function getOne() {
        if (IS_AJAX) {
            $Order = D('Order');
            $this->ajaxReturn($Order->getOne(I('post.id')));
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
            $Order = D('Order');
            $this->assign('object', $Order->getDetails(I('get.id')));
            $this->display('details');
        } else {
            $this->error('非法操作！');
        }
    }

}