<?php
ob_start();
session_start();
@$user_session=$_SESSION["Adminnamess"];
@$pwd_session=$_SESSION["password"];
header("Content-type: text/html; charset=utf-8");    
error_reporting(E_ALL ^E_NOTICE);

if($user_session == ""){
	echo "<script language=javascript> alert('请重新登陆！');window.parent.location.href='login.php';</script>";
	exit;
}else{
	require("conn/conn.php");
	$sql="select * from admin where user='".$user_session."' and password='".$pwd_session."'";
    $res=mysqli_query($conn,$sql);
    $row=mysqli_num_rows($res);
    if($row==0){
        echo "<script language=javascript> alert('请重新登陆！');window.parent.location.href='login.php';</script>";
	    exit;
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Cache-Control" content="no-siteapp" />
<link rel="Bookmark" href="/favicon.ico" >
<link rel="Shortcut Icon" href="/favicon.ico" />
<!--[if lt IE 9]>
<script type="text/javascript" src="lib/html5shiv.js"></script>
<script type="text/javascript" src="lib/respond.min.js"></script>
<![endif]-->
<link rel="stylesheet" type="text/css" href="static/h-ui/css/H-ui.min.css" />
<link rel="stylesheet" type="text/css" href="static/h-ui.admin/css/H-ui.admin.css" />
<link rel="stylesheet" type="text/css" href="lib/Hui-iconfont/1.0.8/iconfont.css" />
<link rel="stylesheet" type="text/css" href="static/h-ui.admin/skin/default/skin.css" id="skin" />
<link rel="stylesheet" type="text/css" href="static/h-ui.admin/css/style.css" />
<!--[if IE 6]>
<script type="text/javascript" src="lib/DD_belatedPNG_0.0.8a-min.js" ></script>
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<title>旅行小程序后台</title>
</head>
<body>
<header class="navbar-wrapper">
	<div class="navbar navbar-fixed-top">
		<div class="container-fluid cl"> <a class="logo navbar-logo f-l mr-10 hidden-xs" href="/aboutHui.shtml"></a> 

		<nav id="Hui-userbar" class="nav navbar-nav navbar-userbar hidden-xs">
			<ul class="cl">
				<li></li>
				<li class="dropDown dropDown_hover">
					<a href="#" class="dropDown_A"> <i class="Hui-iconfont">&#xe6d5;</i></a>
					<ul class="dropDown-menu menu radius box-shadow">	
						<li><a href="#">退出</a></li>
				</ul>
			</li>

			</ul>
		</nav>
	</div>
</div>
</header>
<aside class="Hui-aside">
	<div class="menu_dropdown bk_2">
		<dl id="menu-article">
			<dt class="selected"><i class="Hui-iconfont">&#xe616;</i>旅行小程序后台<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
			<dd style="display: block;">
				<ul id="ul1">
				    <li><a data-href="user_list.php" data-title="用户列表" href="javascript:void(0)">用户列表</a></li>
				    <li><a data-href="12dengji.php" data-title="订单列表" href="javascript:void(0)">订单列表</a></li>
				    <li><a data-href="tuanduigongzi.php" data-title="内容管理" href="javascript:void(0)">内容管理</a></li>
					<li><a data-href="tixian.php" data-title="提现申请" href="javascript:void(0)">提现申请</a></li>
					<li><a data-href="ruzhang.php" data-title="提现记录" href="javascript:void(0)">提现记录</a></li>	
					<li><a data-href="adds.php" data-title="地区管理" href="javascript:void(0)">地区管理</a></li>	
					
				   <!--  <li><a data-href="kouchulist.php" data-title="扣除积分列表" href="javascript:void(0)">扣除积分列表</a></li>
				    <li><a data-href="tuanduigongzi.php" data-title="团队工资列表" href="javascript:void(0)">团队工资列表</a></li>-->
				    
					
				
			</ul>
		</dd>
	</dl>
	


</div>
</aside>
<div class="dislpayArrow hidden-xs"><a class="pngfix" href="javascript:void(0);" onClick="displaynavbar(this)"></a></div>
<section class="Hui-article-box">
	<div id="Hui-tabNav" class="Hui-tabNav hidden-xs">
		<div class="Hui-tabNav-wp">
			<ul id="min_title_list" class="acrossTab cl">
				<li class="active">
					<span title="报表" data-href="user_list.php">报表</span>
					<em></em></li>
		</ul>
	</div>
		<div class="Hui-tabNav-more btn-group"><a id="js-tabNav-prev" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d4;</i></a><a id="js-tabNav-next" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d7;</i></a></div>
</div>
	<div id="iframe_box" class="Hui-article">
		<div class="show_iframe">
			<div style="display:none" class="loading"></div>
			<iframe scrolling="yes" frameborder="0" src="user_list.php"></iframe>
	</div>
</div>
</section>

<div class="contextMenu" id="Huiadminmenu">
	<ul>
		<li id="closethis">关闭当前 </li>
		<li id="closeall">关闭全部 </li>
</ul>
</div>
<!--_footer 作为公共模版分离出去-->
<script type="text/javascript" src="lib/jquery/1.9.1/jquery.min.js"></script> 
<script type="text/javascript" src="lib/layer/2.4/layer.js"></script>
<script type="text/javascript" src="static/h-ui/js/H-ui.min.js"></script>
<script type="text/javascript" src="static/h-ui.admin/js/H-ui.admin.js"></script> <!--/_footer 作为公共模版分离出去-->

<!--请在下方写此页面业务相关的脚本-->
<script type="text/javascript" src="lib/jquery.contextmenu/jquery.contextmenu.r2.js"></script>
<script type="text/javascript">
$(function(){
	/*$("#min_title_list li").contextMenu('Huiadminmenu', {
		bindings: {
			'closethis': function(t) {
				console.log(t);
				if(t.find("i")){
					t.find("i").trigger("click");
				}		
			},
			'closeall': function(t) {
				alert('Trigger was '+t.id+'\nAction was Email');
			},
		}
	});*/
});
/*个人信息*/
function myselfinfo(){
	layer.open({
		type: 1,
		area: ['300px','200px'],
		fix: false, //不固定
		maxmin: true,
		shade:0.4,
		title: '查看信息',
		content: '<div>管理员信息</div>'
	});
}

/*资讯-添加*/
function article_add(title,url){
	var index = layer.open({
		type: 2,
		title: title,
		content: url
	});
	layer.full(index);
}
/*图片-添加*/
function picture_add(title,url){
	var index = layer.open({
		type: 2,
		title: title,
		content: url
	});
	layer.full(index);
}
/*产品-添加*/
function product_add(title,url){
	var index = layer.open({
		type: 2,
		title: title,
		content: url
	});
	layer.full(index);
}
/*用户-添加*/
function member_add(title,url,w,h){
	layer_show(title,url,w,h);
}

var list=document.getElementById('ul1').children;//获取所有的li标签
for(var i=0;i<list.length;i++) {//遍历每一个li标签
   function outer ( ) {
     var num=i+1;
     function inner ( ) {
        $.post("session.php",{"app":""},function(data){
            //alert("aaa:"+data)
			if(data==1)
			{
				 alert('请重新登陆！');window.parent.location.href='login.php';
			}
		})	
     }
     return inner;
   }
   //给每一个li标签注册单击事件
   list[i].onclick=outer();
}
</script> 


<style>
.copyrights{text-indent:-9999px;height:0;line-height:0;font-size:0;overflow:hidden;}
</style>
<div class="copyrights" id="links20210126">
	Collect from <a href="#"  title="旅行小程序后台">旅行小程序后台</a>
	<a href="#" title="旅行小程序后台">旅行小程序后台</a>
</div>
</body>
</html>