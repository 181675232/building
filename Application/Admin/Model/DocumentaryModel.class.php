<?php
namespace Admin\Model;
use Think\Model;

class DocumentaryModel extends Model {

    //入库基本验证
    protected $_validate = array(
        //产品长度不合法
        //array('product', '2,20', '产品长度不合法', self::EXISTS_VALIDATE, 'length', self::MODEL_INSERT),
    );
	
	//客户表自动完成
    protected $_auto = array(
        array('create_time', 'get_time', self::MODEL_INSERT, 'function'),
        array('d_date', 'get_time', self::MODEL_BOTH, 'function'),
    );
	
    //获取产品列表
    public function getList($page, $rows, $order, $sort, $keywords, $date, $date_from, $date_to, $neg=false) {
        $map = array();
        $keywords_map = array();
        $date_map = array();

        //如果有关键字，进行组装
        if ($keywords) {
            $keywords_map['title'] = array('like', '%'.$keywords.'%');
            $keywords_map['company'] = array('like', '%'.$keywords.'%');
            $keywords_map['d_name'] = array('like', '%'.$keywords.'%');
            $keywords_map['_logic'] = 'OR';
        }


        if ($date_from && $date_to) {
            $map["$date"] = array(array('egt', date($date_from)), array('elt', date($date_to)));
        } else if ($date_from) {
            $map["$date"] = array('egt', date($date_from));
        } else if ($date_to) {
            $map["$date"] = array('elt', date($date_to));
        }

        //把关键字SQL组入$map
        if (!empty($keywords_map)) {
            $map['_complex'] = $keywords_map;
        }

        //把创建时间SQL组入$map
        if (!empty($date_map)) {
            $map["$date"] = $date_map["$date"];
        }

        //是否只获取谈判中的跟单
        if ($neg) {
            $map['evolve'] = '谈判中';
        }

        $object = $this->field('id,sn,title,company,way,evolve,next_contact,d_name,d_date,
											remark,enter,create_time')
                       ->where($map)
                       ->order(array($sort=>$order))
                       ->limit(($rows * ($page - 1)), $rows)
                       ->select();

        return array(
            'total'=>$this->count(),
            'rows'=>$object ? $object : '',
        );
    }


    //添加跟单
    public function register($title, $cid, $sid, $company, $d_name, $way, $evolve, $next_contact, $remark) {
        $data = array(
            'title'=>$title,
            'cid'=>$cid,
            'sid'=>$sid,
            'company'=>$company,
            'd_name'=>$d_name,
            'way'=>$way,
            'evolve'=>$evolve,
            'next_contact'=>$next_contact,
            'remark'=>$remark,
            'sn'=>get_time_string(),
            'enter'=>session('admin')['name']
        );

        if ($this->create($data)) {
            $cid = $this->add();
            return $cid ? $cid : 0;
        } else {
            return $this->getError();
        }
    }

	//删除产品
    public function remove($ids) {
        return $this->delete($ids);
    }
	
    //获取一条跟单信息
    public function getDocumentary($id) {
        $map['id'] = $id;
        return $this->field('id,title,sid,cid,company,d_name,way,evolve,next_contact,d_date,enter,remark')->where($map)->find();
    }

    //修改客户信息
    public function update($id, $title, $cid, $sid, $company, $d_name, $way, $evolve, $next_contact, $remark) {
        $data = array(
            'id'=>$id,
            'title'=>$title,
            'cid'=>$cid,
			'sid'=>$sid,
			'company'=>$company,
            'd_name'=>$d_name,
			'way'=>$way,
            'evolve'=>$evolve,
            'next_contact'=>$next_contact,
            'remark'=>$remark,
			'enter'=>session('admin')['name']
        );

        if ($this->create($data)) {
            $cid = $this->save();
            return $cid ? $cid : 0;
        } else {
            return $this->getError();
        }
    }

    //获取单个跟单详情
    public function getDetails($id) {
        $map['id'] = $id;
        $object = $this->field('id,sid,cid,company,way,evolve,next_contact,d_name,d_date,remark,enter,create_time')->where($map)->find();
        return $object;
    }

}