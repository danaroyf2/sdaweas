<?php


namespace app\admin\controller;

use app\admin\model\Admins;
use app\admin\model\WechatPlatform;
use app\admin\model\WechatService;
use think\Db;
use think\Paginator;
use app\Common;
use app\admin\iplocation\Ip;

class Fangke extends Base{
    
    public function index(){

        $login = $_SESSION['Msg'];
        $visiter_name=isset($_POST['visiter_name'])?$_POST['visiter_name']:'';
        $ip=isset($_POST['ip'])?$_POST['ip']:'';
        $map['business_id']=$login['business_id'];
        if($visiter_name!=''){$map=['visiter_name'=>$visiter_name,'business_id'=>$login['business_id']];}
        if($ip!=''){$map=['ip'=>$ip,'business_id'=>$login['business_id']];}
        if($visiter_name!='' && $ip!=''){$map=['visiter_name'=>$visiter_name,'ip'=>$ip,'business_id'=>$login['business_id']];}
        $list=db('wolive_visiter')->where($map)->paginate(9);
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
        
       // print_r($arr);
        $this->assign('lists',$arr);
        // $lister=db('wolive_servererlog')
        // ->order('id desc')
        // ->paginate(9);
        //halt($lister);
        $page = $list->render();
      //  $this->assign('lister',$lister);
        $this->assign('part', "访客");
        $this->assign('page', $page);
        
        return $this->fetch();
    }
}