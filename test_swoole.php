<?php
/**
 * 创建一个TCP服务器,监听本机 9898 端口
 * User: wgg
 * Date: 18-8-13
 * Time: 上午11:30
 */


//创建Server对象,监听127.0.0.1:9898端口
$server = new swoole_server('127.0.0.1',9898);

//监听连接事件
$server->on('connect',function($server,$fd){
    echo 'Client: Connect.';
});

//监听接收事件,收到什么发送什么
$server->on('receive',function($server,$fd,$fromId,$data){
    $server->send($fd,'Server: '.$data);
});

//监听连接关闭事件
$server->on('close',function($server,$fd){
    echo 'Client: Close';
});

$server->start();