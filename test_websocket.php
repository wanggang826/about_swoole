<?php
$user = $_POST['name'];
if(!$user){
    header('Location:http://sw.wanggangg.top/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title><?php echo $user;?></title>
</head>
<body>
<div style="text-align:center">
    <div id="div" style="width:99%; height:180px;overflow:auto;background: #bef8ff;display: inline-block;text-align: left;">

    </div>
    <br>
    <div style="display: inline-block;width: 99%">
        <table style="width: 100%">
            <tr>
                <td width="90%"><input type="text" id="text" style="width: 100%;height: 25px;padding-left: 0;margin-left: 0"></td><td ><input type="button" value="发送" onclick="sendMassage('all')" style="height: 31px;width: 100%;"></td>
            </tr>
        </table>
    </div>
</div>
</body>
<script src="jquery-1.8.2.min.js"></script>
<script>
    var wsServer = 'ws://47.95.236.88:9999';//这里的IP应该更改
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
    function sendMassage(to_user){
        var massage=document.getElementById('text').value;
        var msg = '{"type":"2","msg":"'+massage+'","from_user":"<?php echo $user;?>","to_user":"'+to_user+'"}';
        websocket.send(msg);
        $('#text').val('');
    }

</script>
</html>
