<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-04 04:26:50
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-12-13 15:41:14
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/function/ercode.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%B
 */
/**
 * 二维码生成类
 * 
 */
use think\Db;
class Foxercode{
    /**
     * 获得普通域名
     */
    public static function getptyuming($notyuming){
        $where=[
            'leixing'=>'3',
            'type'=>'0',
            'suoshuid'=>null,
        ];

        $host=Db::table('wolive_yuming')
        ->where($where)
        ->orderRaw('rand()')
        ->limit(1)
        ->value('name');
        return $host;
    }
    
    /**
     * 获得专属普通域名
     */
    public static function getzsyuming($service_id,$notyuming){
        $where=[
            'leixing'=>'2',
            'type'=>'0',
            'suoshuid'=>['like','%,'.$service_id.',%']
            //'name'=>['notin',[$notyuming]],
        ];

        if(!empty($notyuming)){
            $where['name']=['notin',[$notyuming]];
        }

        $host=$yuminglist=Db::table('wolive_yuming')
        ->where($where)
        ->orderRaw('rand()')
        ->limit(1)
        ->value('name');
        return $host;
    }

    /**
     * 创建二维码
     */
    public static function creatercode($finduser,$log,$notyuming=''){
        //$host=$notyuming;
        //检查域名是否被封
        $finduseryuming=Db::table('wolive_yuming')->where(['name'=>$finduser['ptym']])->find();
        if($finduseryuming['type']!='0'){
            //被封重新抽取域名
            $host=self::getptyuming($notyuming);
            if(empty($host)){
                errorR('无可用域名');
            }
        }else{
            //未被封使用原域名
            $host=$finduser['ptym'];
        }
       

        $finduser['erweicode']=$finduser['service_id'].time();
        $finduser['erweicode']=md5($finduser['erweicode']);
        $finduser['erweicode']=substr($finduser['erweicode'], 0, 5);
        $theme='7571f9';
        $finduser['qrurl']='/index/index/home?visiter_id=&visiter_name=&avatar=&business_id='.$finduser['business_id'].'&groupid='.$finduser['groupid'].'&special='.$finduser['service_id'].'&theme='.$theme;
        //dump($finduser['qrurl']);
        $finduser['ptym']=$host;
        if(!empty($log)){
            svaeqrlog($log,$finduser);
        }
        db('wolive_service')->where(['service_id'=>$finduser['service_id']])->update(['erweicode'=>$finduser['erweicode'],'qrurl'=>$finduser['qrurl'],'isqrcodeshixiao'=>'0','ertouxiang'=>$finduser['avatar'],'ptym'=>$finduser['ptym']]);
        
        $add_survival_host_data=[
            'service_id'=>$finduser['service_id'],
            'host'=>$host,
            'erweicode'=>$finduser['erweicode'],
        ];
        if(empty(db('wolive_survival_host')->where($add_survival_host_data)->find())){
            db('wolive_survival_host')->insert($add_survival_host_data);
        }
        Db::table('wolive_yuming')->where(['suoshuid'=>$finduser['kami']])->update(['suoshuid'=>null]);
        Db::table('wolive_yuming')->where(['name'=>$host])->update(['suoshuid'=>$finduser['kami']]);
        
        return $finduser;
    }
    
