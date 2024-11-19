<?php
require("conn/conn.php");
 $id=$_GET["id"];
 $sql="select * from shangpin where id=$id";	
        $rs=mysqli_query($conn,$sql);
        while($row=mysqli_fetch_assoc($rs))
        {
         echo $row["text"];
        }
?>