<?php
/**
 * Created by PhpStorm.
 * User: yiming
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
    $count = $redis->sCard('fd');
    $ws->push($request->fd, 'hello, welcome ☺                     当前'.$count.'人连接在线');
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) use($redis) {
    global $redis;
    $fds  = $redis->sMembers('fd');
    $data = json_decode($frame->data,true);
    if($data['type'] ==1 ){
        $redis->set($frame->fd,json_encode(['fd'=>$frame->fd,'user'=>$data['user']]));
        //通知所有用户新用户上线
        $fds = $redis->sMembers('fd');$users=[];
        $i=0;
        foreach ($fds as $fd_on){
            $info = $redis->get($fd_on);
            $users[$i]['fd']   = $fd_on;
            $users[$i]['name'] = json_decode($info,true)['user'];
            $message = "欢迎 <b style='color: darkmagenta ;'>".$data['user']."</b> 进入聊天室";
            $push_data = ['message'=>$message,'users'=>$users];
            $ws->push($fd_on,json_encode($push_data));
            $i++;
        }
    }else if($data['type'] ==2){
        if($data['to_user'] == 'all'){
            foreach ($fds as $fd){
                $message = "<b style='color: crimson'>".$data['from_user']." say:</b>  ".$data['msg'];
                $push_data = ['message'=>$message];
                $ws->push($fd,json_encode($push_data));
            }
        }
    }
    echo "Message: {$frame->data}\n";

    //循环所有连接人发送内容
    //foreach($ws->connections as $key => $fd) {
        //$user_message = $frame->data;
        //$ws->push($fd, $frame->fd.'say:'.$user_message);
    //}
});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) use ($redis){
    global $redis;
    $redis->sRem('fd',$fd);
    $fds = $redis->sMembers('fd');
    $i=0;
    foreach ($fds as $fd_on){
        $user = json_decode($redis->get($fd),true)['user'];
        $info = $redis->get($fd_on);
        $users[$i]['fd']   = $fd_on;
        $users[$i]['name'] = json_decode($info,true)['user'];
        $message = "<b style='color: blueviolet'>".$user."</b> 离开聊天室了";
        $push_data = ['message'=>$message,'users'=>$users];
        $ws->push($fd_on,json_encode($push_data));
        $i++;
    }
    echo "client-{$fd} is closed\n";
});

$ws->start();
