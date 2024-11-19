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
<title>订单列表</title>
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
				    <th>订单编号</th>
				    <th>购买者</th>
				    <th>出售人</th>
				    <th>商品名称</th>
				    <th>订单时间</th>
				    <th>订单金额</th>
				    <th>订单状态</th>
				     <th>同意</th>
				       <th>驳回</th>
				    
				    
	              	


				</tr>
			</thead>
			<tbody>
		<?php
        require("conn/conn.php");
        $date = date('Y-m-d H:i:s' );
        $sql="select  * from dingdan  order by Id desc";		
        //echo $sql;		
        $rs=mysqli_query($conn,$sql);
        while($row=mysqli_fetch_assoc($rs))
        {
            ?>
            <tr class="text-c">
               
                <td><?php echo $row["id"]?></td>
                 <td >
                <?php
                $sqlbuy="select  * from user where id=".$row["buyid"];		
        //echo $sql;		
        $rsbuy=mysqli_query($conn,$sqlbuy);
    $rowbuy=mysqli_fetch_assoc($rsbuy);
    echo $rowbuy["name"];
                ?>
                       
                    </td>
                <td >
                <?php
                $sqlchu="select  * from user where id=".$row["chuid"];		
        //echo $sql;		
        $rschu=mysqli_query($conn,$sqlchu);
    $rowchu=mysqli_fetch_assoc($rschu);
    echo $rowchu["name"];
                ?>
                       
                    </td>

                 <td >
 <?php
                $sqlsp="select  * from shangpin where id=".$row["spid"];		
        //echo $sql;		
        $rssp=mysqli_query($conn,$sqlsp);
    $rowsp=mysqli_fetch_assoc($rssp);
    echo $rowsp["title"];
                ?>
       
              </td>
              
              <td><?php echo  $row["shijian"] ?></td>
              <td><?php echo  $row["money"] ?></td>
              <td><?php 
              if( $row["type"]==0)
              {
                  echo "未支付";
              }
              if( $row["type"]==1)
              {
                  echo "已支付";
              }
              if( $row["type"]==2)
              {
                  echo "订单取消";
              }
              if( $row["type"]==3)
              {
                  echo "退款中";
              }
               if( $row["type"]==4)
              {
                  echo "已退款";
              }  if( $row["type"]==5)
              {
                  echo "已完成";
              }
            
              
              ?></td>
              
              
              <td>
                  <?php
                    if( $row["type"]==3)
                    {
                        
                        
                        ?>
                          <a style="text-decoration:none; color: #FFFFFF ;" onClick="dltype3(this,'<?php echo $row["dingdan"]?>')" href="javascript:;" title="同意"> <div class="btn btn-success">同意退款</div></a> 
                        <?php
                    }
                  ?>
                  
                   
              </td>
              
                <td>
                  <?php
                    if( $row["type"]==3)
                    {
                        
                        
                        ?>
                                 <a style="text-decoration:none; color: #FFFFFF ;" onClick="dltype4(this,<?php echo $row["id"]?>)"  href="javascript:;" title="驳回"> <div class="btn btn-success">驳回</div></a> 
                        <?php
                    }
                  ?>
                  
                   
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
	"aaSorting": [[ 0, "desc" ]],//默认第几个排序
	"bStateSave": true,//状态保存
	"pading":false,
	"aoColumnDefs": [
	  //{"bVisible": false, "aTargets": [ 3 ]} //控制列的隐藏显示
	  {"orderable":false,"aTargets":[1]}// 不参与排序的列
	]
});

/*同意*/
function dltype3(obj,id){
	layer.confirm('确定退款吗？',function(index){
		$.post("../API/api.php",{"app":"refundNo","dingdan":id,},function(data){
		    console.log(data);
		    var jsonArr = JSON.parse( data );
		     console.log(jsonArr);
		     console.log(jsonArr.data.err_code_des);
			if(jsonArr.code==0)
			{
				layer.msg('执行成功!',{icon:1,time:1000});
				location.reload();
			}
			else
			{
			    layer.msg(jsonArr.data.err_code_des,{icon:1,time:1000});
				//location.reload();
			}
		})	
	});
}
/*驳回*/
function dltype4(obj,id){
	layer.confirm('确定驳回吗？',function(index){
		$.post("php.php",{"app":"dltype4","id":id,},function(data){
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