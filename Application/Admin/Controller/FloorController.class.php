<?php
namespace Admin\Controller;
use Think\Controller;

class FloorController extends CommonController {

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
            $table = M('Floor');
            //分页
            $page = I('post.page') ? I('post.page') : 1;
            $pagesize = I('post.rows') ? I('post.rows') : 20;
            $pages = ($page - 1) * $pagesize;

            //条件
            $where = array();
            if (I('post.keywords')) {
                $keywords = I('post.keywords');
                $where['title'] = array('like', '%'.$keywords.'%');
            }
            if (I('post.pid')) {
                $where['pid'] = I('post.pid');
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
            //管理 or 职员
//            if (session('admin')['level']){
//                $level_uid = session('admin')['id'];
//                $where['_string'] = "uid = $level_uid or user_id = $level_uid";
//            }
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
            $building= M('building');
            $area = M('area');
            //$admin_qs = M('admin_qs');
            foreach ($data as $key=>$val){
                $data[$key]['building'] = $building->where("id = '{$val['pid']}' and proid = '{$where['proid']}'")->getField('title');
                $data[$key]['count'] = $area->where("pid = '{$val['id']}' and proid = '{$where['proid']}'")->count();
                //$data[$key]['username'] = $admin_qs->join('left join t_admin on t_admin.id = t_admin_qs.uid')->where("t_admin_qs.pid = '{$val['id']}' and t_admin.proid = '{$where['proid']}'")
            }
            $this->ajaxReturn(array('total'=>$count,'rows'=>$data ? $data : ''));
        } else {
            $this->error('非法操作！');
        }
    }

    //添加
    public function add() {
        if (IS_AJAX) {
            print_r(I('post.'));
            exit;
            $table = M('Floor');
            $where['title'] = I('post.title');
            $where['pid'] = $map['bid'] = $data['building'] = I('post.pid');
            $where['simg'] = I('post.simg');
            $areas = explode(',',I('post.areas'));
            $uid = explode(',',I('post.uid'));
            $where['addtime'] = $map['addtime'] = time();
            $where['proid'] = $map['proid'] = $data['proid'] = C('proid');
            $id = $table->add($where);
            if ($id) {
                $area = M('area');
                $admin_qs = M('admin_qs');
                $map['pid'] = $data['floor'] = $id;
                foreach ($areas as $val){
                    if ($val){
                        $map['title'] = $val;
                        $area->add($map);
                    }
                }
                foreach ($uid as $value){
                    if ($value){
                        $data['uid'] = $value;
                        $admin_qs->add($data);
                    }
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
            print_r(I('post.'));
            exit;
            $table = M('Floor');
            $where['title'] = I('post.title');
            $where['pid'] = $map['bid'] = $data['building'] = I('post.pid');
            $where['simg'] = I('post.simg');
            $areas = explode(',',I('post.areas'));
            $uid = explode(',',I('post.uid'));
            $where['addtime'] = $map['addtime'] = time();
            $where['proid'] = $map['proid'] = $data['proid'] = C('proid');

            $id = $table->save($where);
            if ($id) {
                $area = M('area');
                $admin_qs = M('admin_qs');
                $map['pid'] = $data['floor'] = $id;
                foreach ($areas as $val){
                    if ($val){
                        $map['title'] = $val;
                        $area->add($map);
                    }
                }
                foreach ($uid as $value){
                    if ($value){
                        $data['uid'] = $value;
                        $admin_qs->add($data);
                    }
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

    //获取所有
    public function getall() {
        if (IS_AJAX) {
            $table = M('Floor');
            $where['proid'] = C('proid');
            $data = $table->field('id,title')->where($where)->select();
            $this->ajaxReturn($data);
        } else {
            $this->error('非法操作！');
        }
    }

    //获取所有职位
    public function getuser() {
        if (IS_AJAX) {
            $table = M('floor');
            $proid = C('proid');
            $res = $table->field('id,pid')->find();
            $admin_qs = M('admin_qs');
            $data = $admin_qs->field('t_admin.id,t_admin.username,t_level.title')
                ->join('left join t_admin on t_admin.id = t_admin_qs.uid')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->where("t_admin_qs.building = '{$res['pid']}' and t_admin_qs.floor = '{$res['id']}' and t_admin.proid = '{$proid}'")->select();
            $this->ajaxReturn($data);
        } else {
            $this->error('非法操作！');
        }
    }

    //获取
    public function getone() {
        if (IS_AJAX) {
            $table = M('Floor');
            $where['id'] = I('post.id');
            $object = $table->field('*')
                ->where($where)->find();
            $area = M('area');
            $admin = M('admin_qs');
            $object['areas'] = $area->field('id,title')->where("pid = '{$where['id']}'")->order('id asc')->select();
            $object['users'] = $admin->field('t_admin.id,t_admin.username,t_level.title name')
                ->join('left join t_admin on t_admin.id = t_admin_qs.uid')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->where("floor = '{$where['id']}'")->order('t_admin_qs.id asc')->select();
            $this->ajaxReturn($object);
        } else {
            $this->error('非法操作！');
        }
    }

    //详情
    public function details() {
        if (IS_AJAX) {
            $table = M('Floor');
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
            $table = M('Floor');
            echo $table->delete(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

}