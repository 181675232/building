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

//            import('ORG.Util.Auth');//加载类库
//            $auth=new Auth();
//            if(!$auth->check(MODULE_NAME.'-'.ACTION_NAME,session('uid'))){
//                $this->error('你没有权限');
//            }
        $auth = new \Org\Util\Auth();

        //检测是否有权限没有权限就做相应的处理
        if(!$auth->check(CONTROLLER_NAME.'-'.ACTION_NAME,$session['id'])){
            if (IS_AJAX){
                json('400','您没有操作权限！',CONTROLLER_NAME.'-'.ACTION_NAME);
                exit();
            }else{
                alertBack('您没有操作权限！');
            }
        }
//        if(!$rbac::AccessDecision()){
//            if (ACTION_NAME == 'delete'){
//                echo 1;
//                exit;
//            }else {
//                alertBack('您没有此操作权限！');
//            }
//        }
    }
}