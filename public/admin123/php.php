<?php
ini_set('session.use_trans_sid', 1); //设置时间 
ini_get('session.use_trans_sid');//得到ini中设定值 
ini_set('session.use_only_cookies', 0); //设置时间 
ini_get('session.use_only_cookies');//得到ini中设定值 
ob_start();
session_start();

define('IN_SYS', TRUE);
require("conn/conn.php");
$app=isset($_POST["app"])?$_POST["app"]:$_GET["app"];
if($app=="add_user")
{	
	$user=$_POST["user"];
	$pwd=$_POST["pwd"];
	$f=$_POST["f"];
	$ft=$_POST["ft"];
	$time=date("Y-m-d");
	$show_sql="select * from user where user='$user'";
	$show_rs=mysqli_query($conn, $show_sql);
	if(mysqli_num_rows($show_rs)<=0)
	{
		$in_sql="insert into user (user,pwd,f,ft,times) values ('$user','$pwd',$f,$ft,'$time')";
		if(mysqli_query($conn,$in_sql))echo 1;
	}
}

if($app=="daochu"){
    require_once 'PHPExcel.php';
    $shi=$_POST['shi'];
    $ex_url='excel/'.$shi.'.xls';
    $objPHPExcel=new PHPExcel();
    $objPHPExcel->getSecurity()->setLockWindows(true);
    $objPHPExcel->getSecurity()->setLockStructure(true);
    $objPHPExcel->getSecurity()->setWorkbookPassword("abc");
    
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','昵称');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','电话');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','会员到期时间');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','性别');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','积分');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','生日');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','体重');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H1','身高');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I1','是否是教练');
    $sql="select * from user where ifedit=1";
    $rs=mysqli_query($conn,$sql);
    $i=1;
    while($row=mysqli_fetch_assoc($rs)){
        $j=++$i;
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$j,$row['name']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$j,$row['ipone']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$j,$row['hytime']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$j,$row['sex']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$j,$row['jifen']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$j,$row['sr']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$j,$row['weight']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$j,$row['heigth']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$j,$row['jiaolian']==0?'用户':'教练');
    }
    $objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
    $objWriter->setTempDir(".");
    @$objWriter->save($ex_url);
    echo $ex_url;
}






if($app=="daochu1"){
    require_once 'PHPExcel.php';
    $shi=$_POST['shi'];
    $ex_url='excel/'.$shi.'.xls';
    $objPHPExcel=new PHPExcel();
    $objPHPExcel->getSecurity()->setLockWindows(true);
    $objPHPExcel->getSecurity()->setLockStructure(true);
    $objPHPExcel->getSecurity()->setWorkbookPassword("abc");
    
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','兑换码');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','状态');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','生成时间');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','类型');
   
    $sql="select * from lipinka";
    $rs=mysqli_query($conn,$sql);
    $i=1;
    $tian="未知";
    while($row=mysqli_fetch_assoc($rs)){
        $j=++$i;
        if($row['type']==1)
        {
            $tian="月卡";
        }
         if($row['type']==2)
        {
            $tian="季卡";
        }
         if($row['type']==3)
        {
            $tian="年卡";
        }
         if($row['type']==4)
        {
            $tian="半年";
        }
         if($row['type']==5)
        {
            $tian="天卡";
        }
         if($row['type']==6)
        {
            $tian="周卡";
        }
        
        
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$j,$row['duihuanhao']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$j,$row['zhuangtai']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$j,$row['shijian']);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$j,$tian);
       
    }
    $objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
    $objWriter->setTempDir(".");
    @$objWriter->save($ex_url);
    echo $ex_url;
}











if($app=="zengsong"){
        $uid=$_POST['id'];
        $day=$_POST['cid'];
       $time=date("Y-m-d",time());
     
       $dqi=date("Y-m-d",strtotime("$time +$day day"));
       $u_sql="update user set hytime=if(hytime>'$time',date_add(hytime, interval $day day),'$dqi') where id=$uid";
       if(mysqli_query($conn,$u_sql)){
           $in_sql="INSERT INTO `zengsong`( `uid`, `rqi`, `times`) VALUES ($uid,$day,now())";
           mysqli_query($conn,$in_sql);
           echo 1;
       }
}

