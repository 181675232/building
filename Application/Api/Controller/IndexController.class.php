<?php
namespace Api\Controller;
use Monolog\Handler\MailHandler;
use Think\Controller;
use Think;
use Think\Exception;
use Api\Controller\CommonController;

class IndexController extends CommonController { 
	//基本配置
    //url
    private $url = 'http://101.200.81.192:8082';

	//Jpush key
	private $title = 'JollyBuilding';
	private $app_key='bea2018a7e27f608345fa373';
	private $master_secret = 'f050562fffc5362275eb3219';
	
	//融云
	private $appKey = '8brlm7ufr41w3';
	private $appSecret = '7dYWxJCyLIA';
	
	//oss
//	private $url = 'http://jolly.img-cn-beijing.aliyuncs.com'; //外网
//	private $url_nei = 'oss-cn-beijing-internal.aliyuncs.com';
//	private $AccessKeyID='0zHXT3orDFaVxFWn';
//	private $AccessKeySecret = 'yjBwj2Om9l4Bu8qSu7yx3XOAcSxFhv';
	
	//测试
	public function test(){
		
	}

    //短信登录
    public  function  ses_login(){
        $data['phone'] = I('post.phone') ? I('post.phone') : json('404','缺少参数 phone');
        $data['jpushid'] = I('post.jpushid') ? I('post.jpushid') : json('404','缺少参数 jpushid');
        if (!checkPhone($data['phone'])) {
            json('400', '手机格式不正确');
        }
        $code = I('post.code') ? I('post.code') : json('404','缺少参数 code');
        if (S('code'.$data['phone'])){
            if (S('code'.$data['phone']) != $code){
                json('400','验证码不正确');
            }
        }else {
            json('400','验证码已失效');
        }
        $table = M('admin');
        $res = $table->field('id,jpushid,token,level,phone')->where("phone='{$data['phone']}'")->find();
        if ($res){
            if (!$res['token']){
                $data['simg'] = $this->url.'/Public/upfile/touxiang.jpg';
                $rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
                $r = $rongyun->getToken($res['id'],$data['phone'],$data['simg']);
                if($r){
                    $rong = json_decode($r);
                    if ($rong->code == 200){
                        $data['simg'] = '/Public/upfile/touxiang.jpg';
                        $data['token'] = $rong->token;
                    }else {
                        json('400','融云内部错误');
                    }
                }else {
                    json('400','融云token获取失败');
                }
            }
            $data['logintime']  = time();
            if ($table->where("id = '{$res['id']}'")->save($data)){
                $res['jpushid'] = $data['jpushid'];
                $res['phone'] = $data['phone'];
                if(!$res['token']) $res['token'] = $data['token'];
                json('200','成功',$res);
            }else {
                json('400','更新数据失败');
            }
        }else {
            json('400','请先注册');
        }
    }

    //密码登录
    public  function  login(){
        $data['phone'] = I('post.phone') ? I('post.phone') : json('404','缺少参数 phone');
        $data['jpushid'] = I('post.jpushid') ? I('post.jpushid') : json('404','缺少参数 jpushid');
        if (!checkPhone($data['phone'])) {
            json('400', '手机格式不正确');
        }
        $password = I('post.password') ? I('post.password') : json('404','缺少参数 password');
        $table = M('admin');
        $res = $table->field('id,jpushid,level,password,token,phone')->where("phone='{$data['phone']}'")->find();
        if ($res){
            if ($res['password'] == md5(trim($password))){
                unset($res['password']);
            }else{
                json('400','密码错误');
            }
            if (!$res['token']){
                $data['simg'] = $this->url.'/Public/upfile/touxiang.jpg';
                $rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
                $r = $rongyun->getToken($res['id'],$data['phone'],$data['simg']);
                if($r){
                    $rong = json_decode($r);
                    if ($rong->code == 200){
                        $data['simg'] = '/Public/upfile/touxiang.jpg';
                        $data['token'] = $rong->token;
                    }else {
                        json('400','融云内部错误');
                    }
                }else {
                    json('400','融云token获取失败');
                }
            }
            $data['logintime']  = time();
            if ($table->where("id = '{$res['id']}'")->save($data)){
                $res['jpushid'] = $data['jpushid'];
                $res['phone'] = $data['phone'];
                if(!$res['token']) $res['token'] = $data['token'];
                json('200','成功',$res);
            }else {
                json('400','更新数据失败');
            }
        }else {
            json('400','请先注册');
        }
    }
    
