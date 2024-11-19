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
<script src="laydate/laydate.js"></script> <!-- 改成你的路径 -->
<script>
lay('#version').html('-v'+ laydate.v);
//时间选择器
laydate.render({
  elem: '#test5'
  ,type: 'datetime'
});
laydate.render({
  elem: '#test6'
  ,type: 'datetime'
});
</script>
<!--[if IE 6]>
<script type="text/javascript" src="lib/DD_belatedPNG_0.0.8a-min.js" ></script>
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<!--/meta 作为公共模版分离出去-->

<title>添加团课</title>
</head>
<body>
    		<?php
        require("conn/conn.php");
        function fileimg($file){
            //得到文件名称
            $name = $file['name'];
            $type = strtolower(substr($name,strrpos($name,'.')+1)); //得到文件类型，并且都转化成小写
            $allow_type = array('jpg','jpeg','gif','png'); //定义允许上传的类型
            //判断文件类型是否被允许上传
            if(!in_array($type, $allow_type)){
              //如果不被允许，则直接停止程序运行
              return ;
            }
            //判断是否是通过HTTP POST上传的
            if(!is_uploaded_file($file['tmp_name'])){
              //如果不是通过HTTP POST上传的
              return ;
            }
            $upload_path = "uploads/"; //上传文件的存放路径
            //开始移动文件到相应的文件夹
            $image_name = time().rand(100,999).".".$type;
            if(move_uploaded_file($file['tmp_name'],$upload_path.$image_name)){
              return $image_name;
            }else{
              return 0;
            }
        }
        //添加数据
        if($_POST['add_xinxi'])
        {
                    $id=$_GET["id"];
          
            
            $name=$_POST['name'];//团课名称
            $stshijian=$_POST['stshijian'];//团课开始时间
            $ovshijian=$_POST['ovshijian'];//团课结束时间
            
            $type=$_POST['type'];//团课类型
            $jiaolian=$_POST['jiaolian'];//团课教练
            $zongrenshu=$_POST['zongrenshu'];//可最多参与人数
            $zhuyi=$_POST['zhuyi'];//注意事项
            $renqun=$_POST['renqun'];//适合人群
       
            
            
        	$u_up_sql="update kecheng set name='$name',stshijian='$stshijian',ovshijian='$ovshijian',type='$type',jiaolian='$jiaolian',zhuyi='$zhuyi',zongrenshu='$zongrenshu',renqun='$renqun' where id=$id";
        	
         
            
            
        	if(mysqli_query($conn,$u_up_sql))
        	{
        	    echo '<script type="text/javascript">alert("修改成功");</script >';
        	}
        	else
        	{
        	    echo '<script type="text/javascript">alert("修改失败");</script >';
        	}
        	
        }
        
            ?>
            <?php
            $id=$_GET["id"];
              $sqlcha=" select * from kecheng where id=$id";	
        $rscha=mysqli_query($conn,$sqlcha);
        $rowcha=mysqli_fetch_assoc($rscha);
            
            ?>
