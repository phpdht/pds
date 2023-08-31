<?php
//swoole version 1.9.5
/*
 * 安装swoole pecl install swoole
 * 设置服务器 ulimit -n 100000
 * 关闭防火墙和后台规则 防止端口不通
 */
error_reporting(E_ALL |E_NOTICE );
ini_set('date.timezone','Asia/Shanghai');
ini_set("memory_limit","-1");
//swoole_process::setaffinity(array(0));
define('MAX_REQUEST', 3000);// 允许最大连接数, 不可大于系统ulimit -n的值
define('AUTO_FIND_TIME', 30000);//定时寻找节点时间间隔 /毫秒
define('AUTO_FIND_AFTER', 1000);//定时寻找节点时间间隔 /毫秒

define('MAX_NODE_SIZE', 2000);//保存node_id最大数量
define('BIG_ENDIAN', pack('L', 1) === pack('N', 1));
define('ROOT_PATH', dirname(__FILE__));
define('BASEPATH', ROOT_PATH.'/dht_client_task/');



require_once ROOT_PATH . '/Env.php';



$config = require_once BASEPATH . '/config.php';

require_once ROOT_PATH . '/dht_server/inc/Node.class.php'; //node_id类
require_once ROOT_PATH . '/dht_server/inc/Bencode.class.php';//bencode编码解码类
require_once ROOT_PATH . '/dht_server/inc/Base.class.php';//基础操作类
require_once ROOT_PATH . '/dht_server/inc/Func.class.php';
require_once BASEPATH . '/inc/DhtClient.class.php';
require_once BASEPATH . '/inc/DhtServer.class.php';
require_once ROOT_PATH . '/dht_server/inc/Metadata.class.php';

$nid = Base::get_node_id();// 伪造设置自身node id
$table = array();// 初始化路由表
$time = microtime(true);
// 长期在线node
$bootstrap_nodes = array(
    array('router.bittorrent.com', 6881),
    array('dht.transmissionbt.com', 6881),
    array('router.utorrent.com', 6881),
    ['dht.aelitis.com',6881]
);

Func::Logs(date('Y-m-d H:i:s', time()) . " - 服务启动...".PHP_EOL,1);//记录启动日志

//SWOOLE_PROCESS 使用进程模式，业务代码在Worker进程中执行
//SWOOLE_SOCK_UDP 创建udp socket
$serv = new swoole_server('0.0.0.0', 31739, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
$serv->set(array(
    'worker_num' => $config['worker_num'],//设置启动的worker进程数
    'daemonize' => $config['daemonize'],//是否后台守护进程
    'max_request' => 100, //防止 PHP 内存溢出, 一个工作进程处理 X 次任务后自动重启 (注: 0,不自动重启)
    'dispatch_mode' => 2,//保证同一个连接发来的数据只会被同一个worker处理
    'log_file' => BASEPATH . '/logs/error.log',
    'max_conn'=>65535,//最大连接数
    'heartbeat_check_interval' => 5, //启用心跳检测，此选项表示每隔多久轮循一次，单位为秒
    'heartbeat_idle_time' => 10, //与heartbeat_check_interval配合使用。表示连接最大允许空闲的时间
	'task_worker_num'=>$config['task_worker_num'],
	'task_max_request'=>0
));

$serv->on('WorkerStart', function( $serv, $worker_id){
   global $table,$bootstrap_nodes,$config;
    echo  'start';
    var_dump($config);
    DhtServer::send_response([],array($config['server_ip'],$config['server_port']));



});

/*
$server，swoole_server对象
$fd，TCP客户端连接的文件描述符
$from_id，TCP连接所在的Reactor线程ID
$data，收到的数据内容，可能是文本或者二进制内容
 */
$serv->on('Packet', function($serv, $data, $fdinfo){


});


$serv->on('task', function ($server, $task_id, $reactor_id, $data) {
	global $config;



    $server->finish("OK");
});

$serv->on('finish', function ($server, $task_id, $data) {
	//var_dump($server->stats()).PHP_EOL;
    //echo "AsyncTask[$task_id] finished: {$data}\n";
});


$serv->start();

