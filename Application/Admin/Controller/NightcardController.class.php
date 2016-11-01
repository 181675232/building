<?php
namespace Admin\Controller;
use Think\Controller;

class NightcardController extends CommonController {

    //加载首页
    public function index() {
//        if (session('Night_card')) {
            $this->display();
//        } else {
//            $this->redirect('Login/index');
//        }
    }

    //列表
    public function show() {
        if (IS_AJAX) {
            $table = M('Night_card');
            //分页
            $page = I('post.page') ? I('post.page') : 1;
            $pagesize = I('post.rows') ? I('post.rows') : 20;
            $pages = ($page - 1) * $pagesize;

            //条件
            $where = array();
            if (I('post.keywords')) {
                $keywords = I('post.keywords');
                $map['name'] = array('like', '%'.$keywords.'%');
                $map['phone'] = array('like', '%'.$keywords.'%');
                $map['_logic'] = 'OR';
                $map['username'] = array('like', '%'.$keywords.'%');
            }
            if ($map){
                $where['_complex'] = $map;
            }
            if (I('post.date_from')) $starttime = strtotime(I('post.date_from'));
            if (I('post.date_to')) $stoptime = strtotime(I('post.date_to').' 23:59:59');
            $datetype = I('post.date') ? I('post.date') : 'addtime';
            if ($starttime && $stoptime) {
                $where["$datetype"] = array(array('egt', date($starttime)), array('elt', date($stoptime)));
            } else if ($starttime) {
                $where["$datetype"] = array('egt', date($starttime));
            } else if ($stoptime) {
                $where["$datetype"] = array('elt', date($stoptime));
            }
            //排序
            $order = I('post.order');
            $sort = I('post.sort');
            if ($order && $sort){
                $orders[$sort] = $order;
            }else{
                //默认排序
                $orders['id'] = 'desc';
            }
            $where['proid'] = C('proid');
            $count = $table->where($where)->count();
            $data = $table->field('*')
                ->where($where)
                ->order($orders)->limit($pages,$pagesize)->select();
            $admin = M('admin');
            foreach ($data as $key=>$val){
                $data[$key]['username'] = $admin->where("id = '{$val['uid']}'")->getField('username');
            }
            $this->ajaxReturn(array('total'=>$count,'rows'=>$data ? $data : ''));
        } else {
            $this->error('非法操作！');
        }
    }

    //添加
    public function add() {
        if (IS_AJAX) {
            $table = M('Night_card');
            $where = I('post.');
            $where['starttime'] = strtotime($where['starttime']);
            $where['stoptime'] = strtotime($where['stoptime'].' 23:59:59');

            $where['uid'] = session('admin')['id'];
            $where['proid'] = C('proid');
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
            $table = M('Night_card');
            $where = I('post.');
            $where['starttime'] = strtotime($where['starttime']);
            $where['stoptime'] = strtotime($where['stoptime'].' 23:59:59');
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

    //获取所有职位
    public function getListAll() {
        if (IS_AJAX) {
            $table = D('Night_card');
            $this->ajaxReturn($table->getListAll());
        } else {
            $this->error('非法操作！');
        }
    }

    //获取一条数据
    public function getone() {
        if (IS_AJAX) {
            $table = M('Night_card');
            $where['id'] = I('post.id');
            $object = $table->field('*')
                ->where($where)->find();
            $object['starttime'] = date('Y-m-d',$object['starttime']);
            $object['stoptime'] = date('Y-m-d',$object['stoptime']);
            $this->ajaxReturn($object);
        } else {
            $this->error('非法操作！');
        }
    }

    //详情
    public function details() {
        if (IS_AJAX) {
            $table = M('Night_card');
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
            $table = M('Night_card');
            echo $table->delete(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

}