if($app=="add_config")
{	
	$telegram=$_POST["telegram"];
	$pd=$_POST["pd"];
	$facebook=$_POST["facebook"];
	$twitter=$_POST["twitter"];
	$kefu=$_POST["kefu"];
		$u_up_sql="update config set telegram='$telegram',pd='$pd',kefu='$kefu',twitter='$twitter',facebook='$facebook'  where id=1";
		if(mysqli_query($conn,$u_up_sql))echo 1;
	
}


if($app=="tixian_ty")
{	
	$tixianid=$_POST["tixianid"];
	$type=$_POST["type"];
    $u_up_sql="update money set type=$type where id=$tixianid";
	if(mysqli_query($conn,$u_up_sql))echo 1;
	
}

//轮播删除

if($app=="txxianchu")
{	
	$id=$_POST["id"];

    $u_up_sql="DELETE FROM `lunbo` WHERE id=$id";
	if(mysqli_query($conn,$u_up_sql))echo 1;
	
}


if($app=="update_user")
{	
	$uid=$_POST["uid"];
	$password=$_POST["password"];
	$beizhu=$_POST["beizhu"];
    $u_up_sql="update user set beizhu='$beizhu',password='$password' where id=".$uid;
	if(mysqli_query($conn,$u_up_sql))echo 1;
	
}



//生成健身房兑换卡卡号
if($app=="yangmaodang")
{	
	$adds=$_POST["adds"];//数量
	$jysid=$_POST["jysid"];//类型
	
	$shijian=date('Y-m-d H:i:s', time());
  
    for($i = 1; $i <=$adds; $i ++)
    {
        // $yonghu_sql="select count(*) as zhuceguo from lipinka";
        // $rsyonghu=mysqli_query($conn,$yonghu_sql);
        // $row_yonghu=mysqli_fetch_assoc($rsyonghu);
       // $bianhao=$row_yonghu["zhuceguo"].time().$row_yonghu["zhuceguo"]-1;
      
        $strs = date('YmdHis') . rand(10000000,99999999);
        //echo $strs."<br>";
       $bianhao=substr(str_shuffle($strs),mt_rand(0,strlen($strs)-11),11);
       // echo $bianhao."<br>";

         $in_sql="insert into lipinka (duihuanhao,type,zhuangtai,overtime,shijian) values ('$bianhao',$jysid,0,'$shijian','$shijian')";
         mysqli_query($conn,$in_sql);
    }
    
echo 1;
}



          

//提现申请同意
if($app=="dltype1")
{	
	$id=$_POST["id"];//用户id
	
    $dltype_sql="update tixian set type=1 where id=$id";
    if(mysqli_query($conn,$dltype_sql))echo 1;

}
//提现申请拒绝
if($app=="dltype2")
{	
	$id=$_POST["id"];//用户id
	$s_sql="select * from tixian where id=$id";
	$s_rs=mysqli_query($conn,$s_sql);
	$s_row=mysqli_fetch_assoc($s_rs);
	$money=$s_row['money'];
	$uid=$s_row['uid'];
    $dltype_sql="update tixian set type=2 where id=$id";
    if(mysqli_query($conn,$dltype_sql)){
        $u_sql="update user set money=money+$money where id=$uid";
        mysqli_query($conn,$u_sql);
        echo 1;
    }

}










