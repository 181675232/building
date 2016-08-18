<?php
namespace Common\Model;
use Think\Model;

class UserModel extends Model{
	protected $_validate = array(		
		array('title','require','昵称必须填写！'), 
	);
	
	protected $_auto =  array( 
//       array('name','getName',3,'callback'), // 对name字段在新增和编辑的时候回调getName方法
         array('create_ts','strtotime',3,'function'), // 对update_time字段在更新的时候写入当前时间戳
		array('create_ts','implode',3,'function'),
     );
	
}