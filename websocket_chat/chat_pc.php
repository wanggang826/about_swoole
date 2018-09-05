<?php
$user = $_POST['name'];
if(!$user){
    header('Location:http://sw.wanggangg.top/websocket_chat/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>聊天室--本人:<?php echo $user;?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <link rel="stylesheet" href="static/css/chat.css" type="text/css">
<!--    <script type="text/javascript" src="static/js/chat.js"></script>-->
</head>
<body>
<div class="all">
    <div class="chat_index">
        <!--banner-->
        <div class="chat_banner">

        </div>

        <div class="chat_body">
            <!--在线列表-->
            <div class="chat_online">
                <!--搜索-->
                <div class="search_online">
                    <form>
                        <input type="text" placeholder="" readonly value="在线用户">
                    </form>
                </div>
                <div class="online_friend">
                    <ul id="user_list">
                        <?php
                            $redis = new \Redis();
                            $redis->connect('127.0.0.1', 6379);
                            $fds = $redis->sMembers('fd');
                            $i=0;
                            foreach ($fds as $fd_on){
                                $info = $redis->get($fd_on);
                                $users[$i]['fd']   = $fd_on;
                                $users[$i]['name'] = json_decode($info,true)['user'];
                                $i++;
                            }
                            $html='';
                            if(!empty($users)){
                                foreach ($users as $key=>$value){
                                    $html.= "<li> <div class='a_friend'><div class=''><div class='head_text'>".$value['name']."</div></div>";
                                    $html.= "</li>";
                                }
                            }
                            echo $html;
                        ?>
                    </ul>
                </div>

            </div>
            <!--聊天界面-->
            <div class="chat_main">
                <div class="chat_div" id="div">
<!--                    <ul id="chat_ul" class="chat_content">-->
<!---->
<!--                    </ul>-->

                </div>

                <div class="send_message">
                        <input type="text" placeholder="请输入消息" id="send_txt">
                        <input type="button" value="发送" id="send_btn" onclick="sendMassage('all')">
                </div>
            </div>
            <!--名片-->
            <div class="chat_namecard">

            </div>
        </div>

    </div>
</div>
</body>
<script src="./static/js/jquery-1.8.2.min.js"></script>
<script>
    var wsServer = 'ws://sw.wanggangg.top:9999';//这里的IP应该更改
    //    var wsServer = 'ws://47.95.236.88:9988';//这里的IP应该更改
    //    var wsServer = 'ws://10.10.10.11:9988';//这里的IP应该更改
    var websocket = new WebSocket(wsServer);
    websocket.onopen = function (evt) {
        console.log("Connected to WebSocket server.");
        websocket.send('{"user":"<?php echo $user;?>" ,"type":"1"}')
    };

    websocket.onclose = function (evt) {
        console.log("Disconnected");
    };

    websocket.onmessage = function (evt) {
        console.log(typeof(evt.data));
        var data = eval('(' + evt.data + ')');
        var message = data.message;
        if(data['users']){
            var users = data.users,html='';
            for(var i=0;i<users.length;i++){
                html+= "<li> <div class='a_friend'><div class=''><div class='head_text'>"+users[i].name+"</div></div></li>"
            }
            $('#user_list').html(html);
        }
        $('#div').append(message+"<br>");
        $('#div').scrollTop($('#div')[0].scrollHeight);
        // document.getElementById('div').style.background = evt.data;
        console.log('Retrieved data from server: ' + evt.data);
    };

    websocket.onerror = function (evt, e) {
        console.log('Error occured: ' + evt.data);
    };

    var send_input = document.getElementById('send_txt');
    send_input.onkeydown = function (){
        if(event.keyCode == 13){
            sendMassage('all');
        }
    }
    function sendMassage(to_user){
        var massage=document.getElementById('send_txt').value;
        if(massage){
            var msg = '{"type":"2","msg":"'+massage+'","from_user":"<?php echo $user;?>","to_user":"'+to_user+'"}';
            websocket.send(msg);
            $('#send_txt').val('');
        }else{
            alert('请不要惜字如金')
        }


    }

</script>

</html>
