<?php
ini_set('session.use_trans_sid', 1); //设置时间 
ini_get('session.use_trans_sid');//得到ini中设定值 
ini_set('session.use_only_cookies', 0); //设置时间 
ini_get('session.use_only_cookies');//得到ini中设定值 
ob_start();
session_start();
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<!--[if lt IE 9]>
<script type="text/javascript" src="lib/html5shiv.js"></script>
<script type="text/javascript" src="lib/respond.min.js"></script>
<![endif]-->
<link href="static/h-ui/css/H-ui.min.css" rel="stylesheet" type="text/css" />
<link href="static/h-ui.admin/css/H-ui.login.css" rel="stylesheet" type="text/css" />
<link href="static/h-ui.admin/css/style.css" rel="stylesheet" type="text/css" />
<link href="lib/Hui-iconfont/1.0.8/iconfont.css" rel="stylesheet" type="text/css" />
<!--[if IE 6]>
<script type="text/javascript" src="lib/DD_belatedPNG_0.0.8a-min.js" ></script>
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<title>后台登录 - H-ui.admin v3.1</title>
</head>
<body>
<input type="hidden" id="TenantId" name="TenantId" value="" />
<div class="header"></div>
<div class="loginWraper">
  <div id="loginform" class="loginBox">
    <form class="form form-horizontal" action="" method="post">
      <div class="row cl">
        <label class="form-label col-xs-3"><i class="Hui-iconfont">&#xe60d;</i></label>
        <div class="formControls col-xs-8">
          <input id="" name="Username" type="text" placeholder="账户" class="input-text size-L">
        </div>
      </div>
      <div class="row cl">
        <label class="form-label col-xs-3"><i class="Hui-iconfont">&#xe60e;</i></label>
        <div class="formControls col-xs-8">
          <input id="" name="Password" type="password" placeholder="密码" class="input-text size-L">
        </div>
      </div>
      <div class="row cl">
        <div class="formControls col-xs-8 col-xs-offset-3">
          <input  type="submit" class="btn btn-success radius size-L" name="submit" value="&nbsp;登&nbsp;&nbsp;&nbsp;&nbsp;录&nbsp;">
          	
        </div>
      </div>
    </form>
  </div>
</div>
<?php
require("conn/conn.php");
if (isset($_POST["submit"]))
{
  $username = trim($_POST["Username"]);
  $password = trim($_POST["Password"]);
  if($password!=''){$password=md5($password);}
   $sql="select * from wolive_admin where username='".$username."' and password='".$password."'";
   $res=mysqli_query($conn,$sql);
   $b=mysqli_fetch_assoc($res);
   if($b["username"]!=$username)
	{
	     echo "<script>alert('管理员帐户或密码错误,请重新输入!');location.href='login.php';</script>";
	     exit();
    }
    else
    {
		 $_SESSION["Adminnamess"] = $username;
		 $_SESSION["password"] = $password;
		 echo "<script>location.href='index.php';</script>"; 
		 exit;
	 }
} 
?>
<div class="footer">Copyright 99哈希官方 by H-ui.admin v3.1</div>
<script type="text/javascript" src="lib/jquery/1.9.1/jquery.min.js"></script> 
<script type="text/javascript" src="static/h-ui/js/H-ui.min.js"></script>

</body>
</html>