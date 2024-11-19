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
   
     border-radius: 25px;
}
    
</style>
<!--[if IE 6]>
<script type="text/javascript" src="lib/DD_belatedPNG_0.0.8a-min.js" ></script>
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<title>礼品卡列表</title>
</head>
<body>
    <?php
require("conn/conn.php");
$date = date('Y-m-d');

?>

<!--<a href="fangyangmao.php" class="btn btn-primary radius">礼品卡生成</a>
	<a class="btn btn-primary radius" data-title="导出excel" data-href="article-add.html" onclick="daochu1()" href="javascript:;">导出excel</a>-->
	<div class="mt-20">
		<table class="table table-border table-bordered table-bg table-hover table-sort table-responsive">
			<thead>
				<tr class="text-c">
					<th>礼品卡编号</th>
					<th>礼品卡兑换码</th>
					<th>卡类型</th>
	                <th>生成时间</th>	                                
	                <th>使用状态</th> 
	               
				</tr>
			</thead>
			<tbody>
		<?php
      
        $sql="select  * from lipinka  order by id desc";		
        //echo $sql;		
        $rs=mysqli_query($conn,$sql);
        while($row=mysqli_fetch_assoc($rs))
        {
            ?>
            <tr class="text-c">
               <td><?php echo $row["id"]?></td>
                <td><?php echo $row["duihuanhao"]?></td>
                <td><?php
                  $sql1="select  * from huiyuanka  where id=".$row["type"];		
        //echo $sql;		
        $rs1=mysqli_query($conn,$sql1);
        $row1=mysqli_fetch_assoc($rs1);
        echo $row1["name"];
              
                ?></td>
                <td><?php echo $row["shijian"]?></td>
                <td><?php
                if($row["zhuangtai"]==0)
                {
                    echo "未使用";
                }
                else {
                   echo "使用人编号：".$row["zhuangtai"];
                  
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



          
     
</article>
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
	"aaSorting": [[ 1, "desc" ]],//默认第几个排序
	"bStateSave": true,//状态保存
	"pading":false,
	"aoColumnDefs": [
	  //{"bVisible": false, "aTargets": [ 3 ]} //控制列的隐藏显示
	  {"orderable":false,"aTargets":[]}// 不参与排序的列
	]
});

/*同意*/
function article_del(obj,id){
	layer.confirm('确认要同意吗？',function(index){
		$.post("php.php",{"app":"tixian_ty","tixianid":id,"type":1},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('已提现成功!',{icon:1,time:1000});
			}
		})	
	});
}




//导出
function daochu1(){
    
    layer.confirm('确定导出信息吗？',function(index){
	    var shi= new Date().getTime();
		$.post("php.php",{"app":"daochu1","shi":shi,},function(data){
				layer.msg('导出成功!',{icon:1,time:1000});
				window.location.href="excel/"+shi+'.xls';
				//window.open("excel/"+shi+'.xls');
		})	
	});
        
	   
}






/*删除*/
function txxianchu(obj,id){
	layer.confirm('确认要删除吗？',function(index){
		$.post("php.php",{"app":"txxianchu","tixianid":id,"type":1},function(data){
			if(data==1)
			{
				$(obj).parents("tr").remove();
				layer.msg('已删除!',{icon:1,time:1000});
			}
		})	
	});
}
/*驳回*/
function article_bohui(obj,id){
	layer.confirm('确认要驳回吗？',function(index){
		$.post("php.php",{"app":"tixian_ty","tixianid":id,"type":2},function(data){
			if(data==1)
			{
				$(obj).parents("td").find(".td-manage").html('已驳回');
				layer.msg('已驳回!',{icon:1,time:1000});
				location.reload();
			}
		})	
	});
}
</script> 
</body>
</html>