<?php
namespace Admin\Controller;
use Think\Controller;

class NavsController extends CommonController {

    //加载首页
    public function index() {
//        if (session('admin')) {
        $this->display();
//        } else {
//            $this->redirect('Login/index');
//        }
    }

    //列表
    public function show() {

        if (IS_AJAX) {
//            $table = M('Nav');
//            $where = array();
//            $data = $table->field('id,title')->where($where)->select();
            $data = group_recursion('Nav',0);
            $res = group_recursion_show($data);
            $this->ajaxReturn(array('rows'=>$res ? $res : ''));
        } else {
            $this->error('非法操作！');
        }
    }

    //添加
    public function add() {
        if (IS_AJAX) {
            $table = M('Nav');
            $where = I('post.');
            if ($table->where("name = '{$where['name']}'")->find()){
                echo '节点已存在';
                exit;
            }
            $auth = $where['auth'];
            unset($where['auth']);
            if ($where['pid']){
                $level = $table->where("id = '{$where['pid']}'")->getField('level');
                $where['level'] = $level+1;
            }
            $where['addtime'] = time();
            $id = $table->add($where);
            if ($id) {
                $arr = explode(',',$auth);
                $rule = M('rule');
                foreach ($arr as $val){
                    $array = explode('_',$val);
                    $res['name'] = $where['name'].'_'.$array[0];
                    $res['title'] = $array[1];
                    $res['pid'] = $id;
                    $rule->add($res);
                }
                echo $id ? $id : 0;
                exit;
            } else {
                echo '操作失败！';
                exit;
            }
        } else {
            $this->error('非法操作！');
        }
    }

    //修改
    public function edit() {
        if (IS_AJAX) {
            $table = M('Nav');
            $where = I('post.');
//            if ($table->where("title = '{$where['title']}'")->find()){
//                echo '职位名称已存在';
//                exit;
//            }
            $id = $table->save($where);
            if ($id) {
                echo $id ? $id : 0;
                exit;
            } else {
                echo '操作失败！';
                exit;
            }
        } else {
            $this->error('非法操作！');
        }
    }

    //状态修改
    public function state(){
        if (IS_AJAX) {
            $table = M('Nav');
            $where = I('post.');
            echo $table->save($where);
        } else {
            $this->error('非法操作！');
        }
    }

    //获取所有职位
    public function getall() {
        if (IS_AJAX) {
            $data = group_recursion('Nav',0);
            $res = group_recursion_show($data);
            $arr['id'] = 0;
            $arr['title'] = '无上级类别';
            $arr['titles'] = '　　<span class="folder-open"></span>无上级类别';
            $obj[] = $arr;
            foreach ($res as $val){
                $obj[] = $val;
            }
            $this->ajaxReturn($obj);
        } else {
            $this->error('非法操作！');
        }
    }

    //获取
    public function getone() {
        if (IS_AJAX) {
            $table = M('Nav');
            $where['id'] = I('post.id');
            $object = $table->field('*')
                ->where($where)->find();
            $this->ajaxReturn($object);
        } else {
            $this->error('非法操作！');
        }
    }

    //详情
    public function details() {
        if (IS_AJAX) {
            $table = M('Nav');
            $where['id'] = I('post.id');
            $object = $table->field('*')
                ->where($where)->find();
            $this->ajaxReturn($object);
        } else {
            $this->error('非法操作！');
        }
    }



    //删除
    public function delete() {
        if (IS_AJAX) {
            $table = M('Nav');
            echo $table->delete(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

}