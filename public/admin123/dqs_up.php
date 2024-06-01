<!--_meta 作为公共模版分离出去-->
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
<!--/meta 作为公共模版分离出去-->

<title>转账</title>
</head>
<body>
<?php
require("../conn.php");
$id=isset($_GET["id"])?	$_GET["id"]:0;
$sql="select * from kjiang where Id=$id";
$rs=mysqli_query($conn, $sql);
$row=mysqli_fetch_assoc($rs);
?>
<article class="page-container">
	<form action="" method="post" class="form form-horizontal" id="form-member-add">
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>轮数：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="<?php echo $row["title"]?>" disabled="disabled" id="user" name="username" style="width:300px;">
			</div>
		</div>
		<div class="row cl" >
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>开始时间：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="<?php echo $row["times"]?>" disabled="disabled" id="f" name="mobile" style="width:300px;">
			</div>
		</div>
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>局数列表：</label>
			<div class="formControls col-xs-8 col-sm-9" id="jushu">
				<?php
                	$arr=json_decode($row["json_data"]);
					for($i=0;$i<100;$i++)
					{
						?>
						<input type="text" class="input-text jushu" value="<?php echo $arr[$i][4].$arr[$i][5]?>" placeholder=""  name="ft" style=" text-align:center;width:80px;color:<?php echo $arr[$i][3]?>" date-k="<?php echo $arr[$i][0]?>" data-time = "<?php echo $arr[$i][1]?>" data-urls = "<?php echo $arr[$i][2]?>" data-color = "<?php echo $arr[$i][3]?>" data-title = "<?php echo $arr[$i][4]?>" data-shu = "<?php echo $arr[$i][5]?>">
						<?php
						if(($i+1)%6==0)
						{
							echo "<br>";
						}
					}
				?>
				
			</div>
		</div>
		<div class="row cl">
			<div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
				<input class="btn btn-primary radius" type="submit"  value="&nbsp;&nbsp;编辑&nbsp;&nbsp;">
			</div>
		</div>
	</form>
</article>

<!--_footer 作为公共模版分离出去-->
<script type="text/javascript" src="lib/jquery/1.9.1/jquery.min.js"></script> 
<script type="text/javascript" src="lib/layer/2.4/layer.js"></script>
<script type="text/javascript" src="static/h-ui/js/H-ui.min.js"></script> 
<script type="text/javascript" src="static/h-ui.admin/js/H-ui.admin.js"></script> <!--/_footer 作为公共模版分离出去-->

<!--请在下方写此页面业务相关的脚本--> 
<script type="text/javascript" src="lib/My97DatePicker/4.8/WdatePicker.js"></script>
<script type="text/javascript" src="lib/jquery.validation/1.14.0/jquery.validate.js"></script> 
<script type="text/javascript" src="lib/jquery.validation/1.14.0/validate-methods.js"></script> 
<script type="text/javascript" src="lib/jquery.validation/1.14.0/messages_zh.js"></script>
<script type="text/javascript">
$(function(){
	$('.skin-minimal input').iCheck({
		checkboxClass: 'icheckbox-blue',
		radioClass: 'iradio-blue',
		increaseArea: '20%'
	});
	$("#jushu input").blur(function(){
		var color,urls,shu;
		str=$(this).val();
		title=str.slice(0,2);
		shu=str.slice(2,3);
		//alert("shu:"+shu);
		if(title=="黑桃" || title=="草花")
		{ 
			urls="a"+shu+".jpg";
			color="#000";
			if(title=="草花"){urls="c"+shu+".jpg";}
		}
		if(title=="红心" || title=="方块")
		{
			urls="b"+shu+".jpg";
			color="#F00";
			if(title=="方块"){urls="d"+shu+".jpg";}
		}
		if(title=="大王" || title=="小王")
		{
			color="#fc0";
		}
		$(this).attr("data-urls",urls);
		$(this).attr("data-color",color);
		$(this).attr("data-title",title);
		$(this).attr("data-shu",shu);
		$(this).css("color",color)
	}) 
	$("#form-member-add").validate({
		rules:{
			username:{
				required:true,
				minlength:2,
				maxlength:16
			},
			api:{
				required:true,
			},
			mobile:{
				required:true,
			},
		},
		onkeyup:false,
		focusCleanup:true,
		success:"valid",
		submitHandler:function(form){	
			var $inputArr = $('.jushu');//获取class为resAccount的input对象
			var json_arr=new Array();
			$inputArr.each(function(){
				var arr=new Array();
				var k=$(this).attr("data-k");
				var time=$(this).attr("data-time");
				var urls=$(this).attr("data-urls");
				var color=$(this).attr("data-color");
				var title=$(this).attr("data-title");
				var shu=$(this).attr("data-shu");
				arr.push(k);
				arr.push(time);
				arr.push(urls);
				arr.push(color);
				arr.push(title);
				arr.push(shu);
				json_arr.push(arr);
				
			})
			//console.log(json_arr);
			$.post("php.php",{"app":"dqs_up","json_arr":json_arr,"id":<?php echo $id?>},function(date){
          		console.log(date);  
          		if(date==1)
          		{
          			alert("修改成功");
          			var index = parent.layer.getFrameIndex(window.name);
					parent.layer.close(index);
          		}
          		else
          		{
          			alert("修改失败");
          		}        		
          	});
			//$(form).ajaxSubmit();
			//var index = parent.layer.getFrameIndex(window.name);
			//parent.$('.btn-refresh').click();
			
		}
	});
});
</script> 
<!--/请在上方写此页面业务相关的脚本-->
</body>
</html>