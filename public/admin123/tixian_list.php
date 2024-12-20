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
<style>
#TRX
{
    color: #9D9D9D;
    background-color: #9D9D9D;
    
}
    
</style>
<!--[if IE 6]>
<script type="text/javascript" src="lib/DD_belatedPNG_0.0.8a-min.js" ></script>
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<title>已提现列表</title>
</head>
<body>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 已提现列表 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
    <?php
      require("conn/conn.php");
     $sql_usdt="select  sum(money) as usdthe from money where type =1 and uort=1";		
     $rs_usdt=mysqli_query($conn,$sql_usdt);
     $row_usdt=mysqli_fetch_assoc($rs_usdt);
     $sql_trx="select  sum(money) as trxhe from money where type =1 and uort=2";		
     $rs_trx=mysqli_query($conn,$sql_trx);
     $row_trx=mysqli_fetch_assoc($rs_trx);
    ?>
   提现USDT总金额：<?php echo round($row_usdt["usdthe"],2)?>   USDT     
    <br/>
     提现TRX总金额：<?php echo round($row_trx["trxhe"],2)?>    TRX 
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
					<th>提现编号</th>
					<th>提现用户ID</th>
					<th>提现用户姓名</th>
	                <th>提现地址</th>	                                
	                <th>提现金额</th> 
	                <th>货币类型</th> 
	                <th>提现时间</th>
	                
				</tr>
			</thead>
			<tbody>
		<?php
      
        $sql="select  * from money where type =1 order by Id desc";		
        //echo $sql;		
        $rs=mysqli_query($conn,$sql);
        while($row=mysqli_fetch_assoc($rs))
        {
            ?>
            <tr class="text-c">
               
                <td><?php echo $row["id"]?></td>
                
      
                <?php
                 $sqluser="select  * from user  where id=".$row["uid"];		
                    //echo $sql;		
                    $rsuser=mysqli_query($conn,$sqluser);
                    while($rowuser=mysqli_fetch_assoc($rsuser))
               {
                ?>
                <td ><?php echo $rowuser["id"]?></td>
                <td><?php echo $rowuser["beizhu"]?></td>
                <td><?php echo $rowuser["adds"]?></td>
                
                <?php
               }
                ?>
                
                
                
                <td><?php echo $row["money"]?></td>
                 <td>
                      	<?php
                	if($row["uort"]==1)
                	{
                		?>
                		<span class="label label-success radius">USDT</span>
                		<?php
                	}
					else if($row["uort"]==2)
					{
						?>
						<span class="label label-defaunt radius">TRX</span>
						<?php
					}	
                	?>
                	
                     
                     
                    </td>
                <td><?php echo $row["times"]?></td>
               
               
			
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
	"aaSorting": [[ 4, "desc" ]],//默认第几个排序
	"bStateSave": true,//状态保存
	"pading":false,
	"aoColumnDefs": [
	  //{"bVisible": false, "aTargets": [ 3 ]} //控制列的隐藏显示
	  {"orderable":false,"aTargets":[0,1,5]}// 不参与排序的列
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
		$.post("php.php",{"app":"user_del","id":id},function(data){
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
	layer.confirm('确认要禁用吗？',function(index){
		$.post("php.php",{"app":"type_user","id":id,"type":1},function(date){
			if(date==1)
			{
				$(obj).parents("tr").find(".td-manage").prepend('<a style="text-decoration:none" onClick="article_start(this,'+id+')" href="javascript:;" title="开启"><i class="Hui-iconfont">&#xe6e1;</i></a>');
				$(obj).parents("tr").find(".td-status").html('<span class="label label-defaunt radius">禁用</span>');
				$(obj).remove();
				layer.msg('禁用成功!',{icon: 6,time:1000});
			}
			else
			{
				layer.msg('启动失败!',{icon: 5,time:1000});
			}
			
		});
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