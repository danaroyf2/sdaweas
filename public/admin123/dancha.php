<?php
require("conn/conn.php");
   $date = date('Y-m-d');
   $zuotian = date("Y-m-d",strtotime("-1 day"));

   $tliushui=0;
   $uliushui=0;
       
          
   //and addsin='$addsin' or addsin='$addsinshuzi'
     
                $yhaddsout="TXMaNiXewefdfHGfiSte7EaEjZvftyynpM";//获取用户的地址
                //用户用户id

                               
        
                    
                      $yonghuid_sql= "select * from user where adds='".$yhaddsout."'";
                      $yonghuid_rs=mysqli_query($conn,$yonghuid_sql);
                      $rowyonghuid=mysqli_fetch_assoc($yonghuid_rs);
                      $rowyonghuid=$rowyonghuid["id"];//获取用户id
                    
           
            		//判断昨日所有地址里面投注trx的总和
                   	$tzongjine="select sum(money) as trxzonghe from addsin where addsout='".$yhaddsout."' and token='trx' and times like '%$date%'" ;
              
                   	
                	 $trizonge=mysqli_query($conn,$tzongjine);
                	 $rowtrizonge=mysqli_fetch_assoc($trizonge);
                	 $trxzonghe=$rowtrizonge["trxzonghe"];
                	 //计算反水千分之三比例
                	 $tfanshui=floor($trxzonghe)*0.002;
                    //判断昨日所有地址里面投注USDT的总和
                     $uzongjine="select sum(money) as usdtzonghe from addsin where addsout='".$yhaddsout."' and token='trc20-usdt' and times like '%$date%'" ;
                     $urizonge=mysqli_query($conn,$uzongjine);
                     $rowusdtzonge=mysqli_fetch_assoc($urizonge);
                	 $usdtzonghe=$rowusdtzonge["usdtzonghe"];
                	  //计算反水千分之三比例
                     $ufanshui=floor($usdtzonghe)*0.002;
                      //计算出款钱包给地址用户转TRX总和
                     $sql_trxouthe="select sum(money) as outtrxhe from addsout where addsin='".$yhaddsout."' and token='trx' and times like '%$date%'" ;
                	 $rs_trxouthe=mysqli_query($conn,$sql_trxouthe);
                	 $row_trxouthe=mysqli_fetch_assoc($rs_trxouthe);
                	 $outtrxzonghe1=$row_trxouthe["outtrxhe"];
                     
                     //计算出款钱包给地址用户转USDT总和
                     $sql_usdtouthe="select sum(money) as outusdthe from addsout where addsin='".$yhaddsout."' and token='trc20-usdt' and times like '%$date%'" ;
                	 $rs_usdtouthe=mysqli_query($conn,$sql_usdtouthe);
                	 $row_usdtouthe=mysqli_fetch_assoc($rs_usdtouthe);
                	 $outusdtzonghe1=$row_usdtouthe["outusdthe"];
                
                	  $trxlirun=round($trxzonghe,2)-round($outtrxzonghe1,2);//投注的金额-给他回款的金额
                	  $usdtlirun=round($usdtzonghe,2)-round($outusdtzonghe1,2);
                	  
                	  if($trxzonghe>9 || $usdtzonghe>9)
                	  {
                	     	  
                     //写入财报表
                    $caibao_sql="insert into sssycaibao (uid,adds,tliushui,uliushui,tshuying,ushuying,shijian,ufanshui,tfanshui) values ('$rowyonghuid','$yhaddsout','$trxzonghe','$usdtzonghe','$trxlirun','$usdtlirun','$date','$ufanshui','$tfanshui')";


                    $rs_caibao=mysqli_query($conn,$caibao_sql); 
                	  }
                

           
?>