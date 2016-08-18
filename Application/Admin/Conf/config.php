<?php
return array(
	//'配置项'=>'配置值'
	
	//权限
	'USER_AUTH_ON' => true, //是否需要认证，设置为true时$rbac::AccessDecision()函数才会根据当前的操作检查权限并返回true或false，，设为false只返回true
	'USER_AUTH_TYPE' => 1, //认证类型,2代表每次进行操作的时候都会数据库取出权限(权限更改即时生效)，1代表只在登录的时候取出权限(权限更改下次登录时生效)
	'USER_AUTH_KEY' => 'userid', //认证识别号,执行$rbac::saveAccessList();的时候回用以这个为键值的session去数据库取权限
		//定义rbac超级管理员,登录成功之后把用户名和这个值进行比对，一样就是超级管理员
	'RBAC_SUPERADMIN'   =>  'admin',
	'ADMIN_AUTH_KEY' => 'admin', //超级管理员识别,当当前用户是超级管理员时，把键值为这个值的session这个设置为true，当前用户就能进行一切操作
	'ADMINISTRATOR' => 'admin',
	'USER_AUTH_MODEL' => 'Admin', // 默认验证数据表模型
	'AUTH_PWD_ENCODER' => 'md5', // 用户认证密码加密方式
	'USER_AUTH_GATEWAY' => '/Public/login', //认证网关,执行$rbac::checkLogin()函数(检查是否登录),如果没有登录，去到这个设置的网址(当前url直接加上这个设置的值)
	'NOT_AUTH_MODULE' => 'Public,Index', // 默认无需认证模块
	'REQUIRE_AUTH_MODULE' => '', // 默认需要认证模块
	'NOT_AUTH_ACTION' => 'nav,editpass,ajaxstate,ajax,pinleiajax,selectajax,passajax,selectajax1,selectajax3,config,delete_check', // 默认无需认证操作
	'REQUIRE_AUTH_ACTION' => '', // 默认需要认证操作
	'GUEST_AUTH_ON' => false, // 是否开启游客授权访问
	'GUEST_AUTH_ID' => 0, // 游客的用户ID
//	'RBAC_ERROR_PAGE' => '/thinkphps/Public/error404.html',
//	'DB_LIKE_FIELDS'            =>'title|remark',
//	'RBAC_DB_DSN' =>'mysql',
	'RBAC_ROLE_TABLE'           =>'t_role',
	'RBAC_USER_TABLE'           =>'t_role_user',
	'RBAC_ACCESS_TABLE'         =>'t_access',
	'RBAC_NODE_TABLE'           =>'t_nav',

);
