<?php
namespace Admin\Controller;
use Think\Controller;

class IndexController extends Controller {

    //加载后台
    public function index() {
        if (session('admin')) {
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