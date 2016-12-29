<?php
namespace Admin\Controller;
use Think\Controller;

class IndexController extends Controller {

    //加载后台
    public function index() {

        if (session('admin')) {
            $task = M('day_task');
            $qs = M('qs');
            $proid = session('admin')['proid'];
            //php获取今日开始时间戳和结束时间戳
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
//            $sum['today'] = $ip->where("addtime > $beginToday and addtime < $endToday")->count() ? $ip->where("addtime > $beginToday and addtime < $endToday")->count() : 0;
//            $sum['yesterday'] = $ip->where("addtime > $beginYesterday and addtime < $endYesterday")->count() ? $ip->where("addtime > $beginYesterday and addtime < $endYesterday")->count() : 0;
//            $sum['week'] = $ip->where("addtime > ($time-7*24*60*60)")->count() ? $ip->where("addtime > ($time-7*24*60*60)")->count() : 0;
//            $sum['month'] = $ip->where("addtime > ($time-30*24*60*60)")->count() ? $ip->where("addtime > ($time-30*24*60*60)")->count() : 0;
//            $sum['count'] = $ip->count() ? $ip->count() : 0;
//            $this->assign('sum',$sum);
            //线图
            $date = $str = $str1 = $str2 = $str3 = '';
//            $data = $qs->where("proid = '{$proid}'  and state = 4 and addtime > '($beginToday-(29*24*60*60))' and addtime < '($endToday-(29*24*60*60))'")->count();
//            print_r($data);
//            exit;
            for ($i=29;$i>=0;$i--){
                $date .= "'".date('m-d',$beginToday-($i*24*60*60))."',";
                $beginToday1 = date('Y-m-d H:i:s',$beginToday-($i*24*60*60));
                $endToday1 = date('Y-m-d H:i:s',$endToday-($i*24*60*60));
                $str .= $task->where("proid = '{$proid}' and state = 3 and starttime > '{$beginToday1}' and starttime < '{$endToday1}'")->count().',';
                $str1 .= $task->where("proid = '{$proid}' and starttime > '{$beginToday1}' and starttime < '{$endToday1}'")->count().',';
                $str2 .= $qs->where("proid = '{$proid}'  and state = 4 and addtime > '($beginToday-($i*24*60*60))' and addtime < '($endToday-($i*24*60*60))'")->count().',';
                $str3 .= $qs->where("proid = '{$proid}' and addtime > '($beginToday-($i*24*60*60))' and addtime < '($endToday-($i*24*60*60))'")->count().',';
            }
            $categories = "[".substr($date, 0,-1)."]";

            $task_count = "[".substr($str, 0,-1)."]";
            $task_all = "[".substr($str1, 0,-1)."]";
            $qs_count = "[".substr($str2, 0,-1)."]";
            $qs_all = "[".substr($str3, 0,-1)."]";
            $this->assign('categories',$categories);
            $this->assign('task_count',$task_count);
            $this->assign('task_all',$task_all);
            $this->assign('qs_count',$qs_count);
            $this->assign('qs_all',$qs_all);

            //饼图
            $task1 = $task2 = $task3 = $task4 = 0;
            $tasks = $task->where("proid = '{$proid}'")->select();
            foreach ($tasks as $val){
                if ($val['state'] == 1){
                    $task1++;
                }elseif($val['state'] == 2){
                    $task2++;
                }else{
                    if ($val['truestoptime'] > $val['stoptime']){
                        $task4++;
                    }else{
                        $task3++;
                    }
                }
            }
            $this->assign('task1',$task1);
            $this->assign('task2',$task2);
            $this->assign('task3',$task3);
            $this->assign('task4',$task4);

            //扇形
            $qs1 = $qs2 = $qs3 = $qs4 = 0;
            $qss = $qs->where("proid = '{$proid}'")->select();
            foreach ($qss as $val){
                if ($val['state'] == 1){
                    $qs1++;
                }elseif($val['state'] == 2){
                    $qs2++;
                }elseif($val['state'] == 3){
                    $qs3++;
                }elseif($val['state'] == 4){
                    $qs4++;
                }
            }
            $this->assign('qs1',$qs1);
            $this->assign('qs2',$qs2);
            $this->assign('qs3',$qs3);
            $this->assign('qs4',$qs4);

//            $this->assign('usergroup',$res);
//            $data['task_count'] = $task->where("proid = '{$proid}' and state = 3")->count();
//            $data['task_all'] = $task->where("proid = '{$proid}'")->count();
//            $data['qs_count'] = $qs->where("proid = '{$proid}' and state = 4")->count();
//            $data['qs_all'] = $qs->where("proid = '{$proid}'")->count();
//            $this->assign($data);
            $this->display();
        } else {
            $this->redirect('Login/index');
        }
    }

    //获取菜单导航
    public function getNav() {
        $Nav = D('Nav');
        $this->ajaxReturn($Nav->getNav(0));
    }

    //修改密码
    public function edit() {
        $id = session('admin')['id'];
        $table = M('admin');
        $pass = md5(I('post.pass'));
        $res = $table->where("id = $id")->getField('password');
        if ($pass != $res){
            echo '原密码不正确';
        }else{
            $where['password'] = md5(I('post.password'));
            $table->where("id = $id")->save($where);
            echo 1;
        }
    }

    //	上传图片
    function upload(){
        header("Content-Type:text/html; charset=utf-8");
        $upload = new \Think\Upload();// 实例化上传类
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Public/upfile/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        $upload->saveName = time().'_'.mt_rand(); //文件名

        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            echo '上传失败！';
        }else{// 上传成功
            $data['status'] = 1;
            $data['msg'] = '文件上传成功！';
            $data['name'] = $info['Filedata']['name'];
            $data['path'] = '/Public/upfile/'.$info['Filedata']['savepath'].$info['Filedata']['savename'];
            $data['size'] = $info['Filedata']['size'];
            $data['ext'] = $info['Filedata']['ext'];
            if (!empty($_GET['IsThumbnail'])){
                $data['thumb'] = '/Public/upfile/'.$info['Filedata']['savepath'].'thumb_'.$info['Filedata']['savename'];
                $image = new \Think\Image();
                $image->open('.'.$data['path']);
                // 生成一个居中裁剪为150*150的缩略图并保存为thumb.jpg
                $image->thumb(150, 150,\Think\Image::IMAGE_THUMB_CENTER)->save('.'.$data['thumb']);
            }
            echo json_encode($data);
            exit;
        }
    }

    //	上传图片
    function execl(){
        header("Content-Type:text/html; charset=utf-8");
        $upload = new \Think\Upload();// 实例化上传类
        $upload->exts      =     array('xlsx', 'xls');// 设置附件上传类型
        $upload->rootPath  =     './Public/upfile/excel/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        $upload->saveName = time().'_'.mt_rand(); //文件名

        // 上传文件
        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            echo '上传失败！';
        }else{// 上传成功
            $data['status'] = 1;
            $data['msg'] = '文件上传成功！';
            $data['name'] = $info['Filedata']['name'];
            $data['path'] = '/Public/upfile/excel/'.$info['Filedata']['savepath'].$info['Filedata']['savename'];
            $data['size'] = $info['Filedata']['size'];
            $data['ext'] = $info['Filedata']['ext'];
//            if (!empty($_GET['IsThumbnail'])){
//                $data['thumb'] = '/Public/upfile/'.$info['Filedata']['savepath'].'thumb_'.$info['Filedata']['savename'];
//            }
            echo json_encode($data);
            exit;
        }
    }

}