<?php
use think\Db;
use think\Request;



include_once('function/basic.php');
include_once('function/tongbuData.php');
include_once('function/Foxercode.php');
include_once('function/publicfunction.php');
if (!function_exists('db')) {
    /**
     * 实例化数据库类
     * @param string        $name 操作的数据表名称（不含前缀）
     * @param array|string  $config 数据库配置参数
     * @param bool          $force 是否强制重新连接
     * @return \think\db\Query
     */
    function db($name = '', $config = [], $force = false)
    {
        return Db::connect($config, $force)->name($name);
    }
}


function svaeqrlog($content,$login=''){
    $request = request();
    
    $ip=$request->ip();
    //根据ip获取地区
    require PUBLIC_PATH.'/lib/getip2/index.php';
    $diqu=getipinfo($ip);
    $diqu=$diqu['region'];
    //获取操作系统
    $os = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($os, 'Windows') !== false) {  
        $os="操作系统：Windows";  
    } elseif (strpos($os, 'Mac') !== false) {  
        $os="操作系统：Mac";  
    } elseif (strpos($os, 'Linux') !== false) {  
        $os="操作系统：Linux";  
    } else {  
        $os="操作系统未知";  
    }  
    //halt($os);
    
    //halt($diqu);
    $addtime=time();
    
    if(empty($login)){
         $login = $_SESSION['Msg'];
    }
    $service_id=$login['service_id'];
    
    $data=[
        'server'=>$login['nick_name'],
        'service_id'=>$service_id,
        'content'=>$content,
        'time'=>$addtime,
        'ip'=>$ip,
        'diqu'=>$diqu,
        'os'=>$os,
    ];
    
    db('wolive_servererlog')->insert($data);
    
    //halt($ip);
}



 function showTime($time){
    return date('Y年m月d日 H:i:s',$time);
 }


//获取当前域名
//需引入 use think\Request;
function thisyuming()
{
    $request = Request::instance();
    $domain=$request->domain();
    return $domain;
} 


/*

接口网址：
http://wxapi2.jnoo.com/api/wxapijnoo3224/6444897c769ff6b8e13141a78a33c4bf?domain=baidu.com

提交参数（get）：domain=baidu.com 注意：网址出现 & 符号时，请urlencode

返回数据（json）：例：{"status":1,"ret_code":0,"info":"域名正常"}。
返回参数说明：
status：-1参数提交不正确 -2key不正确-3vip已到期-4请求频率过高1域名正常2域名被封3微信内无法正常打开
info:相关说明
ret_code:忽略
到期时间：2024-01-09 23:22:24
请求频率：1秒1次
在线批量检测(需要扫码登录)：http://wxapi.jnoo.com/more
通过接口自己开发定时检测源码demo点击下载
通知接口：https://wxapi.jnoo.com/notify/3224/6444897c769ff6b8e13141a78a33c4bf?msg=测试通知内容&url=baidu.com （参数msg和url你可以自定义，如无法使用请把msg参数urlencode）
http://wxapixg.jnoo.com/api/wxapijnoo3224/6444897c769ff6b8e13141a78a33c4bf?domain=baidu.com
*/

function wxfengjincheck($url){
       $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://wxapi2.jnoo.com/api/wxapijnoo3359/ecc57299fcadcea7eb6233ec637be5e7?domain='.$url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Cookie: PHPSESSID=7o9l7qeka0vtamkl4o8be60nd4'
          ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        $response=json_decode($response,$assoc=true, $depth = 512,$options=0);
        //halt(dump($response['status']==1));
        //只有当检测接口明确返回被封的时候才封
        if(in_array($response['status'],[2,3])){
            return true;
        }else{
            //halt($response);
            return false;
        }
}
       


function wxfengjincheck2($url){
        return true;
        $url='http://'.$url;
        $he=get_headers('http://mp.weixinbridge.com/mp/wapredirect?url='.$url);
        //halt($he);
        if($he[6] !== 'Location: '.$url.''){
            //return false;
            return true;
        }else{
            return true;
        }
}

function wxfengjincheck1($url){
    $url='http://'.$url;
    $api = get_headers('http://mp.weixinbridge.com/mp/wapredirect?url='.$url);
    if($api[6] !== 'Location: '.$url.''){
        $result = false;
    }else{
        $result = true;
    }
    return $result;
}

//通过ip获取城市
function getCityByIP($ip){
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip='.$ip,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_POSTFIELDS => array('json' => 'true','ip' => '39.97.83.93'),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    //echo $response;
    $response=json_decode($response,$assoc=true, $depth = 512,$options=0);
    dump($response);
    
    $ipdata=[
        'type'=>$response['data']['isp'],
        'shen'=>$response['data']['prov'],
    ];
    
    dump($ipdata);
}

