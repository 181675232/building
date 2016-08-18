<?php
namespace Common\Model;
use Think\Model;

class AdminModel extends Model{
	protected $_validate = array(		
		array('name','require','帐号不能为空！'), 
		array('name','','帐号名称已经存在！',0,'unique',1), // 在新增的时候验证name字段是否唯一
		array('password','6,20','密码不能小于6位,不能大于18位',1,'length'), // 自定义函数验证密码格式
		array('code','require','验证码不能为空！'), //默认情况下用正则进行验证
	);	
}