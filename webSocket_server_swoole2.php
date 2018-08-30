<?php
/**
 * Created by PhpStorm.
 * User: ubt
 * Date: 18-8-22
 * Time: 上午11:37
 */

class Server
{
    private $serv;

    public function __construct()
    {
        $this->serv = new swoole_websocket_server("0.0.0.0", 9999);
        $this->serv->set(array(
            'worker_num' => 4,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'debug_mode' => 1,
            'task_worker_num' => 4
        ));
        //启动开始
        $this->serv->on('Start', array($this, 'onStart'));

        //与onStart同级
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));

        //webSocket open 连接触发回调
        $this->serv->on('open', array($this, 'onOpen'));

        //webSocket send 发送触发回调
        $this->serv->on('message', array($this, 'onMessage'));

        //webSocket close 关闭触发回调
        $this->serv->on('Close', array($this, 'onClose'));

        //tcp连接 触发 在 webSocket open 之前回调
        $this->serv->on('Connect', array($this, 'onConnect'));

        //tcp 模式下（eg:telnet ） 发送信息才会触发  webSocket 模式下没有触发
        $this->serv->on('Receive', array($this, 'onReceive'));

        // task_worker进程处理任务的回调   处理比较耗时的任务
        $this->serv->on('Task', array($this, 'onTask'));

        // task_worker进程处理任务结束的回调
        $this->serv->on('Finish', array($this, 'onFinish'));

        // 服务开启
        $this->serv->start();


    }


    public function onStart(swoole_websocket_server $serv)
    {
//        $this->serv->tick(1000, function() {
//            echo 1;
//        });
        echo "Start\n";
    }

    public function onWorkerStart(swoole_websocket_server $serv,$worker_id)
    {
        //判断是worker进程还是 task_worker进程 echo 次数 是worker_num+task_worker_num
        if($worker_id<$this->serv->setting['worker_num']){
            echo  'worder'.$worker_id."\n";
        }else{
            echo  'task_worker'.$worker_id."\n";
        }
        //     echo "workerStart{$worker_id}\n";
    }


    public function onOpen(swoole_websocket_server $serv,$request)
    {
        echo "server: handshake success with fd{$request->fd}\n";
    }

    public function onMessage(swoole_websocket_server $serv,$frame)
    {
        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        $param = array(
            'fd' => $frame->fd
        );
        $this->serv->task( json_encode( $param ) );
//            $server->push($frame->fd, "this is server");
    }


    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";
        echo "{$from_id}\n";
    }

    public function onReceive( swoole_websocket_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";
        // send a task to task worker.
//        $param = array(
//            'fd' => $fd
//        );
//        $serv->task( json_encode( $param ) );
        echo "Continue Handle Worker\n";
    }


    public function onClose($serv, $fd)
    {
        echo "Client {$fd} close connection\n";
    }


    public function onTask($serv, $task_id, $from_id, $data)
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

    public function onFinish($serv,$task_id, $data) {
        echo "Task {$task_id} finish\n";
        echo "Result: {$data}\n";
    }

}

$server = new Server();
