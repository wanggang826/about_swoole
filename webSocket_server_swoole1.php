<?php
/**
 * Created by PhpStorm.
 * User: ubt
 * Date: 18-8-24
 * Time: 下午4:11
 */
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("0.0.0.0", 9999);

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    var_dump($request->fd, $request->get, $request->server);
    //获取所有连接人存为数组

    $GLOBALS['fd'][] = $request->fd;
    //$ws->push($request->fd, "hello, welcome\n");
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    echo "Message: {$frame->data}\n";

//循环所有连接人发送内容

    foreach($GLOBALS['fd'] as $key => $val){
        $ws->push($val,$frame->data);
    }
    //$ws->push($frame->fd, "{$frame->data}");
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    echo "client-{$fd} is closed\n";
});

$ws->start();
