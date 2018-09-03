<?php
/**
 * Created by PhpStorm.
 * User: ubt
 * Date: 18-8-20
 * Time: 下午5:03
 */
function client($argv)
{
    $client= new swoole_client(SWOOLE_SOCK_TCP);
    //连接到服务器
    if (!$client->connect($argv[1],$argv[2],'0.5')) {
        die("connect failed.");
    }

    //向服务器发送数据
    if (!$client->send('data: '.$argv[3])) {
        die("send failed.");
    }
    echo "success send data: ".$argv[3]."\n";
    $client->close();
}
client($argv);