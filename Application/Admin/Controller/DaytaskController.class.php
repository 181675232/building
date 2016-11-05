<?php
namespace Admin\Controller;
use Think\Controller;

class DaytaskController extends CommonController {

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
            $table = M('all_day_task');
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
            if (I('post.state')){
                if (I('post.state') == 1){
                    $where['_string'] = 'state = 1 or state = 2';
                }else{
                    $where['state'] = I('post.state');
                }
            }
            if (I('post.date_from')) $starttime = strtotime(I('post.date_from'));
            if (I('post.date_to')) $stoptime = strtotime(I('post.date_to').' 23:59:59');
            $datetype = I('post.date') ? I('post.date') : 'addtime';
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
            $this->ajaxReturn(array('total'=>$count,'rows'=>$data ? $data : ''));
        } else {
            $this->error('非法操作！');
        }
    }

    //添加
    public function add() {
        if (IS_AJAX) {
            $table = M('all_day_task');
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
            $table = M('all_day_task');
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
            $table = D('all_day_task');
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
            $table = M('all_day_task');
            $where['id'] = I('post.id');
            $object = $table->field('*')
                ->where($where)->find();
            $object['content'] = htmlspecialchars_decode($object['content']);
            $this->ajaxReturn($object);
        } else {
            $this->error('非法操作！');
        }
    }

    //导出
    public function export() {
        $table = M('all_day_task');

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
            $table = M('all_day_task');
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
            $table = M('all_day_task');
            echo $table->delete(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

}