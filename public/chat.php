<?php
ob_start();
//可以单独做一个跳转页面，用来登录后刷新cookies

if(isset($_GET['ka'])) {
    $key = $_GET['ka'];
    $k_md5 = md5($key);
    //登录通过
    if(isset($_COOKIE[$key])){
        //查看cookie有没有保存过
        echo 'cookie里有，' . $_COOKIE[$key];
    }else{
        setcookie($key, $k_md5, time()+3600);
        echo 'cookie里没有，' . $_COOKIE[$key];
    }
}elseif (isset($_POST['k'])) {
    //登录操作
    $key = $_POST['k'];

    //做登录校验…………

    $current_url = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"] . "?ka=" . $key;
    echo "<script>window.location.href='{$current_url}';</script><a href='{$current_url}'>跳转中...</a>";
}
?>
<html>
<head><title>chat</title></head>
<body>
<form method="post" action="">
    <input class="layui-input" name="k" autofocus autocomplete="off" placeholder="登录账号" title="请输入登录账号">
    <button type="submit">立即登入</button>
</form>
</body>
</html>