    //发送验证码(不检测调用)
    public function yzm(){
        $phone = I('post.phone') ? I('post.phone') : json('404','缺少参数 phone');
        if (!checkPhone($phone)) {
            json('400', '手机格式不正确');
        }
        yzm($phone);
    }

    //忘记密码/重置密码
    public function forgetpass(){
        $user = M('admin');
        $phone = I('post.phone') ? I('post.phone') : json('404','缺少参数 phone');
        if (!checkPhone($phone)) {
            json('400', '手机格式不正确');
        }
        $id = $user->field('id')->where("phone = $phone")->getField('id');
        if (!$id) json('400','手机号未注册！');

        $password = I('post.password') ? I('post.password') : json('404','缺少参数 password');
        if (mb_strlen(trim($password)) < 6){
            json('400','密码不能小于6位');
        }

        $pass = I('post.pass') ? I('post.pass') : json('404','缺少参数 pass');
        if ($password != $pass){
            json('400','两次密码输入不一致，请重新输入');
        }

        $code = I('post.code') ? I('post.code') : json('404','缺少参数 code');
        if (S('code'.$phone)){
            if (S('code'.$phone != $code)){
                json('400','验证码不正确');
            }
        }else {
            json('400','验证码已失效');
        }

        $data['password'] = md5(trim(I('post.password')));
        if ($user->where("id = $id")->save($data)){
            json('200','成功');
        }else {
            json('400','新密码不能和原密码相同');
        }
    }
    
    //修改密码
    public function passedit(){
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');

        $user = M('admin');
        $where['password'] = md5(I('post.fpass'));
        if (!$user->where($where)->find()){
            json('400','原密码输入有误');
        }

        $password = I('post.password') ? I('post.password') : json('404','缺少参数 password');
        if (mb_strlen(trim($password)) < 6){
            json('400','密码不能小于6位');
        }

        $pass = I('post.pass') ? I('post.pass') : json('404','缺少参数 pass');
        if ($password != $pass){
            json('400','两次密码输入不一致，请重新输入');
        }

        $data['password'] = md5(trim($password));
        if ($user->where("id = '{$where['id']}'")->save($data)){
            json('200','成功');
        }else {
            json('400','新密码不能和原密码相同');
        }
    }  

    //一键现场默认页
    public function dynamic_list(){
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('dynamic');
        $data = $table->field('t_dynamic.id,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_role.name')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->join('left join t_floor on t_floor.id = t_dynamic.floor')
            ->join('left join t_area on t_area.id = t_dynamic.area')
            ->join('left join t_admin on t_admin.id = t_dynamic.uid')
            ->join('left join t_role_user on t_role_user.user_id = t_admin.id')
            ->join('left join t_role on t_role.id = t_role_user.role_id')
            ->order('t_dynamic.addtime desc')->limit($pages,20)->select();
        if ($data){
            $img = M('img')->field('id,simg,pid')->where('type = "dynamic"')->select();
            foreach ($data as $key=>$val){
                foreach ($img as $k=>$v){
                    if ($val['id'] == $v['pid']){
                       $data [$key]['img'][] = $v['simg'];
                    }
                }
            }
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有现场数据');
        }
    }

