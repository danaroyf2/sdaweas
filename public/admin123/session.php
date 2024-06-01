<?php
ob_start();
session_start();
@$user_session=$_SESSION["Adminnamess"];
@$pwd_session=$_SESSION["password"];
header("Content-type: text/html; charset=utf-8");    
error_reporting(E_ALL ^E_NOTICE);

if($user_session == ""){
	echo 1;
	exit;
}else{
	require("conn/conn.php");
	$sql="select * from admin where user='".$user_session."' and password='".$pwd_session."'";
    $res=mysqli_query($conn,$sql);
    $row=mysqli_num_rows($res);
    if($row==0){
        echo 1;
	    exit;
    }
}
?>