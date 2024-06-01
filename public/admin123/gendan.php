<?php
require("../conn.php");
$user_id=isset($_POST["uid"])?$_POST["uid"]:1;
$sql="select * from list where uid=$user_id order by Id desc";
$rs=mysqli_query($conn, $sql);
$row=mysqli_fetch_assoc($rs);
$c=mysqli_num_rows($rs);
$wjyf=json_decode($row["y_title"]);
$z_wjyf=array_sum($wjyf);
if($c>0)
{
	$f_sql="select * from user where Id=$user_id";
	$f_rs=mysqli_query($conn, $f_sql);
	$f_row=mysqli_fetch_assoc($f_rs);
	if($z_wjyf<=$f_row["f"])
	{
		$arr_json=array(
			"z_wjyf"=>array_sum($wjyf),
			"wjyf"=>$wjyf
		);
		$json_string=file_get_contents('fenshu.json');
		$arr=json_decode($json_string,true);  //将json字符串转成php数组
		$arr[0]=$arr[0]+$wjyf[0];
		$arr[1]=$arr[1]+$wjyf[1];
		$arr[2]=$arr[2]+$wjyf[2];
		$arr[3]=$arr[3]+$wjyf[3];
		$arr[4]=$arr[4]+$wjyf[4];
		$json_string=json_encode($arr);  
		file_put_contents('fenshu.json',$json_string); 
		echo json_encode($arr_json);
	}
	else
	{
		echo 0;
	}
	
}
else
{
	echo $c;
}
?>