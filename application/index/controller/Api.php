<?php

namespace app\index\controller;

use think\Controller;

class Api extends Controller{
    
    public function _initialize(){
        //允许跨域设置
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Methods:POST,GET');
        header('Access-Control-Expose-Headers:*');
        header("Access-Control-Allow-Headers:token,Origin, X-Requested-With, Content-Type, Accept");
    }
    
    //http://ncumtbg.store/index.php/index/Api/getQrcodeUrlByKami
    //通过卡密获取用户二维码分享链接
    public function getQrcodeUrlByKami(){
        $kami=input('kami');
        //检查卡密是否到期
        $checkres=checkendtime($kami);
        if($checkres['code']!='1'){
             return json(['code'=>'-1','data'=>$checkres['msg']]);
        }
        //查找用户
        $finduser=db('wolive_service')->where(['kami'=>$kami])->find();
       
        $b=\Foxercode::getercode($finduser['service_id']);
        $thisdate=date('Y-m-d',time());
        $usernum=\GlobleTongbu::getUserContent($finduser['business_id'],$thisdate);
         $returnData=[
            'img'=>$b['qrimgpath'],
            'url'=>$b['url'],
            'num'=>$usernum,
        ];
        return json(['code'=>'1','data'=>$returnData]);
    }
    //重新生成二维码
    public function reCreatQrcodeUrlByKami(){
       $kami=input('kami');
        //查找用户
        $finduser=db('wolive_service')->where(['kami'=>$kami])->find();
        $a=\Foxercode::regetercode($finduser['service_id']);
        $b=\Foxercode::getercode($finduser['service_id']);
        $thisdate=date('Y-m-d',time());
        $usernum=\GlobleTongbu::getUserContent($finduser['business_id'],$thisdate);
         $returnData=[
            'img'=>$b['qrimgpath'],
            'url'=>$b['url'],
            'num'=>$usernum,
        ];
        return json(['code'=>'1','data'=>$returnData]);
    }
    //http://ncumtbg.store/index.php/index/Api/tongbu
    //用户同步数据
    public function tongbu(){
        //\GlobleTongbu::tongbuAvatar();   die();
        $redKami=input('redkami');
        $setKami=input('setkami');
        $select=input('select');
        $setKami=explode(',',$setKami);
        $select=explode(',',$select);
        //检查数据
        if(empty($redKami)){
            return json(['code'=>'0','data'=>'包装卡密不能为空']);
        }
        if(empty($setKami[0])){
            return json(['code'=>'0','data'=>'目标卡密不能为空']);
        }
        //获取包装卡密的用户
        $finduser=db('wolive_service')->where(['kami'=>$redKami])->find();
        if(empty($finduser)){
            return json(['code'=>'0','data'=>'卡密输入错误!']);
        }
        $fromServerId=$finduser['service_id'];
        //循环同步
        foreach ($setKami as $v){
            $mubiaoUser=db('wolive_service')->where(['kami'=>$v])->find();
            if(empty($mubiaoUser)){
                $findbusiness=db('wolive_business')->where(['business_name'=>$redKami])->find();
                $findbusiness['business_name']=$v;
                unset($findbusiness['id']);
                $business_id=db('wolive_business')->insertGetId($findbusiness);
                
                
                $mubiaoUser=$finduser;
                $mubiaoUser['user_name']=$v;
                
                $mubiaoUser['avatar']='/assets/images/admin/avataradmin2.png';
                $mubiaoUser['nick_name']='在线客服';
                $mubiaoUser['password']=$v;
                $mubiaoUser['kami']=$v;
                $mubiaoUser['business_id']=$business_id;
                $mubiaoUser['erweicode']='';
                //$mubiaoUser['erweicode']='';
                $mubiaoUser['ertouxiang']='';
                $mubiaoUser['zhuanshuyuming']='';
                $mubiaoUser['state']='offline';
                $mubiaoUser['token']='';
                
                $mubiaoUser['zsercode']='';
                $mubiaoUser['zhuanshuyuming']='';
                $mubiaoUser['isqrcodeshixiao']='0';
                
                //erweicode
                
                unset($mubiaoUser['service_id']);
                db('wolive_service')->insert($mubiaoUser);
                $mubiaoUser=db('wolive_service')->where(['kami'=>$v])->find();
                /*
                $findkami=db('wolive_kami')->where(['kami'=>$redKami])->find();
                $findkami['kami']=$v;
                $findkami['business_id']=$mubiaoUser['service_id'];
                
                $findkami['shijian']=date('Y-m-d H:i:s',time());
                unset($findkami['id']);
                db('wolive_kami')->insert($findkami);
                */
                //
               
            }
            if(!empty($mubiaoUser)){
                $toServerId=$mubiaoUser['service_id'];
                //halt($toServerId);
                 //dump($select);
                foreach ($select as $s){
                   
                    if($s=='avatar'){
                       \GlobleTongbu::tongbuAvatar($fromServerId,$toServerId);
                    }
                    if($s=='nickname'){
                       \GlobleTongbu::tongbuNickname($fromServerId,$toServerId);
                    }
                    if($s=='greet'){
                       \GlobleTongbu::tongbuDazhaohu($fromServerId,$toServerId);
                    }
                    if($s=='quickReply'){
                       \GlobleTongbu::tongbuKuaijiehuifu($fromServerId,$toServerId);
                    }
                    if($s=='answer'){
                       \GlobleTongbu::tongbuZhineingwenda($finduser['business_id'],$mubiaoUser['business_id']);
                    }
                    if($s=='mass'){
                       \GlobleTongbu::tongbuQunfa($fromServerId,$toServerId);
                    }
                    
                    
                    
                }
                //
            }
            //激活卡密
            //1.查询是否存在卡密
            $findKami=db('wolive_kami')->where(['kami'=>$v])->find();
            //2.存在卡密更新时间和 business_id
            if(!empty($findKami) && empty($findKami['business_id'])){
                $daytime=3600*24;
                $thistimeStrm=time();
                $endtimeStrm=$thistimeStrm+(3600*24*($findKami['shichang']));
                $thistime=date('Y-m-d H:i:s',$thistimeStrm);
                $endtime=date('Y-m-d H:i:s',$endtimeStrm);
                $update_data=[
                    //'shijian'=>$thistime,
                    'jihuo'=>$thistime,
                    'dqtime'=>$endtime,
                    //'dqtime'=>(3600*24*($findKami['shichang'])),
                    'business_id'=>$mubiaoUser['business_id'],
                ];
                db('wolive_kami')->where(['kami'=>$v])->update($update_data);
                db('wolive_business')->where(['id'=>$mubiaoUser['business_id']])->update(['expire_time'=>strtotime($endtime)]);
            }
            //3.没有就跳过
            
            //end for
        }
       return json(['code'=>'1','data'=>'同步成功!']);
    }
    
