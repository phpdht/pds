<?php

class DhtClient
{

    public static $_bt_protocol = 'BitTorrent protocol';
    public static $BT_MSG_ID = 20;
    public static $EXT_HANDSHAKE_ID = 0;
    public static $PIECE_LENGTH = 16384;
    public static $last_ip = '';

    /**
     * 处理接收到的find_node回复
     * @param  array $msg 接收到的数据
     * @param  array $address 对端链接信息
     * @return void
     */
    public static function response_action($msg, $address)
    {
        global $table;
        // 先检查接收到的信息是否正确
        if (!isset($msg['r']['nodes']) || !isset($msg['r']['nodes'][1])) return;
        // 对nodes数据进行解码

//        echo '朋友'.$address[0].'在线'.PHP_EOL;
        $nodes = Base::decode_nodes($msg['r']['nodes']);
        // 对nodes循环处理
        foreach ($nodes as $node) {
            // 将node加入到路由表中
            self::append($node);
        }
        //echo '路由表nodes数量 '.count($table).PHP_EOL;
    }

    /**
     * 处理对端发来的请求
     * @param  array $msg 接收到的请求数据
     * @param  array $address 对端链接信息
     * @return void
     */
    public static function request_action($msg, $address)
    {
        switch ($msg['q']) {
            case 'ping'://确认你是否在线
//                echo '朋友'.$address[0].'正在确认你是否在线'.PHP_EOL;
//                Func::Log('朋友'.$address[0].'正在确认你是否在线');
                self::on_ping($msg, $address);
                break;
            case 'find_node': //向服务器发出寻找节点的请求
//                echo "f".PHP_EOL;
//                echo '朋友'.$address[0].'向你发出寻找节点的请求'.PHP_EOL;
                self::on_find_node($msg, $address);
                break;
            case 'get_peers':
//                echo "c".PHP_EOL;
//                echo '朋友'.$address[0].'向你发出查找资源的请求'.PHP_EOL;
                // 处理get_peers请求
                self::on_get_peers($msg, $address);
                break;
            case 'announce_peer':
                echo "-".PHP_EOL;
//                echo '朋友' . $address[0] . '找到资源了 通知你一声' . PHP_EOL;
                // 处理announce_peer请求
                self::on_announce_peer($msg, $address);
                break;
            default:
                break;
        }
    }

    /**
     * 添加node到路由表
     * @param  Node $node node模型
     * @return boolean       是否添加成功
     */
    public static function append($node)
    {
        global $nid, $table;
        // 检查node id是否正确
        if (!isset($node->nid[19]))
            return false;

        // 检查是否为自身node id
        if ($node->nid == $nid)
            return false;

        // 检查node是否已存在
        if (in_array($node, $table))
            return false;

        if ($node->port < 1 or $node->port > 65535)
            return false;

        // 如果路由表中的项达到200时, 删除第一项
        if (count($table) >= MAX_NODE_SIZE){
            array_shift($table);
        }

        return array_push($table, $node);
    }

    public static function on_ping($msg, $address)
    {
        global $nid;
        // 获取对端node id
        $id = $msg['a']['id'];
        // 生成回复数据
        $msg = array(
            't' => $msg['t'],
            'y' => 'r',
            'r' => array(
                'id' => Base::get_neighbor($id, $nid)
            )
        );

        // 将node加入路由表
        self::append(new Node($id, $address[0], $address[1]));
        // 发送回复数据
        DhtServer::send_response($msg, $address);
    }

    public static function on_find_node($msg, $address)
    {
        Func::debug("find_node");

        if(mt_rand(1,Env::get('FIND_NODE_RATE',20)) >5){
            return;
        }

        global $nid;
        // 获取对端node id
        $id = $msg['a']['id'];
        // 生成回复数据
	
        $msg = array(
            't' => $msg['t'],
            'y' => 'r',
            'r' => array(
                'id' => Base::get_neighbor($id, $nid),
                'nodes' => Base::encode_nodes(self::get_nodes(20))
            )
        );
        // 将node加入路由表
        self::append(new Node($id, $address[0], $address[1]));
        // 发送回复数据
        DhtServer::send_response($msg, $address);
    }

    /**
     * 处理get_peers请求
     * @param  array $msg 接收到的get_peers请求数据
     * @param  array $address 对端链接信息
     * @return void
     */
    public static function on_get_peers($msg, $address)
    {
        global $nid;

        // 获取info_hash信息
        $infohash = $msg['a']['info_hash'];
        // 获取node id
        $id = $msg['a']['id'];

        // 生成回复数据
        $msg = array(
            't' => $msg['t'],
            'y' => 'r',
            'r' => array(
                'id' => Base::get_neighbor($id, $nid),
                'nodes' => "",
                'token' => substr($infohash, 0, 2)
            )
        );


        // 将node加入路由表
        self::append(new Node($id, $address[0], $address[1]));
        // 向对端发送回复数据
        DhtServer::send_response($msg, $address);
    }

    /**
     * 处理announce_peer请求
     * @param  array $msg 接收到的announce_peer请求数据
     * @param  array $address 对端链接信息
     * @return void
     */
    public static function on_announce_peer($msg, $address)
    {
        global $nid,$config,$serv,$task_num;
        $infohash = $msg['a']['info_hash'];
        $port = $msg['a']['port'];
        $token = $msg['a']['token'];
        $id = $msg['a']['id'];
        $tid = $msg['t'];

        // 验证token是否正确
        if (substr($infohash, 0, 2) != $token) return;

        if (isset($msg['a']['implied_port']) && $msg['a']['implied_port'] != 0) {
            $port = $address[1];
        }

        if ($port >= 65536 || $port <= 0) {
            return;
        }

        if ($tid == '') {
            //return;
        }

        // 生成回复数据
        $msg = array(
            't' => $msg['t'],
            'y' => 'r',
            'r' => array(
                'id' => $nid
            )
        );

        if($address[0] == self::$last_ip){
            return;
        }
        self::$last_ip = $ip = $address[0];
        // 发送请求回复
        DhtServer::send_response($msg, $address);

		$task_id = $serv->task(array('ip'=>$ip,'port'=>$port,'infohash'=>swoole_serialize::pack($infohash)));
//        Func::Logs( "Dispath AsyncTask: [id=$task_id] \n",2);

        return;
    }

public static function get_nodes($len = 8)
{
    global $table;
    Func::debug("get_nodes:".count($table));
    if (count($table) <= $len){
        return $table;
    }
    $tabl2e= $table;
	
	shuffle($tabl2e);

    $nodes = array();

    for ($i = 0; $i < $len; $i++) {
        $nodes[] = $tabl2e[$i];
    }
    return $nodes;
}

}