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
    <title><?php echo $user;?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <link rel="stylesheet" href="static/css/chat.css" type="text/css">
    <script type="text/javascript" src="static/js/chat.js"></script>
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
                        <input type="text" placeholder="搜索联系人">
                    </form>
                </div>
                <div class="online_friend">
                    <ul>
                        <li>
                            <div class="a_friend">
                                <div class="head_portrait">
                                    <div class="head_text">
                                        黄
                                    </div>
                                </div>
                                <div class="friend">

                                    <div class="name">天狼星</div>
                                    <div class="this_time">4-12-15:10</div>

                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="a_friend">
                                <div class="head_portrait">
                                    <div class="head_text">
                                        黄
                                    </div>
                                </div>
                                <div class="friend">

                                    <div class="name">天狼星</div>
                                    <div class="this_time">4-12-15:10</div>

                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="a_friend">
                                <div class="head_portrait">
                                    <div class="head_text">
                                        黄
                                    </div>
                                </div>
                                <div class="friend">

                                    <div class="name">天狼星</div>
                                    <div class="this_time">4-12-15:10</div>

                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="a_friend">
                                <div class="head_portrait">
                                    <div class="head_text">
                                        黄
                                    </div>
                                </div>
                                <div class="friend">

                                    <div class="name">天狼星</div>
                                    <div class="this_time">4-12-15:10</div>

                                </div>
                            </div>
                        </li>
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
        $('#div').append(evt.data+"<br>");
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
        var msg = '{"type":"2","msg":"'+massage+'","from_user":"<?php echo $user;?>","to_user":"'+to_user+'"}';
        websocket.send(msg);
        $('#send_txt').val('');
    }

</script>

</html>
