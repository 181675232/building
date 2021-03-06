<?php
namespace Admin\Controller;
use Think\Controller;

class QsController extends CommonController {

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
            $table = M('all_qs');
            //print_r(I('post.'));
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
            if (I('post.state')) {
                $where['state'] = I('post.state');
            }
            if (I('post.bid')) {
                $where['bid'] = I('post.bid');
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
                $where['_string'] = "uid = $level_uid or user_id = $level_uid";
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
            $where['proid'] = session('admin')['proid'];
            $count = $table->where($where)->count();
            $data = $table->field('*')
                ->where($where)
                ->order($orders)->limit($pages,$pagesize)->select();
            $this->ajaxReturn(array('total'=>$count,'rows'=>$data ? $data : ''));
        } else {
            $this->error('非法操作！');
        }
    }

    //添加
    public function add() {
        if (IS_AJAX) {
            $table = M('all_qs');
            $where = I('post.');
            $where['addtime'] = time();
            $where['proid'] = session('admin')['proid'];
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
            $table = M('all_qs');
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
            $table = D('all_qs');
//            $where['proid'] = session('admin')['proid'];
//            $data = $table->field('id,title')->where($where)->select();
            $this->ajaxReturn($table->getListAll());
        } else {
            $this->error('非法操作！');
        }
    }

    //获取
    public function getone() {
        if (IS_AJAX) {
            $table = M('all_qs');
            $where['id'] = I('get.id');
            $data = $table->field('*')
                ->where($where)->find();
            $proid = session('admin')['proid'];
            //$object['content'] = htmlspecialchars_decode($object['content']);
            $schedule = M('qs_schedule');
            $data['schedule'] = $schedule->field('t_qs_schedule.id,t_qs_schedule.title,t_qs_schedule.addtime,t_qs_schedule.uid,t_admin.username,t_admin.simg,t_level.title name')
                ->join('left join t_admin on t_admin.id = t_qs_schedule.uid')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->where("t_qs_schedule.proid = '{$proid}' and t_qs_schedule.pid = '{$data['id']}'")->order('t_qs_schedule.addtime desc')->select();
            $img = M('img');
            foreach ($data['schedule'] as $key=>$val){
                $data['schedule'][$key]['img'] = $img->where("pid = '{$val['id']}' and type = 'qs_schedule'")->getField('simg',true);
                $data['schedule'][$key]['img'] = $data['schedule'][$key]['img'] ? $data['schedule'][$key]['img'] : array();
            }
            $this->assign($data);
            $this->display('Qs_details');
        } else {
            $this->error('非法操作！');
        }
    }

    //导出
    public function export() {
        $table = M('all_qs');

        /*--------post参数--------*/
        $keywords = I('post.qs_search_keywords');
        if (I('post.qs_search_date_from')) $starttime = strtotime(I('post.qs_search_date_from'));
        if (I('post.qs_search_date_to')) $stoptime = strtotime(I('post.qs_search_date_to').' 23:59:59');
        $datetype = I('post.qs_search_date') ? I('post.qs_search_date') : 'stoptime';
        if (I('post.qs_search_state')) $state = I('post.qs_search_state');
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
        $where['proid'] = session('admin')['proid'];
        $data = $table->field('*')->where($where)->order($orders)->select();
        $execl = new \Org\Util\Excel();
        $execl->excel_qs($data,date('YmdHis',time()));
    }

    //详情
    public function details() {
        if (IS_AJAX) {
            $table = M('all_qs');
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
            $table = M('all_qs');
            echo $table->delete(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

}