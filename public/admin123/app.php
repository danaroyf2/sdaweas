<?php
$arr;
$dq_time=date("Y-m-d H:i:s");  
$json_string=file_get_contents('wei.json');
$arr=json_decode($json_string,true);  //将json字符串转成php数组
if($arr=="" || !array_key_exists("pai", $arr))
{
	new_yilun();
	$json_string=file_get_contents('wei.json');
	$arr=json_decode($json_string,true);
}
if(array_key_exists("pai", $arr) && ($arr["pai"][0]=="null" || $arr["pai"][0]=="" || strtotime(end($arr["pai"])[1])<=strtotime($dq_time)))
{
	new_yilun();
	$json_string=file_get_contents('wei.json');
	$arr=json_decode($json_string,true); 

}
	$k_arr["title"]=$arr["title"];
	$w_arr["title"]=$arr["title"];
	if(count($arr["pai"])<100)
	{
		$ky_json_string=file_get_contents('kai.json');
		$ky_arr=json_decode($ky_json_string,true);
		if($ky_arr["pai"]!=null)
		{
			$k_arr["pai"]=$ky_arr["pai"];
		}
		
	}
	//print_r($arr["pai"]);
	for($i=0;$i<count($arr["pai"]);$i++)
	{
		$times=$arr["pai"][$i][1];		
		$times=date("Y-m-d H:i:s",strtotime("$times -9 second"));
		//print_r($arr["pai"][$i][1]."<br>");
		if(strtotime($times)<=strtotime($dq_time))
		{
			$k_arr["pai"][]=$arr["pai"][$i];
		}
		else
		{
			$w_arr["pai"][]=$arr["pai"][$i];
		}
		
	}	
	$fen_arr=array(0,0,0,0,0);
	$json_string=json_encode($fen_arr);  
	file_put_contents('fenshu.json',$json_string); 
	file_put_contents('kai.json',json_encode($k_arr)); 
	file_put_contents('wei.json',json_encode($w_arr)); 




function new_yilun()
{
	require("../conn.php");
	$sql="select * from kjiang where DATE_FORMAT(times,'%Y%m%d %H%i%s')<=DATE_FORMAT(now(),'%Y%m%d %H%i%s') order by times desc";
	$rs=mysqli_query($conn, $sql);
	$row=mysqli_fetch_assoc($rs);
	$json=json_decode($row["json_data"]);
	$arr1=array(
		"title"=>$row["title"],
		"pai"=>$json
	);
	$k_tims=array(
		"title"=>$row["title"],
		"pai"=>$json["pai"][0][1],
	);
	file_put_contents('kai.json',json_encode($k_tims)); 
	$json_string=json_encode($arr1);  
	file_put_contents('wei.json',$json_string); 
	
	
	
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	date_default_timezone_set('PRC');
 
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	date_default_timezone_set('PRC');
 
	/** 引入PHPExcel */
	require_once '../PHPExcel.php';
 	$ex_url='../zips/第'.$row["title"].'轮.xls';
	$objPHPExcel=new PHPExcel();
	$objPHPExcel->getSecurity()->setLockWindows(true);
	$objPHPExcel->getSecurity()->setLockStructure(true);
	$objPHPExcel->getSecurity()->setWorkbookPassword("abc");
	$colArr = array("A","B","C","D","E","F");
	$arr;
	$j=0;
	$h=1;
	//print_r($json);
	for($i=0;$i<100;$i++)
	{
		$neirong=$json[$i][4].$json[$i][5];
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colArr[$j].$h,$neirong);
		$j++;
		if (($i + 1) % 6 == 0) {
			$j=0;
			$h++;
		}
	
	}
	$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
	$objWriter->setTempDir(".");
	@$objWriter->save($ex_url);
	
	
	
	
	$pwd=$row["pwd"];
	$zipArc = new ZipArchive();
	$url='../zips/第'.$row["title"].'轮.zip';
	if ($zipArc->open($url, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
	
	//设置密码 注意此处不是加密,仅仅是设置密码
	
		if (!$zipArc->setPassword($pwd)) {
		
		throw new RuntimeException('Set password failed');
		
		}
	
	//往压缩包内添加文件
	
	$zipArc->addFile($ex_url);
	
	//加密文件 此处文件名及路径是压缩包内的
	
		if (!$zipArc->setEncryptionName($ex_url, ZipArchive::EM_AES_256)) {
		
		throw new RuntimeException('Set encryption failed');
		
		}
	
	}
	$zipArc->close();

	if($row["json_data"]=='')
	{
		return -1;
	}
	unlink ($ex_url);
}
?>