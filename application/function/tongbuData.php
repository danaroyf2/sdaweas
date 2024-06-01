<?php
use think\Db;

class GlobleTongbu{
    

    
    //同步头像
    public static function tongbuAvatar($FromServerId,$ToServerID){
        //echo('tongbuAvatar');
        //dump('同步头像');
        $findData=db('wolive_service')->where(['service_id'=>$FromServerId])->find();
        //dump($findData);
        $res=false;
        if(!empty($findData)){
            $res=db('wolive_service')->where(['service_id'=>$ToServerID])->update(['avatar'=>$findData['avatar']]);
        }
        
        return $res;
    }
    
     //同步昵称
    public static function tongbuNickname($FromServerId,$ToServerID){
        //echo('tongbuAvatar');
        $findData=db('wolive_service')->where(['service_id'=>$FromServerId])->find();
        $res=false;
        if(!empty($findData)){
            $res=db('wolive_service')->where(['service_id'=>$ToServerID])->update(['nick_name'=>$findData['nick_name']]);
        }
        return $res;
    }
    
     //同步打招呼
    public static function tongbuDazhaohu($FromServerId,$ToServerID){
        
         //获取群发
        $findlis=db('wolive_sentence')->where(['service_id'=>$FromServerId])->select();
        //halt($findlis);
        $AddData=[];
        foreach ($findlis as $item){
            unset($item['sid']);
            $item['service_id']=$ToServerID;
            $AddData[]=$item;
        }
        //先执行删除
        $resDel=db('wolive_sentence')->where(['service_id'=>$ToServerID])->delete();
        //执行同步
        $res=db('wolive_sentence')->insertAll($AddData);
        return $res;
        
    }
    
    //同步快捷回复
    public static function tongbuKuaijiehuifu($FromServerId,$ToServerID){
          //获取群发
        $findlis=db('wolive_reply')->where(['service_id'=>$FromServerId])->select();
        //halt($findlis);
        $AddData=[];
        foreach ($findlis as $item){
            unset($item['id']);
            $item['service_id']=$ToServerID;


            $AddData[]=$item;
        }
        //先执行删除
        $resDel=db('wolive_reply')->where(['service_id'=>$ToServerID])->delete();
        //执行同步
        $res=db('wolive_reply')->insertAll($AddData);
        return $res;
    }
    
    
    //同步智能问答
    public static function tongbuZhineingwenda($FromServerId,$ToServerID){
          //获取群发
        $findlis=db('wolive_question')->where(['business_id'=>$FromServerId])->select();
        //halt($findlis);
        $AddData=[];
        foreach ($findlis as $item){
            unset($item['qid']);
            $item['business_id']=$ToServerID;
            $AddData[]=$item;
        }
        //先执行删除
        $resDel=db('wolive_question')->where(['business_id'=>$ToServerID])->delete();
        //执行同步
        $res=db('wolive_question')->insertAll($AddData);
        return $res;
    }
    
    
     //同步群发
    public static function tongbuQunfa($FromServerId,$ToServerID){
        //获取群发
        $findlis=db('wolive_qunfatem')->where(['server_id'=>$FromServerId])->select();
        $AddData=[];
        foreach ($findlis as $item){
            $AddData[]=[
                'server_id'=>$ToServerID,
                'type'=>$item['type'],
                'content'=>$item['content'],
                'weigh'=>$item['weigh'],
            ];
        }
        //先执行删除
        $resDel=db('wolive_qunfatem')->where(['server_id'=>$ToServerID])->delete();
        //执行同步
        $res=db('wolive_qunfatem')->insertAll($AddData);
        return $res;
    }
    
    
    //批量替换
    public static function Plliangtihuan($table,$ziduan,$whereStr,$oldtext,$newText){
        $creatSQL="UPDATE `".$table."` SET `".$ziduan."` = REPLACE(`".$ziduan."`, '".$oldtext."', '".$newText."') WHERE ".$whereStr.";";
        $res=Db::execute($creatSQL);
        //dump($creatSQL);
        //dump($res);
    }
    //根据日期获取用户数量
    public static function getUserContent($business_id,$StartTime,$endTime=''){
        if(empty($endTime)){
            $endTime=$StartTime;
        }
        $whereTime['business_id']=$business_id;
        //$whereTime['visiter_id']=$business_id;
        $whereTime['timestamp']=['BETWEEN',
            [
                $StartTime.' 00:00:00.000000',
                $endTime.' 23:59:29.000000',
            ]
        ];
        //$usernum=db('wolive_visiter')->where($whereTime)->count();
        //$usernum=db('wolive_queue')->where($whereTime)->count();
        $usernum=db('wolive_visiter')
        ->where($whereTime)
        ->group('ip')  
        ->count();
      
        
        return $usernum;
    }
        
}


?>