<article class="page-container">
	<form action="" method="post" class="form form-horizontal" enctype="multipart/form-data" id="form-member-add">
	    
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>团课名称：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="<?php echo $rowcha["name"];?>" placeholder="" id="name" name="name" style="width:300px;">
			</div>
		</div>
		
	
		
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>团课开始时间：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" placeholder="<?php echo $rowcha["stshijian"];?>"   value="<?php echo $rowcha["stshijian"];?>" id="test5" name="stshijian" style="width:300px;">
			</div>
				</div>
		
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>团课结束时间：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" placeholder="<?php echo $rowcha["ovshijian"];?>" value="<?php echo $rowcha["ovshijian"];?>" id="test6" name="ovshijian" style="width:300px;">
			</div>
				</div>
		<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>团课类型：</label>
			<div class="formControls col-xs-8 col-sm-9">
			    <span class="select-box" style="width:300px">
                  <select class="select" size="1" name="type">
                    <option value="默认select" selected>----请选择-----</option>
                          <?php
                      $fanshui_sql1="select * from tuantype";//  查询前一天符合条件投注的用户所有订单
	      $rsfanshui1=mysqli_query($conn,$fanshui_sql1);
          while($rowfanshui1=mysqli_fetch_assoc($rsfanshui1))
          { 
                    ?>
                    <option value="<?php echo $rowfanshui1["id"]?>"><?php echo $rowfanshui1["name"]?></option>
<?php
}
?>   
                  </select>
                </span>
			</div>
			</div>
			<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>团课教练：</label>
			<div class="formControls col-xs-8 col-sm-9">
			    <span class="select-box" style="width:300px">
                  <select class="select" size="1" name="jiaolian">
                    <option value="默认select" selected>----请选择-----</option>
                   <?php
                      $fanshui_sql="select * from jiaolian";//  查询前一天符合条件投注的用户所有订单
	      $rsfanshui=mysqli_query($conn,$fanshui_sql);
          while($rowfanshui=mysqli_fetch_assoc($rsfanshui))
          { 
                    ?>
                    <option value="<?php echo $rowfanshui["id"]?>"><?php echo $rowfanshui["name"]?></option>
                   
                    <?php
          }
                    ?>
                  </select>
                </span>
			</div>
			</div>
				<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>可最多参与人数：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="<?php echo $rowcha["zongrenshu"];?>" placeholder="" id="zongrenshu" name="zongrenshu" style="width:300px;">
			</div>
			
	</div>
	<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>注意事项：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="<?php echo $rowcha["zhuyi"];?>" placeholder="" id="zhuyi" name="zhuyi" style="width:300px;">
			</div>
			
				</div>
				<div class="row cl">
			<label class="form-label col-xs-4 col-sm-3"><span class="c-red">*</span>适合人群：</label>
			<div class="formControls col-xs-8 col-sm-9">
				<input type="text" class="input-text" value="<?php echo $rowcha["renqun"];?>" placeholder="" id="renqun" name="renqun" style="width:300px;">
			</div>
			</div>
			
	
			
<br /><br />
		<div class="row cl">
			<div class="col-xs-8 col-sm-9 col-xs-offset-4 col-sm-offset-3">
				<input class="btn btn-primary radius" type="submit" name="add_xinxi" value="&nbsp;&nbsp;修改&nbsp;&nbsp;">
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
 <script type="text/javascript" charset="utf-8" src="ueditor/ueditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="ueditor/ueditor.all.js"> </script>
    <!--建议手动加在语言，避免在ie下有时因为加载语言失败导致编辑器加载失败-->
    <!--这里加载的语言文件会覆盖你在配置项目里添加的语言类型，比如你在配置项目里配置的是英文，这里加载的中文，那最后就是中文-->
    <script type="text/javascript" charset="utf-8" src="ueditor/lang/zh-cn/zh-cn.js"></script>
	<script type="text/javascript" charset="utf-8" src="ueditor/demo.js"></script>

<script type="text/javascript">
$(function(){
	$('.skin-minimal input').iCheck({
		checkboxClass: 'icheckbox-blue',
		radioClass: 'iradio-blue',
		increaseArea: '20%'
	});
	
// 	$("#form-member-add").validate({
// 		rules:{
// 			username:{
// 				required:true,
// 				minlength:2,
// 				maxlength:16
// 			},
// 			api:{
// 				required:true,
// 			},
// 			mobile:{
// 				required:true,
// 			},
// 		},
// 		onkeyup:false,
// 		focusCleanup:true,
// 		success:"valid",
// 		submitHandler:function(form){
		    
// 			var imgs=$("#imgs").val();
		
// 			$.post("php.php",{"app":"yangmaodang","imgs":imgs},function(date){
//           		console.log(date);  
//           		if(date==1)
//           		{
//           			alert("生成成功");
//           		window.location.reload(true);
          			
//           				parent.layer.close(index);
//           		}
//           		else
//           		{
//           			alert("生成失败，卡类型有误");
//           			window.location.reload(true);
//           		}        		
//           	});
// 			//$(form).ajaxSubmit();
// 			//var index = parent.layer.getFrameIndex(window.name);
// 			//parent.$('.btn-refresh').click();
			
// 		}
// 	});
});
</script> 
<!--/请在上方写此页面业务相关的脚本-->
</body>
</html>