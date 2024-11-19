<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-08 04:37:54
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-11-08 07:03:24
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/api/controller/Question.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\api\controller;
/**
 * 访客
 * 
 */
use think\Db;
use app\admin\iplocation\Ip;

class Fangke extends CRUD{

    public function index(){
        $pagesize=10;
        $page=input('page','1');
        
        $login = $this->serverUser;
        $visiter_name=isset($_POST['visiter_name'])?$_POST['visiter_name']:'';
        $ip=isset($_POST['ip'])?$_POST['ip']:'';
        $map['business_id']=$login['business_id'];
        if($visiter_name!=''){$map=['visiter_name'=>$visiter_name,'business_id'=>$login['business_id']];}
        if($ip!=''){$map=['ip'=>$ip,'business_id'=>$login['business_id']];}
        if($visiter_name!='' && $ip!=''){$map=['visiter_name'=>$visiter_name,'ip'=>$ip,'business_id'=>$login['business_id']];}
        
       
        
        $list=db('wolive_visiter')->where($map)
        ->paginate($pagesize,false,['page' =>$page]);
        //->paginate(9);
        $arr=[];
        foreach ($list as $l) {
            $zctime=db('wolive_chats')->where(['visiter_id'=>$l['visiter_id'],'business_id'=>$login['business_id']])->order('cid asc')->find();
            $lxtime=db('wolive_chats')->where(['visiter_id'=>$l['visiter_id'],'business_id'=>$login['business_id']])->order('cid desc')->find();
             $ips=$l['ip'];
             $l['adds']=Ip::find($ips);
             $l['extends']=json_decode($l['extends']);
             $l['zctime']=$zctime['timestamp']==''?'--':date("Y-m-d H:i:s",$zctime['timestamp']);
             $l['lxtime']=$lxtime['timestamp']==''?'--':date("Y-m-d H:i:s",$lxtime['timestamp']);
             $arr[]=$l;
        }
        $returndata=new \stdClass;
        $returndata->total=db('wolive_visiter')->where($map)->count();
        $returndata->data=$arr;
        $this->success('获取成功!',$returndata);
        
      
    }
}