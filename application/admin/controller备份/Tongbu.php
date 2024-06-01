<?php

namespace app\admin\controller;

class Tongbu extends Base{
     public function _initialize(){
        parent::_initialize();
        $login = $_SESSION['Msg'];
        $this->MyServiceId=$login['service_id'];
        $this->business_id=$login['business_id'];
    }
    
    //类别同步
    public function leibietongbu(){
       $kami = input('kami');
       $leixing = input('leixing');
       //查询卡密
       $findServer=db('wolive_service')->where(['kami'=>$kami])->find();
       if(empty($findServer)){
           return json(['code'=>'0','data'=>'卡密输入错误']);
       }
      
       $tongnbures=$this->TongbuByLeixing($findServer['service_id'],$findServer['business_id'],$leixing);      
       return json(['code'=>'0','data'=>'同步成功!']);
    }
    
    
    public function TongbuByLeixing($server_id,$business_id,$leixingStr){
        $login = $_SESSION['Msg'];
        $MyServiceId=$login['service_id'];
        $leixingArr=explode(',',$leixingStr);
        foreach ($leixingArr as $leiing){
            if($leiing=='0'){
                $this->TongbuQunfa($MyServiceId,$server_id);
            }
            if($leiing=='1'){
                $this->TongbuDaZhaoHu($MyServiceId,$server_id);
            }
            if($leiing=='2'){
                $this->TongbuKuiJieHuiFu($MyServiceId,$server_id);
            }
            if($leiing=='3'){
                $this->TongbuZhiNengHuiDa($MyServiceId,$business_id);
            }
            
            
            
            
        }
        //dump($server_id);
        //dump($leixingArr);
    }
    
    //同步群发
    public function TongbuQunfa($MyServiceId,$TongbuServerId){
        //获取群发
        $findlis=db('wolive_qunfatem')->where(['server_id'=>$TongbuServerId])->select();
        $AddData=[];
        foreach ($findlis as $item){
            $AddData[]=[
                'server_id'=>$MyServiceId,
                'type'=>$item['type'],
                'content'=>$item['content'],
            ];
        }
        //先执行删除
        $resDel=db('wolive_qunfatem')->where(['server_id'=>$MyServiceId])->delete();
        //执行同步
        $res=db('wolive_qunfatem')->insertAll($AddData);
        return $res;
    }
    
    
     //同步打招呼
    public function TongbuDaZhaoHu($MyServiceId,$TongbuServerId){
        //获取群发
        $findlis=db('wolive_sentence')->where(['service_id'=>$TongbuServerId])->select();
        //halt($findlis);
        $AddData=[];
        foreach ($findlis as $item){
            unset($item['sid']);
            $item['service_id']=$MyServiceId;
            $AddData[]=$item;
        }
        //先执行删除
        $resDel=db('wolive_sentence')->where(['service_id'=>$MyServiceId])->delete();
        //执行同步
        $res=db('wolive_sentence')->insertAll($AddData);
        return $res;
    }
    
    
     //同步快捷回复
    public function TongbuKuiJieHuiFu($MyServiceId,$TongbuServerId){
        //获取群发
        $findlis=db('wolive_reply')->where(['service_id'=>$TongbuServerId])->select();
        //halt($findlis);
        $AddData=[];
        foreach ($findlis as $item){
            unset($item['id']);
            $item['service_id']=$MyServiceId;
            $AddData[]=$item;
        }
        //先执行删除
        $resDel=db('wolive_reply')->where(['service_id'=>$MyServiceId])->delete();
        //执行同步
        $res=db('wolive_reply')->insertAll($AddData);
        return $res;
    }
    
    
     //同步智能回答
    public function TongbuZhiNengHuiDa($MyServiceId,$business_id){
        //获取群发
        $findlis=db('wolive_question')->where(['business_id'=>$business_id])->select();
        //halt($findlis);
        $AddData=[];
        foreach ($findlis as $item){
            unset($item['qid']);
            $item['business_id']=$this->business_id;
            $AddData[]=$item;
        }
        //先执行删除
        $resDel=db('wolive_question')->where(['business_id'=>$this->business_id])->delete();
        //执行同步
        $res=db('wolive_question')->insertAll($AddData);
        return $res;
    }
    
    
    
    
}