<?php
namespace Admin\Controller;
use Think\Controller;

class CommonController extends Controller {

    public function _initialize(){
        header("Content-Type:text/html; charset=utf-8");
        $session = session('admin');
        if (empty($session)){
            $this->redirect('/Login/index');
            exit;
        }
    }
}