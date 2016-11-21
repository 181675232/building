<?php
namespace Admin\Controller;
use Think\Controller;

class BuildlogController extends CommonController {

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
            $table = M('Buildlog');
            //分页
            $page = I('post.page') ? I('post.page') : 1;
            $pagesize = I('post.rows') ? I('post.rows') : 20;
            $pages = ($page - 1) * $pagesize;

            //条件
            $where = array();
            if (I('post.keywords')) {
                $keywords = I('post.keywords');
                $where['_string'] = "burst LIKE '%$keywords%' or prorecord LIKE '%$keywords%' or record LIKE '%$keywords%'";
            }
            if (I('post.pid')) {
                $where['building'] = I('post.pid');
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
            if (session('admin')['level']){
                $level_uid = session('admin')['id'];
                $where['_string'] = "uid = $level_uid";
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
            $building = M('building');
            $floor = M('floor');
            $area = M('area');
            foreach ($data as $key=>$val){
                $data[$key]['username'] = $admin->where("id = '{$val['uid']}'")->getField('username');
                $buildings = $building->where("id = '{$val['building']}'")->getField('title');
                $floors = $floor->where("id = '{$val['floor']}'")->getField('title');
                $areas = $area->where("id = '{$val['area']}'")->getField('title');
                $data[$key]['build'] = $buildings.' '.$floors.' '.$areas;
            }
            $this->ajaxReturn(array('total'=>$count,'rows'=>$data ? $data : ''));
        } else {
            $this->error('非法操作！');
        }
    }

    //添加
    public function add() {
        if (IS_AJAX) {
            $table = M('Buildlog');
            $where = I('post.');
            $where['addtime'] = time();
            $where['proid'] = C('proid');
            $where['uid'] = session('admin')['id'];
            if ($where['content']){
                $where['content'] = stripslashes(htmlspecialchars_decode($_POST['content']));
            }
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
            $table = M('Buildlog');
            $where = I('post.');
//            if ($table->where("title = '{$where['title']}'")->find()){
//                echo '职位名称已存在';
//                exit;
//            }
            if ($where['content']){
                $where['content'] = stripslashes(htmlspecialchars_decode($_POST['content']));
            }
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
            $table = D('Buildlog');
//            $where['proid'] = C('proid');
//            $data = $table->field('id,title')->where($where)->select();
            $this->ajaxReturn($table->getListAll());
        } else {
            $this->error('非法操作！');
        }
    }

    //获取
    public function getone() {
        if (IS_AJAX) {
            $where['t_buildlog.id'] = I('get.id');
            $where['t_buildlog.proid'] = C('proid');
            $table = M('buildlog');
            $data = $table->field('t_buildlog.id,t_buildlog.addtime,t_admin.username,t_admin.phone,t_buildlog.weather,t_buildlog.wind,t_buildlog.c,t_buildlog.burst,t_buildlog.prorecord,t_buildlog.record,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area')
                ->join('left join t_admin on t_admin.id = t_buildlog.uid')
                ->join('left join t_building on t_building.id = t_buildlog.building')
                ->join('left join t_floor on t_floor.id = t_buildlog.floor')
                ->join('left join t_area on t_area.id = t_buildlog.area')
                ->where($where)->find();
            $data['prorecord'] = nl2p($data['prorecord'],false);
            $data['record'] = nl2p($data['record'],false);
            $data['burst'] = nl2p($data['burst'],false);
            $this->assign($data);
            $this->display('Buildlog_details');
        } else {
            $this->error('非法操作！');
        }
    }

