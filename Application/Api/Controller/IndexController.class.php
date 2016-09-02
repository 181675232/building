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
            $data['date'][$key]['img'] = $table->field('t_img.id,t_img.simg img,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_role.name')
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

    //总控任务实体任务
    public function total_task(){
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $page = I('post.page') ? I('post.page') : 1;
        $table = D('Task');
        $res = $table->task_tree($id);
        if ($res){
            $data = array_page($res,$page);
            if ($data){
                json('200','成功',$data);
            }else{
                json('400','已经是最后一页');
            }
        }else{
            json('400','没有数据');
        }
    }

    //个人资料
    public function user_info(){
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('admin');
        $data = $table->field('t_admin.id,t_admin.phone,t_admin.username,t_admin.sex,t_admin.simg,t_admin.addtime,t_role.name')
            ->join('left join t_role_user on t_role_user.user_id = t_admin.id')
            ->join('left join t_role on t_role.id = t_role_user.role_id')
            ->where("t_admin.id = $id")->find();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','数据获取失败');
        }
    }

    //修改个人资料
    public function user_edit(){
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $where['sex'] = (I('post.sex') == 1 || I('post.sex') == 2) ? I('post.sex') : json('404','非法传值');
        if($_FILES['simg']){
            $file = $_FILES['simg'];
            $rand = '';
            for ($i=0;$i<6;$i++){
                $rand.=rand(0,9);
            }
            $type = explode('.', $file['name']);
            $simg = date('YmdHis').$rand.'.'.end($type);
            $dir = date('Y-m-d');
            if (!is_dir('./Public/upfile/'.$dir)){
                mkdir('./Public/upfile/'.$dir,0777);
            }
            if (move_uploaded_file($file['tmp_name'], './Public/upfile/'.$dir.'/'.$simg)){
                $where['simg'] = '/Public/upfile/'.$dir.'/'.$simg;
                create_thumb($simg,$dir);
            }else {
                json('400','头像上传失败');
            }
        }
        $table = M('admin');
        $data = $table->save($where);
        if ($data){
            json('200','成功');
        }else{
            json('400','没有任何修改');
        }
    }

    //发布施工日志
    public function buildlog_add(){
        $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['addtime'] = I('post.addtime') ? I('post.addtime') : json('404','缺少参数 addtime');
        if (checkTime($where['addtime'])){
            $where['addtime'] = strtotime($where['addtime']);
            //php获取今日开始时间戳和结束时间戳
            $beginToday=mktime(0,0,0,date('m',time()),date('d',time()),date('Y',time()));
            $endToday=mktime(0,0,0,date('m',time()),date('d',time())+1,date('Y',time()))-1;
            if ($beginToday > $where['addtime']) json('400','您只能发布当日的施工日志');
            if ($endToday < $where['addtime']) json('400','您只能发布当日的施工日志');
        }else{
            json('404','时间格式不正确');
        }

        $where['building'] = I('post.building') ? I('post.building') : json('404','缺少参数 building');
        $where['floor'] = I('post.floor') ? I('post.floor') : json('404','缺少参数 floor');
        $where['area'] = I('post.area') ? I('post.area') : 0;

        $where['weather'] = I('post.weather') ? I('post.weather') : json('404','缺少参数 weather');
        $where['wind'] = I('post.wind') ? I('post.wind') : json('404','缺少参数 wind');
        $where['c'] = I('post.c') ? I('post.c') : json('404','缺少参数 c');

        if (I('post.burst')){
            $where['burst'] = I('post.burst');
            if(mb_strlen($where['burst'],'utf8') > 200) json('400','输入内容不能大于200个字符');
        }
        if (I('post.prorecord')){
            $where['prorecord'] = I('post.prorecord');
            if(mb_strlen($where['prorecord'],'utf8') > 200) json('400','输入内容不能大于200个字符');
        }
        if (I('post.record')){
            $where['record'] = I('post.record');
            if(mb_strlen($where['record'],'utf8') > 200) json('400','输入内容不能大于200个字符');
        }

        $table = M('buildlog');
        $data = $table->add($where);
        if ($data){
            json('200','成功');
        }else{
            json('400','没有任何修改');
        }
    }

    //我的施工日志列表
    public function buildlog_list(){
        $uid = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $type = I('post.type') ? I('post.type') : 0;
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;

        //php获取本月起始时间戳和结束时间戳
        $beginThismonth=mktime(0,0,0,date('m',time()),1,date('Y',time()));
        $endThismonth=mktime(23,59,59,date('m',time()),date('t',time()),date('Y',time()));
        if ($type == 1){
            $where['t_buildlog.addtime'] = array('egt',$beginThismonth);
            $where['t_buildlog.addtime'] = array('elt',$endThismonth);
        }elseif($type == 2){
            $where['t_buildlog.addtime'] = array('egt',time()-3*30*24*60*60);
        }elseif($type == 3){
            $where['t_buildlog.addtime'] = array('egt',time()-6*30*24*60*60);
        }
        $where['t_buildlog.uid'] = $uid;
        $table = M('buildlog');
        $data = $table->field('t_buildlog.id,t_buildlog.addtime,t_buildlog.uid,substring(t_buildlog.prorecord,1,10) title,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_role.name')
            ->join('left join t_building on t_building.id = t_buildlog.building')
            ->join('left join t_floor on t_floor.id = t_buildlog.floor')
            ->join('left join t_area on t_area.id = t_buildlog.area')
            ->join('left join t_admin on t_admin.id = t_buildlog.uid')
            ->join('left join t_role_user on t_role_user.user_id = t_admin.id')
            ->join('left join t_role on t_role.id = t_role_user.role_id')
            ->where($where)->order('t_buildlog.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //我的施工日志详情
    public function buildlog_info(){
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('buildlog');
        $data = $table->field('t_buildlog.id,t_buildlog.addtime,t_buildlog.weather,t_buildlog.wind,t_buildlog.c,t_buildlog.burst,t_buildlog.prorecord,t_buildlog.record,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area')
            ->join('left join t_building on t_building.id = t_buildlog.building')
            ->join('left join t_floor on t_floor.id = t_buildlog.floor')
            ->join('left join t_area on t_area.id = t_buildlog.area')
            ->where("t_buildlog.id = $id")->find();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //修改我的施工日志
    public function buildlog_edit(){
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $where['addtime'] = I('post.addtime') ? I('post.addtime') : json('404','缺少参数 addtime');
        if (checkTime($where['addtime'])){
            $where['addtime'] = strtotime($where['addtime']);
            //php获取今日开始时间戳和结束时间戳
            $beginToday=mktime(0,0,0,date('m',time()),date('d',time()),date('Y',time()));
            $endToday=mktime(0,0,0,date('m',time()),date('d',time())+1,date('Y',time()))-1;
            if ($beginToday > $where['addtime']) json('400','您只能修改当日的施工日志');
            if ($endToday < $where['addtime']) json('400','您只能修改当日的施工日志');
        }else{
            json('404','时间格式不正确');
        }
        //存在就赋值
        if (isset($_POST['building'])) $where['building'] = I('post.building');
        if (isset($_POST['floor'])) $where['floor'] = I('post.floor');
        if (isset($_POST['area'])) $where['area'] = I('post.area');
        if (isset($_POST['weather'])) $where['weather'] = I('post.weather');
        if (isset($_POST['wind'])) $where['wind'] = I('post.wind');
        if (isset($_POST['c'])) $where['c'] = I('post.c');

        if (I('post.burst')){
            $where['burst'] = I('post.burst');
            if(mb_strlen($where['burst'],'utf8') > 200) json('400','输入内容不能大于200个字符');
        }
        if (I('post.prorecord')){
            $where['prorecord'] = I('post.prorecord');
            if(mb_strlen($where['prorecord'],'utf8') > 200) json('400','输入内容不能大于200个字符');
        }
        if (I('post.record')){
            $where['record'] = I('post.record');
            if(mb_strlen($where['record'],'utf8') > 200) json('400','输入内容不能大于200个字符');
        }

        $table = M('buildlog');
        $data = $table->save($where);
        if ($data){
            json('200','成功');
        }else{
            json('400','没有任何修改');
        }
    }

    //整合施工日志列表
    public function buildlog_all_list(){
        $type = I('post.type') ? I('post.type') : 0;
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;

        //php获取本月起始时间戳和结束时间戳
        $beginThismonth=mktime(0,0,0,date('m',time()),1,date('Y',time()));
        $endThismonth=mktime(23,59,59,date('m',time()),date('t',time()),date('Y',time()));
        if ($type == 1){
            $where['addtime'] = array('egt',$beginThismonth);
            $where['addtime'] = array('elt',$endThismonth);
        }elseif($type == 2){
            $where['addtime'] = array('egt',time()-3*30*24*60*60);
        }elseif($type == 3){
            $where['addtime'] = array('egt',time()-6*30*24*60*60);
        }
        $table = M('buildlog');
        $data = $table->field('addtime,substring(t_buildlog.prorecord,1,10) title')
            ->group('addtime')
            ->where($where)->order('addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //整合施工日志详情
    public function buildlog_all_info(){
        $addtime = I('post.addtime') ? I('post.addtime') : json('404','缺少参数 addtime');
        $table = M('buildlog');
        $res = $table->field('t_buildlog.id,t_buildlog.addtime,t_buildlog.weather,t_buildlog.wind,t_buildlog.c,t_buildlog.burst,t_buildlog.prorecord,t_buildlog.record,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_role.name')
            ->join('left join t_building on t_building.id = t_buildlog.building')
            ->join('left join t_floor on t_floor.id = t_buildlog.floor')
            ->join('left join t_area on t_area.id = t_buildlog.area')
            ->join('left join t_admin on t_admin.id = t_buildlog.uid')
            ->join('left join t_role_user on t_role_user.user_id = t_admin.id')
            ->join('left join t_role on t_role.id = t_role_user.role_id')
            ->where("t_buildlog.addtime = $addtime")->select();
        foreach ($res as $val){
            $data['addtime'] = $val['addtime'];
            $data['weather'] = $val['weather'];
            $data['wind'] = $val['wind'];
            $data['c'] = $val['c'];
            if ($val['burst']){
                $data['burst'] .= $val['name'].' '.$val['username'].' '.$val['building'].$val['floor'].$val['area'].' : '.$val['burst'].'/n';
            }
            if ($val['prorecord']){
                $data['prorecord'] .= $val['name'].' '.$val['username'].' '.$val['building'].$val['floor'].$val['area'].' : '.$val['prorecord'].'/n';
            }
            if ($val['record']){
                $data['record'] .= $val['name'].' '.$val['username'].' '.$val['building'].$val['floor'].$val['area'].' : '.$val['record'].'/n';
            }
        }
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //夜间许可证列表
    public function night_card(){
        $type = I('post.type') ? I('post.type') : 1;
        if ($type == 1){
            $where['t_night_card.stoptime'] = array('egt',time());
        }else{
            $where['t_night_card.stoptime'] = array('lt',time());
        }
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('night_card');
        $data = $table->field('t_night_card.*,t_admin.username,t_admin.simg img')
            ->join('left join t_admin on t_admin.id = t_night_card.uid')
            ->where($where)->order('t_night_card.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //申请动火证
    public function add_fire_card(){
        $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['starttime'] = I('post.starttime') ? I('post.starttime') : json('404','缺少参数 starttime');
        if (checkTimeDate($where['starttime'])){
            $where['starttime'] = strtotime($where['starttime']);
        }else{
            json('404','时间格式不正确');
        }
        $where['stoptime'] = I('post.stoptime') ? I('post.stoptime') : json('404','缺少参数 stoptime');
        if (checkTimeDate($where['stoptime'])){
            $where['stoptime'] = strtotime($where['stoptime']);
            if ($where['stoptime'] < $where['starttime']) json('400','开始时间不能大于结束时间');
            if ($where['stoptime'] < time()) json('400','结束时间不能小于当前时间');
        }else{
            json('404','时间格式不正确');
        }

        $where['building'] = I('post.building') ? I('post.building') : json('404','缺少参数 building');
        $where['floor'] = I('post.floor') ? I('post.floor') : json('404','缺少参数 floor');
        $where['area'] = I('post.area') ? I('post.area') : 0;
        $where['builder'] = I('post.builder') ? I('post.builder') : json('404','缺少参数 builder');
        $where['look_fire'] = I('post.look_fire') ? I('post.look_fire') : json('404','缺少参数 look_fire');
        $where['is_fire'] = I('post.is_fire') ? I('post.is_fire') : 1;
        $where['desc'] = I('post.desc') ? I('post.desc') : json('404','缺少参数 desc');

        $table = M('fire_card');
        $where['addtime'] = time();
        $res = $table->add($where);
        if ($res){
            json('200','申请成功');
        }else{
            json('400','申请失败');
        }
    }

    //我的动火证列表
    public function my_fire_card(){
        $type = I('post.type') ? I('post.type') : 1;
        if ($type == 1){
            $where['t_fire_card.stoptime'] = array('egt',time());
            $where['t_fire_card.state'] = 1;
        }else{
            $map['t_fire_card.stoptime'] = array('lt',time());
            $map['t_fire_card.state'] = 1;
            $where1['_complex'] = $map;
            $where1['t_fire_card.state']  = array('neq',1);
            $where1['_logic'] = 'or';
            $where['_complex'] = $where1;
        }
        $where['t_fire_card.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('fire_card');
        $data = $table->field('t_fire_card.id,t_fire_card.starttime,t_fire_card.stoptime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_fire_card.state')
            ->join('left join t_building on t_building.id = t_fire_card.building')
            ->join('left join t_floor on t_floor.id = t_fire_card.floor')
            ->join('left join t_area on t_area.id = t_fire_card.area')
            ->where($where)->order('t_fire_card.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //管理端动火证列表
    public function fire_card_list(){
        $type = I('post.type') ? I('post.type') : 1;
        if ($type == 1){
            $where['t_fire_card.stoptime'] = array('egt',time());
            $where['t_fire_card.state'] = 1;
        }else{
            $map['t_fire_card.stoptime'] = array('lt',time());
            $map['t_fire_card.state'] = 1;
            $where['_complex'] = $map;
            $where['t_fire_card.state']  = array('neq',1);
            $where['_logic'] = 'or';
        }
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('fire_card');
        $data = $table->field('t_fire_card.id,t_fire_card.starttime,t_fire_card.stoptime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_fire_card.state')
            ->join('left join t_building on t_building.id = t_fire_card.building')
            ->join('left join t_floor on t_floor.id = t_fire_card.floor')
            ->join('left join t_area on t_area.id = t_fire_card.area')
            ->where($where)->order('t_fire_card.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //已通过正在使用动火证列表
    public function fire_card_suss_list(){
        $where['t_fire_card.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;

        $where['t_fire_card.stoptime'] = array('egt',time());
        $where['t_fire_card.state'] = 2;

        $table = M('fire_card');
        $data = $table->field('t_fire_card.id,t_fire_card.starttime,t_fire_card.stoptime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_fire_card.state')
            ->join('left join t_building on t_building.id = t_fire_card.building')
            ->join('left join t_floor on t_floor.id = t_fire_card.floor')
            ->join('left join t_area on t_area.id = t_fire_card.area')
            ->where($where)->order('t_fire_card.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //动火证详情
    public function fire_card_info(){
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('fire_card');
        $data = $table->field('t_fire_card.id,t_fire_card.uid,t_fire_card.starttime,t_fire_card.stoptime,t_fire_card.builder,t_fire_card.look_fire,t_fire_card.is_fire,t_fire_card.desc,t_fire_card.sid,IFNULL(a.username,"") susername,t_admin.username,t_role.name,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area')
            ->join('left join t_building on t_building.id = t_fire_card.building')
            ->join('left join t_floor on t_floor.id = t_fire_card.floor')
            ->join('left join t_area on t_area.id = t_fire_card.area')
            ->join('left join t_admin on t_admin.id = t_fire_card.uid')
            ->join('left join t_role_user on t_role_user.user_id = t_admin.id')
            ->join('left join t_role on t_role.id = t_role_user.role_id')
            ->join('left join t_admin a on a.id = t_fire_card.sid')
            ->where("t_fire_card.id = $id")->find();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //审核动火证
    public function fire_card_state(){
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $where['sid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['state'] = I('post.state') == 2 ? 2 : 3;
        $table = M('fire_card');
        $res = $table->field('stoptime,state')->find($where['id']);
        if ($res['stoptime'] < time()) json('400','审核无效，动火证已过期');
        if ($res['state'] != 1) json('400','审核无效，请不要重复操作');
        $where['statetime'] = time();
        $data = $table->save($where);
        if ($data){
            json('200','操作成功');
        }else{
            json('400','审核失败');
        }
    }


    //
    public function word_view(){
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $res =  file_get_contents($url);
        print_r($res);
        exit;
        $filename = './Public/upfile/123123.doc';
        $content = shell_exec('antiword -w 0 UTF-8.txt '.$filename);
        print_r($content);
        //$content = shell_exec(‘/usr/local/bin/antiword -m UTF-8.txt ’.$filename);

    }



//    public function add_dynamic(){
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