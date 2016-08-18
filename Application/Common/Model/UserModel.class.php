<?php
namespace Common\Model;
use Think\Model;

class OwnerModel extends Model{	
	protected $_validate = array(		
		array('name','require','昵称必须填写！'), 
		array('phone','','手机号已经存在！',1,'unique',1),// 在新增的时候验证name字段是否唯一
		array('password','6,20','密码不能小于6位,不能大于20位',0,'length',1), // 自定义函数验证密码格式
		array('phone','checkPhone','手机号输入不正确！',1,'function'), //默认情况下用正则进行验证
	);
	
	protected $_auto =  array( 
//       array('name','getName',3,'callback'), // 对name字段在新增和编辑的时候回调getName方法
         array('addtime','time',1,'function'), // 对update_time字段在更新的时候写入当前时间戳
     );
}