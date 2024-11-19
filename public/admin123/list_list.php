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
<link rel="stylesheet" type="text/css" href="static/h-ui/css/H-ui.min.css" />
<link rel="stylesheet" type="text/css" href="static/h-ui.admin/css/H-ui.admin.css" />
<link rel="stylesheet" type="text/css" href="lib/Hui-iconfont/1.0.8/iconfont.css" />
<link rel="stylesheet" type="text/css" href="static/h-ui.admin/skin/default/skin.css" id="skin" />
<link rel="stylesheet" type="text/css" href="static/h-ui.admin/css/style.css" />
<!--[if IE 6]>
<script type="text/javascript" src="lib/DD_belatedPNG_0.0.8a-min.js" ></script>
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<title>转账列表</title>
</head>
<body>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span>上分记录<a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<!--<div class="text-c">
		<button onclick="removeIframe()" class="btn btn-primary radius">关闭选项卡</button>
	 <span class="select-box inline">
		<select name="" class="select">
			<option value="0">全部分类</option>
			<option value="1">分类一</option>
			<option value="2">分类二</option>
		</select>
		</span> 日期范围：
		<input type="text" onfocus="WdatePicker({ maxDate:'#F{$dp.$D(\'logmax\')||\'%y-%M-%d\'}' })" id="logmin" class="input-text Wdate" style="width:120px;">
		-
		<input type="text" onfocus="WdatePicker({ minDate:'#F{$dp.$D(\'logmin\')}',maxDate:'%y-%M-%d' })" id="logmax" class="input-text Wdate" style="width:120px;">
		<input type="text" name="" id="" placeholder=" 资讯名称" style="width:250px" class="input-text">
		<button name="" id="" class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 搜资讯</button>
	</div>-->
	<div class="text-c"> 
		<input type="text" name="" id="del_time" value="<?php echo date("Y-m-d",strtotime("-1 day"));?>" style="width:250px" class="input-text">
		<a  class="btn btn-danger"  onClick="article_stop(this,0)" href="javascript:;"><i class="Hui-iconfont">&#xe6e2;</i>下注记录</a>
	</div>
	<div class="mt-20">
		<table class="table table-border table-bordered table-bg table-hover table-sort table-responsive">
			<thead>
				<tr class="text-c">
					<th>用户账号</th>
					<th>期号</th>  
					<th>投注信息</th>             
	                <th>投注量</th>
	                <th>收益</th> 
	                <th>投注时间</th>
	                <th>操作</th>  
				</tr>
			</thead>
			<tbody>
		<?php
        require("../conn.php");
        $sql="select  * from list  order by Id desc";		
        //echo $sql;		
        $rs=mysqli_query($conn,$sql);
        while($row=mysqli_fetch_assoc($rs))
        {
        	$arr=json_decode($row["y_title"]);
            ?>
            <tr class="text-c">
               
                <td><?php echo $row["user"]?></td>                
                <td>第<?php echo $row["q_id"]?>轮<?php echo $row["q_title"]?>局</td>               
                <td><?php echo "[黑桃:".$arr[0]."],[红心:".$arr[1]."],[草花:".$arr[2]."],[方块:".$arr[3]."],[大小王:".$arr[4]."]";?></td>
                <td><?php echo array_sum($arr);?></td>
                <td><?php echo $row["jieguo"]?></td>
                <td><?php echo $row["time"]?></td>
                <td>
                	 <a style="text-decoration:none" onClick="article_del(this,<?php echo $row["Id"]?>)" href="javascript:;" title="删除"><i class="Hui-iconfont">&#xe6e2;</i></a>
                </td>
               
            </tr>
            <?php
        }
        ?>
			</tbody>
		</table>
	</div>
</div>
<!--_footer 作为公共模版分离出去-->
<script type="text/javascript" src="lib/jquery/1.9.1/jquery.min.js"></script> 
<script type="text/javascript" src="lib/layer/2.4/layer.js"></script>
<script type="text/javascript" src="static/h-ui/js/H-ui.min.js"></script> 
<script type="text/javascript" src="static/h-ui.admin/js/H-ui.admin.js"></script> <!--/_footer 作为公共模版分离出去-->

<!--请在下方写此页面业务相关的脚本-->
<script type="text/javascript" src="lib/My97DatePicker/4.8/WdatePicker.js"></script> 
<script type="text/javascript" src="lib/datatables/1.10.0/jquery.dataTables.min.js"></script> 
<script type="text/javascript" src="lib/laypage/1.2/laypage.js"></script>
<script type="text/javascript">
$('.table-sort').dataTable({
	"aaSorting": [[ 2, "desc" ]],//默认第几个排序
	"bStateSave":false,//状态保存
	"pading":false,
	"aoColumnDefs": [
	  //{"bVisible": false, "aTargets": [ 3 ]} //控制列的隐藏显示
	  {"orderable":false,"aTargets":[0,1]}// 不参与排序的列
	]
});

/*资讯-添加*/
function article_add(title,url,w,h){
	var index = layer.open({
		type: 2,
		title: title,
		content: url
	});
	layer.full(index);
}
/*资讯-编辑*/
function article_edit(title,url,id,w,h){
	var index = layer.open({
		type: 2,
		title: title,
		content: url
	});
	layer.full(index);
}
/*资讯-删除*/
function article_del(obj,id){
	layer.confirm('确认要删除吗？',function(index){
		$.post("php.php",{"app":"list_del","id":id},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('已删除!',{icon:1,time:1000});
			}
		})	
	});
}

/*资讯-发布*/
function article_stop(obj,id){
	layer.confirm('确认要删除吗？',function(index){
		var times=$("#del_time").val();
		console.log(times);
		$.post("php.php",{"app":"list_time_del","times":times},function(data){
			console.log(data);
			if(data==1)
			{
				layer.msg('已删除!',{icon:1,time:1000},function(){
					location.reload();
				});
				
			}
		})	
	});
}



/*资讯-发布*/
function article_start(obj,id){
	layer.confirm('确认要启用吗？',function(index){
		$.post("php.php",{"app":"type_user","id":id,"type":0},function(date){
			if(date==1)
			{
				$(obj).parents("tr").find(".td-manage").prepend('<a style="text-decoration:none" onClick="article_stop(this,'+id+')" href="javascript:;" title="禁用"><i class="Hui-iconfont">&#xe6e0;</i></a>');
				$(obj).parents("tr").find(".td-status").html('<span class="label label-success radius">正常</span>');
				$(obj).remove();
				layer.msg('启动成功!',{icon: 6,time:1000});
			}
			else
			{
				layer.msg('启动失败!',{icon: 5,time:1000});
			}
			
		});
	});
}

</script> 
</body>
</html>