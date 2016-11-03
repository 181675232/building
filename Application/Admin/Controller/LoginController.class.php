<?php
namespace Admin\Controller;
use Think\Controller;

class LoginController extends Controller {
    //登录页
    public function index() {
        if (session('admin')) {
            $this->redirect('Index/index');
        } else {
            $this->display();
        }
    }

    //验证帐号
    public function checkUser() {
        if (IS_AJAX) {
            $table = M('Admin');
            $map['name'] = I('post.name');
            $map['password'] = md5(I('post.password'));
            $object = $table->field('id,level,name,username,state,login_count,proid')
                ->where($map)->find();
            if ($object['proid'] == C('proid') || $object['proid'] == 0) {
                if ($object) {
                    if (!$object['state'] == 2) return -1;
                    //if (!$object['name']) return -2;
                    $role = M('Level')->field('title,level')->where("id = '{$object['level']}'")->find();
                    $arr = array(
                        'id' => $object['id'],
                        'name' => $object['name'],
                        'username' => $object['username'],
                        'level' => $role['level'],
                        'title' => $role['title'],
                    );
                    session('admin', $arr);
                    $update = array(
                        'id' => $object['id'],
                        'logintime' => time(),
                        'ip' => get_client_ip(),
                        'login_count' => $object['login_count'] + 1
                    );
                    $table->save($update);
                    echo $object['id'];
                    exit;
                    //写入日志
//                $param = array(
//                    'user'=>$object['name'].'('.$object['username'].')',
//                    'type'=>'登录系统',
//                    'module'=>'人事管理 >> 登录帐号',
//                    'ip'=>get_client_ip()
//                );
                    //tag('log', $param);
                    //               return $object['id'];

                } else {
                    return 0;
                }
            }else{
                return 0;
            }
        } else {
            $this->error('非法操作！');
        }
    }

    //退出登录
    public function out() {
        session('admin',null);
        $this->redirect('Login/index');
    }

}