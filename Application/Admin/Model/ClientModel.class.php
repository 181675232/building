<?php
namespace Admin\Model;
use Think\Model;

class ClientModel extends Model {

    //入库基本验证
    protected $_validate = array(
        //产品长度不合法
        //array('product', '2,20', '产品长度不合法', self::EXISTS_VALIDATE, 'length', self::MODEL_INSERT),
    );
	
	//客户表自动完成
    protected $_auto = array(
        array('create_time', 'get_time', self::MODEL_INSERT, 'function'),
    );
	
    //获取产品列表
    public function getList($page, $rows, $order, $sort, $keywords, $date, $date_from, $date_to) {
        $map = array();
        $keywords_map = array();
        $date_map = array();

        //如果有关键字，进行组装
        if ($keywords) {
            $keywords_map['company'] = array('like', '%'.$keywords.'%');
            $keywords_map['name'] = array('like', '%'.$keywords.'%');
            $keywords_map['tel'] = array('like', '%'.$keywords.'%');
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

        $object = $this->field('id,company,name,tel,
                                enter,source,create_time')
                       ->where($map)
                       ->order(array($sort=>$order))
                       ->limit(($rows * ($page - 1)), $rows)
                       ->select();

        return array(
            'total'=>$this->count(),
            'rows'=>$object ? $object : '',
        );
    }


    //添加档案
    public function register($company, $name, $tel, $source) {
        $data = array(
            'company'=>$company,
            'name'=>$name,
            'tel'=>$tel,
            'source'=>$source,
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
	
    //获取一条客户信息
    public function getPost($id) {
        $map['id'] = $id;
        return $this->field('id,company,name,tel,enter,source,create_time')->where($map)->find();
    }

    //修改客户信息
    public function update($id, $name, $company, $tel, $source) {
        $data = array(
            'id'=>$id,
            'name'=>$name,
			'company'=>$company,
			'tel'=>$tel,
			'source'=>$source,
			'enter'=>session('admin')['name']
        );

        if ($this->create($data)) {
            $cid = $this->save();
            return $cid ? $cid : 0;
        } else {
            return $this->getError();
        }
    }

    //获取单个客户详情
    public function getDetails($id) {
        $map['id'] = $id;
        $object = $this->field('id,company,name,tel,source,enter,create_time')->where($map)->find();
        return $object;
    }

}