    //获取楼层区结构
    public function get_building_floor(){
        $data = M('building')->field('id,title')->order('id asc')->select();
        $floor = M('floor')->field('id,title,pid')->order('id asc')->select();
        $area = M('area')->field('id,title,pid')->order('id asc')->select();

        foreach ($data as $key=>$value){
            foreach ($floor as $k=>$val){
                if ($value['id'] == $val['pid']){
                    foreach ($area as $v){
                        if ($v['pid'] == $val['id']) {
                            $val['child'][] = $v;
                        }
                    }
                    if (!$val['child']) $val['child'] = array(); //如果层中没有区的话 赋空数组
                    $data[$key]['child'][] = $val;
                }
            }
        }

        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有现场数据');
        }
    }

    //发布现场动态
    public function add_dynamic(){
        $file = $_FILES ? $_FILES : json('400','至少传一张图片');
        $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['content'] = I('post.content') ? I('post.content') : json('404','缺少参数 content');
        if(mb_strlen($where['content'],'utf8') > 200) json('400','输入内容不能大于200个字符');

        $where['building'] = I('post.building') ? I('post.building') : json('404','缺少参数 building');
        $where['floor'] = I('post.floor') ? I('post.floor') : json('404','缺少参数 floor');
        $where['area'] = I('post.area') ? I('post.area') : 0;

        $where['addtime'] = $data['addtime'] = time();
        $table = M('dynamic');
        $data['pid'] = $table->add($where);
        if ($data['pid']){
            $data['type'] = 'dynamic';
            $img = M('img');
            foreach ($file as $val){
                $rand = '';
                for ($i=0;$i<6;$i++){
                    $rand.=rand(0,9);
                }
                $type = explode('.', $val['name']);
                $simg = date('YmdHis').$rand.'.'.end($type);
                $dir = date('Y-m-d');
                if (!is_dir('./Public/upfile/'.$dir)){
                    mkdir('./Public/upfile/'.$dir,0777);
                }
                if (move_uploaded_file($val['tmp_name'], './Public/upfile/'.$dir.'/'.$simg)){
                    $data['simg'] = '/Public/upfile/'.$dir.'/'.$simg;
                    create_thumb($simg,$dir);
                    $img->add($data);
                }
            }
            json('200','成功');
        }else {
            json('400','发布失败');
        }
        json('404');
    }

    //一键现场楼层相册格式
    public function dynamic_photo(){
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('dynamic');
        $data = $table->field('t_dynamic.building id,t_building.title,t_img.simg,count(t_dynamic.id) as count')
            ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->group('t_dynamic.building')
            ->order('t_dynamic.building asc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有现场数据');
        }
    }

    //一键现场楼层相册内图片
    public function dynamic_photo_img(){
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('dynamic');
        $data['count'] = $table->field('t_img.id,t_img.simg img,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_role.name')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->join('left join t_floor on t_floor.id = t_dynamic.floor')
            ->join('left join t_area on t_area.id = t_dynamic.area')
            ->join('left join t_admin on t_admin.id = t_dynamic.uid')
            ->join('left join t_role_user on t_role_user.user_id = t_admin.id')
            ->join('left join t_role on t_role.id = t_role_user.role_id')
            ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')
            ->where("t_dynamic.building = $id")->count();
        $data['img'] = $table->field('t_img.id,t_img.simg img,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_role.name')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->join('left join t_floor on t_floor.id = t_dynamic.floor')
            ->join('left join t_area on t_area.id = t_dynamic.area')
            ->join('left join t_admin on t_admin.id = t_dynamic.uid')
            ->join('left join t_role_user on t_role_user.user_id = t_admin.id')
            ->join('left join t_role on t_role.id = t_role_user.role_id')
            ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')
            ->where("t_dynamic.building = $id")->order('t_dynamic.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有现场数据');
        }
    }

    //一键现场时间格式
    public function dynamic_date_img(){
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('dynamic');
        $data['count'] = $table->field('t_img.id,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_role.name')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->join('left join t_floor on t_floor.id = t_dynamic.floor')
            ->join('left join t_area on t_area.id = t_dynamic.area')
            ->join('left join t_admin on t_admin.id = t_dynamic.uid')
            ->join('left join t_role_user on t_role_user.user_id = t_admin.id')
            ->join('left join t_role on t_role.id = t_role_user.role_id')
            ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')->count();
        $data['date'] = $table->field('FROM_UNIXTIME(t_dynamic.addtime,"%Y-%m-%d") datetime')
            ->group('datetime')
            ->order('t_dynamic.addtime desc')->limit($pages,20)->select();
        foreach ($data['date'] as $key=>$val){
            $data['date'][$key]['img'] = $table->field('t_img.id,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_role.name')
                ->join('left join t_building on t_building.id = t_dynamic.building')
                ->join('left join t_floor on t_floor.id = t_dynamic.floor')
                ->join('left join t_area on t_area.id = t_dynamic.area')
                ->join('left join t_admin on t_admin.id = t_dynamic.uid')
                ->join('left join t_role_user on t_role_user.user_id = t_admin.id')
                ->join('left join t_role on t_role.id = t_role_user.role_id')
                ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')
                ->where("FROM_UNIXTIME(t_dynamic.addtime,'%Y-%m-%d') = '{$val['datetime']}'")->order('t_dynamic.addtime desc')->select();
        }
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有现场数据');
        }
    }

    //获取下级总控大纲
    public function total_task_tree(){
        $id = I('post.id') ? I('post.id') : 0;
        $table = M('task');
        $data = $table->field('id,title')->where("pid = '{$id}'")->order('id asc')->select();
        $arr = array();
        foreach ($data as $val){
            if ($table->where("pid = '{$val['id']}'")->find()){
                $arr[] = $val;
            }
        }
        if ($arr){
            json('200','成功',$arr);
        }else{
            json('400','没有数据');
        }
    }

    //总控任务
    public function total_task(){
        $arrCate = array(  //待排序数组
            array( 'id'=>1, 'name' =>'顶级栏目一', 'parent_id'=>0),
            array( 'id'=>2, 'name' =>'顶级栏目二', 'parent_id'=>0),
            array( 'id'=>3, 'name' =>'栏目三', 'parent_id'=>1),
            array( 'id'=>4, 'name' =>'栏目四', 'parent_id'=>3),
            array( 'id'=>5, 'name' =>'栏目五', 'parent_id'=>4),
            array( 'id'=>6, 'name' =>'栏目六', 'parent_id'=>2),
            array( 'id'=>7, 'name' =>'栏目七', 'parent_id'=>6),
            array( 'id'=>8, 'name' =>'栏目八', 'parent_id'=>6),
            array( 'id'=>9, 'name' =>'栏目九', 'parent_id'=>7),
        );
        print_r(getMenuTree($arrCate, 1, 0));

//        $table = D('Task');
//        $data = $table->task_tree(0);
//        print_r($data);
    }





















