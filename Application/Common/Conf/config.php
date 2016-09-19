<?php
return array(
// 	'配置项'=>'配置值'
    'SESSION_AUTO_START' => true, //是否开启session
    'TMPL_CACHE_ON'=>false,      // 默认开启模板缓存
// 	禁止模块访问
// 	'MODULE_DENY_LIST'=>array('Home','Common','Runtime'),
//	允许模块访问
    'MODULE_ALLOW_LIST'=>array('Admin','Api','Home'),
//	设置默认加载模块
    'DEFAULT_MODULE'=>'Admin',
//	减少目录结构
    'TMPL_FILE_DEPR'=>'_',
//	URL模式
    'URL_MODEL' => 2,
//	URL大小写
    'URL_CASE_INSENSITIVE' => true,
//  数据库
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '101.200.81.192', // 服务器地址
    'DB_NAME'               =>  'php8082',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'Jolly@#2009',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  't_',    // 数据库表前缀
    'DB_CHARSET'			=>  'utf8',      // 数据库编码
    'DB_DEBUG'				 =>  TRUE, // 数据库调试模式 开启后可以记录SQL日志


    //'DB_CONFIG1' = array('db_type'  => 'mysql',    'db_user'  => 'root',    'db_pwd'   => '1234',    'db_host'  => 'localhost',    'db_port'  => '3306',    'db_name'  => 'thinkphp'),
    'DB_CONFIG2' => 'mysql://root:Jolly@#2009@101.200.81.192:3306/jolly',
    'SHOW_PAGE_TRACE' => false,		//页面trace

    //缓存
    'DATA_CACHE_TYPE' => 'redis',
    'DATA_CACHE_TIME' => '0',
    'DATA_CACHE_PREFIX' => 'building',

    /*Redis设置*/
    'REDIS_HOST'            => '127.0.0.1', //主机
    'REDIS_PORT'            => '6379', //端口
    'REDIS_CTYPE'           => 1, //连接类型 1:普通连接 2:长连接
    'REDIS_TIMEOUT'         => 0, //连接超时时间(S) 0:永不超时

    //微信配置
    'WX_APPID'  => 'wx13298176af72b311',
//	'MCH_ID'  => '1235375102',

// 	'DEFAULT_V_LAYER' => 'view',	//修改视图目录
// 	'TMPL_TEMPLATE_SUFFIX' =>  '.tpl',//修改后缀
// 	'VIEW_PATH'=>'./Public/', //修改模板目录
// 	'TOKEN_ON'      =>    true,  // 是否开启令牌验证 默认关闭
// 	'TOKEN_NAME'    =>    '__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
// 	'TOKEN_TYPE'    =>    'md5',  //令牌哈希验证规则 默认为MD5
// 	'TOKEN_RESET'   =>    true,  //令牌验证出错后是否重置令牌 默认为true

    //模版变量替换
    'TMPL_PARSE_STRING'  =>array(
        '__Public__' => __ROOT__.'/Public'
    ),
    //融云
    'rong'            => array(
        'key'    => '8brlm7ufr41w3' ,
        'secret' => '7dYWxJCyLIA' ,
    ) ,
    //Jpush key
    'jpush'            => array(
        'title'    => 'JollyBuilding' ,
        'key'    => 'bea2018a7e27f608345fa373' ,
        'secret' => 'f050562fffc5362275eb3219' ,
    ) ,
    'url'              => 'http://101.200.81.192:8082',

);
