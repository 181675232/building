<?php
namespace Admin\Controller;
use Think\Controller;

class StaffController extends Controller {

    //加载档案页
    public function index()
    {
        if (session('admin')) {
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取档案列表
    public function getList() {
        if (IS_AJAX) {
            $Staff = D('Staff');
            $this->ajaxReturn($Staff->getList(I('post.page'), I('post.rows'), I('post.order'), I('post.sort'),
                I('post.keywords'), I('post.date'), I('post.date_from'), I('post.date_to'),
                I('post.gender'), I('post.pid'), I('post.entry_status'), I('post.marital_status'),
                I('post.education'), I('post.type'), I('post.id_card'), I('post.nation'), I('post.uid'),session('admin')['proid']));
        } else {
            $this->error('非法操作！');
        }
    }

    //获取未关联档案列表
    public function getNotRelationList() {
        if (IS_AJAX) {
            $Staff = D('Staff');
            $this->ajaxReturn($Staff->getNotRelationList());
        } else {
            $this->error('非法操作！');
        }
    }

    //添加档案
    public function register() {
        if (IS_AJAX) {
            $Staff = D('Staff');
            echo $Staff->register(I('post.name'), I('post.gender'), I('post.number'), I('post.pid'), I('post.type'),
                I('post.tel'), I('post.id_card'), I('post.nation'), I('post.marital_status'),
                I('post.entry_status'), I('post.entry_date'), I('post.dimission_date'), I('post.politics_status'),
                I('post.specialty'), I('post.education'), I('post.health'), I('post.registered'),
                I('post.registered_address'), I('post.graduate_date'), I('post.graduate_colleges'),
                I('post.intro'), I('post.details'),session('admin')['proid']);
        } else {
            $this->error('非法操作！');
        }
    }
    //导入
    public function import() {
        if (IS_AJAX) {
            $where = I('post.');
            if($where['file']){
                $excel = new \Org\Util\Excel();
                $where['sheet'] = $where['sheet'] ? $where['sheet'] : 'Sheet1';

                $id = $excel->excelimport_work('.'.$where['file'],$where['sheet'],$where['uid']);
                if ($id) {
                    echo $id ? $id : 0;
                    exit;
                } else {
                    echo '操作失败！';
                    exit;
                }
            }else{
                echo '没有上传文件！';
                exit;
            }
        } else {
            $this->error('非法操作！');
        }
    }

    //删除档案
    public function remove() {
        if (IS_AJAX) {
            $Staff = D('Staff');
            echo $Staff->remove(I('post.ids'));
        } else {
            $this->error('非法操作！');
        }
    }

    //获取一条档案信息
    public function getStaff() {
        if (IS_AJAX) {
            $Staff = D('Staff');
            $this->ajaxReturn($Staff->getStaff(I('post.id')));
        } else {
            $this->error('非法操作！');
        }
    }

    //修改帐号
    public function update() {
        if (IS_AJAX) {
            $Staff = D('Staff');
            echo $Staff->update(I('post.id'), I('post.gender'), I('post.number'), I('post.pid'), I('post.type'),
                I('post.tel'), I('post.id_card'), I('post.nation'), I('post.marital_status'),
                I('post.entry_status'), I('post.entry_date'), I('post.dimission_date'), I('post.politics_status'),
                I('post.specialty'), I('post.education'), I('post.health'), I('post.registered'),
                I('post.registered_address'), I('post.graduate_date'), I('post.graduate_colleges'),
                I('post.intro'), I('post.details'));
        } else {
            $this->error('非法操作！');
        }
    }


    //获取帐号详情
    public function getDetails() {
        if (IS_AJAX) {
            $Staff = D('Staff');
            $this->assign('object', $Staff->getDetails(I('get.id')));
            $this->display('details');
        } else {
            $this->error('非法操作！');
        }
    }
}