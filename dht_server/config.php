<?php
return array(
    'db'        => array(
        'host' => Env::get('MYSQL_HOST', 'mysql57'),
        'user' => Env::get('MYSQL_USERNAME', 'phpdhtspider'),
        'pass' => Env::get('MYSQL_PASSWORD', 'phpdhtspider123'),
        'name' => Env::get('MYSQL_DATABASE', 'phpdhtspider'),
    ),
    'daemonize' => false,//是否后台守护进程
);
