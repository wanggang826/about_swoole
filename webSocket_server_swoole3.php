<?php
/**
 * Created by PhpStorm.
 * User: yiming
 * Date: 18-9-1
 * Time: 上午9:49
 */
class WebSocketServer {
    private $server;
    public function __construct()
    {
        $this->server = new swoole_websocket_server("0.0.0.0",9988);
        $this->server->set(array(
//            'daemonize'       => true,
//            'worker_num'      => 4,
//            'task_worker_num' => 4
        ));

        $fd_table = new swoole_table( 1024 );
        $fd_table->column( "user",swoole_table::TYPE_STRING, 30 );
        $fd_table->column( "time", swoole_table::TYPE_STRING, 20 );
        $fd_table->create();

        $user_table = new swoole_table(1024);
        $user_table->column("fd",swoole_table::TYPE_INT,8);
        $user_table->create();

        $this->server->fd = $fd_table;
        $this->server->user = $user_table;

        //启动开始
//        $this->server->on('Start',[$this,'onStart']);

        //与onStart同级
//        $this->server->on('workerStart',[$this,'onWorkerStart']);

        //webSocket open 连接触发回调
        $this->server->on('open',[$this,'onOpen']);

        //webSocket send 发送触发回调
        $this->server->on('message', [$this, 'onMessage']);

        //webSocket close 关闭触发回调
        $this->server->on('Close', [$this, 'onClose']);


        //tcp连接 触发 在 webSocket open 之前回调
//        $this->server->on('Connect', [$this, 'onConnect']);


        //tcp 模式下（eg:telnet ） 发送信息才会触发  webSocket 模式下没有触发
        $this->server->on('Receive', [$this, 'onReceive']);


        // task_worker进程处理任务的回调   处理比较耗时的任务
//        $this->server->on('Task', [$this, 'onTask']);


        // task_worker进程处理任务结束的回调
//        $this->server->on('Finish', [$this, 'onFinish']);

        // 服务开启
        $this->server->start();

    }

    public function createTable(){

    }

    public function onStart( $server)
    {
//        $this->server->tick(1000, function() {
//            echo 1;
//        });
        echo "Start\n";
    }

    public function onWorkerStart($server,$worker_id)
    {
        //判断是worker进程还是 task_worker进程 echo 次数 是worker_num+task_worker_num
        if($worker_id<$server->setting['worker_num']){
            echo  'worder'.$worker_id."\n";
        }else{
            echo  'task_worker'.$worker_id."\n";
        }
        //     echo "workerStart{$worker_id}\n";
    }

    public function onOpen( $server,$request)
    {
        $this->server->fd->set($request->fd,['user'=>'']);
        echo "server: handshake success with fd{$request->fd}\n";
        $count =count($server->connections);
        $server->push($request->fd, 'hello, welcome ☺                     当前'.$count.'人连接在线');
    }

    public function onMessage( $server,$frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $data = json_decode($frame->data,true);
        if($data['type'] ==1 ){
            $server->fd->set($frame->fd,['user'=>$data['user']]);
            //通知所有用户新用户上线
            foreach($server->connections as $key => $fd) {
                $server->push($fd, "欢迎 <b style='color: darkmagenta ;'>".$data['user']."</b> 进入聊天室");
            }
        }else if($data['type'] ==2){
            if($data['to_user'] == 'all'){
                foreach($server->connections as $key => $fd) {
                    $server->push($fd, "<b style='color: crimson'>".$data['from_user']." say:</b>  ".$data['msg']);
                }
            }
        }
    }


    public function onConnect( $server, $fd, $from_id ) {
        echo "Client {$fd} connect\n";
        echo "{$from_id}\n";
    }

    public function onReceive( $server, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";
        // send a task to task worker.
//        $param = array(
//            'fd' => $fd
//        );
//        $server->task( json_encode( $param ) );
        echo "Continue Handle Worker\n";
    }


    public function onClose($server, $fd)
    {
        echo "Client {$fd} close connection\n";
        foreach($server->connections as $key => $on_fd) {
            $user = $server->fd->get($fd)['user'];
            $server->push($on_fd, "<b style='color: blueviolet'>".$user."</b> 离开聊天室了");
        }
    }


    public function onTask($server, $task_id, $from_id, $data)
    {
        echo "This Task {$task_id} from Worker {$from_id}\n";
        echo "Data: {$data}\n";
        for ($i = 0; $i < 10; $i++) {
            sleep(1);
            echo "Taks {$task_id} Handle {$i} times...\n";
        }
        $fd = json_decode($data, true)['fd'];
        echo  "Data in Task {$task_id}";
//        $serv->send($fd, "Data in Task {$task_id}");
        return "Task {$task_id}'s result";
    }

    public function onFinish($server,$task_id, $data) {
        echo "Task {$task_id} finish\n";
        echo "Result: {$data}\n";
    }

}
new WebSocketServer();