<?php


/**
 *  删除字符串中的空格和特殊字符
 */
function formatstr($string){
$result = preg_replace('/[^a-zA-Z0-9]/', '', $string);  
return $result;
}

//错误返回
function errorR($data){
     header('Content-Type: application/json');  
     echo(json_encode(['code'=>'-1','data'=>$data]));die();
}
/**
 *  获得完整的域名
 */
function getIntactHost($host){
     if(this_web_port_80!='80' && !empty(this_web_port_80)){
            $host=$host.':'.this_web_port_80;
     }
     return $host;
}


?>