    //http://ncumtbg.store/index.php/index/Api/exitHuashu
    //批量修改话术
    public function exitHuashu(){
        //dump(input());
        $setKami=input('setkami');
        $setKami=explode(',',$setKami);
        
        $oldtext=input('oldtext');
        $newtext=input('newtext');
        foreach ($setKami as $v){
            $mubiaoUser=db('wolive_service')->where(['kami'=>$v])->find();
            if(!empty($mubiaoUser)){
                //修改打招呼
                $where1="service_id=".$mubiaoUser['service_id']." AND type='text'";
                \GlobleTongbu::Plliangtihuan('wolive_sentence','content',$where1,$oldtext,$newtext);
                 \GlobleTongbu::Plliangtihuan('wolive_sentence','content_src',$where1,$oldtext,$newtext);
                //修改快捷回复
                $where2="service_id=".$mubiaoUser['service_id']." AND type='text'";
                \GlobleTongbu::Plliangtihuan('wolive_reply','content',$where2,$oldtext,$newtext);
                //修改智能问答
                 $where3="business_id=".$mubiaoUser['business_id']." AND type='text'";
                \GlobleTongbu::Plliangtihuan('wolive_question','question',$where3,$oldtext,$newtext);
                \GlobleTongbu::Plliangtihuan('wolive_question','keyword',$where3,$oldtext,$newtext);
                \GlobleTongbu::Plliangtihuan('wolive_question','answer',$where3,$oldtext,$newtext);
                \GlobleTongbu::Plliangtihuan('wolive_question','answer_src',$where3,$oldtext,$newtext);
                //修改群发
                $where4="server_id=".$mubiaoUser['service_id']." AND type='text'";
                \GlobleTongbu::Plliangtihuan('wolive_qunfatem','content',$where4,$oldtext,$newtext);
                
            }
        }
        
        return json(['code'=>'1','data'=>'修改成功!']);
       
    }
    //获取日志增加信息
    //http://ncumtbg.store/index.php/index/Api/getuseraddlog
    public function getuseraddlog(){
         $setKami=input('setkami');
         $setKami=explode(',',$setKami);
         $thisdate=date('Y-m-d',time());
         $getDatenum=7;
         $datelist=[];
         for($d=0;$d<$getDatenum;$d++){
             $datelist[]=[
              'showdate'=>date('m-d', strtotime('-' . $d . ' days')),
              'finddate'=>date('Y-m-d', strtotime('-' . $d . ' days')),
             ];
         }
         $returnlis=[];
         foreach ($setKami as $v){
             $finduser=db('wolive_service')->where(['kami'=>$v])->find();
             if(!empty($finduser)){
                 $item=[];
                 $item['kami']=$v;
                 foreach ($datelist as $deteItem){
                     $item['datenum'][]=\GlobleTongbu::getUserContent($finduser['business_id'],$deteItem['finddate']);
                 }
                 $returnlis[]=$item;
             }
         }
         

         //halt($returnlis);
         return json(['code'=>'1','data'=>['datelist'=>$datelist,'kamidate'=>$returnlis]]);
    }
    
    
    //http://ncumtbg.store/index.php/index/Api/getqrcodeurl
    public function getqrcodeurl(){
        $url=input('url');
        $img=input('img');
        $qrname='hello'.time();
        $savepath='qrcoe/qr'.$qrname.'.png';

        $qrimgpath=\app\extra\ercode\ercode::createErCode2($url,$savepath,$img);
        $qrimgpath=thisyuming().$qrimgpath;
        return json(['code'=>'1','data'=>$qrimgpath]);
    }
    /*
    批量修改验证码
    */
    public function changeyanzhengma(){
        $setKami=input('setkami');
        $select=input('select');
        
        if(empty($setKami)){
            return json(['code'=>'0','data'=>'卡密不能为空']);
        }
        
        if(empty($select)){
            return json(['code'=>'0','data'=>'请勾选验证码开关']);
        }
        
        //$mubiaoUser=db('wolive_service')->where(['kami'=>['in',$setKami]])->select();

        $res=db('wolive_service')->where(['kami'=>['in',$setKami]])->update(['yanzhengma'=>$select]);
        if(!empty($res)){
             return json(['code'=>'1','data'=>'更新成功!']);
        }else{
            return json(['code'=>'0','data'=>'更新失败!']);
        }
    }
}