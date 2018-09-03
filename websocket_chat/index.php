<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title>websoket</title>
</head>
<body>
<div style="background: #60fff4;text-align: center;height: 450px;">
<form action="" method="post" style="display: inline-block;overflow:auto;margin-top: 180px;" name="form">
输入用户名<input type="text" name="name" value="" style="height: 25px" id="name">
        <input type="submit" value="提交" style="height: 30px">
</div>
</form>
</body>

<script>
    var user_input = document.getElementById('name');
    user_input.onkeypress = function (){
        if(event.keyCode == 13){
            form.submit();
        }
    }
    if(/Android|webOS|iPhone|iPod|BlackBerry/i.test(navigator.userAgent)) {
        document.form.action='chat.php';
    } else {
        document.form.action='chat_pc.php';
    }
</script>
</html>