    /**
     * 创建专属二维码
     */
    public static function creatzhuanshuercode($finduser,$log,$notyuming=''){
        $host=$notyuming;
        $host=self::getzsyuming($finduser['service_id'],$notyuming);
        //$host=self::getzsyuming($finduser['service_id'],'');
        //dump($notyuming);
        //halt($host);
        if(empty($host)){
            $this->error('无可用域名');
        }

        $finduser['zsercode']=$finduser['service_id'].time();
        $finduser['zsercode']=md5($finduser['zsercode']);
        $finduser['zsercode']=substr($finduser['zsercode'], 0, 5);
        $theme='7571f9';
        $finduser['zsqrurl']='/index/index/home?visiter_id=&visiter_name=&avatar=&business_id='.$finduser['business_id'].'&groupid='.$finduser['groupid'].'&special='.$finduser['service_id'].'&theme='.$theme;
        //dump($finduser['qrurl']);
        $finduser['zhuanshuyuming']=$host;
        if(!empty($log)){
            svaeqrlog($log,$finduser);
        }
        db('wolive_service')->where(['service_id'=>$finduser['service_id']])->update(['zsercode'=>$finduser['zsercode'],'zsqrurl'=>$finduser['zsqrurl'],'ertouxiang'=>$finduser['avatar'],'zhuanshuyuming'=>$finduser['zhuanshuyuming']]);
        
        $add_survival_host_data=[
            'service_id'=>$finduser['service_id'],
            'zhuanshuyuming'=>$host,
            'zsercode'=>$finduser['zsercode'],
        ];
        if(empty(db('wolive_survival_zshost')->where($add_survival_host_data)->find())){
            db('wolive_survival_zshost')->insert($add_survival_host_data);
        }
        
        return $finduser;
    }
    /**
     * 失效普通二维码
     */
    public static function shixiaoqrcode($service_id){
        
        
        $finduser=db('wolive_service')->where(['service_id'=>$service_id])->find();
        if(empty($finduser['ptym'])){
            errorR('无可用域名');
        }
        
        $result=db('wolive_service')->where(['service_id'=>$service_id])->update(['isqrcodeshixiao'=>'1']);
        if(!empty($result)){
            svaeqrlog('失效普通二维码',$finduser);
            //删除存活二维码记录
            db('wolive_survival_host')->where(['service_id'=>$service_id])->delete();
            return true;
        }else{
            return false;
        }
    }
    /**
     * 获取二维码
     */
    public static function getercode($service_id){
        $finduser=db('wolive_service')->where(['service_id'=>$service_id])->find();
        
        $checkres=checkendtime($finduser['kami']);
        if($checkres['code']!='1'){
            errorR($checkres['msg']);
            //halt($checkres['msg']);
           //return $this->error($checkres['msg']);
        }
       
        if(empty($finduser['erweicode']) || ($finduser['avatar']!=$finduser['ertouxiang']) || (empty($finduser['ptym'])) ){
            //dump($finduser['ptym']);
            if($finduser['isqrcodeshixiao']!='1'){
                $finduser=self::creatercode($finduser,'生成普通二维码',$finduser['ptym']);
            }
            
        }
        if(wxfengjincheck($finduser['ptym'])){
          
            $finduser['isqrcodeshixiao']='3';
        }
        
        $host='http://'.$finduser['ptym'];
        $host=getIntactHost($host);
       
        
        $url=$host.'/a?yzm='.$finduser['erweicode'].'&service_id='.$service_id;
        
        $savepath='qrcoe/qr'.$finduser['erweicode'].'.png';
        $isHave=file_exists(PUBLIC_PATH.'/'.$savepath);
        //halt(thisyuming().$finduser['avatar']);
        //$isHave=false;
        if($isHave==false){
            $qrimgpath=\app\extra\ercode\ercode::createErCode2($url,$savepath,thisyuming().$finduser['avatar']);
        }else{
            $qrimgpath='/'.$savepath;
        }

        $return_data=[
            'url'=>$url,
            'service'=>$finduser,
            'qrimgpath'=>THISHOST.$qrimgpath,
        ];
        return $return_data;
    }
    /**
     * 重新生成普通二维码
     */
    public static function regetercode($service_id){
        $finduser=db('wolive_service')->where(['service_id'=>$service_id])->find();
        //halt($finduser);
        $result=self::creatercode($finduser,'',$finduser['ptym']);
        if(!empty($result)){
            //svaeqrlog('重新生成普通二维码',$finduser);
            return true;
        }else{
            return false;
        }
    }

    //-----------------------------------------------------
    /**
     * 生成专属ercode
     * 
     */
    public static function zuanshuercode($service_id){
        $finduser=db('wolive_service')->where(['service_id'=>$service_id])->find();
      
        
        $savepath='zsqrcoe/qr'.str_replace('.','',$finduser['zsercode']).$service_id.'.png';
        $savePath=PUBLIC_PATH.'/'.$savepath;
        $findzsymcun= db('wolive_survival_zshost')->where(['service_id'=>$finduser['service_id']])->find();
        //halt($findzsymcun);
        if(empty($findzsymcun)){    
            
            $finduser=self::creatzhuanshuercode($finduser,'生成专属二维码',$finduser['zhuanshuyuming']);
            $host=$finduser['zhuanshuyuming'];
        }else{
            $host=$finduser['zhuanshuyuming'];
        }

  
        //erweicode
        //halt($finduser);
       
        $host='http://'.$finduser['zhuanshuyuming'].'/';
        $url=$host.'b?yzm='.$finduser['zsercode'].'&service_id='.$service_id;
        
    
        
        
        $isHave=file_exists($savePath);
        //halt($isHave);
        //dump($isHave);
        //dump(thisyuming().$finduser['avatar']);
        //$isHave=false;
        if($isHave==false){
            $qrimgpath=\app\extra\ercode\ercode::createErCode2($url,$savepath,thisyuming().$finduser['avatar']);
        }else{
            $qrimgpath='/'.$savepath;
        }

        $return_data=[
            'url'=>$url,
            'service'=>$finduser,
            'qrimgpath'=>THISHOST.$qrimgpath,
        ];

        return $return_data;
       
    }

     /**
     * 失效专属二维码
     */
    public static function shixiaozuanshuercode($service_id){
        $finduser=db('wolive_service')->where(['service_id'=>$service_id])->find();
        svaeqrlog('失效专属二维码',$finduser);
            //删除存活二维码记录
        $res=db('wolive_survival_zshost')->where(['service_id'=>$service_id])->delete();
        if($res){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 重新生成专属二维码
     */
    public function zsrecreatqrcode($service_id){
        $finduser=db('wolive_service')->where(['service_id'=>$service_id])->find();
        //halt($finduser);
        $result=self::creatzhuanshuercode($finduser,'重新生成专属二维码',$finduser['zhuanshuyuming']);
        if(!empty($result)){
            //svaeqrlog('重新生成普通二维码',$finduser);
            return true;
        }else{
            return false;
        }
        
    }
}