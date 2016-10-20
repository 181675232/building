<?php
namespace Admin\Model;
use Think\Model;

class NavModel extends Model {

    //获取菜单导航
//    public function getNav($id = 0) {
//        $map['nid'] = $id;
//        $object = $this->field('id,text,state,url,iconCls')->where($map)->select();
//        return $object ? $object : '';
//    }

    public function getNav($bid) {
        $object = $this->field('id,title as text,url,pid')->where("bid = $bid")->select();
        $tree = array();

        //先筛选出根节点
        foreach ($object as $key=>$value) {
            if ($value['pid'] == $bid) {
                $tree[] = $value;
            }
        }

        //将子节点合并到对应的根节点
        foreach ($tree as $treeKey=>$treeValue) {
            foreach ($object as $objectKey=>$objectValue) {
                if ($treeValue['id'] == $objectValue['pid']) {
                    $tree[$treeKey]['children'][] = $objectValue;
                }
            }
        }
        return $tree;
    }

}