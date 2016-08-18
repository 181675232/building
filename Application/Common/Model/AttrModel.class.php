<?php
namespace Common\Model;
use Think\Model;

class AttrModel extends Model{
	
	public function group_info($bid = 0){
		$nav = M('attr');
		$arr = array();
		$data['pid'] = $bid;
		$result = $nav->where($data)->order('ord asc,id asc')->select();
		$arr = $result;
 		if ($result){
			foreach ($result as $key => $val){			
				if ($val['id'] != null){			
					$arr[$key]['catid'] = $this->group_info($val['id']);
				}else {
					return array();
				}
			}		
			return $arr;
 		}else {
 			return array();
 		}	
	}
	
}