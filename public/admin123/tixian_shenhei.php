<?php
require("conn/conn.php");
$id=$_GET["id"];
$sql="update money SET type=1 where id=$id";
$rs=mysqli_query($conn,$sql);


$did=$_GET["did"];
$sqljj="update money SET type=2 where id=$did";
$rsjj=mysqli_query($conn,$sqljj);
?>