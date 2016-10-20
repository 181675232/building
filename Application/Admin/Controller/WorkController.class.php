<?php
namespace Admin\Controller;
use Admin\Model\OrderModel;
use Think\Controller;

class WorkController extends Controller {

    //加载工作页
    public function index() {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取工作列表
    public function getList() {
        if (IS_AJAX) {
            $Work = D('Work');
            $this->ajaxReturn($Work->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                                                I('post.keywords'), I('post.date'), I('post.date_from'), I('post.date_to'), I('post.state'), I('post.type')));
        } else {
            $this->error('非法操作！');
        }
    }
	
    //添加信息
    public function register() {
        if (IS_AJAX) {
            $Work = D('Work');
            echo $Work->register(I('post.title'), I('post.type'), I('post.start_date'), I('post.start_date'));
        } else {
            $this->error('非法操作！');
        }
    }
	
	//作废工作计划
    public function cancel() {
        if (IS_AJAX) {
            $Work = D('Work');
            echo $Work->cancel(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }
	
    //获取一条工作计划
    public function getOne() {
        if (IS_AJAX) {
            $Work = D('Work');
            $this->ajaxReturn($Work->getOne(I('post.id')));
        } else {
            $this->error('非法操作！');
        }
    }

    public function getStage() {
        if (IS_AJAX) {
            $Work = D('Work');
            $this->ajaxReturn($Work->getStage(I('post.id')));
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

    //添加工作阶段
    public function addStage() {
        if (IS_AJAX) {
            $Work = D('Work');
            $this->ajaxReturn($Work->addStage(I('post.work_id'), I('post.stage')));
        } else {
            $this->error('非法操作！');
        }
    }

    //完成工作阶段
    public function finish() {
        if (IS_AJAX) {
            $Work = D('Work');
            $this->ajaxReturn($Work->finish(I('post.work_id')));
        } else {
            $this->error('非法操作！');
        }
    }

}