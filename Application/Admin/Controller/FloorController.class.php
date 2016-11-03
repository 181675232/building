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
            $table = M('Floor');
            $where['title'] = I('post.title');
            $where['area'] = I('post.area');
            $floors = I('post.floor');
            $areas = I('post.areas');
            $map['simg'] = I('post.simg');
            $map['uid'] = I('post.uid');
            $where['addtime'] = $map['addtime'] = $data['addtime'] = time();
            $where['proid'] = $map['proid'] = $data['proid'] = C('proid');
            $id = $table->add($where);
            if ($id) {
                $floor = M('floor');
                $area = M('area');
                $map['pid'] = $id;
                for ($i=1;$i<=$floors;$i++){
                    $map['title'] = $i.'层';
                    $data['pid'] = $floor->add($map);
                    if ($data['pid'] && $areas){
                        $data['bid'] = $id;
                        for ($j=1;$j<=$areas;$j++){
                            $data['title'] = $j.'区';
                            $area->add($data);
                        }
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
            $table = M('Floor');
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
            $table = D('Floor');
            C('proid');
            $this->ajaxReturn($table->getListAll());
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
                ->join('left t_level on t_level.id = t_admin.level')
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
            $object['content'] = htmlspecialchars_decode($object['content']);
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