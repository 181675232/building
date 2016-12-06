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

        $table = M('admin');
        $data = $table->field('t_level.title')
            ->join("left join t_level on t_admin.level = t_level.id")
            ->where('t_admin.id = 100000')
            ->find();
        json('200','',$data);
//        $str = "撒地方纪律就dsf";
//        echo $s = unicode_encode($str);
//        echo unicode_decode($s);
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
                $table->where("jpushid = '{$data['jpushid']}' and id != '{$res['id']}'")->setField('jpushid','');
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
        $uid = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $pages = ($page - 1)*20;
        $table = M('dynamic');
        $data = $table->field('t_dynamic.id,t_dynamic.upper,t_dynamic.uid,t_dynamic.content,t_dynamic.addtime,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_admin.username,t_admin.simg,t_level.title name')
            ->join('left join t_building on t_building.id = t_dynamic.building')
            ->join('left join t_floor on t_floor.id = t_dynamic.floor')
            ->join('left join t_area on t_area.id = t_dynamic.area')
            ->join('left join t_admin on t_admin.id = t_dynamic.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where("t_dynamic.proid = $proid")->order('t_dynamic.addtime desc')->limit($pages,20)->select();
        if ($data){
            $img = M('img')->field('id,simg,pid')->where('type = "dynamic"')->select();
            $upper = M('upper');
            foreach ($data as $key=>$val){
                $data[$key]['is_upper'] = $upper->where("uid = '{$uid}' and pid = '{$val['id']}' and proid = '{$proid}'")->find() ? 1 : 0;
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
        if($id == 1){
            $data = $table->field('username,simg')->find(1);
            json('200','成功',$data);
        }
        $data = $table->field('t_admin.id,t_admin.phone,t_admin.username,t_admin.sex,t_admin.simg,t_admin.desc,t_admin.addtime,t_admin.level,t_level.title name')
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
        $res['simg'] = $where['simg'];
        if ($data){
            json('200','成功',$res);
        }else{
            json('400','没有任何修改');
        }
    }

    //获取个人今日发布任务
    public function user_day_task(){
        $date = get_month_week_day();
        $map['t_day_task.stoptime'] = array(array('egt',date('Y-m-d H:i',$date['beginday'])),array('elt',date('Y-m-d H:i',$date['endday'])));
        $map['t_day_task.truestoptime'] = array(array('egt',date('Y-m-d H:i',$date['beginday'])),array('elt',date('Y-m-d H:i',$date['endday'])));
        $map['t_day_task.state'] = array('neq',3);
        $map['_logic'] = 'or';
        $where['_complex'] = $map;
        $where['t_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_day_task.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $table = M('day_task');
        $res = $table->field('t_day_task.id,t_day_task.title,t_day_task.state,t_day_task.bai,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_day_task.stoptime')
            ->join('left join t_building on t_building.id = t_day_task.building')
            ->join('left join t_floor on t_floor.id = t_day_task.floor')
            ->join('left join t_area on t_area.id = t_day_task.area')
            ->where($where)->order('t_day_task.stoptime desc')->select();
        if ($res){
            foreach ($res as $val){
                $time = strtotime(date('Y-m-d 23:59:59',strtotime($val['stoptime'])));
                if ($date['endday'] > $time){
                    $data['y'][] = $val['title'].' '.$val['building'].$val['floor'].$val['area'].' 完成度'.($val['bai']*100).'%';
                }else{
                    $data['x'][] = $val['title'].' '.$val['building'].$val['floor'].$val['area'].' 完成度'.($val['bai']*100).'%';
                }
            }
            $data['y'] = $data['y'] ? $data['y'] : array();
            $data['x'] = $data['x'] ? $data['x'] : array();
            json('200','成功',$data);
        }else{
            json('400','没有数据');
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
        }
        if (I('post.prorecord')){
            $where['prorecord'] = I('post.prorecord');
        }
        if (I('post.record')){
            $where['record'] = I('post.record');
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
        }
        if (I('post.prorecord')){
            $where['prorecord'] = I('post.prorecord');
        }
        if (I('post.record')){
            $where['record'] = I('post.record');
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
            //推送
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
        $data = $table->field('t_fire_card.id,t_fire_card.uid,t_fire_card.simg fire_simg,t_fire_card.starttime,t_fire_card.stoptime,t_fire_card.builder,t_fire_card.look_fire,t_fire_card.is_fire,t_fire_card.desc,t_fire_card.sid,IFNULL(a.username,"") susername,t_admin.username,t_level.title name,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area')
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
                json('400','图片上传失败');
            }
        }
        $table = M('fire_card');
        $res = $table->field('stoptime,state')->find($where['id']);
        if ($res['stoptime'] < time()) json('400','审核无效，动火证已过期');
        if ($res['state'] != 1) json('400','审核无效，请不要重复操作');
        $where['statetime'] = time();
        $data = $table->save($where);
        if ($data){
            //推送
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
                        $content = '{"message":"'.$res['title'].' '.$res['username'].'加入本群"}';
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
                $content = '{"message":"'.$res['title'].' '.$res['username'].'退出本群","extra":"'.$where['groups_id'].'"}';
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
                $content = '{"message":"'.$ress['title'].' '.$ress['username'].'退出本群"}';
                $rongyun->messageGroupPublish($where['user_id'],$where['groups_id'],$content);
                $content = '{"content":" 您被移出'.$res['title'].'群组","extra":"'.$where['groups_id'].'"}';
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
                        $content = '{"content":" 您所在的群组'.$res['title'].'解散了","extra":"'.$where['groups_id'].'"}';
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
        $data = $table->field('t_groups.id,t_groups.addtime,t_groups.title,t_groups.desc,t_groupsuser.level,t_groups.uid,t_admin.username,t_admin.simg,t_level.title name')
            ->join('left join t_groups on t_groups.id = t_groupsuser.groups_id')
            ->join('left join t_admin on t_admin.id = t_groups.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
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
        $data = $table->field('t_groups.id,t_groups.addtime,t_groups.title,t_groups.desc,t_groups.uid,t_admin.level,t_admin.username,t_admin.simg,t_level.title name')
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
        $table = M('day_task');
        $workers = M('task_work');
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


            $res = $table->add($where);
            if($res) {
                $where1['pid'] = $res;
                if ($val->workers){
                    foreach ($val->workers as $v){
                        $where1['uid'] = $v->id;
                        $where1['num'] = $v->num;
                        $where1['proid'] = $proid;
                        $workers->add($where1);
                    }
                }
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
        if (I('post.type')) $where['type'] = I('post.type');
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
        if (I('post.starttime')) $starttime = I('post.starttime');
        if (I('post.stoptime')) $stoptime = I('post.stoptime');
        if ($starttime && $stoptime) {
            $where["starttime"] = array(array('egt', $starttime), array('elt', $stoptime));
        } else if ($starttime) {
            $where["starttime"] = array('egt', $starttime);
        } else if ($stoptime) {
            $where["starttime"] = array('elt', $stoptime);
        }
        $type = I('post.type') ? I('post.type') : 0;
        if ($type == 1){
            $where['state'] = array('neq',3);
            $where['stoptime'] = array('elt',date('Y-m-d H:i:s',time()));
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
            $map['t_day_task.stoptime'] = array(array('egt',date('Y-m-d H:i',$date['beginday'])),array('elt',date('Y-m-d H:i',$date['endday'])));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }elseif ($type == 2){
            $map['t_day_task.stoptime'] = array(array('egt',date('Y-m-d H:i',$date['beginweek'])),array('elt',date('Y-m-d H:i',$date['endweek'])));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }elseif ($type == 3){
            $map['t_day_task.stoptime'] = array(array('egt',date('Y-m-d H:i',$date['beginmonth'])),array('elt',date('Y-m-d H:i',$date['endmonth'])));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $where['t_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_day_task.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $table = M('day_task');
        $data = $table->field('t_day_task.id,t_day_task.confirm,t_day_task.title,t_day_task.state,t_day_task.bai,t_day_task.uid,t_admin.username,t_admin.simg,t_level.title name,t_day_task.user_id,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_day_task.starttime,t_day_task.stoptime,t_day_task.truestarttime,t_day_task.truestoptime,now() as time')
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
            $map['t_day_task.stoptime'] = array(array('egt',date('Y-m-d H:i',$date['beginday'])),array('elt',date('Y-m-d H:i',$date['endday'])));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }elseif ($type == 2){
            $map['t_day_task.stoptime'] = array(array('egt',date('Y-m-d H:i',$date['beginweek'])),array('elt',date('Y-m-d H:i',$date['endweek'])));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }elseif ($type == 3){
            $map['t_day_task.stoptime'] = array(array('egt',date('Y-m-d H:i',$date['beginmonth'])),array('elt',date('Y-m-d H:i',$date['endmonth'])));
            $map['t_day_task.state'] = array('neq',3);
            $map['_logic'] = 'or';
            $where['_complex'] = $map;
        }
        $where['t_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_day_task.user_id'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $table = M('day_task');
        $data = $table->field('t_day_task.id,t_day_task.confirm,t_day_task.title,t_day_task.state,t_day_task.bai,t_day_task.uid,t_admin.username,t_admin.simg,t_level.title name,t_day_task.user_id,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_day_task.starttime,t_day_task.stoptime,t_day_task.truestarttime,t_day_task.truestoptime,now() as time')
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
        $data = $table->field('t_day_task.id,t_day_task.confirm,t_day_task.title,t_day_task.state,t_day_task.bai,t_admin.username,t_admin.simg,t_level.title name,t_day_task.user_id uid,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_day_task.starttime,t_day_task.stoptime,t_day_task.truestarttime,t_day_task.truestoptime,now() as time')
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
        $map['confirm'] = 2;
        $map['truestarttime'] = date('Y-m-d H:i:s',time());
        $table = M('day_task');
        if ($table->where($where)->save($map)){
            json('200','成功');
        }else{
            json('400','重复操作');
        }
    }

    //任务确认
    public function confirm_day_task(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $map['confirm'] = 2;
        $table = M('day_task');
        if ($table->where($where)->save($map)){
            json('200','成功');
        }else{
            json('400','重复操作');
        }
    }

    //添加任务进度
    public function add_task_schedule(){
        $where['proid'] = $data['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['pid'] = I('post.pid') ? I('post.pid') : json('404','缺少参数 pid');
        $where['bai'] = I('post.bai') ? I('post.bai') : json('404','缺少参数 bai');
        $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $file = $_FILES ? $_FILES : '';
        if (isset($_POST['title'])) $where['title'] = $_POST['title'];
        $task = M('day_task');
        $res = $task->field('bai')->where("id = '{$where['pid']}' and proid = '{$where['proid']}'")->find();
        if ($res['bai'] > $where['bai']) json('400','新的进度必须大于上次的进度');
        if ($res['bai'] == 1) json('400','此任务已经完成');
        $table = M('task_schedule');
        $where['addtime'] = time();
        $data['pid'] = $table->add($where);
        if ($data['pid']){
            if ($where['bai'] == 1){
                $map['state'] = 3;
                $map['truestoptime'] = date('Y-m-d H:i:s',time());
            }
            $map['bai'] = $where['bai'];
            $where1['id'] = $where['pid'];
            $where1['proid'] = $where['proid'];
            if (!$task->where($where1)->save($map)){
                $table->where("id = '{$data['pid']}' and proid = '{$where['proid']}'")->delete();
            }
            $data['type'] = 'task_schedule';
            $data['addtime'] = time();
            $img = M('img');
            if ($file){
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
            }

            json('200','成功');
        }else {
            json('400','发布失败');
        }
    }

    //日任务加急提醒
    public function day_task_urgent(){
        $where['proid'] = $data['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('day_task');
        $res = $table->where($where)->find();
        if($res) {
            $map['title'] = date('Y-m-d',strtotime($res['starttime'])).'日任务加急提醒';
            $map['content'] = $res['title'];
            $map['type'] = 'day_task';
            $map['typeid'] = $res['id'];
            $map['user_id'] = $res['uid'];
            $map['proid'] = $where['proid'];
            $map['uid'] = $res['user_id'];
            $map['addtime'] = time();
            if (M('message')->add($map)) {
                $push['ids'] = $map['uid'];
                $push['type'] = $map['type'];
                $push['typeid'] = $map['typeid'];
                $push['content'] = '日任务加急提醒';
                send_curl($this->url.'/Api/Index/push',$push);
            }
            json('200','成功');
        }else{
            json('400','失败');
        }
    }

    //日任务详情
    public function day_task_info(){
        $where['t_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_day_task.id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('day_task');
        $data = $table->field('t_day_task.id,t_day_task.confirm,t_day_task.title,t_day_task.desc,t_day_task.state,t_day_task.bai,t_day_task.uid,t_admin.username,t_admin.simg,t_level.title name,t_day_task.user_id,a.username fusername,a.simg fsimg,l.title fname,t_building.title building,t_floor.title floor,IFNULL(t_area.title,"") area,t_day_task.starttime,t_day_task.stoptime,t_day_task.truestarttime,t_day_task.truestoptime,now() as time')
            ->join('left join t_building on t_building.id = t_day_task.building')
            ->join('left join t_floor on t_floor.id = t_day_task.floor')
            ->join('left join t_area on t_area.id = t_day_task.area')
            ->join('left join t_admin on t_admin.id = t_day_task.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->join('left join t_admin a on a.id = t_day_task.user_id')
            ->join('left join t_level l on l.id = a.level')
            ->where($where)->find();
        $workers = M('task_work');
        $data['workers'] = $workers->field('t_worker.title,t_task_work.num')
            ->join('left join t_worker on t_worker.id = t_task_work.uid')
            ->where("t_task_work.pid = '{$data['id']}' and t_task_work.proid = '{$where['t_day_task.proid']}'")->select();
        $data['workers'] =  $data['workers'] ?  $data['workers'] : array();
        $schedule = M('task_schedule');
        $data['schedule'] = $schedule->field('t_task_schedule.id,t_task_schedule.bai,FROM_UNIXTIME(t_task_schedule.addtime,"%Y-%m-%d %H:%i") datetime,t_task_schedule.uid,t_admin.username,t_admin.simg,t_level.title name')
            ->join('left join t_admin on t_admin.id = t_task_schedule.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where("t_task_schedule.proid = '{$where['t_day_task.proid']}' and t_task_schedule.pid = '{$where['t_day_task.id']}'")->order('t_task_schedule.addtime desc')->select();
        $img = M('img');
        foreach ($data['schedule'] as $key=>$val){
            $data['schedule'][$key]['img'] = $img->where("pid = '{$val['id']}' and type = 'task_schedule'")->getField('simg',true);
            $data['schedule'][$key]['img'] = $data['schedule'][$key]['img'] ? $data['schedule'][$key]['img'] : array();
        }
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','失败');
        }

    }

    //分包今日任务
    public function fenbao_day_task(){
        $type = I('post.type') ? I('post.type') : 1;
        $date = get_month_week_day();

        if ($type == 1){
            $map['state'] = array('neq',3);
        }else{
            $map['stoptime'] = array(array('egt',date('Y-m-d H:i',$date['beginday'])),array('elt',date('Y-m-d H:i',$date['endday'])));
            $map['state'] = 3;
        }
        $where['_complex'] = $map;
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['user_id'] = I('post.user_id') ? I('post.user_id') : json('404','缺少参数 user_id');
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

    //分包历史任务
    public function fenbao_all_day_task(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['user_id'] = I('post.user_id') ? I('post.user_id') : json('404','缺少参数 user_id');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        if (I('post.keyword')){
            $keyword = I('post.keyword');
            $where['content'] = array('like',"%{$keyword}%");
        }
        if (I('post.building')) $where['buildingid'] = I('post.building');
        if (I('post.starttime')) $where['starttime'] = array('egt',I('post.starttime'));
        if (I('post.stoptime')) $where['starttime'] = array('elt',I('post.stoptime'));
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

    //所有任务分布
    public function all_day_task_table(){
        $where['t_all_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        if (I('post.starttime')) $where['t_all_day_task.starttime'] = array('egt',I('post.starttime'));
        if (I('post.stoptime')) $where['t_all_day_task.starttime'] = array('elt',I('post.stoptime'));
        $table = M('all_day_task');
        $sum = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        //土建部门
        $arr = array();
        $where['t_admin.level'] = 15;
        $arr['title'] = '土建';
        $arr['count'] = $tujian = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        $arr['tu_bai'] = round($arr['count']/$sum,2);
        $where['t_all_day_task.state'] = 3;
        $count = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        $arr['bai'] = round($count/$arr['count'],2);
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);
        $data['groups'][] = $arr;
        //钢筋部门
        $arr = array();
        $where['t_admin.level'] = 14;
        $arr['title'] = '钢筋';
        $arr['count'] = $gangjin = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        $arr['tu_bai'] = round($arr['count']/$sum,2);
        $where['t_all_day_task.state'] = 3;
        $count = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        $arr['bai'] = round($count/$arr['count'],2);
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);
        $data['groups'][] = $arr;
        //机电部门
        $arr = array();
        $where['t_admin.level'] = 13;
        $arr['title'] = '机电';
        $arr['count'] = $jidian = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        $arr['tu_bai'] = round($arr['count']/$sum,2);
        $where['t_all_day_task.state'] = 3;
        $count = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        $arr['bai'] = round($count/$arr['count'],2);
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);
        $data['groups'][] = $arr;

        //土建内部
        $where['t_admin.level'] = 15;
        $data['tujian'] = $table->field('t_all_day_task.uid,t_all_day_task.username,t_all_day_task.simg,t_all_day_task.name,count(t_all_day_task.id) as count')
            ->join('left join t_admin on t_admin.id = t_all_day_task.uid')
            ->group('t_admin.id')
            ->where($where)->select();
        $where['t_all_day_task.state'] = 3;
        $count = $table->field('t_all_day_task.uid,count(t_all_day_task.id) as count')
            ->join('left join t_admin on t_admin.id = t_all_day_task.uid')
            ->group('t_admin.id')
            ->where($where)->select();
        foreach ($data['tujian'] as $key=>$val){
            $data['tujian'][$key]['tu_bai'] = round($val['count']/$tujian,2);
            foreach ($count as $value){
                if ($val['uid'] == $value['uid']){
                    $data['tujian'][$key]['bai'] = round($value['count']/$val['count'],2);
                }
            }
            if(!$data['tujian'][$key]['bai']) $data['tujian'][$key]['bai'] = 0;
        }
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);

        //钢筋内部
        $where['t_admin.level'] = 14;
        $data['gangjin'] = $table->field('t_all_day_task.uid,t_all_day_task.username,t_all_day_task.simg,t_all_day_task.name,count(t_all_day_task.id) as count')
            ->join('left join t_admin on t_admin.id = t_all_day_task.uid')
            ->group('t_admin.id')
            ->where($where)->select();
        $where['t_all_day_task.state'] = 3;
        $count = $table->field('t_all_day_task.uid,count(t_all_day_task.id) as count')
            ->join('left join t_admin on t_admin.id = t_all_day_task.uid')
            ->group('t_admin.id')
            ->where($where)->select();
        foreach ($data['gangjin'] as $key=>$val){
            $data['gangjin'][$key]['tu_bai'] = round($val['count']/$gangjin,2);
            foreach ($count as $value){
                if ($val['uid'] == $value['uid']){
                    $data['gangjin'][$key]['bai'] = round($value['count']/$val['count'],2);
                }
            }
            if(!$data['gangjin'][$key]['bai']) $data['gangjin'][$key]['bai'] = 0;
        }
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);

        //机电内部
        $where['t_admin.level'] = 13;
        $data['jidian'] = $table->field('t_all_day_task.uid,t_all_day_task.username,t_all_day_task.simg,t_all_day_task.name,count(t_all_day_task.id) as count')
            ->join('left join t_admin on t_admin.id = t_all_day_task.uid')
            ->group('t_admin.id')
            ->where($where)->select();
        $where['t_all_day_task.state'] = 3;
        $count = $table->field('t_all_day_task.uid,count(t_all_day_task.id) as count')
            ->join('left join t_admin on t_admin.id = t_all_day_task.uid')
            ->group('t_admin.id')
            ->where($where)->select();
        foreach ($data['jidian'] as $key=>$val){
            $data['jidian'][$key]['tu_bai'] = round($val['count']/$jidian,2);
            foreach ($count as $value){
                if ($val['uid'] == $value['uid']){
                    $data['jidian'][$key]['bai'] = round($value['count']/$val['count'],2);
                }
            }
            if(!$data['jidian'][$key]['bai']) $data['jidian'][$key]['bai'] = 0;
        }
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);

        json('200','成功',$data);
    }

    //延时任务分布
    public function delayed_day_task_table(){
        $where['_string'] = "(t_all_day_task.stoptime < now() and (t_all_day_task.state != 3)) or t_all_day_task.truestoptime > t_all_day_task.stoptime";
        $where['t_all_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        if (I('post.starttime')) $where['t_all_day_task.starttime'] = array('egt',I('post.starttime'));
        if (I('post.stoptime')) $where['t_all_day_task.starttime'] = array('elt',I('post.stoptime'));
        $table = M('all_day_task');
        $sum = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        //土建部门
        $arr = array();
        $where['t_admin.level'] = 15;
        $arr['title'] = '土建';
        $arr['count'] = $tujian = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        $arr['tu_bai'] = round($arr['count']/$sum,2);
        $count = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->sum('bai');
        $arr['bai'] = round($count/$arr['count'],2);
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);
        $data['groups'][] = $arr;
        //钢筋部门
        $arr = array();
        $where['t_admin.level'] = 14;
        $arr['title'] = '钢筋';
        $arr['count'] = $gangjin = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        $arr['tu_bai'] = round($arr['count']/$sum,2);
        $count = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->sum('bai');
        $arr['bai'] = round($count/$arr['count'],2);
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);
        $data['groups'][] = $arr;
        //机电部门
        $arr = array();
        $where['t_admin.level'] = 13;
        $arr['title'] = '机电';
        $arr['count'] = $jidian = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->count();
        $arr['tu_bai'] = round($arr['count']/$sum,2);
        $count = $table->join('left join t_admin on t_admin.id = t_all_day_task.uid')->where($where)->sum('bai');
        $arr['bai'] = round($count/$arr['count'],2);
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);
        $data['groups'][] = $arr;

        //土建内部
        $where['t_admin.level'] = 15;
        $data['tujian'] = $table->field('t_all_day_task.uid,t_all_day_task.username,t_all_day_task.simg,t_all_day_task.name,count(t_all_day_task.id) as count,sum(bai) as bai')
            ->join('left join t_admin on t_admin.id = t_all_day_task.uid')
            ->group('t_admin.id')
            ->where($where)->select();
        foreach ($data['tujian'] as $key=>$val){
            $data['tujian'][$key]['tu_bai'] = round($val['count']/$tujian,2);
            $data['tujian'][$key]['bai'] = round($val['bai']/$val['count'],2);
        }
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);

        //钢筋内部
        $where['t_admin.level'] = 14;
        $data['gangjin'] = $table->field('t_all_day_task.uid,t_all_day_task.username,t_all_day_task.simg,t_all_day_task.name,count(t_all_day_task.id) as count,sum(bai) as bai')
            ->join('left join t_admin on t_admin.id = t_all_day_task.uid')
            ->group('t_admin.id')
            ->where($where)->select();
        foreach ($data['gangjin'] as $key=>$val){
            $data['gangjin'][$key]['tu_bai'] = round($val['count']/$gangjin,2);
            $data['gangjin'][$key]['bai'] = round($val['bai']/$val['count'],2);
        }
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);

        //机电内部
        $where['t_admin.level'] = 13;
        $data['jidian'] = $table->field('t_all_day_task.uid,t_all_day_task.username,t_all_day_task.simg,t_all_day_task.name,count(t_all_day_task.id) as count,sum(bai) as bai')
            ->join('left join t_admin on t_admin.id = t_all_day_task.uid')
            ->group('t_admin.id')
            ->where($where)->select();
        foreach ($data['jidian'] as $key=>$val){
            $data['jidian'][$key]['tu_bai'] = round($val['count']/$jidian,2);
            $data['jidian'][$key]['bai'] = round($val['bai']/$val['count'],2);
        }
        unset($where['t_admin.level']);
        unset($where['t_all_day_task.state']);
        json('200','成功',$data);
    }

    //楼任务分布
    public function task_building_tongji(){
        $where['t_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_day_task.building'] = I('post.building') ? I('post.building') : json('404','缺少参数 building');
        $type = I('post.type') ? I('post.type') : 0;
        $date = get_month_week_day();
        if ($type == 1){
            $starttime = $date['beginyestoday'];
            $stoptime = $date['endyestoday'];
            $where['t_day_task.starttime'] = array(array('egt',date('Y-m-d H:i',$starttime)),array('elt',date('Y-m-d H:i',$stoptime)));
        }elseif ($type == 2){
            $starttime = $date['beginweek'];
            $stoptime = $date['endweek'];
            $where['t_day_task.starttime'] = array(array('egt',date('Y-m-d H:i',$starttime)),array('elt',date('Y-m-d H:i',$stoptime)));
        }elseif ($type == 3){
            $starttime = $date['beginmonth'];
            $stoptime = $date['endmonth'];
            $where['t_day_task.starttime'] = array(array('egt',date('Y-m-d H:i',$starttime)),array('elt',date('Y-m-d H:i',$stoptime)));
        }elseif($type == 4){
            $starttime = I('post.starttime') ? I('post.starttime') : json('404','缺少参数 starttime');
            $stoptime = I('post.stoptime') ? I('post.stoptime') : json('404','缺少参数 stoptime');
            $where['t_day_task.starttime'] = array(array('egt',$starttime),array('elt',$stoptime));
        }
        $table = M('day_task');
        $data = M('building')->field('id,title,area')->find($where['building']);
        $data['starttime'] = $starttime ? date('Y-m-d H:i',$starttime) : '';
        $data['stoptime'] = $stoptime ? date('Y-m-d H:i',$stoptime) : '';
        if ($data){
            $count = $table->where($where)->count();
            $where['state'] = 3;
            $cc = $table->where($where)->count();
            $data['bai'] = round($cc/$count,2);
            unset($where['state']);
            $data['floor'] = $table->field('t_day_task.floor,t_floor.title,count(t_day_task.floor) as count')
                ->join('left join t_floor on t_floor.id = t_day_task.floor')
                ->group('t_day_task.floor')
                ->where($where)->order('t_day_task.floor asc')->select();
            foreach ($data['floor'] as $key=>$val){
                $where['floor'] = $val['floor'];
                $where['state'] = 3;
                $data['floor'][$key]['cc'] = $table->where($where)->count();
            }
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //首页
    public function index(){
        $where['t_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $uid = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $date = get_month_week_day();
        $table = M('day_task');
        $time = date('Y/m月d日',time());
        $weekarray=array("日","一","二","三","四","五","六");
        $data['date'] = $time." 星期".$weekarray[date("w")];
        $data['task_count'] = $table->where("proid = '{$where['t_day_task.proid']}' and uid = '{$uid}' and state != 3")->count();
        $count = $table->where($where)->count();
        $where['state'] = 3;
        $cc = $table->where($where)->count();
        $res['title'] = '项目总进度';
        $res['bai'] = round($cc/$count,2);
        $data['task'][] = $res;
        $res = array();
        unset($where['state']);
        $starttime = $date['beginmonth'];
        $stoptime = $date['endmonth'];
        $where['t_day_task.starttime'] = array(array('egt',date('Y-m-d H:i',$starttime)),array('elt',date('Y-m-d H:i',$stoptime)));
        $count = $table->where($where)->count();
        $where['state'] = 3;
        $cc = $table->where($where)->count();
        $res['title'] = '本月计划完成度';
        $res['bai'] = round($cc/$count,2);
        $data['task'][] = $res;
        $res = array();
        unset($where['state']);
        $starttime = $date['beginweek'];
        $stoptime = $date['endweek'];
        $where['t_day_task.starttime'] = array(array('egt',date('Y-m-d H:i',$starttime)),array('elt',date('Y-m-d H:i',$stoptime)));
        $count = $table->where($where)->count();
        $where['state'] = 3;
        $cc = $table->where($where)->count();
        $res['title'] = '本周计划完成度';
        $res['bai'] = round($cc/$count,2);
        $data['task'][] = $res;
        $res = array();
        unset($where['state']);
        $starttime = $date['beginyestoday'];
        $stoptime = $date['endyestoday'];
        $where['t_day_task.starttime'] = array(array('egt',date('Y-m-d H:i',$starttime)),array('elt',date('Y-m-d H:i',$stoptime)));
        $count = $table->where($where)->count();
        $where['state'] = 3;
        $cc = $table->where($where)->count();
        $res['title'] = '昨日计划完成度';
        $res['bai'] = round($cc/$count,2);
        $data['task'][] = $res;
        json('200','成功',$data);
    }

    //日周月总计划总览
    public function all_task_tongji(){
        $where['t_day_task.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $type = I('post.type') ? I('post.type') : 0;
        $date = get_month_week_day();
        if ($type == 1){
            $starttime = $date['beginyestoday'];
            $stoptime = $date['endyestoday'];
            $where['t_day_task.starttime'] = array(array('egt',date('Y-m-d H:i',$starttime)),array('elt',date('Y-m-d H:i',$stoptime)));
        }elseif ($type == 2){
            $starttime = $date['beginweek'];
            $stoptime = $date['endweek'];
            $where['t_day_task.starttime'] = array(array('egt',date('Y-m-d H:i',$starttime)),array('elt',date('Y-m-d H:i',$stoptime)));
        }elseif ($type == 3){
            $starttime = $date['beginmonth'];
            $stoptime = $date['endmonth'];
            $where['t_day_task.starttime'] = array(array('egt',date('Y-m-d H:i',$starttime)),array('elt',date('Y-m-d H:i',$stoptime)));
        }else{
            if (I('post.statetime') && I('post.stoptime')){
                $starttime = I('post.statetime');
                $stoptime = I('post.stoptime');
                $where['t_day_task.starttime'] = array(array('egt',$starttime),array('elt',date($stoptime)));
            }
        }
        $table = M('day_task');
        $data['starttime'] = $starttime ? $starttime : '';
        $data['stoptime'] = $stoptime ? $stoptime : '';

        $count = $table->where($where)->count();
        $where['state'] = 3;
        $cc = $table->where($where)->count();
        $data['bai'] = round($cc/$count,2);
        unset($where['state']);
        $data['building'] = $table->field('t_day_task.building,t_building.title,t_building.area,count(t_day_task.building) as count')
            ->join('left join t_building on t_building.id = t_day_task.building')
            ->group('t_day_task.building')
            ->where($where)->order('t_day_task.building asc')->select();
        foreach ($data['building'] as $key=>$val){
            $where['building'] = $val['building'];
            $where['state'] = 3;
            $data['building'][$key]['cc'] = $table->where($where)->count();
            $data['building'][$key]['bai'] = round($data['building'][$key]['cc']/$val['count'],2);
        }
        json('200','成功',$data);

    }

    //获取天气
    function tianqi(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $city = M('pro')->where("id = $proid")->getField('city');
        $res = tianqi($city);
        if ($res['reason'] == 'successed!'){
            unset($res['result']['data']['realtime']['wind']['offset']);
            unset($res['result']['data']['realtime']['wind']['windspeed']);
            json('200','成功',$res['result']['data']['realtime']);
        }else{
            json('400','获取失败');
        }
    }

    //获取责任分包
    function get_qs_fenbao(){
        $where['t_admin.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_admin_qs.building'] = I('post.building') ? I('post.building') : json('404','缺少参数 building');
        $where['t_admin_qs.floor'] = I('post.floor') ? I('post.floor') : json('404','缺少参数 floor');
        $table = M('admin_qs');
        $data = $table->field('t_admin.id,t_admin.username,t_admin.simg')
            ->join('left join t_admin on t_admin_qs.uid = t_admin.id')
            ->where($where)->select();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有分包');
        }
    }

    //问题分类列表
    function get_issue(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $table = M('issue');
        $data = $table->field('id,title')->where("pid = 0")->select();
        foreach ($data as $key=>$val){
            $data[$key]['catid'] = $table->field('id,title')->where("pid = '{$val['id']}' and proid = '{$where['proid']}'")->select();
            foreach ($data[$key]['catid'] as $k=>$v){
                $data[$key]['catid'][$k]['catid'] = $table->field('id,title')->where("pid = '{$v['id']}' and proid = '{$where['proid']}'")->select();
            }
        }
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //获取层图纸
    function get_floor_simg(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['pid'] = I('post.building') ? I('post.building') : json('404','缺少参数 building');
        $where['id'] = I('post.floor') ? I('post.floor') : json('404','缺少参数 floor');
        $table = M('floor');
        $data['simg'] = $table->where($where)->getField('simg');
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //发布问题
    function add_qs(){
        $where['proid'] = $data['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $file = $_FILES ? $_FILES : json('400','至少传一张图片');
        $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['user_id'] = I('post.user_id') ? I('post.user_id') : json('404','缺少参数 user_id');
        $where['x'] = I('post.x') ? I('post.x') : json('404','缺少参数 x');
        $where['y'] = I('post.y') ? I('post.y') : json('404','缺少参数 y');
        $where['stoptime'] = I('post.stoptime') ? I('post.stoptime') : json('404','缺少参数 stoptime');
        if (checkTimeDate($where['stoptime'])){
            $where['stoptime'] = strtotime($where['stoptime']);
            if ($where['stoptime'] < time()) json('400','结束时间不能小于当前时间');
        }else{
            json('404','时间格式不正确');
        }
        $where['pid'] = I('post.pid') ? I('post.pid') : json('404','缺少参数 pid');
        $issue = M('issue');
        $res = $issue->field('id,title,pid')->where("id = '{$where['pid']}' and proid = '{$where['proid']}'")->find();
        $ress = $issue->field('id,title,pid')->where("id = '{$res['pid']}'")->find();
        $resss = $issue->field('id,title')->where("id = '{$ress['pid']}'")->find();
        $where['bid'] = $resss['id'];
        $where['building'] = I('post.building') ? I('post.building') : json('404','缺少参数 building');
        $where['floor'] = I('post.floor') ? I('post.floor') : json('404','缺少参数 floor');
        $where['area'] = I('post.area') ? I('post.area') : 0;
        $where['type'] = I('post.type') ? I('post.type') : 1;
        if ($_POST['title']) $where['title'] = $_POST['title'];
        $table = M('qs');
        $where['addtime'] = $data['addtime'] = time();
        $data['pid'] = $table->add($where);
        if ($data['pid']){
            $data['type'] = 'qs';
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
            $map['title'] = date('Y-m-d',$where['stoptime']).'质量安全问题';
            $map['content'] = $resss['title'].' '.$ress['title'].' '.$res['title'] ;
            $map['type'] = 'qs';
            $map['typeid'] = $data['pid'];
            $map['user_id'] = $where['uid'];
            $map['proid'] = $where['proid'];
            $map['uid'] = $where['user_id'];
            $map['addtime'] = time();
            if (M('message')->add($map)) {
                $push['ids'] = $map['uid'];
                $push['type'] = $map['type'];
                $push['typeid'] = $map['typeid'];
                $push['content'] = '质量安全问题';
                send_curl($this->url.'/Api/Index/push',$push);
            }
            if ($where['type'] > 1){
                $phone = M('admin')->where("id = '{$where['user_id']}'")->getField('phone');
                if ($phone){
                    sms($phone,$map['title'].',请尽快查看');
                }
            }
            json('200','成功');
        }else {
            json('400','发布失败');
        }
    }

    //问题列表
    function qs_list(){
        $where['proid'] = $data['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        if (I('post.bid')) $where['bid'] = I('post.bid');
        if (I('post.uid')){
            $where['uid'] = I('post.uid');
            $level = M('admin')->where("id = '{$where['uid']}'")->getField('level');
            if($level){
                $res = M('level')->where("id = '{$level}'")->getField('level');
            }
            if($res == 0){
                unset($where['uid']);
            }
        }
        if (I('post.user_id')) $where['user_id'] = I('post.user_id');
        if (I('post.building')) $where['buildingid'] = I('post.building');
        if (I('post.type')) $where['type'] = I('post.type');
        if (I('post.state')) $state = I('post.state');
        if (I('post.pid')){
            $pid = I('post.pid');
            $where['_string'] = "bid2 = $pid or bid3 = $pid";
        }
        if (I('post.starttime')){
            $starttime = I('post.starttime');
            if (checkTimeDate($starttime)){
                $starttime = strtotime($starttime);
                $where['stoptime'] = array('egt',$starttime);
            }else{
                json('404','时间格式不正确');
            }
        }
        if (I('post.stoptime')) {
            $stoptime = I('post.stoptime');
            if (checkTimeDate($stoptime)) {
                $stoptime = strtotime($stoptime);
                //if ($stoptime < $starttime) json('400', '开始时间不能大于结束时间');
                $where['addtime'] = array('elt',$stoptime);
            } else {
                json('404', '时间格式不正确');
            }
        }
        if ($state){
            if($state == 9){
                $where['state'] = array(array('eq',1),array('eq',2),'or');
            }else{
                $where['state'] = array(array('neq',5),array('eq',$state));
            }
        }else{
            $where['state'] = array('neq',5);
        }
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('all_qs');
        $data = $table->field('id,building,floor,area,type,issue,uid,username,simg,name,user_id,fusername,fsimg,fname,stoptime,state')
            ->where($where)->limit($pages,20)->order('addtime desc')->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //问题数量
    function qs_count(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        if (I('post.bid')) $where['bid'] = I('post.bid');
        if (I('post.uid')){
            $where['uid'] = I('post.uid');
            $level = M('admin')->where("id = '{$where['uid']}'")->getField('level');
            if($level){
                $res = M('level')->where("id = '{$level}'")->getField('level');
            }
            if($res == 0){
                unset($where['uid']);
            }
        }
        if (I('post.user_id')) $where['user_id'] = I('post.user_id');
        if (I('post.building')) $where['buildingid'] = I('post.building');
        if (I('post.type')) $where['type'] = I('post.type');
        if (I('post.pid')){
            $pid = I('post.pid');
            $where['_string'] = "bid2 = $pid or bid3 = $pid";
        }
        if (I('post.starttime')){
            $starttime = I('post.starttime');
            if (checkTimeDate($starttime)){
                $starttime = strtotime($starttime);
                $where['stoptime'] = array('egt',$starttime);
            }else{
                json('404','时间格式不正确');
            }
        }
        if (I('post.stoptime')) {
            $stoptime = I('post.stoptime');
            if (checkTimeDate($stoptime)) {
                $stoptime = strtotime($stoptime);
                //if ($stoptime < $starttime) json('400', '开始时间不能大于结束时间');
                $where['addtime'] = array('elt',$stoptime);
            } else {
                json('404', '时间格式不正确');
            }
        }
        $where['state'] = array('neq',5);
        $table = M('all_qs');
        $data['all'] = $table->field('id')->where($where)->count();
        $where['state'] = 1;
        $data['count1'] = $table->field('id')->where($where)->count();
        $where['state'] = 2;
        $data['count2'] = $table->field('id')->where($where)->count();
        $where['state'] = 3;
        $data['count3'] = $table->field('id')->where($where)->count();
        $where['state'] = 4;
        $data['count4'] = $table->field('id')->where($where)->count();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //质量安全问题统计
    function qs_tongji(){

        $type = I('post.type') ? I('post.type') : 0;
        $date = get_month_week_day();
        if ($type == 1){
            $starttime = $date['beginday'];
            $stoptime = $date['endday'];
            $where['addtime'] = array('elt',$stoptime);
            $where['stoptime'] = array('egt',$starttime);
        }elseif ($type == 2){
            $starttime = $date['beginweek'];
            $stoptime = $date['endweek'];
            $where['addtime'] = array('elt',$stoptime);
            $where['stoptime'] = array('egt',$starttime);
        }elseif ($type == 3){
            $starttime = $date['beginmonth'];
            $stoptime = $date['endmonth'];
            $where['addtime'] = array('elt',$stoptime);
            $where['stoptime'] = array('egt',$starttime);
        }elseif ($type == 4){
            $starttime = $date['beginyear'];
            $stoptime = $date['endyear'];
            $where['addtime'] = array('elt',$stoptime);
            $where['stoptime'] = array('egt',$starttime);
        }else{
            if (I('post.starttime')){
                $starttime = I('post.starttime');
                if (checkTimeDate($starttime)){
                    $starttime = strtotime($starttime);
                    $where['stoptime'] = array('egt',$starttime);
                }else{
                    json('404','时间格式不正确');
                }
            }
            if (I('post.stoptime')) {
                $stoptime = I('post.stoptime');
                if (checkTimeDate($stoptime)) {
                    $stoptime = strtotime($stoptime);
                    //if ($stoptime < $starttime) json('400', '开始时间不能大于结束时间');
                    $where['addtime'] = array('elt',$stoptime);
                } else {
                    json('404', '时间格式不正确');
                }
            }
        }
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        if (I('post.bid')) $where['bid'] = I('post.bid');
        $where['state'] = array('neq',5);
        $where['level'] = array('in','13,14,15');
        $table = M('all_qs');
        $data['starttime'] = $starttime ? date('Y-m-d H:i',$starttime) : '';
        $data['stoptime'] = $stoptime ? date('Y-m-d H:i',$stoptime) : '';
        $res = $table->field('id,state,level,stoptime')->where($where)->select();
        foreach ($res as $key => $val){
            $res[$key]['stoptime'] = date('Y-m-d H:i:s',$val['stoptime']);
        }
        if (!$res){
            json('400','没有数据');
        }

        $res0['type'] = 0; //类型
        $res1['type'] = 1;
        $res2['type'] = 2;
        $res3['type'] = 3;
        $res4['type'] = 4;
        $res13['type'] = $res113['type'] = $res213['type'] = $res313['type'] =  $res413['type'] = 13;
        $res14['type'] = $res114['type'] = $res214['type'] = $res314['type'] =  $res414['type'] = 14;
        $res15['type'] = $res115['type'] = $res215['type'] = $res315['type'] =  $res415['type'] = 15;

        $res0['count'] = $res1['count'] = $res2['count'] = $res3['count'] = $res4['count'] = $res113['count'] = $res114['count'] = $res115['count']
            = $res213['count'] = $res214['count'] = $res215['count'] = $res313['count'] = $res314['count'] = $res315['count'] = $res13['count'] = $res14['count'] = $res15['count']
            = $res413['count'] = $res414['count'] = $res415['count'] = 0;
        $res0['count'] = count($res); //总数
        $res0['bai'] = 1; //百分比
        foreach ($res as $val){
            if ($val['state'] == 1) $res1['count'] ++;
            if ($val['state'] == 2) $res2['count'] ++;
            if ($val['state'] == 3) $res3['count'] ++;
            if ($val['state'] == 4) $res4['count'] ++;
            if ($val['level'] == 13) $res13['count'] ++;
            if ($val['level'] == 14) $res14['count'] ++;
            if ($val['level'] == 15) $res15['count'] ++;
            if ($val['state'] == 1 && $val['level'] == 13) $res113['count'] ++;
            if ($val['state'] == 1 && $val['level'] == 14) $res114['count'] ++;
            if ($val['state'] == 1 && $val['level'] == 15) $res115['count'] ++;
            if ($val['state'] == 2 && $val['level'] == 13) $res213['count'] ++;
            if ($val['state'] == 2 && $val['level'] == 14) $res214['count'] ++;
            if ($val['state'] == 2 && $val['level'] == 15) $res215['count'] ++;
            if ($val['state'] == 3 && $val['level'] == 13) $res313['count'] ++;
            if ($val['state'] == 3 && $val['level'] == 14) $res314['count'] ++;
            if ($val['state'] == 3 && $val['level'] == 15) $res315['count'] ++;
            if ($val['state'] == 4 && $val['level'] == 13) $res413['count'] ++;
            if ($val['state'] == 4 && $val['level'] == 14) $res414['count'] ++;
            if ($val['state'] == 4 && $val['level'] == 15) $res415['count'] ++;
        }
        $data['all'][] = $res0;
        $res1['bai'] = round($res1['count']/$res0['count'],2);
        $data['all'][] = $res1;
        $res2['bai'] = round($res2['count']/$res0['count'],2);
        $data['all'][] = $res2;
        $res3['bai'] = round($res3['count']/$res0['count'],2);
        $data['all'][] = $res3;
        $res4['bai'] = round($res4['count']/$res0['count'],2);
        $data['all'][] = $res4;

        $res13['bai'] = round($res13['count']/$res0['count'],2);
        $data['distribution']['all'][] = $res13;
        $res14['bai'] = round($res14['count']/$res0['count'],2);
        $data['distribution']['all'][] = $res14;
        $res15['bai'] = round($res15['count']/$res0['count'],2);
        $data['distribution']['all'][] = $res15;

        $res113['bai'] = round($res113['count']/$res1['count'],2);
        $data['distribution']['type1'][] = $res113;
        $res114['bai'] = round($res114['count']/$res1['count'],2);
        $data['distribution']['type1'][] = $res114;
        $res115['bai'] = round($res115['count']/$res1['count'],2);
        $data['distribution']['type1'][] = $res115;

        $res213['bai'] = round($res213['count']/$res2['count'],2);
        $data['distribution']['type2'][] = $res213;
        $res214['bai'] = round($res214['count']/$res2['count'],2);
        $data['distribution']['type2'][] = $res214;
        $res215['bai'] = round($res215['count']/$res2['count'],2);
        $data['distribution']['type2'][] = $res215;

        $res313['bai'] = round($res313['count']/$res3['count'],2);
        $data['distribution']['type3'][] = $res313;
        $res314['bai'] = round($res314['count']/$res3['count'],2);
        $data['distribution']['type3'][] = $res314;
        $res315['bai'] = round($res315['count']/$res3['count'],2);
        $data['distribution']['type3'][] = $res315;

        $res413['bai'] = round($res413['count']/$res4['count'],2);
        $data['distribution']['type4'][] = $res413;
        $res414['bai'] = round($res414['count']/$res4['count'],2);
        $data['distribution']['type4'][] = $res414;
        $res415['bai'] = round($res415['count']/$res4['count'],2);
        $data['distribution']['type4'][] = $res415;


        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }


    //开始问题
    function qs_start(){
        $where['proid'] = $data['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['user_id'] = $data['uid'] = I('post.user_id') ? I('post.user_id') : json('404','缺少参数 user_id');
        $where['id'] = $data['pid'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('qs');
        $res = $table->field('uid,stoptime,state')->where($where)->find();
        if ($res){
            if ($res['state'] == 5) json('400','问题已取消');
            if (!$table->where($where)->setField('state',2)){
                json('400','重复操作');
            }
            $data['title'] = '确认开始';
            $data['addtime'] = time();
            if (M('qs_schedule')->add($data)){
                $map['title'] = date('Y-m-d',$res['stoptime']).'质量安全问题';
                $map['content'] = '质量安全问题确认开始';
                $map['type'] = 'qs';
                $map['typeid'] = $data['pid'];
                $map['user_id'] = $where['user_id'];
                $map['proid'] = $where['proid'];
                $map['uid'] = $res['uid'];
                $map['addtime'] = time();
                if (M('message')->add($map)) {
                    $push['ids'] = $map['uid'];
                    $push['type'] = $map['type'];
                    $push['typeid'] = $map['typeid'];
                    $push['content'] = '质量安全问题确认开始';
                    send_curl($this->url.'/Api/Index/push',$push);
                }
                json('200','成功');
            }else{
                json('400','操作失败');
            }
        }else{
            json('400','问题不存在');
        }
    }

    //问题完成提交
    function qs_confirm(){
        $where['proid'] = $data1['proid'] = $data['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['user_id'] = $data1['uid'] = I('post.user_id') ? I('post.user_id') : json('404','缺少参数 user_id');
        $where['id'] = $data1['pid'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $file = $_FILES ? $_FILES : json('400','至少传一张图片');
        if(isset($_POST['title'])) $data1['title'] = $_POST['title'];
        $table = M('qs');
        $res = $table->field('uid,stoptime')->where($where)->find();
        if ($res){
            if (!$table->where($where)->setField('state',3)){
                json('400','重复操作');
            }
            $data1['addtime'] = $data['addtime'] = time();
            $data['pid'] = M('qs_schedule')->add($data1);
            if ($data['pid']){
                $data['type'] = 'qs_schedule';
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
                $map['title'] = date('Y-m-d',$res['stoptime']).'质量安全问题';
                $map['content'] = '质量安全问题已完成，请查看';
                $map['type'] = 'qs';
                $map['typeid'] = $data1['pid'];
                $map['user_id'] = $where['user_id'];
                $map['proid'] = $where['proid'];
                $map['uid'] = $res['uid'];
                $map['addtime'] = time();
                if (M('message')->add($map)) {
                    $push['ids'] = $map['uid'];
                    $push['type'] = $map['type'];
                    $push['typeid'] = $map['typeid'];
                    $push['content'] = '质量安全问题已完成';
                    send_curl($this->url.'/Api/Index/push',$push);
                }
                json('200','成功');
            }else{
                json('400','操作失败');
            }
        }else{
            json('400','问题不存在');
        }
    }

    //问题不合格
    function qs_state(){
        $where['proid'] = $data1['proid'] = $data['proid']  = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['uid'] = $data1['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['id'] = $data1['pid'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $data1['title'] = I('post.title') ? I('post.title') : json('404','缺少参数 title');
        $file = $_FILES ? $_FILES : '';
        $table = M('qs');
        $res = $table->field('user_id,stoptime')->where($where)->find();
        if ($res){
            if (!$table->where($where)->setField('state',2)){
                json('400','重复操作');
            }
            $data1['addtime'] = $data['addtime'] = time();
            $data['pid'] = M('qs_schedule')->add($data1);
            if ($data['pid']){
                $map['title'] = date('Y-m-d',$res['stoptime']).'质量安全问题';
                $map['content'] = '质量安全问题不合格，请尽快查看';
                $map['type'] = 'qs';
                $map['typeid'] = $data1['pid'];
                $map['user_id'] = $where['uid'];
                $map['proid'] = $where['proid'];
                $map['uid'] = $res['user_id'];
                $map['addtime'] = time();
                if (M('message')->add($map)) {
                    $push['ids'] = $map['uid'];
                    $push['type'] = $map['type'];
                    $push['typeid'] = $map['typeid'];
                    $push['content'] = '质量安全问题不合格';
                    send_curl($this->url.'/Api/Index/push',$push);
                }
                if($file){
                    $data['type'] = 'qs_schedule';
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
                }
                json('200','成功');
            }else{
                json('400','操作失败');
            }
        }else{
            json('400','问题不存在');
        }
    }

    //问题核销
    function qs_finish(){
        $where['proid'] = $data1['proid'] = $int1['proid'] = $int2['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['uid'] = $data1['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['id'] = $data1['pid'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('qs');
        $res = $table->field('user_id,type,stoptime')->where($where)->find();
        if ($res){
            if (!$table->where($where)->setField('state',4)){
                json('400','重复操作');
            }
            $data1['title'] = '确认完成';
            $data1['addtime'] = time();
            if (M('qs_schedule')->add($data1)){
                $map['title'] = date('Y-m-d',$res['stoptime']).'质量安全问题';
                $map['content'] = '质量安全问题已完成';
                $map['type'] = 'qs';
                $map['typeid'] = $data1['pid'];
                $map['user_id'] = $where['uid'];
                $map['proid'] = $where['proid'];
                $map['uid'] = $res['user_id'];
                $map['addtime'] = $int1['addtime'] = $int2['addtime'] = time();
                if (M('message')->add($map)) {
                    if($res['stoptime'] >= $map['addtime']){
                        $ints = M('ints');
                        $int1['uid'] = $map['user_id'];
                        $int1['type'] = 'qs';
                        $int1['pid'] = $where['id'];
                        $int1['num'] = $res['type'];
                        $int1['bang'] = 1;
                        $ints->add($int1);
                        $int2['uid'] = $map['uid'];
                        $int2['type'] = 'qs';
                        $int2['pid'] = $where['id'];
                        $int2['num'] = $res['type'];
                        $int2['bang'] = 2;
                        $ints->add($int2);
                    }

                    $push['ids'] = $map['uid'];
                    $push['type'] = $map['type'];
                    $push['typeid'] = $map['typeid'];
                    $push['content'] = '质量安全问题已完成';
                    send_curl($this->url.'/Api/Index/push',$push);
                }
                json('200','成功');
            }else{
                json('400','操作失败');
            }
        }else{
            json('400','问题不存在');
        }
    }

    //问题取消
    function qs_del(){
        $where['proid'] = $data1['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['uid'] = $data1['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['id'] = $data1['pid'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('qs');
        $res = $table->field('user_id,stoptime,state')->where($where)->find();
        if ($res){
            if ($res['state'] != 1){
                json('400','问题已被确认，无法取消');
            }
            if (!$table->where($where)->setField('state',5)){
                json('400','重复操作');
            }
            $data1['title'] = '问题被取消';
            $data1['addtime'] = time();
            if (M('qs_schedule')->add($data1)){
                $map['title'] = date('Y-m-d',$res['stoptime']).'质量安全问题';
                $map['content'] = '质量安全问题已取消';
                $map['type'] = 'qs';
                $map['typeid'] = $data1['pid'];
                $map['user_id'] = $where['uid'];
                $map['proid'] = $where['proid'];
                $map['uid'] = $res['user_id'];
                $map['addtime'] = time();
                if (M('message')->add($map)) {
                    $push['ids'] = $map['uid'];
                    $push['type'] = $map['type'];
                    $push['typeid'] = $map['typeid'];
                    $push['content'] = '质量安全问题已取消';
                    send_curl($this->url.'/Api/Index/push',$push);
                }
                json('200','成功');
            }else{
                json('400','操作失败');
            }
        }else{
            json('400','问题不存在');
        }
    }

    //问题详情
    public function qs_info(){
        $where['proid'] = $data1['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = $data1['pid'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('all_qs');
        $data = $table->field('id,building,floor,area,x,y,title,issue,type,uid,username,simg,phone,name,user_id,fusername,fsimg,fphone,fname,stoptime,state')
            ->where($where)->order('addtime desc')->limit($pages,20)->find();
        $data['time'] = ''.time();
        if ($data){
            $floor = M('floor');
            $data['tuzhi'] = $floor->where("pid = '{$data['building']}' and id = '{$data['floor']}' and proid = '{$where['proid']}'")->getField('simg');
            $img = M('img');
            $data['img'] = $img->where("pid = '{$data['id']}' and type = 'qs' and proid = '{$where['proid']}'")->getField('simg',true);
            $schedule = M('qs_schedule');
            $data['schedule'] = $schedule->field('t_qs_schedule.id,t_qs_schedule.title,t_qs_schedule.addtime,t_qs_schedule.uid,t_admin.username,t_admin.simg,t_level.title name')
                ->join('left join t_admin on t_admin.id = t_qs_schedule.uid')
                ->join('left join t_level on t_admin.level = t_level.id')
                ->where("t_qs_schedule.pid = '{$data['id']}' and t_qs_schedule.proid = '{$where['proid']}'")->order('t_qs_schedule.addtime desc')->select();
            foreach ($data['schedule'] as $key=>$val){
                $data['schedule'][$key]['img'] = $img->where("pid = '{$val['id']}' and type = 'qs_schedule' and proid = '{$where['proid']}'")->getField('simg',true);
                $data['schedule'][$key]['img'] = $data['schedule'][$key]['img'] ? $data['schedule'][$key]['img'] : array();
            }
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //获取罚款分类
    public function get_find_group(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $data = M('find_group')->field('id,title')->select();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //罚款
    public function add_find(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['user_id'] = I('post.user_id') ? I('post.user_id') : json('404','缺少参数 user_id');
        $where['pid'] = I('post.pid') ? I('post.pid') : json('404','缺少参数 pid');
        $admin = M('admin');
        $user = $admin->field('id,username')->where("proid = '{$where['proid']}' and id = '{$where['user_id']}'")->find();
        if (!$admin) json('404','分包不存在');
        $where['price'] = I('post.price') ? I('post.price') : json('404','缺少参数 price');
        if (I('post.title')) $where['title'] = I('post.title');
        if ($where['price'] < 0) json('400','罚款金额必须大于零');
        $where['addtime'] = time();
        $table = M('find');
        if ($where['pid'] == 1){
            $where['state'] = 2;
            $res = $table->add($where);
            if ($res){
                if (!$admin->where("proid = '{$where['proid']}' and id = '{$where['user_id']}'")->setDec('money',$where['price'])){
                    $table->delete($res);
                    json('400','操作失败');
                }
                $message = M('message');
                //推送罚款人
                $map1['title'] = date('Y-m-d',time()).'罚款通知';
                $map1['content'] = '您被罚款'.$where['price'].'元';
                $map1['type'] = 'find';
                $map1['typeid'] = $res;
                $map1['user_id'] = $where['uid'];
                $map1['uid'] = $where['user_id'];
                $map1['proid'] = $where['proid'];
                $map1['addtime'] = time();

                if ($message->add($map1)){
                    $push1['ids'] = $map1['uid'];
                    $push1['type'] = $map1['type'];
                    $push1['typeid'] = $map1['typeid'];
                    $push1['content'] = '罚款通知';
                    send_curl($this->url.'/Api/Index/push',$push1);
                }

                //推送所有人
                $id = $admin->where("proid = '{$where['proid']}' and id != '{$where['user_id']}'")->getField('id',true);
                $ids = implode(',',$id);

                $map['title'] = date('Y-m-d',time()).'罚款公告';
                $map['content'] = $user['username'].'被罚款'.$where['price'].'元';
                $map['type'] = 'system';
                $map['typeid'] = 0;
                $map['user_id'] = 1;
                $map['proid'] = $where['proid'];
                $map['addtime'] = time();
                foreach ($id as $val){
                    $map['uid'] = $val;
                    $message->add($map);
                }
                $push['ids'] = $ids;
                $push['type'] = 'system';
                $push['typeid'] = 0;
                $push['content'] = '罚款公告';
                send_curl($this->url.'/Api/Index/push',$push);
                json('200','成功');
            }else{
                json('400','操作失败');
            }
        }else{
            $res = $table->add($where);
            if ($res){
                $message = M('message');
                //推送罚款人
                $map1['title'] = date('Y-m-d',time()).'罚款确认通知';
                $map1['content'] = '您被罚款'.$where['price'].'元，请您进行确认';
                $map1['type'] = 'find';
                $map1['typeid'] = $res;
                $map1['user_id'] = $where['uid'];
                $map1['uid'] = $where['user_id'];
                $map1['proid'] = $where['proid'];
                $map1['addtime'] = time();
                if ($message->add($map1)){
                    $push1['ids'] = $map1['uid'];
                    $push1['type'] = $map1['type'];
                    $push1['typeid'] = $map1['typeid'];
                    $push1['content'] = '罚款确认通知';
                    send_curl($this->url.'/Api/Index/push',$push1);
                }
                json('200','成功');
            }else{
                json('400','操作失败');
            }
        }
    }

    //我发布的罚款
    public function my_find_list(){
        $where['t_find.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_find.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('find');
        $data = $table->field("t_find.id,t_find.title,t_find_group.title as group_title,t_find.state,t_find.price,t_find.uid,t_admin.username,t_admin.simg,t_level.title name,t_find.user_id,a.username fusername,a.simg fsimg,l.title fname,t_find.addtime")
            ->join('left join t_find_group on t_find_group.id = t_find.pid')
            ->join('left join t_admin on t_admin.id = t_find.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->join('left join t_admin a on a.id = t_find.user_id')
            ->join('left join t_level l on l.id = a.level')
            ->where($where)->order('t_find.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //我被罚款的列表
    public function find_me_list(){
        $where['t_find.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_find.user_id'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('find');
        $data = $table->field("t_find.id,t_find.title,t_find_group.title as group_title,t_find.state,t_find.price,t_admin.username,t_admin.simg,t_level.title name,t_find.user_id,a.username fusername,a.simg fsimg,l.title fname,t_find.addtime")
            ->join('left join t_find_group on t_find_group.id = t_find.pid')
            ->join('left join t_admin on t_admin.id = t_find.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->join('left join t_admin a on a.id = t_find.user_id')
            ->join('left join t_level l on l.id = a.level')
            ->where($where)->order('t_find.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //分包确认罚款
    public function state_find(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        if (M('find')->where($where)->setField('state',2)){
            $res = M('find')->field('user_id,price')->where($where)->find();
            M('admin')->where("proid = '{$where['proid']}' and id = '{$res['user_id']}'")->setDec('money',$res['price']);
            json('200','成功');
        }else{
            json('400','重复操作');
        }
    }

    //撤销罚款
    public function del_find(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        if (M('find')->where($where)->setField('state',3)){
            json('200','成功');
        }else{
            json('400','重复操作');
        }
    }


    //获取紧急预警分类
    public function get_warning_group(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $data = M('warning_group')->field('id,title')->select();
        if ($data){
            json('200','成功',$data);
        }else {
            json('400','发布失败');
        }
    }

    //发布紧急预警
    public function add_warning(){
        $where['proid'] = $data['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $file = $_FILES;
        if($file['mp3']){
            $where['count'] = I('post.count') ? I('post.count') : json('404','缺少参数 count');
            $rand = '';
            for ($i=0;$i<6;$i++){
                $rand.=rand(0,9);
            }
            $type = explode('.', $file['mp3']['name']);
            $simg = date('YmdHis').$rand.'.'.end($type);
            $dir = date('Y-m-d');
            if (!is_dir('./Public/upfile/'.$dir)){
                mkdir('./Public/upfile/'.$dir,0777);
            }
            if (move_uploaded_file($file['mp3']['tmp_name'], './Public/upfile/'.$dir.'/'.$simg)){
                $where['mp3'] = '/Public/upfile/'.$dir.'/'.$simg;
            }
            unset($file['mp3']);
        }
        $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        if (I('post.title')) $where['title'] = I('post.title');
        $where['stoptime'] = I('post.stoptime') ? strtotime(I('post.stoptime')) : time();
        $where['pid'] = I('post.pid') ? I('post.pid') : json('404','缺少参数 pid');
        $table = M('warning');
        $where['addtime'] = $data['addtime'] = time();

        $data['pid'] = $table->add($where);
        if ($data['pid']){
            if ($file){
                $data['type'] = 'warning';
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
            }
            $ids = I('post.ids');
            $admin = M('admin');
            $user = M('warning_user');
            if ($ids){
                $push['ids'] = $ids;
                $res = explode(',',$ids);
            }else{
                $res = $admin->where("proid = '{$where['proid']}' and id != '{$where['uid']}'")->getField('id',true);
                $push['ids'] = $res;
            }
            $map['pid'] = $data['pid'];
            $map['proid'] = $data['proid'];
            foreach ($res as $val){
                $map['uid'] = $val;
                $user->add($map);
            }
            $push['type'] = 'warning';
            $push['typeid'] = $data['pid'];
            $push['content'] = date('Y-m-d',time()).'紧急预警';
            send_curl($this->url.'/Api/Index/push',$push);
//            $phone = M('admin')->where("id = '{$where['user_id']}'")->getField('phone');
//            if ($phone){
//                sms($phone,$push['content'].',请尽快查看！');
//            }
            json('200','成功');
        }else {
            json('400','发布失败');
        }
    }

    //紧急预警列表
    public function warning_list(){
        $where['t_warning_user.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_warning_user.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('warning_user');
        $data = $table->field('t_warning.id,t_warning.title,t_warning.uid,t_admin.username,t_admin.simg,t_level.title name,t_warning.stoptime,IFNULL(t_warning_user.state,"0") as state')
            ->join('left join t_warning on t_warning.id = t_warning_user.pid')
            ->join('left join t_admin on t_admin.id = t_warning.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where($where)->order('t_warning.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //我发布的紧急预警列表
    public function my_add_warning(){
        $where['t_warning.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['t_warning.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('warning');
        $data = $table->field('t_warning.id,t_warning.title,t_warning.uid,t_admin.username,t_admin.simg,t_level.title name,t_warning.stoptime,IFNULL(t_warning_user.state,"0") as state')
            ->join('left join t_admin on t_admin.id = t_warning.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->join('left join t_warning_user on t_warning.id = t_warning_user.pid and t_warning.uid = t_warning_user.uid')
            ->where($where)->order('t_warning.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //紧急预警首页
    public function warning_index(){
        $where['t_warning_user.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_warning_user.uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $table = M('warning_user');
        $data = $table->field('t_warning.title,t_warning.mp3,t_warning.addtime')
            ->join('left join t_warning on t_warning.id = t_warning_user.pid')
            ->where($where)->order('t_warning.addtime desc')->find();
        if ($data){
            $where['t_warning_user.state'] = 1;
            $data['count'] = $table->field('t_warning.id')
                ->join('left join t_warning on t_warning.id = t_warning_user.pid')
                ->where($where)->count();
            if (!$data['title']){
                if ($data['mp3']){
                    $data['title'] = '语音';
                }else{
                    $data['title'] = '图片';
                }
            }
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //确认紧急预警
    public function warning_state(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['pid'] = I('post.pid') ? I('post.pid') : json('404','缺少参数 pid');
        $table = M('warning_user');
        $map['state'] = 2;
        $map['addtime'] = time();
        $data = $table->where($where)->setField($map);
        if ($data){
            json('200','成功');
        }else{
            json('400','操作失败');
        }
    }

    //紧急预警详情
    public function warning_info(){
        $where['t_warning.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_warning.id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $uid = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $table = M('warning');
        $data = $table->field('t_warning.id,t_warning.title,t_warning.mp3,t_warning.count,t_warning_group.title as type,t_warning.uid,t_admin.username,t_admin.simg,t_level.title name,t_warning.stoptime')
            ->join("left join t_warning_user on t_warning.id = t_warning_user.pid and t_warning_user.uid = $uid")
            ->join('left join t_admin on t_admin.id = t_warning_user.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->join('left join t_warning_group on t_warning_group.id = t_warning.pid')
            ->where($where)->find();
        $data['state'] = M('warning_user')->where("uid = '{$uid}' and pid = '{$where['t_warning.id']}' and proid = '{$where['t_warning.proid']}'")->getField('state');
        $data['state'] = $data['state'] ? $data['state'] : '0';
        $img = M('img')->where("pid = '{$data['id']}' and type = 'warning' and proid = '{$where['t_warning.proid']}'")->getField('simg',true);
        $data['img'] = $img ? $img : array();
        $user = M('warning_user')->field('t_warning_user.uid,t_admin.username,t_admin.simg,t_level.title name,t_warning_user.state,t_warning_user.addtime')
            ->join('left join t_admin on t_admin.id = t_warning_user.uid')
            ->join('left join t_level on t_level.id = t_admin.level')
            ->where("t_warning_user.proid = '{$where['t_warning.proid']}' and t_warning_user.pid = '{$where['t_warning.id']}'")->order('t_warning_user.state desc,t_warning_user.addtime asc')->select();
        $data['user'] = $user ? $user : array();
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','没有数据');
        }
    }

    //单条消息设为已读
    public function state_one_message(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $message = M('message');
        $message->where($where)->setField('state',2);
        json('200','成功');
    }

    //工种列表
    public function work_list(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $table = M('worker');
        $data = $table->field('id,title')->select();
        json('200','成功',$data);
    }

    //点赞
    public function upper(){
        if (I('post.')){
            $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
            $where['uid'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
            $where['pid'] = I('post.pid') ? I('post.pid') : json('404','缺少参数 pid');
            $table = M('upper');
            if ($table->where($where)->find()){
                json('400','已赞过');
            }else {
                $where['addtime'] = time();
                if ($table->add($where)){
                    M('dynamic')->where("id = '{$where['pid']}'")->setInc('upper',1);
                    json('200','成功');
                }else {
                    json('400','操作失败');
                }
            }
        }
        json('404');
    }

    //我的金额
    public function user_money(){
        $where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('post.id') ? I('post.id') : json('404','缺少参数 id');
        $table = M('admin');
        $data = $table->field('money')->where($where)->find();
        json('200','成功',$data);
    }

    //我的罚款记录
    public function user_find_list(){
        $where['t_find.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['t_find.user_id'] = I('post.uid') ? I('post.uid') : json('404','缺少参数 uid');
        $where['t_find.state'] = 2;
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('find');
        $data = $table->field('t_find_group.title,t_find.price,t_find.addtime')
            ->join('left join t_find_group on t_find_group.id = t_find.pid')
            ->where($where)->order('t_find.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //方案列表
    public function word_list(){
        $where['t_word.proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        if (I('post.keyword')) $where['t_word.title'] = array('like','%'.$_POST['keyword'].'%');
        $page = I('post.page') ? I('post.page') : 1;
        $pages = ($page - 1)*20;
        $table = M('word');
        $data = $table->field('t_word.id,t_word.title,t_word.desc,t_word_group.title as group_name,t_word.addtime')
            ->join('left join t_word_group on t_word_group.id = t_word.pid')
            ->where($where)->order('t_word.addtime desc')->limit($pages,20)->select();
        if ($data){
            json('200','成功',$data);
        }elseif($pages > 1){
            json('400','已经是最后一页');
        }else{
            json('400','没有数据');
        }
    }

    //方案详情
    public function word_info(){
        //$where['proid'] = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $where['id'] = I('get.id') ? I('get.id') : json('404','缺少参数 id');
        $table = M('word');
        $data = $table->field('id,title,content')->where($where)->find();
        $data['content'] = $data['content'];
        $this->assign($data);
        $this->display('Index_newsweb');
    }

    //荣誉榜
    public function bang(){
        $proid = I('post.proid') ? I('post.proid') : json('404','缺少参数 proid');
        $type = I('post.type') ? I('post.type') : 1;
        $table = M('ints');
        if ($type == 1){
            $data = $table->field('t_admin.id,t_admin.username,t_admin.simg,t_level.title name,sum(t_ints.num) as num')
                ->join('left join t_admin on t_admin.id = t_ints.uid')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->group('t_ints.uid')
                ->where("t_ints.bang = 1 and t_ints.proid = '{$proid}'")->order('num desc')->select();
        }elseif ($type == 2){
            $data = $table->field('t_admin.id,t_admin.username,t_admin.simg,t_level.title name,sum(t_ints.num) as num')
                ->join('left join t_admin on t_admin.id = t_ints.uid')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->group('t_ints.uid')
                ->where("t_ints.bang = 2 and t_ints.proid = '{$proid}'")->order('num desc')->select();
        }else{
            $dynamic = M('dynamic');
            $data = $dynamic->field('t_admin.id,t_admin.username,t_admin.simg,t_level.title name,count(t_img.id) as num')
                ->join('left join t_admin on t_admin.id = t_dynamic.uid')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->join('left join t_img on t_img.pid = t_dynamic.id and t_img.type = "dynamic"')
                ->group('t_dynamic.uid')
                ->where("t_dynamic.proid = '{$proid}'")->order('num desc')->select();
        }
        if ($data){
            json('200','成功',$data);
        }else{
            json('400','暂时还没有人入榜');
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