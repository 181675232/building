<?php
namespace Admin\Model;
use Think\Model;

class PostModel extends Model {

    //职位信息自动验证
    protected $_validate = array(
        //职位名称长度不合法！
        array('name', '2,20', '帐号长度不合法', self::EXISTS_VALIDATE, 'length', self::MODEL_INSERT),
        //职位名称已存在
        array('name', '', '职位名称已存在', self::EXISTS_VALIDATE, 'unique', self::MODEL_BOTH),
    );

    //职位表自动完成
    protected $_auto = array(
        array('create_time', 'get_time', self::MODEL_INSERT, 'function'),
    );

    //获取职位列表
    public function getList($page, $rows, $order, $sort, $name, $date, $date_from, $date_to) {
        $map = array();

        if ($name) {
            $map['name'] = array('like', '%'.$name.'%');
        }

        if ($date_from && $date_to) {
            $map["$date"] = array(array('egt', date($date_from)), array('elt', date($date_to.' 23:59:59')));
        } else if ($date_from) {
            $map["$date"] = array('egt', date($date_from));
        } else if ($date_to) {
            $map["$date"] = array('elt', date($date_to.' 23:59:59'));
        }

        $object = $this->field('id,name,create_time')
                       ->where($map)
                       ->order(array($sort=>$order))
                       ->limit(($rows * ($page - 1)), $rows)
                       ->select();

        return array(
            'total'=>$this->count(),
            'rows'=>$object ? $object : '',
        );
    }

    //获取所有职位
    public function getListAll() {
        return $this->field('id,name')->select();
    }

    //添加职位
    public function register($name) {
        $data = array(
            'name'=>$name
        );

        if ($this->create($data)) {
            $pid = $this->add();
            return $pid ? $pid : 0;
        } else {
            if ($this->getError() == '职位名称已存在') {
                return -1;
            }
            return $this->getError();
        }
    }

    //获取一条职位信息
    public function getPost($id) {
        $map['id'] = $id;
        return $this->field('id,name')->where($map)->find();
    }

    //修改职位
    public function update($id, $name) {
        $data = array(
            'id'=>$id,
            'name'=>$name
        );

        if ($this->create($data)) {
            $uid = $this->save();
            return $uid ? $uid : 0;
        } else {
            if ($this->getError() == '职位名称已存在') {
                return -1;
            }
            return $this->getError();
        }
    }

    //删除职位
    public function remove($ids) {
        return $this->delete($ids);
    }

}