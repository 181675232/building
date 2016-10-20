<?php
namespace Admin\Model;
use Think\Model;

class ReceiptModel extends Model {

    //入库基本验证
    protected $_validate = array(
        //产品长度不合法
        //array('product', '2,20', '产品长度不合法', self::EXISTS_VALIDATE, 'length', self::MODEL_INSERT),
    );

	
    //获取产品列表
    public function getList($page, $rows, $order, $sort, $keywords, $date, $date_from, $date_to) {
        $map = array();
        $keywords_map = array();
        $date_map = array();

        //如果有关键字，进行组装
        if ($keywords) {
            $keywords_map['sn'] = array('like', '%'.$keywords.'%');
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

        $object = $this->field('id,sn,order_title,way,remark,enter,create_time,order_amount')
                       ->where($map)
                       ->order(array($sort=>$order))
                       ->limit(($rows * ($page - 1)), $rows)
                       ->select();

        return array(
            'total'=>$this->count(),
            'rows'=>$object ? $object : '',
        );
    }


    //添加订单
    public function register($order_id, $order_title, $order_amount, $way, $remark) {
        $data = array(
            'order_id'=>$order_id,
            'order_title'=>$order_title,
            'order_amount'=>$order_amount,
            'way'=>$way,
            'remark'=>$remark,
            'sn'=>get_time_string(),
            'enter'=>session('admin')['name']
        );

        if ($this->create($data)) {
            $data['create_time'] = get_time();
            $id = $this->add($data);
            if ($id) {
                $map['id'] = $order_id;
                $update = array(
                    'pay_state'=>'已付款'
                );
                M('Order')->where($map)->save($update);
                return $id;
            } else {
                return 0;
            }
        } else {
            return $this->getError();
        }
    }

    //删除产品
    public function remove($ids) {
        return $this->relation('Extend')->delete($ids);
    }
	
    //获取一条信息
    public function getOne($id) {
        $map['crm_order.id'] = $id;
        $object =  $this->relation('Extend')
                    ->field('crm_order.id,crm_order.title,crm_order.documentary_id,crm_order.amount,crm_documentary.title AS d_title')
                    ->join('crm_documentary ON crm_order.documentary_id=crm_documentary.id', 'LEFT')
                    ->where($map)
                    ->find();
        $object['Extend']['details'] = htmlspecialchars_decode($object['Extend']['details']);
        return $object;
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
        $map['crm_order.id'] = $id;
        $object =  $this->relation('Extend')
            ->field('crm_order.id,crm_order.sn,crm_order.title,crm_order.documentary_id,crm_order.amount,crm_documentary.title AS d_title')
            ->join('crm_documentary ON crm_order.documentary_id=crm_documentary.id', 'LEFT')
            ->where($map)
            ->find();
        $object['Extend']['details'] = htmlspecialchars_decode($object['Extend']['details']);
        return $object;
    }

}