    //预打印
    public function prints() {
        if (IS_AJAX) {
            $where['t_buildlog.addtime'] = strtotime(I('get.id'));
            $where['t_buildlog.proid'] = C('proid');
            $table = M('buildlog');
            $res = $table->field('t_buildlog.id,t_buildlog.addtime,t_buildlog.weather,t_buildlog.wind,t_buildlog.c,t_buildlog.burst,t_buildlog.prorecord,t_buildlog.record,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_level.title name')
                ->join('left join t_building on t_building.id = t_buildlog.building')
                ->join('left join t_floor on t_floor.id = t_buildlog.floor')
                ->join('left join t_area on t_area.id = t_buildlog.area')
                ->join('left join t_admin on t_admin.id = t_buildlog.uid')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->where($where)->select();
            foreach ($res as $val){
                $data['addtime'] = $val['addtime'];
                $data['weather'] = $val['weather'];
                $data['wind'] = $val['wind'];
                $data['c'] = $val['c'];
                if ($val['burst']){
                    $data['burst'] .= $val['name'].' '.$val['username'].' '.$val['building'].$val['floor'].$val['area'].' : '.$val['burst'].'<br />';
                }
                if ($val['prorecord']){
                    $data['prorecord'] .= $val['name'].' '.$val['username'].' '.$val['building'].$val['floor'].$val['area'].' : '.$val['prorecord'].'<br />';
                }
                if ($val['record']){
                    $data['record'] .= $val['name'].' '.$val['username'].' '.$val['building'].$val['floor'].$val['area'].' : '.$val['record'].'<br />';
                }
            }
            $data['burst'] = nl2p($data['burst'],false);
            $data['prorecord'] = nl2p($data['prorecord'],false);
            $data['record'] = nl2p($data['record'],false);
            $this->assign($data);
            $this->display('Buildlog_prints');
        } else {
            $this->error('非法操作！');
        }
    }

    //导出
    public function export() {
        $table = M('Buildlog');

        /*--------post参数--------*/
        $keywords = I('post.daytask_search_keywords');
        if (I('post.daytask_search_date_from')) $starttime = strtotime(I('post.daytask_search_date_from'));
        if (I('post.daytask_search_date_to')) $stoptime = strtotime(I('post.daytask_search_date_to').' 23:59:59');
        $datetype = I('post.daytask_search_date') ? I('post.daytask_search_date') : 'stoptime';
        if (I('post.daytask_search_state')) $state = I('post.daytask_search_state');
        /*--------post参数--------*/

        //条件
        $where = array();
        if ($keywords) {
            $where['title'] = array('like', '%'.$keywords.'%');
        }
        if ($state){
            if ($state == 1){
                $where['_string'] = 'state = 1 or state = 2';
            }else{
                $where['state'] = $state;
            }
        }
        if (I('post.date_from')) $starttime = strtotime(I('post.date_from'));
        if (I('post.date_to')) $stoptime = strtotime(I('post.date_to').' 23:59:59');
        //$datetype = I('post.date') ? I('post.date') : 'addtime';
        if ($starttime && $stoptime) {
            $where["$datetype"] = array(array('egt', date('Y-m-d H:i:s',$starttime)), array('elt', date('Y-m-d H:i:s',$stoptime)));
            //$where["$datetype"] = array(array('egt', date($starttime)), array('elt', date($stoptime)));
        } else if ($starttime) {
            $where["$datetype"] = array('egt', date('Y-m-d H:i:s',$starttime));
            //$where["$datetype"] = array('egt', date($starttime));
        } else if ($stoptime) {
            $where["$datetype"] = array('elt', date('Y-m-d H:i:s',$stoptime));
            //$where["$datetype"] = array('elt', date($stoptime));
        }
        //管理 or 职员
        if (session('admin')['level']){
            $level_uid = session('admin')['id'];
            $where['_string'] = "uid = $level_uid or user_id = $level_uid";
        }
        //排序

        $orders['stoptime'] = 'desc';
        $where['proid'] = C('proid');
        $data = $table->field('*')->where($where)->order($orders)->select();
        $execl = new \Org\Util\Excel();
        $execl->excel_daytask($data,date('YmdHis',time()));
    }

    //详情
    public function details() {
        if (IS_AJAX) {
            $table = M('Buildlog');
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
            $table = M('Buildlog');
            echo $table->delete(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

}