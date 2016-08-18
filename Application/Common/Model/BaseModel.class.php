<?php
namespace Common\Model;
use Think\Model;

class BaseModel extends Model{
	protected $_validate = array(		
		array('title','require','网站标题不能为空！'),
	);
	
}