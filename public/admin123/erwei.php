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

<title>查看二维码</title>
</head>
<body>
    <?php
     require("conn/conn.php");
      $name=$_GET['name'];
      $img=$_GET['img'];
    ?>
    <div style="text-align: center;">
<img src="uploads/<?php echo $img?>"><br>
姓名：<?php echo $name?>
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