//    public function add_dynamic(){
//        $pass = I('post.pass') ? I('post.pass') : json('404','缺少参数 pass');
//        $table = M('dynamic');
//        if($_FILES){
//            $data1 = $_FILES['simg'];
//            $rand = '';
//            for ($i=0;$i<6;$i++){
//                $rand.=rand(0,9);
//            }
//            $type = explode('.', $data1['name']);
//            $simg = date('YmdHis').$rand.'.'.end($type);
//            if (move_uploaded_file($data1['tmp_name'], './Public/upfile/'.$simg)){
//                $data['simg'] 	= '/Public/upfile/'.$simg;
//                $data['thumb_simg']	= create_thumb($simg);
//            }else {
//                json('400','头像上传失败');
//            }
//        }else{
//            json('400','请上传头像');
//        }
//        if (I('post.ids')){
//            $ids = explode(',', I('post.ids'));
//            $message = M('message');
//            $user = M('user');
//            $userinfo = $user->find(I('post.uid'));
//            $data1['title'] = $userinfo['username'].' 发布了新动态';
//            $data1['content'] = $where['title'];
//            $data1['type'] = 'dynamic';
//            $data1['typeid'] = $data['pid'];
//            $data1['state'] = 0;
//            $jpush = new \Org\Util\Jpush($this->app_key,$this->master_secret);
//            $message_num = M('message_num');
//            $where123['type'] = 3;
//            foreach ($ids as $val){
//                $data1['uid'] = $val;
//                $where123['uid'] = $data1['uid'];
//                if ($message_num->where($where123)->find()){
//                    $message_num->where($where123)->setInc('num');
//                }else {
//                    $where123['num'] = 1;
//                    $message_num->add($where123);
//                }
//                if ($message->add($data1)){
//                    $userdata = $user->field('jpushid')->find($val);
//                    if ($userdata['jpushid']){
//                        $jpushid[] = $userdata['jpushid'];
//                    }
//                }
//            }
//            $array['type'] = 'message';
//            $content = $data1['title'];
//            if ($jpushid){
//                $jpush->push($jpushid, $this->title,$content,$array);
//            }
//        }
//    }
	
	
	
}