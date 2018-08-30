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
    global $redis;
    var_dump($request->fd, $request->get, $request->server);
    //记录连接
    $redis->sAdd('fd', $request->fd);

    //通知所有用户新用户上线
    $fds = $redis->sMembers('fd');
    foreach ($fds as $fd_on){
        if($fd_on != $request->fd){
            $ws->push($fd_on,$request->fd.'号用户连接上线了');
        }
    }
    $count = $redis->sCard('fd');
    //获取当前所有连接人存为数组
    $GLOBALS['fd'][] = $request->fd;
    $ws->push($request->fd, "hello, welcome\n 您目前是".$request->fd.'号用户☺       当前'.$count.'人连接在线');
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) use($redis) {
    global $redis;
    $fds = $redis->sMembers('fd');
    echo "Message: {$frame->data}\n";
    foreach ($fds as $fd){
        $ws->push($fd,$frame->fd.'号用户: '.$frame->data);
    }
//循环所有连接人发送内容
//    foreach($ws->connections as $key => $fd) {
//        $user_message = $frame->data;
//        $ws->push($fd, $frame->fd.'say:'.$user_message);
//    }
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) use ($redis){
    global $redis;
    $redis->sRem('fd',$fd);
    $fds = $redis->sMembers('fd');
    foreach ($fds as $fd_on){
        $ws->push($fd_on,$fd.'号用户下线断开了');
        $redis->sRem('fd',$fd_on);
    }
    echo "client-{$fd} is closed\n";
});

$ws->start();
