<?php
namespace Admin\Controller;
use Think\Controller;

class TestController extends Controller {

    //测试专用
    public function index() {
        $this->display();
    }

    //获取菜单导航
    public function getNav2() {
        $Nav = D('Nav');
        $this->ajaxReturn($Nav->getNav2());
    }

}