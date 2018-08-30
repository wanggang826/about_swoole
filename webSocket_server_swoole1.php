<?php
/**
 * Created by PhpStorm.
 * User: ubt
 * Date: 18-8-24
 * Time: 下午4:11
 */
//创建websocket服务器对象，监听0.0.0.0:9999端口
$ws = new swoole_websocket_server("0.0.0.0", 9999);

$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);

$ws->set(array(
    'daemonize' => true,
));
//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) use($redis) {

    var_dump($request->fd, $request->get, $request->server);
    //获取所有连接人存为数组
    $redis->sAdd('fd', $request->fd);
    $add_fd = $redis->get('fd');
    $GLOBALS['fd'][] = $request->fd;
    $ws->push($request->fd, "hello, welcome\n".$request->fd.$add_fd.count($GLOBALS['fd']));
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) use($redis) {
    global $redis;
    $fds = $redis->sMembers('fd');
    echo "Message: {$frame->data}\n";
    foreach ($fds as $fd){
        $ws->push($fd,$frame->fd.'--'.$frame->data);
    }
//循环所有连接人发送内容
//    foreach($ws->connections as $key => $fd) {
//        $user_message = $frame->data;
//        $ws->push($fd, $frame->fd.'say:'.$user_message);
//    }

//    foreach($GLOBALS['fd'] as $key => $val){
//        $ws->push($val,$frame->data);
//    }
//    $ws->push($frame->fd, "{$frame->data}");
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) use ($redis){
    $redis->sRem('fd',$fd);
    echo "client-{$fd} is closed\n";
});

$ws->start();
