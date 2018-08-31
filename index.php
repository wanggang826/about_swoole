<?php
$name = $_POST['name'];
$_COOKIE['name'] = $name;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>websoket</title>
</head>
<body>
<form action="" method="post">
输入用户名<input type="text" name="name" value="">(自定义)
<input type="submit" value="提交">
</form>
</body>
</html>