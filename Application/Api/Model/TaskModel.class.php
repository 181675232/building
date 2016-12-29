<?php
namespace Api\Model;
use Think\Model;

class TaskModel extends Model{
    public function task_tree($pid = 0,$proid = 1){
        static  $arr = array(); //使用static代替global
        $table = M('task');
        $result = $table->field('t_task.id,t_task.title,t_task.starttime,t_task.stoptime,t_task.day,IFNULL(t.title,"") as btitle')
            ->join('left join t_task as t on t.id = t_task.bid')
            ->where("t_task.pid = $pid and t_task.proid = $proid")->order('t_task.id asc')->select();
        print_r($result);
        exit;
        if ($result){
            foreach ($result as $key => $val){
                if ($table->where("pid = '{$val['id']}' and proid = $proid")->find()){
                    $this->task_tree($val['id']);
                }else{
                    $arr[] = $val;
                }
            }
            return $arr;
        }
    }

    public function month_task_tree($pid = 0,$proid = 1){
        static  $arr = array(); //使用static代替global
        $table = M('month_task');
        $result = $table->field('t_month_task.id,t_month_task.title,t_month_task.starttime,t_month_task.stoptime,t_month_task.day,IFNULL(t.title,"") as btitle')
            ->join('left join t_month_task as t on t.id = t_month_task.bid')
            ->where("t_month_task.pid = $pid and t_month_task.proid = $proid")->order('t_month_task.id asc')->select();
        if ($result){
            foreach ($result as $key => $val){
                if ($table->where("pid = '{$val['id']}' and proid = $proid")->find()){
                    $this->month_task_tree($val['id']);
                }else{
                    $arr[] = $val;
                }
            }
            return $arr;
        }
    }

    public function week_task_tree($pid = 0,$proid = 1){
        static  $arr = array(); //使用static代替global
        $table = M('week_task');
        $result = $table->field('t_week_task.id,t_week_task.title,t_week_task.starttime,t_week_task.stoptime,t_week_task.day,IFNULL(t.title,"") as btitle')
            ->join('left join t_week_task as t on t.id = t_week_task.bid')
            ->where("t_week_task.pid = $pid and t_week_task.proid = $proid")->order('t_week_task.id asc')->select();
        if ($result){
            foreach ($result as $key => $val){
                if ($table->where("pid = '{$val['id']}' and proid = $proid")->find()){
                    $this->week_task_tree($val['id']);
                }else{
                    $arr[] = $val;
                }
            }
            return $arr;
        }
    }

}