//退款申请同意
if($app=="dltype3")
{	
	$id=$_POST["id"];//用户id
	
    $dltype_sql="update dingdan set type=4 where id=$id";
    $show_sql="select * from dingdan where id=$id";
	$show_rs=mysqli_query($conn, $show_sql);
	$show_row=mysqli_fetch_assoc($show_rs);
	$chuid=$show_row["chuid"];
	$dltype_sql11="update user set chushousl=chushousl-1 where id=$chuid";
	mysqli_query($conn,$dltype_sql11);
   
if(mysqli_query($conn,$dltype_sql))echo 1;

}
//退款申请拒绝
if($app=="dltype4")
{	
		$id=$_POST["id"];//用户id
	
    $dltype_sql="update dingdan set type=5 where id=$id";
   
if(mysqli_query($conn,$dltype_sql))echo 1;

}







//关闭城市
if($app=="dltype6")
{	
		$id=$_POST["id"];//用户id
	
    $dltype_sql="update citydata set type=0 where id=$id";
   
if(mysqli_query($conn,$dltype_sql))echo 1;

}

//开启城市
if($app=="dltype7")
{	
		$id=$_POST["id"];//用户id
	
    $dltype_sql="update citydata set type=1 where id=$id";
   
if(mysqli_query($conn,$dltype_sql))echo 1;

}






















//教练离职
if($app=="lizhi")
{	
	$id=$_POST["id"];//用户id
    $dltype_sql="update jiaolian set ipone=1 where id=$id";
   
if(mysqli_query($conn,$dltype_sql))echo 1;

}
//教练复职
if($app=="ruzhi")
{	
	$id=$_POST["id"];//用户id
    $dltype_sql="update jiaolian set ipone=0 where id=$id";
   
if(mysqli_query($conn,$dltype_sql))echo 1;

}

if($app=="dltype5")
{	
	$id=$_POST["id"];//用户id
    $dltype_sql="update user set dltype=0 where id=$id";
if(mysqli_query($conn,$dltype_sql))echo 1;

}

if($app=="dltype6")
{	
	$id=$_POST["id"];//用户id
    $dltype_sql="update user set dltype=0 where id=$id";
if(mysqli_query($conn,$dltype_sql))echo 1;

}




if($app=="dltypesc")
{	
	$id=$_POST["id"];//用户id
    $dltype_sql="DELETE FROM `user` WHERE  id=$id";
if(mysqli_query($conn,$dltype_sql))echo 1;

}













//将用户拉为白名单用户
if($app=="dltype3")
{	
	$id=$_POST["id"];//用户id
    $dltype_sql="update user set bmd=1 where id=$id";
if(mysqli_query($conn,$dltype_sql))echo 1;

}

//将用户拉为黑名单用户
if($app=="dltypelh")
{	
	$id=$_POST["id"];//用户id
    $dltype_sql="update user set bmd=2 where id=$id";
if(mysqli_query($conn,$dltype_sql))echo 1;

}

//将用户黑名单取消
if($app=="dltype4")
{	
	$id=$_POST["id"];//用户id
    $dltype_sql="update user set bmd=0 where id=$id";
if(mysqli_query($conn,$dltype_sql))echo 1;

}




//将用户黑名单取消
if($app=="dltypelb")
{	
	$id=$_POST["id"];//用户id
    $dltype_sql="update user set bmd=0 where id=$id";
if(mysqli_query($conn,$dltype_sql))echo 1;

}

//数据清理
if($app=="qinglishuju")
{	
  $zuotian = date("Y-m-d",strtotime("-2 day"));
  $dltype_sql="DELETE FROM `addsin` WHERE `times`<'$zuotian'";
  mysqli_query($conn,$dltype_sql);
  $dltype_sql1="DELETE FROM `addsout` WHERE `times`<'$zuotian'";
  if(mysqli_query($conn,$dltype_sql1))echo 1;

}






































if($app=="type_user")
{	
	$id=$_POST["id"];
	$type=$_POST["type"];
	$up_uty_sql="update user set type=$type where Id=$id";
	//echo $up_uty_sql;
	if(mysqli_query($conn,$up_uty_sql))echo 1;
}

