<?php
namespace Api\Model;
use Think\Model;

class TaskModel extends Model{
    public function task_tree($pid = 0){
        static  $arr = array(); //使用static代替global
        $table = M('task');
        $result = $table->field('t_task.id,t_task.title,t_task.starttime,t_task.stoptime,t_task.day,IFNULL(t.title,"") as btitle')
            ->join('left join t_task as t on t.id = t_task.bid')
            ->where("t_task.pid = $pid")->order('t_task.id asc')->select();
        if ($result){
            foreach ($result as $key => $val){
                if ($table->where("pid = '{$val['id']}'")->find()){
                    $this->task_tree($val['id']);
                }else{
                    $arr[] = $val;
                }
            }
            return $arr;
        }
    }
	
}