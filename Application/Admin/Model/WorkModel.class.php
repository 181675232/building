<?php
namespace Admin\Model;
use Think\Model;

class WorkModel extends Model {

    //入库基本验证
    protected $_validate = array(
        //产品长度不合法
        //array('product', '2,20', '产品长度不合法', self::EXISTS_VALIDATE, 'length', self::MODEL_INSERT),
    );
	
	//客户表自动完成
    protected $_auto = array(
        //array('create_time', 'get_time', self::MODEL_INSERT, 'function'),
    );
	
    //获取产品列表
    public function getList($page, $rows, $order, $sort, $keywords, $date, $date_from, $date_to, $state, $type) {
        $map = array();
        $keywords_map = array();
        $date_map = array();

        //如果有关键字，进行组装
        if ($keywords) {
            $keywords_map['title'] = array('like', '%'.$keywords.'%');
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


        //状态
        switch ($state) {
            case '进行中' :
                $map['state'] = array('eq', '进行中');
                break;
            case '已完成' :
                $map['state'] = array('eq', '已完成');
                break;
            case '作废' :
                $map['state'] = array('eq', '作废');
                break;
            default :
                //默认排除作废的
                $map['state'] = array('neq', '作废');
        }

        //类型
        if ($type) {
            $map['type'] = $type;
        }


        $object = $this->field('id,title,type,stage,state,user,
                                start_date,end_date,create_time')
                       ->where($map)
                       ->order(array($sort=>$order))
                       ->limit(($rows * ($page - 1)), $rows)
                       ->select();

        return array(
            'total'=>$this->count(),
            'rows'=>$object ? $object : '',
        );
    }


    //添加工作计划
    public function register($title, $type, $start_date, $end_date) {
        $data = array(
            'title'=>$title,
            'type'=>$type,
            'start_date'=>$start_date,
            'end_date'=>$end_date,
            'stage'=>'创建工作任务',
            'state'=>'进行中',
            'user'=>session('admin')['name'],
            'create_time'=>get_time()
        );

        if ($this->create($data)) {
            $id = $this->add();

            if ($id) {
                //同时写入到附表中的完成进度
                M('workExtend')->add(array(
                    'work_id'=>$id,
                    'stage'=>'创建工作任务',
                    'create_time'=>get_time()
                ));
                return $id;
            } else {
                return 0;
            }
        } else {
            return $this->getError();
        }
    }

	//作废工作计划
    public function cancel($ids) {
        return $this->save(array('id'=>array('in', $ids), 'state'=>'作废'));
    }
	
    //获取一条工作计划
    public function getOne($id) {
        $map['id'] = $id;
        return $this->field('id,title,type,stage,state,start_date,end_date,create_time')->where($map)->find();
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

    //获取完成阶段
    public function getStage($id) {
        $map['work_id'] = $id;
        return M('workExtend')->field('stage,create_time')->where($map)->select();
    }

    //添加工作阶段
    public function addStage($work_id, $state) {
        return M('workExtend')->add(array(
            'work_id'=>$work_id,
            'stage'=>$state,
            'create_time'=>get_time()
        ));
    }

    //设置完成阶段
    public function finish($work_id) {
        if ($this->addStage($work_id, '工作计划完成')) {
            return $this->save(array(
                'id'=>$work_id,
                'state'=>'已完成'
            ));
        } else {
            return 0;
        }
    }

}