if($app=="up_user")
{	
	$id=$_POST["id"];
	$pwd=$_POST["pwd"];
	$ft=$_POST["ft"];
	$u_up_sql="update user set pwd='$pwd',ft=$ft  where id='$id'";
	if(mysqli_query($conn,$u_up_sql))echo 1;
}

if($app=="sf_user")
{	
	$user=$_POST["user"];	
	$f=$_POST["f"];
	$show_sql="select * from user where user='$user'";
	$show_rs=mysqli_query($conn, $show_sql);
	$show_row=mysqli_fetch_assoc($show_rs);
	if(mysqli_num_rows($show_rs)>0)
	{
		$u_f_sql="update user set f=f+$f where user='$user'";
		if(mysqli_query($conn,$u_f_sql))
		{
			$sfl_sql="insert into s_f (uid,user,f,time) values (".$show_row['Id'].",'$user',$f,now())";
			mysqli_query($conn, $sfl_sql);
			echo 1;
		}
			
		
	}
}

//if($app=="new_yilun")
//{
//$dq_time=date("Y-m-d");  
//$sql="select * from kjiang where DATE_FORMAT(times,'%Y%m%d %H%i%s')<=DATE_FORMAT(now(),'%Y%m%d %H%i%s') order by times desc";
//$rs=mysqli_query($conn, $sql);
//$row=mysqli_fetch_assoc($rs);
//$arr=array(
//	"title"=>$row["title"],
//	"pai"=>json_decode($row["json_data"])
//);
//$json_string=json_encode($arr);  
//file_put_contents('wei.json',$json_string); 
//return $json_string;
//}
if($app=="user_log")
{
	$user=$_POST["username"];
	$pwd=$_POST["password"];
	$u_sql="select * from user where user='".$user."' and pwd='".$pwd."'";
   	$u_res=mysqli_query($conn,$u_sql);
   	$b=mysqli_fetch_assoc($u_res);
    if($b["user"]!=$user || $b["type"]==1)
	{
	     
    }
    else
    {
    	$ip=time();
    	$up_u_sql="update user set ip='$ip' where Id=".$b["Id"];
		mysqli_query($conn,$up_u_sql);
    	 $_SESSION["user_Id"] = $b["Id"];
		 $_SESSION["user_user"] = $b["user"];
		 $_SESSION["user_ip"] = $ip;
		 echo 1;
	 }
}
if($app=="dqs_up")
{
	$json_arr=json_encode($_POST["json_arr"],JSON_UNESCAPED_UNICODE );;
	$id=$_POST["id"];
	$dqs_sql="update kjiang set json_data='$json_arr' where Id=$id";
	if(mysqli_query($conn, $dqs_sql))
	{
		echo 1;
	}
}
if($app=="user_del")
{
	$id=$_POST["id"];
	$del_sql="delete from user where Id=$id";
	if(mysqli_query($conn, $del_sql))
	{
		$s_f_sql="delete from s_f where uid=$id";
		mysqli_query($conn, $s_f_sql);
		$d_l_sql="delete from list where uid=$id";
		mysqli_query($conn, $d_l_sql);
		echo 1;
	}
}
if($app=="list_del")
{
	$id=$_POST["id"];
	$d_li_sql="delete from list where Id=$id";
	if(mysqli_query($conn, $d_li_sql))
	{
		echo 1;
	}
}
if($app=="list_time_del")
{
	$times=$_POST["times"];
	$dt_li_sql="delete from list where  DATE_FORMAT(time,'%Y%m%d')=DATE_FORMAT(date('$times'),'%Y%m%d')";
	if(mysqli_query($conn, $dt_li_sql))
	{
		echo 1;
	}
}
if($app=="add_gg")
{
	$t1=$_POST["t1"];
	$t2=$_POST["t2"];
	$t3=$_POST["t3"];
	$t4=$_POST["t4"];
	$up_gg_sql="update zip_pwd set  title='$t1',times='$t2',q='$t3',pwd='$t4' where Id=1";
	if(mysqli_query($conn, $up_gg_sql))
	{
		echo 1;
	}
	
}
