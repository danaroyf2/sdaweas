<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-23 04:51:52
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-12-10 08:11:31
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/function/publicfunction.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

/**
 * 检查卡密是否到期返回提示
 */
function checkendtime($kami){
    $kamiData = db("wolive_kami")
        ->where('kami',$kami)
        ->find();
    if(empty($kamiData)){
        return ['code'=>'0','msg'=>'卡密不正确'];
    }
    $dqtime=$kamiData['dqtime'];
    if(empty($kamiData)){
        return ['code'=>'0','msg'=>'卡密未激活'];
    }
    $thistime=time();
    $dqtimenumber = strtotime($dqtime);  
    if($thistime>$dqtimenumber){
        return ['code'=>'0','msg'=>$dqtime.':卡密已到期'];
    }
    //检查封禁
    $business=db('wolive_business')
            ->where(['business_name'=>$kami])
            ->find();
    if($business['is_recycle']=='1'){
        return ['code'=>'0','msg'=>'卡密封禁'];
    }

    return ['code'=>'1','msg'=>'卡密正常'];
    //halt($kamiData);    
}
/**
 * 获取富文本中的两块
 */
function getfuwenbUrl($str){
    // 正则表达式模式匹配链接  
    $pattern = '/https?:\/\/\S+/';  
    
    // 使用正则表达式提取链接  
    preg_match_all($pattern, $str, $matches);  
    
    // 输出提取到的链接  
    dump($matches);
}
/**
 * url 地址改成a标签
 */
function urlToTag($url){
    return '<a href="'.$url.'" target="_blank" title="111">'.$url.'</a>';
}