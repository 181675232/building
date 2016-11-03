<?php
namespace Admin\Controller;
use Think\Controller;

class FindController extends CommonController {

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
            $table = M('Find');
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
            $where['proid'] = C('proid');
            $count = $table->where($where)->count();
            $data = $table->field('*')
                ->where($where)
                ->order($orders)->limit($pages,$pagesize)->select();
            $find_group = M('Find_group');
            $admin = M('admin');
            foreach ($data as $key=>$val){
                $data[$key]['group_name'] = $find_group->where("id = '{$val['pid']}'")->getField('title');
                $data[$key]['username'] = $admin->where("id = '{$val['uid']}'")->getField('username');
                $data[$key]['uname'] = $admin->where("id = '{$val['user_id']}'")->getField('username');
            }
            $this->ajaxReturn(array('total'=>$count,'rows'=>$data ? $data : ''));
        } else {
            $this->error('非法操作！');
        }
    }

    //添加
    public function add() {
        if (IS_AJAX) {
            $table = M('Find');
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
            $table = M('Find');
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
            $table = D('Find');
            $where['proid'] = C('proid');
            //$data = $table->field('id,title')->where($where)->select();
            $this->ajaxReturn($table->getListAll());
        } else {
            $this->error('非法操作！');
        }
    }

    //获取
    public function getone() {
        if (IS_AJAX) {
            $table = M('Find');
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
        $table = M('Find');

        /*--------post参数--------*/
        $keywords = I('post.find_search_keywords');
        if (I('post.find_search_date_from')) $starttime = strtotime(I('post.find_search_date_from'));
        if (I('post.find_search_date_to')) $stoptime = strtotime(I('post.find_search_date_to').' 23:59:59');
        $datetype = I('post.find_search_date') ? I('post.find_search_date') : 'addtime';
        /*--------post参数--------*/

        //条件
        $where = array();
        if ($keywords) {
            $where['title'] = array('like', '%'.$keywords.'%');
        }
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

        $orders['id'] = 'desc';
        $where['proid'] = C('proid');
        $data = $table->field('*')->where($where)->order($orders)->select();
        $find_group = M('Find_group');
        $admin = M('admin');
        foreach ($data as $key=>$val){
            $data[$key]['group_name'] = $find_group->where("id = '{$val['pid']}'")->getField('title');
            $data[$key]['username'] = $admin->where("id = '{$val['uid']}'")->getField('username');
            $data[$key]['uname'] = $admin->where("id = '{$val['user_id']}'")->getField('username');
        }
        $execl = new \Org\Util\Excel();
        $execl->excel_find($data,date('YmdHis',time()));
    }


    //详情
    public function details() {
        if (IS_AJAX) {
            $table = M('Find');
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
            $table = M('Find');
            echo $table->delete(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

}