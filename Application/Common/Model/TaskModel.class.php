<?php
namespace Common\Model;
use Think\Model;

class TaskModel extends Model{

    //递归获取最底层
    public function recursion( $pid = 0){
        $table = M('task');
        $arr = array();
        $result = $table->where("pid = $pid")->order('id asc')->select();
        $arr = $result;
        if ($result){
            foreach ($result as $key => $val){
                if ($val['id'] != null){
                    $arr[$key]['catid'] = $this->recursion($val['id']);
                }else{

                }
            }
            return $arr;
        }else {
            return array();
        }
    }

	public function nav_info($bid = 0,$state = 0){
		$nav = M('Nav');
		$role = M('access');
		$arr = array();
		if ($state == 0){
			$result = $nav->where("bid = $bid and level !=3")->order('id asc')->select();
		}else {
			$data['bid'] = $bid;
			$data['state'] = $state;
			$data['level'] = array('neq',3);
			$result = $nav->where($data)->order('id asc')->select();
		}		
		$arr = $result;
 		if ($result){
			foreach ($result as $key => $val){			
				if ($val['id'] != null){
					if ($_SESSION['username']!=C('RBAC_SUPERADMIN')){
						if ($bid != 0){	
							$data = $role->field('t_access.role_id')
							->join('left join t_role_user on t_access.role_id = t_role_user.role_id')
							->where("t_role_user.user_id = '{$_SESSION['userid']}' and t_access.node_id = '{$val['id']}'")->find();							
							if (!$data){
								$arr[$key] = array();							
								continue;
							}
						}
					}				
					$arr[$key]['catid'] = $this->nav_info($val['id'],$state);
				}
			}		
			return $arr;
 		}else {
 			return array();
 		}	
	}
	
	public function nav_role($bid = 0,$state = 0){
		$nav = M('Nav');
		$arr = array();
		if ($state == 0){
			$result = $nav->where("bid = $bid and level !=3")->order('id asc')->select();
			foreach ($result as $key=>$val){
				$result[$key]['role'] = $nav->where("bid = '{$val['id']}' and level =3")->select();
			}
		}else {
			$data['bid'] = $bid;
			$data['state'] = $state;
			$data['level'] = array('neq',3);
			$result = $nav->where($data)->order('id asc')->select();
		}
		$arr = $result;
		if ($result){
			foreach ($result as $key => $val){
				if ($val['id'] != null){
					$arr[$key]['catid'] = $this->nav_role($val['id'],$state);
				}
			}
			return $arr;
		}else {
			return array();
		}
	}
	
}