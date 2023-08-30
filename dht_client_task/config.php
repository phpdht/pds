<?php
return array(
    'daemonize'=>false,//是否后台守护进程
    'worker_num'=>1,// 主进程数, 一般为CPU的1至4倍 同时执行任务数量
	'task_worker_num'=>4,//task进程的数量 值越大 CPU占用越高
    'server_ip'=>'127.0.0.1',//服务端ip
    'server_port'=>31738,//服务端端口
);
