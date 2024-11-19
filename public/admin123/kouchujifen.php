<?php
require("conn/conn.php");
$date = date('Y-m-d');
$datemm = date('Y-m');
$banyue = date("Y-m-d",strtotime("-7 day"));
          $jifen_sql="select * from user where jifen>4999";//  查询用户表里面有VIP等级的会员地址
	      $rsjifen=mysqli_query($conn,$jifen_sql);
          while($rowjifen=mysqli_fetch_assoc($rsjifen))
          { 
             $VIPadds=$rowjifen["adds"];//VIP地址
             $yuejifent_sql="select sum(tliushui) as trxzonghe from caibao where adds='$VIPadds' and shijian between '$banyue' and '$date'";
             
             //select sum(tliushui) as trxzonghe from caibao where adds='TKm9WeESTKXYfHkekVRWtHQgSkvrvphSNe' and shijian like  DATE_SUB(CURDATE(), INTERVAL 15 DAY) <= date(shijian)
            //select sum(tliushui) as trxzonghe from caibao where adds='TRb78wTsvNfiQrAiD9PEn9iTBKG2TSHDKM' and shijian between '2023-02-23' and '2023-02-23'
             $rsyuejifent=mysqli_query($conn,$yuejifent_sql);
             $rowyuejifent=mysqli_fetch_assoc($rsyuejifent);
             $yuejifenu_sql="select sum(uliushui) as usdtzonghe from caibao where adds='$VIPadds' and shijian between '$banyue' and '$date'";
             $rsyuejifenu=mysqli_query($conn,$yuejifenu_sql);
             $rowyuejifenu=mysqli_fetch_assoc($rsyuejifenu);
             //计算积分本月总和
             $trxzonghe=$rowyuejifent["trxzonghe"];
             $usdtzonghe=$rowyuejifenu["usdtzonghe"];
             $trxjifen1=$trxzonghe/20;
             $trxjifen=round($trxjifen1,2);
             $usdtjifen=round($usdtzonghe,2);
             $yuezongjifen=$trxjifen+$usdtjifen;  //计算本月所产生的积分总和
             //获取用户当前VIP等级以及需要保底的积分数量
             $VIPdengji=$rowjifen["dengji"];
             if($VIPdengji==3)
             {
                $suoxujifen=1000; 
             }
             else if($VIPdengji==4)
             {
                 $suoxujifen=4000;
             }
             else if($VIPdengji==5)
             {
                   $suoxujifen=10000;
             }
              else if($VIPdengji==6)
             {
                   $suoxujifen=20000;
             }
              else if($VIPdengji==7)
             {
                   $suoxujifen=40000;
             }
              else if($VIPdengji==8)
             {
                   $suoxujifen=100000;
             }
              else if($VIPdengji==9)
             {
                   $suoxujifen=200000;
             }
            
             
              if($yuezongjifen>=$suoxujifen)
              {
               
              }
              else 
              {
                $jifen= $rowjifen["jifen"]-$suoxujifen;

                if($jifen>=1000 && $jifen<5000)
                     {
                      $dengji=2;
                     }
                     else if($jifen>=5000 && $jifen<20000)
                     {
                      $dengji=3;
                     }
                     else if($jifen>=20000 && $jifen<50000)
                     {
                      $dengji=4;
                     }
                     else if($jifen>=50000 && $jifen<100000)
                     {
                      $dengji=5;
                     }
                     else if($jifen>=100000 && $jifen<200000)
                     {
                      $dengji=6;
                     }
                     else if($jifen>=200000 && $jifen<500000)
                     {
                      $dengji=7;
                     }
                     else if($jifen>=500000 && $jifen<1000000)
                     {
                     $dengji=8;
                     }
                     else
                     {
                     $dengji=9;
                     }
             
              $yuanjifen=$rowjifen["jifen"];//原有积分
              $yuandengji=$rowjifen["dengji"];//原有等级
              $kouchujifen=$yuanjifen-$jifen;
              
              
                 $caibao_sql="update user set jifen='$jifen',dengji='$dengji' where adds='$VIPadds'";
                 $rs_caibao=mysqli_query($conn,$caibao_sql);
              
              $tuandui_sql="INSERT INTO `baojijijilu`( `adds`, `yuandengji`, `xiandengji`, `yuanjifen`, `xianjifen`, `shijian`, `kouchujifen`, `baojijifen`, `shijijifen`) VALUES ('$VIPadds',$yuandengji,$dengji,$yuanjifen,$jifen,'$date',$kouchujifen,$suoxujifen,$yuezongjifen)";
              $rs_tuandui=mysqli_query($conn,$tuandui_sql);

             
              
              }
             
            
            
             
          }

?>