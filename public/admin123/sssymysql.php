<?php
require("conn/conn.php");
   $date = date('Y-m-d');
   $zuotian = date("Y-m-d",strtotime("-1 day"));
   $addsin = 'TXTcVYhuzXB9E9CU291MLaTJMsthgaUBeW';
   $addsinshuzi = 'TVaP6rcUvYbw2hiEDqbcK9BzqdkSccXine';
   $addsout = 'TUEHo3B8ZU8oN7YR4pdCDXYbHWShtLny4s';
   $tliushui=0;
   $uliushui=0;
          $qingchu="truncate sssycaibao";//清除表数据
	      $rsqingchu=mysqli_query($conn,$qingchu);
          
          
   //and addsin='$addsin' or addsin='$addsinshuzi'
          $fanshui_sql="select distinct addsout from addsin where times like '%$date%'";//  查询前一天符合条件投注的用户所有订单
	      $rsfanshui=mysqli_query($conn,$fanshui_sql);
          while($rowfanshui=mysqli_fetch_assoc($rsfanshui))
          { 
                $yhaddsout=$rowfanshui["addsout"];//获取用户的地址
                //用户用户id

                               
        
                    
                      $yonghuid_sql= "select * from user where adds='".$rowfanshui["addsout"]."'";
                      $yonghuid_rs=mysqli_query($conn,$yonghuid_sql);
                      $rowyonghuid=mysqli_fetch_assoc($yonghuid_rs);
                      $rowyonghuid=$rowyonghuid["id"];//获取用户id
                    
           
            		//判断昨日所有地址里面投注trx的总和
                   	$tzongjine="select sum(money) as trxzonghe from addsin where addsout='".$rowfanshui["addsout"]."' and token='trx' and times like '%$date%'" ;
              
                   	
                	 $trizonge=mysqli_query($conn,$tzongjine);
                	 $rowtrizonge=mysqli_fetch_assoc($trizonge);
                	 $trxzonghe=$rowtrizonge["trxzonghe"];
                	 //计算反水千分之三比例
                	 $tfanshui=floor($trxzonghe)*0.002;
                    //判断昨日所有地址里面投注USDT的总和
                     $uzongjine="select sum(money) as usdtzonghe from addsin where addsout='".$rowfanshui["addsout"]."' and token='trc20-usdt' and times like '%$date%'" ;
                     $urizonge=mysqli_query($conn,$uzongjine);
                     $rowusdtzonge=mysqli_fetch_assoc($urizonge);
                	 $usdtzonghe=$rowusdtzonge["usdtzonghe"];
                	  //计算反水千分之三比例
                     $ufanshui=floor($usdtzonghe)*0.002;
                      //计算出款钱包给地址用户转TRX总和
                     $sql_trxouthe="select sum(zjmoney) as outtrxhe from addsin where addsout='".$rowfanshui["addsout"]."' and token='trx' and times like '%$date%'" ;
                	 $rs_trxouthe=mysqli_query($conn,$sql_trxouthe);
                	 $row_trxouthe=mysqli_fetch_assoc($rs_trxouthe);
                	 $outtrxzonghe1=$row_trxouthe["outtrxhe"];
                     
                     //计算出款钱包给地址用户转USDT总和
                     $sql_usdtouthe="select sum(zjmoney) as outusdthe from addsin where addsout='".$rowfanshui["addsout"]."' and token='trc20-usdt' and times like '%$date%'" ;
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
                

             }
?>