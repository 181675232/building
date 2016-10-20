<?php
namespace Admin\Controller;
use Think\Controller;

class IssueController extends CommonController {

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
//            $table = M('Issue');
//            $where = array();
//            $data = $table->field('id,title')->where($where)->select();
            $data = group_recursion('Issue',0);
            $res = group_recursion_show($data);
            $this->ajaxReturn(array('rows'=>$res ? $res : ''));
        } else {
            $this->error('非法操作！');
        }
    }

    //添加
    public function add() {
        if (IS_AJAX) {
            $table = M('Issue');
            $where = I('post.');
            if ($table->where("title = '{$where['title']}'")->find()){
                echo '职位名称已存在';
                exit;
            }
            if ($where['pid']){
                $level = $table->where("id = '{$where['pid']}'")->getField('level');
                $where['level'] = $level+1;
            }
            $where['addtime'] = time();
            $id = $table->add($where);
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

    //修改
    public function edit() {
        if (IS_AJAX) {
            $table = M('Issue');
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
            $table = M('Issue');
            $where = I('post.');
            echo $table->save($where);
        } else {
            $this->error('非法操作！');
        }
    }

    //获取所有职位
    public function getListAll() {
        if (IS_AJAX) {
            $table = D('Issue');
            $this->ajaxReturn($table->getListAll());
        } else {
            $this->error('非法操作！');
        }
    }

    //获取
    public function getone() {
        if (IS_AJAX) {
            $table = M('Issue');
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
            $table = M('Issue');
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
            $table = M('Issue');
            echo $table->delete(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

}