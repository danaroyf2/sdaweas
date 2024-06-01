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
<title>用户列表</title>
<style>
#TRX
{
    color: #9D9D9D;
    background-color: #9D9D9D;
    
}
#yangshi
{
    width: 100px;
    height: 50px;
    background-color: #0a6999;
    border: 1px;
    font-size: 16px;
    color: #FFFFFF ;
   line-height:50px;
     text-align: center;
     font-weight:bold;
     margin:0 auto;
     border-radius: 25px;
}

    
</style>
</head>
<body>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 用户列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
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
	<!--<div class="cl pd-5 bg-1 bk-gray mt-20"> 
		<span class="l"><a href="javascript:;" onclick="datadel()" class="btn btn-danger radius"><i class="Hui-iconfont">&#xe6e2;</i> 批量删除</a> 
		<a class="btn btn-primary radius" data-title="添加资讯" data-href="article-add.html" onclick="Hui_admin_tab(this)" href="javascript:;"><i class="Hui-iconfont">&#xe600;</i> 添加资讯</a>
		</span> <span class="r">共有数据：<strong>54</strong> 条</span> 
	</div>-->
	<div class="mt-20">
		<table class="table table-border table-bordered table-bg table-hover table-sort table-responsive">
			<thead>
				<tr class="text-c">
				    <th>用户编号</th>
				    <th>用户昵称</th>
				    <th>身高</th>
				    <th>体重</th>
				    <th>性别</th>
				    <th>电话</th>
					<th>会员到期时间</th>
					<th>积分</th>
					<th>查看订单</th>
	              	<th>身份</th>
	              	


				</tr>
			</thead>
			<tbody>
		<?php
        require("conn/conn.php");
        $date = date('Y-m-d H:i:s' );
        $sql="select  * from user  order by Id desc";		
        //echo $sql;		
        $rs=mysqli_query($conn,$sql);
        while($row=mysqli_fetch_assoc($rs))
        {
            ?>
            <tr class="text-c">
               
                <td><?php echo $row["id"]?></td>
                <td ><?php echo $row["name"]?></td>
                 <td ><?php echo $row["heigth"]?></td>
                  <td ><?php echo $row["weight"]?></td>
                  
                     <td ><?php  
                     if($row["sex"]==0)
                     {
                         echo "女";
                     }
                     else{
                         echo "男";
                     }
                     
                     ?></td>
                  
               
               
                <td ><?php echo $row["ipone"]?></td>
                <td><?php
                if($row["hytime"]>=$date)
                {
                    echo $row["hytime"];
                }
                else
                {
                    echo "非会员";
                }
                ?></td>
                <td><?php echo $row["jifen"]?></td>
                <td><a href="12fanshui.php?id=<?php echo $row["id"]?>"><div id="yangshi">查看订单</div></a></td>
                <td><?php
                if($row["jiaolian"]==1)
                {
                ?>
                <a style="text-decoration:none; color: #FFFFFF ;" onClick="dltype1(this,<?php echo $row["id"]?>)" href="javascript:;" title="教练"> <div id="yangshi">教练</div></a> 
                
                    <?php
                }
                else {
                     ?>
                    <a style="text-decoration:none;color: #FFFFFF ;" onClick="dltype2(this,<?php echo $row["id"]?>)" href="javascript:;" title="学员"><div id="yangshi">学员</div> </a>
                   <?php
                }
                
                
                ?></td>
                
                
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
	"aaSorting": [[ 0, "desc" ]],//默认第几个排序
	"bStateSave": true,//状态保存
	"pading":false,
	"aoColumnDefs": [
	  //{"bVisible": false, "aTargets": [ 3 ]} //控制列的隐藏显示
	  {"orderable":false,"aTargets":[1]}// 不参与排序的列
	]
});

/*同意*/
function dltype1(obj,id){
	layer.confirm('确认将用户调整成会员吗？',function(index){
		$.post("php.php",{"app":"dltype1","id":id,},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('执行成功!',{icon:1,time:1000});
				location.reload();
			}
		})	
	});
}
/*驳回*/
function dltype2(obj,id){
	layer.confirm('确认将用户调整成教练吗？',function(index){
		$.post("php.php",{"app":"dltype2","id":id,},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('执行成功!',{icon:1,time:1000});
				location.reload();
			}
		})	
	});
}



/*驳回*/
function dltype3(obj,id){
	layer.confirm('确定加入白名单吗？',function(index){
		$.post("php.php",{"app":"dltype3","id":id,},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('已加入白名单!',{icon:1,time:1000});
				location.reload();
			}
		})	
	});
}
function dltypelh(obj,id){
	layer.confirm('确定拉黑吗？',function(index){
		$.post("php.php",{"app":"dltypelh","id":id,},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('已拉黑!',{icon:1,time:1000});
				location.reload();
			}
		})	
	});
}

/*驳回*/
function dltype4(obj,id){
	layer.confirm('确定加入白名单吗？',function(index){
		$.post("php.php",{"app":"dltype4","id":id,},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('已取消该用户白名单!',{icon:1,time:1000});
				location.reload();
			}
		})	
	});
}
/*驳回*/
function dltypelb(obj,id){
	layer.confirm('确定移出黑名单吗？',function(index){
		$.post("php.php",{"app":"dltypelb","id":id,},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('已移出!',{icon:1,time:1000});
				location.reload();
			}
		})	
	});
}


/*驳回*/
function dltype5(obj,id){
	layer.confirm('确定取消流水代理吗？',function(index){
		$.post("php.php",{"app":"dltype5","id":id,},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('已取消!',{icon:1,time:1000});
				location.reload();
			}
		})	
	});
}


/*驳回*/
function dltype6(obj,id){
	layer.confirm('确定取消赢负代理吗？',function(index){
		$.post("php.php",{"app":"dltype6","id":id,},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('已取消!',{icon:1,time:1000});
				location.reload();
			}
		})	
	});
}


/*驳回*/
function dltypesc(obj,id){
	layer.confirm('确定删除吗？',function(index){
		$.post("php.php",{"app":"dltypesc","id":id,},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('已删除!',{icon:1,time:1000});
				location.reload();
			}
		})	
	});
}
</script> 
</body>
</html>