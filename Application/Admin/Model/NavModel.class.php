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

    public function getNav($bid = 0) {
        if(session('admin')['id'] == 1){
            $where['state'] = 1;
            $object = $this->field('id,simg,title as text,url,pid')->where($where)->order('ord asc,id asc')->select();
        }else{
            $id = session('admin')['id'];
            $str = M('admin')
                ->join('left join t_level on t_level.id = t_admin.level')
                ->where("t_admin.id = '{$id}'")->getField('t_level.m');

            $where['id'] = array('in',$str);
            $where['state'] = 1;
            $object = $this->field('id,simg,title as text,url,pid')->where($where)->order('ord asc,id asc')->select();
        }

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
                if ($treeValue['id'] == $objectValue['pid']){
                    foreach ($object as $k=>$v){
                        if ($objectValue['id'] == $v['pid']){
                            $objectValue['children'][] = $v;
                        }
                    }
                    $tree[$treeKey]['children'][] = $objectValue;
                }
            }
        }


        return $tree;
    }

    public function getRole($bid = 0) {
        $object = $this->field('id,simg,title as text,url,pid')->where("state = 1")->order('ord asc,id asc')->select();
        $tree = array();

        $rule = M('rule');
        //先筛选出根节点
        foreach ($object as $key=>$value) {
            if ($value['pid'] == $bid) {
                $value['role'] = $rule->field('id,title')->where("pid = '{$value['id']}'")->order('id asc')->select();
                $tree[] = $value;
            }
        }

        //将子节点合并到对应的根节点
        foreach ($tree as $treeKey=>$treeValue) {
            foreach ($object as $objectKey=>$objectValue) {
                if ($treeValue['id'] == $objectValue['pid']){
                    $objectValue['role'] = $rule->field('id,title')->where("pid = '{$objectValue['id']}'")->order('id asc')->select();
                    foreach ($object as $k=>$v){
                        if ($objectValue['id'] == $v['pid']){
                            $v['role'] = $rule->field('id,title')->where("pid = '{$v['id']}'")->order('id asc')->select();
                            $objectValue['children'][] = $v;
                        }
                    }
                    $tree[$treeKey]['children'][] = $objectValue;
                }
            }
        }


        return $tree;
    }

}