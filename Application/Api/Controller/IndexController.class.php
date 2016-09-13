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
        $password = I('post.password') ? I('post.password') : json('404','缺少参数 password');
        $table = M('admin');
        $res = $table->field('id,jpushid,proid,level,password,token,phone')->where("phone='{$data['phone']}'")->find();
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');

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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $pages = ($page - 1)*20;
        $table = M('dynamic');
        $data = $table->field('t_dynamic.id,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_level.title name')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->join('left join t_floor on t_floor.id = t_dynamic.floor')
            ->join('left join t_area on t_area.id = t_dynamic.area')
            ->join('left join t_admin on t_admin.id = t_dynamic.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where("t_dynamic.proid = $proid")->order('t_dynamic.addtime desc')->limit($pages,20)->select();
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $data = M('building')->field('id,title')->where("proid = $proid")->order('id asc')->select();
        $floor = M('floor')->field('id,title,pid')->where("proid = $proid")->order('id asc')->select();
        $area = M('area')->field('id,title,pid')->where("proid = $proid")->order('id asc')->select();


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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $file = $_FILES ? $_FILES : json('400','至少传一张图片');
        $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['content'] = I('post.content') ? I('post.content') : json('404','缺少参数 content');
        if(mb_strlen($where['content'],'utf8') > 200) json('400','输入内容不能大于200个字符');

        $where['building'] = I('post.building') ? I('post.building') : json('404','缺少参数 building');
        $where['floor'] = I('post.floor') ? I('post.floor') : json('404','缺少参数 floor');
        $where['area'] = I('post.area') ? I('post.area') : 0;

        $where['addtime'] = $data['addtime'] = time();
        $where['proid'] = $data['proid'] = $proid;
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('dynamic');
        $data = $table->field('t_dynamic.building id,t_building.title,t_img.simg,count(t_dynamic.id) as count')
            ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->group('t_dynamic.building')
            ->where("t_dynamic.proid = $proid")->order('t_dynamic.building asc')->limit($pages,20)->select();
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('dynamic');
        $data['count'] = $table->field('t_img.id,t_img.simg img,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_level.title name')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->join('left join t_floor on t_floor.id = t_dynamic.floor')
            ->join('left join t_area on t_area.id = t_dynamic.area')
            ->join('left join t_admin on t_admin.id = t_dynamic.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')
            ->where("t_dynamic.building = $id and t_dynamic.proid = $proid")->count();
        $data['img'] = $table->field('t_img.id,t_img.simg img,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_level.title name')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->join('left join t_floor on t_floor.id = t_dynamic.floor')
            ->join('left join t_area on t_area.id = t_dynamic.area')
            ->join('left join t_admin on t_admin.id = t_dynamic.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')
            ->where("t_dynamic.building = $id and t_dynamic.proid = $proid")->order('t_dynamic.addtime desc')->limit($pages,20)->select();
        if ($data['img']){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有现场数据');
        }
    }

    //一键现场时间格式
    public function dynamic_date_img(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('dynamic');
        $data['count'] = $table->field('t_img.id,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_level.title name')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->join('left join t_floor on t_floor.id = t_dynamic.floor')
            ->join('left join t_area on t_area.id = t_dynamic.area')
            ->join('left join t_admin on t_admin.id = t_dynamic.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')
            ->where("t_dynamic.proid = $proid")->count();
        $data['date'] = $table->field('FROM_UNIXTIME(t_dynamic.addtime,"%Y-%m-%d") datetime')
            ->group('datetime')
            ->where("t_dynamic.proid = $proid")->order('t_dynamic.addtime desc')->limit($pages,20)->select();
        foreach ($data['date'] as $key=>$val){
            $data['date'][$key]['img'] = $table->field('t_img.id,t_img.simg img,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_level.title name')
                ->join('left join t_building on t_building.id = t_dynamic.building')
                ->join('left join t_floor on t_floor.id = t_dynamic.floor')
                ->join('left join t_area on t_area.id = t_dynamic.area')
                ->join('left join t_admin on t_admin.id = t_dynamic.uid')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')
                ->where("FROM_UNIXTIME(t_dynamic.addtime,'%Y-%m-%d') = '{$val['datetime']}' and t_dynamic.proid = $proid")->order('t_dynamic.addtime desc')->select();
        }
        if ($data['date']){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有现场数据');
        }
    }

    //获取下级总控大纲
    public function total_task_tree(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $id = I('post.id') ? I('post.id') : 0;
        $type = I('post.type') ? I('post.type') : 1;
        if ($type == 2){
            $table = M('month_task');
        }elseif($type == 3){
            $table = M('week_task');
        }else{
            $table = M('task');
        }
        $data = $table->field('id,title')->where("pid = '{$id}' and proid = '{$proid}'")->order('id asc')->select();
        $arr = array();
        foreach ($data as $val){
            if ($table->where("pid = '{$val['id']}' and proid = '{$proid}'")->find()){
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $page = I('post.page') ? I('post.page') : 1;
        $type = I('post.type') ? I('post.type') : 1;
        $table = D('Task');
        if ($type == 2){
            $res = $table->month_task_tree($id,$proid);
        }elseif($type == 3){
            $res = $table->week_task_tree($id,$proid);
        }else{
            $res = $table->task_tree($id,$proid);
        }
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('admin');
        $data = $table->field('t_admin.id,t_admin.phone,t_admin.username,t_admin.sex,t_admin.simg,t_admin.desc,t_admin.addtime,t_level.title name')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where("t_admin.id = $id and t_admin.proid = $proid")->find();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','数据获取失败');
        }
    }

    //修改个人资料
    public function user_edit(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $where['sex'] = (I('post.sex') == 1 || I('post.sex') == 2) ? I('post.sex') : 1;
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
        $where['proid'] = $proid;
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
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
        $where['proid'] = $proid;
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
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
        $where['t_buildlog.proid'] = $proid;
        $table = M('buildlog');
        $data = $table->field('t_buildlog.id,t_buildlog.addtime,t_buildlog.uid,substring(t_buildlog.prorecord,1,10) title,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_level.title name')
            ->join('left join t_building on t_building.id = t_buildlog.building')
            ->join('left join t_floor on t_floor.id = t_buildlog.floor')
            ->join('left join t_area on t_area.id = t_buildlog.area')
            ->join('left join t_admin on t_admin.id = t_buildlog.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('buildlog');
        $data = $table->field('t_buildlog.id,t_buildlog.addtime,t_buildlog.weather,t_buildlog.wind,t_buildlog.c,t_buildlog.burst,t_buildlog.prorecord,t_buildlog.record,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area')
            ->join('left join t_building on t_building.id = t_buildlog.building')
            ->join('left join t_floor on t_floor.id = t_buildlog.floor')
            ->join('left join t_area on t_area.id = t_buildlog.area')
            ->where("t_buildlog.id = $id and t_buildlog.proid = $proid")->find();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //修改我的施工日志
    public function buildlog_edit(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
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
        $where['proid'] = $proid;
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
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
        $where['proid'] = $proid;
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $addtime = I('post.addtime') ? I('post.addtime') : json('404','缺少参数 addtime');
        $table = M('buildlog');
        $res = $table->field('t_buildlog.id,t_buildlog.addtime,t_buildlog.weather,t_buildlog.wind,t_buildlog.c,t_buildlog.burst,t_buildlog.prorecord,t_buildlog.record,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_level.title name')
            ->join('left join t_building on t_building.id = t_buildlog.building')
            ->join('left join t_floor on t_floor.id = t_buildlog.floor')
            ->join('left join t_area on t_area.id = t_buildlog.area')
            ->join('left join t_admin on t_admin.id = t_buildlog.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where("t_buildlog.addtime = $addtime and t_buildlog.proid = $proid")->select();
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $type = I('post.type') ? I('post.type') : 1;
        if ($type == 1){
            $where['t_night_card.stoptime'] = array('egt',time());
        }else{
            $where['t_night_card.stoptime'] = array('lt',time());
        }
        $where['t_night_card.proid'] = $proid;
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
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
        $where['proid'] = $proid;

        $res = $table->add($where);
        if ($res){
            json('200','申请成功');
        }else{
            json('400','申请失败');
        }
    }

    //我的动火证列表
    public function my_fire_card(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
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
        $where['t_fire_card.proid'] = $proid;
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $type = I('post.type') ? I('post.type') : 1;
        if ($type == 1){
            $where['t_fire_card.stoptime'] = array('egt',time());
            $where['t_fire_card.state'] = 1;
            $where['t_fire_card.proid'] = $proid;
        }else{
            $map['t_fire_card.stoptime'] = array('lt',time());
            $map['t_fire_card.state'] = 1;
            $where1['_complex'] = $map;
            $where1['t_fire_card.state']  = array('neq',1);
            $where1['_logic'] = 'or';
            $where['_complex'] = $where1;
            $where['t_fire_card.proid'] = $proid;
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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;

        $where['t_fire_card.stoptime'] = array('egt',time());
        $where['t_fire_card.state'] = 2;
        $where['t_fire_card.proid'] = $proid;

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
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('fire_card');
        $data = $table->field('t_fire_card.id,t_fire_card.uid,t_fire_card.starttime,t_fire_card.stoptime,t_fire_card.builder,t_fire_card.look_fire,t_fire_card.is_fire,t_fire_card.desc,t_fire_card.sid,IFNULL(a.username,"") susername,t_admin.username,t_level.title name,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area')
            ->join('left join t_building on t_building.id = t_fire_card.building')
            ->join('left join t_floor on t_floor.id = t_fire_card.floor')
            ->join('left join t_area on t_area.id = t_fire_card.area')
            ->join('left join t_admin on t_admin.id = t_fire_card.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->join('left join t_admin a on a.id = t_fire_card.sid')
            ->where("t_fire_card.id = $id and t_fire_card.proid = $proid")->find();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //审核动火证
    public function fire_card_state(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $where['sid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['state'] = I('post.state') == 2 ? 2 : 3;
        $where['proid'] = $proid;
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

    //关于我们
    public function about(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $table = M('base','t_','DB_CONFIG2');
        $data = $table->field('title,phone,email,description as descript,android,ios')->find();
        json('200','成功',$data);
    }

    //意见反馈
    public function add_feedback(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $table = M('feedback','t_','DB_CONFIG2');
        $where = I('post.');
        if ($table->add($where)){
            json('200','成功');
        }else{
            json('400','失败');
        }
    }

    //融云在线状态订阅
    public function login_state(){

        $appKey    = $_GET['appKey'];
        $nonce     = $_GET['nonce'];
        $timestamp = $_GET['timestamp'];
        $signature = $_GET['signature'];
        if( $appKey && $nonce && $timestamp && $signature ){
            if( $appKey != $this->appKey ){
                json( '1002', 'appKey错误!' );
            }
            $appSecret = $this->appSecret;
            $sign      = sha1( $appSecret . $nonce . $timestamp );
            if( $sign != $signature ){
                json( '1002', 'signature错误!' );
            }
            $data = file_get_contents( "php://input" );

            if( $data ){
                json( '1002', '验证通过,数据不可为空!' );
            }

            $data = json_decode( $data, true );
            $data = rong_filter( $data );

            foreach( $data as $k => $v ){
                if( $data[$k]['status'] === '0' ){
                    $logins[] = $data[$k]['userid'];
                }else{
                    $logout[] = $data[$k]['userid'];
                }

            }

            if( !empty( $logins ) ){
                $map['id']     = array( 'in', $logins );
                $res['online'] = '2';
                M( 'admin' )->where( $map )->save( $res );
                unset( $map );
                unset( $res );
            }

            if( !empty( $logout ) ){
                $map['id']     = array( 'in', $logout );
                $res['online'] = '1';
                M( 'admin' )->where( $map )->save( $res );
            }

            json( '200', 'success' );
        }else{
            json( '1002', '参数错误!' );
        }
    }

    //APP启动更新
    public function app_state(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $uid = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');

        $where['logintime'] = time();
        $where['proid'] = $proid;
        $where['state'] = 2;
        $data = M('admin')->where("id = $uid")->save($where);
        if($data){
            json('200','成功');
        }else{
            json('400','数据更新失败');
        }
    }

    //联系人列表
    public function contacts_list(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $uid = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $table = M('team');
        $data = $table->field('id,title')->order('ord asc')->select();
        $admin = M('admin');
        foreach ($data as $key=>$val){
            $data[$key]['user'] = $admin->field('t_admin.id,t_admin.username,t_admin.simg,t_admin.online,t_level.title')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->where("t_level.pid = '{$val['id']}' and t_admin.proid = '{$proid}'")->order('t_admin.online desc,t_admin.level desc')->select();
        }
        if($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //创建群组
    public function addgroup(){
        $where['proid'] = $map['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['uid'] = $map['user_id'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['title'] = I('post.title') ? I('post.title') : json('404','缺少参数 title');
        if(isset($_POST['desc'])) $where['desc'] = I('post.desc');
        $where['addtime'] = $map['addtime'] = time();
        $table = M('groups');
        $map['groups_id'] = $table->add($where);
        if ($map['groups_id']){
            $groupsuser = M('groupsuser');
            $map['level'] = 9;
            $id = $groupsuser->add($map);
            if ($id){
                $rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
                $r = $rongyun->groupCreate($where['uid'], $map['groups_id'], $where['title']);
                $rong = json_decode($r);
                if($rong->code == 200){
                    $data['groupsid'] = $map['groups_id'];
                    json('200','成功',$data);
                }else {
                    $groupsuser->delete($id);
                    $table->delete($map['groups_id']);
                    json('404','系统内部错误');
                }
            }else{
                $table->delete($map['groups_id']);
                json('400','群组创建失败');
            }
        }else {
            json('400','群组创建失败');
        }
    }

    //发送群邀请
    public function groupbyfriend(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $uid = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['groups_id'] = I('post.groups_id') ? I('post.groups_id') : json('404','缺少参数 groups_id');
        $arr = explode(',', $uid);
        $admin = M('admin');
        $groupsuser = M('groupsuser');
        $rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
        foreach($arr as $val){
            $res = $admin->field('t_admin.username,t_level.title')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->where("t_admin.id = '{$val}' and t_admin.proid = '{$where['proid']}'")->find();
            if ($res){
                $where['user_id'] = $val;
                $id = $groupsuser->add($where);
                if($id){
                    $r = $rongyun->groupJoin($val,$where['groups_id']);
                    $rong = json_decode($r);
                    if($rong->code == 200){
                        $content = '{\"message\":\"请在聊天中注意人身财产安全\",\"extra\":\"\"}';
                        $rongyun->messageGroupPublish($val,$where['groups_id'],$content);
                    }else {
                        $groupsuser->delete($id);
                        json('404','系统内部错误');
                    }
                }
            }
        }
        json('200','成功');
    }

    //退出群组
    public function groupquit(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['user_id'] = I('post.user_id') ? I('post.user_id') : json('404','缺少参数 user_id');
        $where['groups_id'] = I('post.groups_id') ? I('post.groups_id') : json('404','缺少参数 groups_id');
        $table = M('groupsuser');
        $level = $table->where($where)->getField('level');
        if($level == 9) json('400','对不起，群主不能退出群组');
        $admin = M('admin');
        if ($table->where($where)->delete()){
            $res = $admin->field('t_admin.username,t_level.title')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->where("t_admin.id = '{$where['user_id']}' and t_admin.proid = '{$where['proid']}'")->find();
            $rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
            $r = $rongyun->groupQuit($where['user_id'],$where['groups_id']);
            $rong = json_decode($r);
            if($rong->code == 200){
                $content = "{'message':'{$res['title']} {$res['username']}退出本群.'}";
                $rongyun->messageGroupPublish($where['user_id'],$where['groups_id'],$content);
                json('200','成功');
            }else {
                json('400','系统内部错误');
            }
        }else {
            json('400','操作失败');
        }
    }

    //移出群组
    public function tichu_group(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $uid = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['user_id'] = I('post.user_id') ? I('post.user_id') : json('404','缺少参数 user_id');
        $where['groups_id'] = I('post.groups_id') ? I('post.groups_id') : json('404','缺少参数 groups_id');
        $table = M('groupsuser');
        $level = $table->where("proid = '{$where['proid']}' and user_id = '{$uid}' and groups_id = '{$where['groups_id']}'")->getField('level');
        if($level != 9) json('400','对不起，您没有操作权限');
        if ($where['user_id'] == $uid) json('400','对不起，您不能将自己移出群组');
        $groups = M('groups');
        $admin = M('admin');
        if ($table->where($where)->delete()){
            $ress = $admin->field('t_admin.username,t_level.title')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->where("t_admin.id = '{$where['user_id']}' and t_admin.proid = '{$where['proid']}'")->find();
            $res = $groups->field('title')->where("id = '{$where['groups_id']}' and proid = '{$where['proid']}'")->find();
            $rongyun = new  \Org\Util\Rongyun($this->appKey,$this->appSecret);
            $r = $rongyun->groupQuit($where['user_id'],$where['groups_id']);
            $rong = json_decode($r);
            if($rong->code == 200){
                $content = "{'message':'{$ress['title']} {$ress['username']}被移除本群.'}";
                $rongyun->messageGroupPublish($where['user_id'],$where['groups_id'],$content);
                $content = "{'content':'{您被移出 {$res['title']} 群组.'}";
                $rongyun->messageSystemPublish(1,$where['user_id'],$content);
                json('200','成功');
            }else {
                json('400','系统内部错误');
            }
        }else {
            json('400','操作失败');
        }
    }

    //解散群组
    public function groupdismiss(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['user_id'] = I('post.user_id') ? I('post.user_id') : json('404','缺少参数 user_id');
        $where['groups_id'] = I('post.groups_id') ? I('post.groups_id') : json('404','缺少参数 groups_id');
        $table = M('groupsuser');
        $level = $table->where("proid = '{$where['proid']}' and user_id = '{$where['user_id']}' and groups_id = '{$where['groups_id']}'")->getField('level');
        if($level != 9) json('400','对不起，您没有操作权限');
        $groups = M('groups');
        $res = $groups->field('title')->where("id = '{$where['groups_id']}' and proid = '{$where['proid']}'")->find();
        if ($res) {
            if ($groups->delete($where['groups_id'])) {
                $arr = $table->where("groups_id = '{$where['groups_id']}' and proid = '{$where['proid']}'")->getField('user_id',true);
                if ($table->where("groups_id = '{$where['groups_id']}' and proid = '{$where['proid']}'")->delete()) {
                    $rongyun = new  \Org\Util\Rongyun($this->appKey, $this->appSecret);
                    $r = $rongyun->groupDismiss($where['user_id'], $where['groups_id']);
                    $rong = json_decode($r);
                    if ($rong->code == 200) {
                        $content = "{'content':'{您所在的群组 {$res['title']} 解散了.'}";
                        $rongyun->messageSystemPublish(1, $arr, $content);
                        json('200', '成功');
                    } else {
                        json('400', '系统内部错误');
                    }
                } else {
                    json('400', '操作失败');
                }
            }
        }else{
            json('400','此群组不存在');
        }
    }

    //我的群组
    public function mygroups(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['user_id'] = I('post.user_id') ? I('post.user_id') : json('404','缺少参数 user_id');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('groupsuser');
        $data = $table->field('t_groups.id,t_groups.addtime,t_groups.title,t_groups.desc,t_groupsuser.level')
            ->join('left join t_groups on t_groups.id = t_groupsuser.groups_id')
            ->where("t_groupsuser.user_id = '{$where['user_id']}' and t_groups.state = 1 and t_groups.proid = '{$where['proid']}'")->order('t_groupsuser.id desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }else {
            json('400','没有群组');
        }
    }

    //群组信息
    public function groups_info(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('groups');
        $data = $table->field('t_groups.id,t_groups.addtime,t_groups.title,t_groups.desc,t_groups.uid,t_admin.username,t_admin.simg,t_level.title name')
            ->join('left join t_admin on t_admin.id = t_groups.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where("t_groups.id = '{$where['id']}' and t_groups.state = 1 and t_groups.proid = '{$where['proid']}'")->find();
        if ($data){
            json('200','成功',$data);
        }else {
            json('400','群组不存在或已禁用');
        }
    }

    //群组成员
    public function groupsuser(){
        $where['t_groupsuser.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_groupsuser.groups_id'] = I('post.groups_id') ? I('post.groups_id') : json('404','缺少参数 groups_id');
        $table = M('groupsuser');
        $data = $table->field('t_admin.id,t_groupsuser.level,t_admin.username,t_admin.simg,t_level.name')
            ->join('left join t_admin on t_admin.id = t_groupsuser.user_id')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where($where)->order('t_groupsuser.level desc,t_groupsuser.id asc')->select();
        if ($data){
            json('200','成功',$data);
        }else {
            json('400','没有成员');
        }
    }

    //可入群人员列表
    public function join_groups_list(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $uid = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $id = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $admin = M('admin');
        $groupsuser = M('groupsuser');
        $user = $admin->field('t_admin.id,t_admin.username,t_admin.simg,t_admin.online,t_level.title')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where("t_admin.id != '{$uid}' and t_admin.proid = '{$proid}'")->order('t_admin.online desc,t_admin.level desc')->select();
        $res = $groupsuser->where("groups_id = $id and proid = $proid")->getField('user_id',true);
        foreach ($user as $val){
            if (!in_array($val['id'],$res)){
                $data[] = $val;
            }
        }
        if($data){
            json('200','成功',$data);
        }else{
            json('400','没有可邀请人员');
        }
    }

    //获取所有分包
    public function get_user_fenbao(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $admin = M('admin');
        $data = $admin->field('id,simg,username')->where("level = 79 and proid = $proid")->order('id asc')->select();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //创建日任务
    public function add_day_task(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $arr = json_decode($_POST['json']);
        foreach ($arr as $val){
            if (!$val->uid) json('404',$val->title.' 缺少参数 uid');
            if (!$val->user_id) json('404',$val->title.' 缺少参数 user_id');
            if (!$val->building) json('404',$val->title.' 缺少参数 proid');
            if (!$val->floor) json('404',$val->title.' 缺少参数 floor');
            if (!$val->title) json('404','缺少参数 title');
            if (!checkTimeDate($val->time)) json('404',$val->title.' 时间格式不正确');
        }
        foreach ($arr as $val){
            $where = array();
            $where['proid'] = $proid;
            $where['uid'] = $val->uid;
            $where['user_id'] = $val->user_id;
            $time = $val->time;
            $where['building'] = $val->building;
            $where['title'] = $val->title;
            $where['floor'] = $val->floor;
            $where['area'] = $val->area ? $val->area : 0;
            if (isset($val->desc)) $where['desc'] = $val->desc;
            $starttime = date('Y-m-d',strtotime($time));
            $where['starttime'] = $starttime.' 08:00';
            $where['stoptime'] = $time;
            $where['addtime'] = date('Y-m-d H:i',time());

            $table = M('day_task');
            $res = $table->add($where);
            if($res) {
                $ids[] = $res;
                $map['title'] = $starttime.' 日任务';
                $map['content'] = $where['title'];
                $map['type'] = 'day_task';
                $map['typeid'] = $res;
                $map['user_id'] = $where['uid'];
                $map['proid'] = $where['proid'];
                $map['uid'] = $where['user_id'];
                $map['addtime'] = time();
                if (M('message')->add($map)) {
                    $push['ids'] = $map['uid'];
                    $push['type'] = $map['type'];
                    $push['typeid'] = $map['typeid'];
                    $push['content'] = '您收到一条日任务安排';
                    send_curl($this->url.'/Api/Index/push',$push);
                }
            }
        }
        json('200','成功');
    }

    //推送
    public function push(){
        $type = I('post.type')?I('post.type'):'';
        $typeid = I('post.typeid')?I('post.typeid'):'';
        $ids = I('post.ids')?I('post.ids'):'';
        $content = I('post.content')?I('post.content'):'';
        $map['id']   = array( 'in', $ids );
        $jpushid = M('admin')->where($map)->getField('jpushid',true);
        if ($jpushid){
            if (isset($type)) $arr['type'] = $type;
            if (isset($typeid)) $arr['typeid'] = $typeid;
            $return = jpush( $jpushid, $content, $arr);
            if( $return ){
                json( '200', '成功!' );
            }else{
                json( '400', '失败1!' );
            }
        }
        json( '400', '失败2!' );
    }

    //我的消息列表
    public function message_list(){
        $where['t_message.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_message.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        if (I('post.type')) $where['t_message.type'] = I('post.type');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $data = M('message')->field('t_message.id,t_message.title,t_message.user_id uid,t_admin.simg,t_message.addtime,t_message.state,t_message.type,t_message.typeid,t_message.content')
            ->join('left join t_admin on t_message.user_id = t_admin.id')
            ->where($where)->order('t_message.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //消息详情
    public function message_info(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $message = M('message');
        $data = $message->field('id,content,addtime,type,typeid')->where($where)->find();
        if ($data){
            $message->where($where)->setField('state','2');
            json('200','成功',$data);
        }else{
            json('400','消息不存在或已删除');
        }
    }

    //删除消息
    public function del_message(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $message = M('message');
        $data = $message->where($where)->delete();
        if ($data){
            json('200','成功');
        }else{
            json('400','消息不存在或已删除');
        }
    }

    //全部设为已读
    public function state_message(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $message = M('message');
        $message->where($where)->setField('state',2);
        json('200','成功');
    }

    //管理端全部日任务
    public function manage_day_task(){
        if (I('post.keyword')){
            $keyword = I('post.keyword');
            $where['content'] = array('like',"%{$keyword}%");
        }
        if (I('post.building')) $where['buildingid'] = I('post.building');
        if (I('post.starttime')) $where['statrtime'] = array('egt',I('post.starttime'));
        if (I('post.stoptime')) $where['statrtime'] = array('elt',I('post.stoptime'));
        $type = I('post.type') ? I('post.type') : 0;
        if ($type == 1){
            $where['state'] = array('neq',3);
            $where['stoptime'] = array('egt',date('Y-m-d H:i:s',time()));
        }elseif ($type == 2){
            $where['state'] = array('neq',3);
        }elseif ($type == 3){
            $where['state'] = 3;
        }
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('all_day_task');
        $data = $table->where($where)->order('stoptime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //发布人任务列表
    public function release_user_task_list(){
        $type = I('post.type') ? I('post.type') : 0;
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $date = get_month_week_day();
        if ($type == 1){
            $map['t_day_task.stoptime'] = array(array('egt',$date['beginday']),array('elt',$date['endday']));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }elseif ($type == 2){
            $map['t_day_task.stoptime'] = array(array('egt',$date['beginweek']),array('elt',$date['endweek']));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }elseif ($type == 3){
            $map['t_day_task.stoptime'] = array(array('egt',$date['beginmonth']),array('elt',$date['endmonth']));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $where['t_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_day_task.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $table = M('day_task');
        $data = $table->field('t_day_task.id,t_day_task.title,t_day_task.state,t_day_task.bai,t_day_task.uid,t_admin.username,t_admin.simg,t_level.title name,t_day_task.user_id,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_day_task.starttime,t_day_task.stoptime,t_day_task.truestarttime,t_day_task.truestoptime,now() as time')
            ->join('left join t_building on t_building.id = t_day_task.building')
            ->join('left join t_floor on t_floor.id = t_day_task.floor')
            ->join('left join t_area on t_area.id = t_day_task.area')
            ->join('left join t_admin on t_admin.id = t_day_task.user_id')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where($where)->order('t_day_task.stoptime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //接收人任务列表
    public function receive_user_task_list(){
        $type = I('post.type') ? I('post.type') : 0;
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $date = get_month_week_day();
        if ($type == 1){
            $map['t_day_task.stoptime'] = array(array('egt',$date['beginday']),array('elt',$date['endday']));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }elseif ($type == 2){
            $map['t_day_task.stoptime'] = array(array('egt',$date['beginweek']),array('elt',$date['endweek']));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }elseif ($type == 3){
            $map['t_day_task.stoptime'] = array(array('egt',$date['beginmonth']),array('elt',$date['endmonth']));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $where['t_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_day_task.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $table = M('day_task');
        $data = $table->field('t_day_task.id,t_day_task.title,t_day_task.state,t_day_task.bai,t_day_task.uid,t_admin.username,t_admin.simg,t_level.title name,t_day_task.user_id,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_day_task.starttime,t_day_task.stoptime,t_day_task.truestarttime,t_day_task.truestoptime,now() as time')
            ->join('left join t_building on t_building.id = t_day_task.building')
            ->join('left join t_floor on t_floor.id = t_day_task.floor')
            ->join('left join t_area on t_area.id = t_day_task.area')
            ->join('left join t_admin on t_admin.id = t_day_task.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where($where)->order('t_day_task.stoptime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //我发布的未完成任务列表
    public function myadd_nofinish_task(){
        $where['t_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_day_task.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $where['t_day_task.state'] = array('neq',3);
        $table = M('day_task');
        $data = $table->field('t_day_task.id,t_day_task.title,t_day_task.state,t_day_task.bai,t_day_task.uid,t_admin.username,t_admin.simg,t_level.title name,t_day_task.user_id,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_day_task.starttime,t_day_task.stoptime,t_day_task.truestarttime,t_day_task.truestoptime,now() as time')
            ->join('left join t_building on t_building.id = t_day_task.building')
            ->join('left join t_floor on t_floor.id = t_day_task.floor')
            ->join('left join t_area on t_area.id = t_day_task.area')
            ->join('left join t_admin on t_admin.id = t_day_task.user_id')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where($where)->order('t_day_task.stoptime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //任务开始
    public function start_day_task(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $map['state'] = 2;
        $map['truestarttime'] = date('Y-m-d H:i:s',time());
        $table = M('day_task');
        if ($table->where($where)->save($map)){
            json('200','成功');
        }else{
            json('400','重复操作');
        }
    }

    //任务开始
    public function start_day_task1(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $map['state'] = 2;
        $map['truestarttime'] = date('Y-m-d H:i:s',time());
        $table = M('day_task');
        if ($table->where($where)->save($map)){
            json('200','成功');
        }else{
            json('400','重复操作');
        }
    }









//    public function word_view(){
//        $filename = './Public/upfile/123123.doc';
//        $content = shell_exec('antiword -w 0 UTF-8.txt '.$filename);
//        print_r($content);
//        //$content = shell_exec(‘/usr/local/bin/antiword -m UTF-8.txt ’.$filename);
//
//    }



}