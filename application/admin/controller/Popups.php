<?php

namespace app\admin\controller;
use app\admin\model\Admins;
use app\admin\model\WechatPlatform;
use app\admin\model\WechatService;
use think\Db;
use think\Paginator;
use app\Common;
/**
 *
 * 后台弹窗处理.
 */
class Popups extends Base
{
    //    展示快捷回复
    public function quickreply(){
        $id=$this->request->param('id',0,'intval');
        $data=[];
        if($id){
            $data=Admins::table('wolive_reply')->where('id',$id)->find();
        }
        $this->assign('data', $data);
        return $this->fetch();
    }
    public function setcustom()
    {
        $post = $this->request->post();
        $post['business_id'] = $_SESSION['Msg']['business_id'];
        $post['content'] = $this->request->post('content','','\app\Common::clearXSS');

        if (isset($post['sid']) && $post['sid']>0) {
            $res = Admins::table('wolive_sentence')->where('sid', $post['sid'])->update(['sid' => $post['sid'], 'content' => $post['content']]);
            $arr = ['code' => 0, 'msg' => '编辑成功'];
            return $arr;
        } else {
//            content	text	内容
//service_id
            $result=Admins::table('wolive_sentence')->insert(
                ['content'=>$post['content'],
                    'service_id'=>$_SESSION['Msg']['service_id']]
            );
            if ($result) {

                $data =['code'=>0,'msg'=>'添加成功'];
                return $data;
            }else{
                $arr = ['code' => 1, 'msg' => '添加失败'];
                return $arr;
            }

        }
    }
    //根据参数生成二维码
    public function getqrcode(){
        $url=input('url');
        $logo='';
        $a= new \app\extra\ercode\ercode();
        $a->createErCode($url);
    }
    
    public function creatPterwema($finduser,$log,$notyuming=''){
        $login = $_SESSION['Msg'];
        $service_id=$login['service_id'];
        $where=[
            'leixing'=>'3',
            'type'=>'0',
            //'name'=>['notin',[$notyuming]],
        ];
        if(!empty($notyuming)){
            $where['name']=['notin',[$notyuming]];
        }
        $host=$notyuming;
        if($finduser['isqrcodeshixiao']=='1' || empty($notyuming)){
            $host=$yuminglist=Db::table('wolive_yuming')
            ->where($where)
            ->orderRaw('rand()')
            ->limit(1)
            ->value('name');
        }
        
        
        //halt($host);
            if(empty($host)){
                halt('无可用域名');
            }
            //二维码判断值为空立即生成
            $finduser['erweicode']=$service_id.time();
            $finduser['erweicode']=md5($finduser['erweicode']);
            $finduser['erweicode']=substr($finduser['erweicode'], 0, 5);
            //$substring = substr($string, 0, 5);
            $theme='7571f9';
            $finduser['qrurl']='/index/index/home?visiter_id=&visiter_name=&avatar=&business_id='.$login['business_id'].'&groupid='.$login['groupid'].'&special='.$login['service_id'].'&theme='.$theme;
            //dump($finduser['qrurl']);
            $finduser['ptym']=$host;
            if(!empty($log)){
                svaeqrlog($log);
            }
            db('wolive_service')->where(['service_id'=>$service_id])->update(['erweicode'=>$finduser['erweicode'],'qrurl'=>$finduser['qrurl'],'isqrcodeshixiao'=>'0','ertouxiang'=>$finduser['avatar'],'ptym'=>$finduser['ptym']]);
            
            $add_survival_host_data=[
                'service_id'=>$login['service_id'],
                'host'=>$host,
                'erweicode'=>$finduser['erweicode'],
            ];
            if(empty(db('wolive_survival_host')->where($add_survival_host_data)->find())){
                db('wolive_survival_host')->insert($add_survival_host_data);
            }
            
            return $finduser;
    }
    
    public function erweima()
    {
        return $this->fetch();die();
        //$url='http://www.baidu.com';
        //获得二维码code
        $login = $_SESSION['Msg'];
        //halt($login);
        $service_id=$login['service_id'];
        $finduser=db('wolive_service')->where(['service_id'=>$service_id])->find();
        //halt($finduser);
        
        if(empty($finduser['erweicode']) || ($finduser['avatar']!=$finduser['ertouxiang']) || (empty($finduser['ptym'])) ){
        //if(1){    
            $finduser=$this->creatPterwema($finduser,'生成普通二维码',$finduser['ptym']);
        }
        //erweicode
        //halt($finduser);
        //$host='http://cdvkg.sbs/';
        //$host=$yuminglist=db('wolive_yuming')->where(['leixing'=>'3','type'=>'0'])->value('name');
        if(!wxfengjincheck($finduser['ptym'])){
            $finduser['isqrcodeshixiao']='3';
        }
        
        $host='http://'.$finduser['ptym'].'/';
        $url=$host.'a?yzm='.$finduser['erweicode'].'&service_id='.$service_id;
        
        $savepath='qrcoe/qr'.$finduser['erweicode'].'.png';
        $isHave=file_exists(PUBLIC_PATH.'/'.$savepath);
        //halt(thisyuming().$finduser['avatar']);
        //$isHave=false;
        if($isHave==false){
            $qrimgpath=\app\extra\ercode\ercode::createErCode2($url,$savepath,thisyuming().$finduser['avatar']);
        }else{
            $qrimgpath='/'.$savepath;
        }
        
        //die();
        //echo(1);die();
        

        
        $this->assign('url',$url);
        $this->assign('service',$finduser);
        $this->assign('qrimgpath',$qrimgpath);
        
        return $this->fetch();
    }
    
    
    public function shixiaoqrcode(){
        
        $service_id=input('service_id');
        $result=db('wolive_service')->where(['service_id'=>$service_id])->update(['isqrcodeshixiao'=>'1']);
        if(!empty($result)){
            svaeqrlog('失效普通二维码');
            //删除存活二维码记录
            db('wolive_survival_host')->where(['service_id'=>$service_id])->delete();
            return json(['code'=>'1','data'=>'失效成功!']);
        }else{
            return json(['code'=>'1','data'=>'失效失败!']);
        }
    }
    
    
    public function zsshixiaoqrcode(){
        $service_id=input('service_id');
        $result=db('wolive_service')->where(['service_id'=>$service_id])->update(['iszhuanshuqrcodeshixiao'=>'1']);
        if(!empty($result)){
            svaeqrlog('失效专属二维码');
            return json(['code'=>'1','data'=>'失效成功!']);
        }else{
            return json(['code'=>'1','data'=>'失效失败!']);
        }
    }
    
    
    public function recreatqrcode(){
        $service_id=input('service_id');
        $finduser=db('wolive_service')->where(['service_id'=>$service_id])->find();
        
        $result=$this->creatPterwema($finduser,'',$finduser['ptym']);
       
        if(!empty($result)){
            svaeqrlog('重新生成普通二维码');
            return json(['code'=>'1','data'=>'重新生成成功!']);
        }else{
            return json(['code'=>'1','data'=>'重新生成失败!']);
        }
    }
    
    
    public function zsrecreatqrcode(){
        $service_id=input('service_id');
        $finduser=db('wolive_service')->where(['service_id'=>$service_id])->find();
          $yuminWhere=[
            'leixing'=>'2',
            'type'=>'0',
            'suoshuid'=>['like','%,'.$service_id.',%'],
            'name'=>['neq',$finduser['zhuanshuyuming']],
        ];
        $yuming=db('wolive_yuming')->where($yuminWhere)->find();
            if(empty($yuming)){
                halt('您没有专属域名1');
            }
            $host=$yuming['name'];
        $data=[
            'zhuanshuyuming'=>$host,
            'iszhuanshuqrcodeshixiao'=>'0',
            ];    
        $result=db('wolive_service')->where(['service_id'=>$service_id])->update($data);
        //$result=db('wolive_service')->where(['service_id'=>$service_id])->update(['isqrcodeshixiao'=>'1']);
        if(!empty($result)){
            svaeqrlog('重新生成专属二维码');
            return json(['code'=>'1','data'=>'重新生成成功!']);
        }else{
            return json(['code'=>'1','data'=>'重新生成失败!']);
        }
    }
    
    
    public function creatzserqode(){
        
    }
    
    //专属二维码生成
    public function zserweima()
    {
      
        //获得二维码code
        $login = $_SESSION['Msg'];
        //halt($login);
        $service_id=$login['service_id'];
        $finduser=db('wolive_service')->where(['service_id'=>$service_id])->find();
        $yuminWhere=[
            'leixing'=>'2',
            'type'=>'0',
            'suoshuid'=>['like','%,'.$service_id.',%']
        ];
        $yuming=db('wolive_yuming')->where($yuminWhere)->find();

        $host=$yuming['name'];
        $savepath='qrcoe/qr'.str_replace('.','',$host).$service_id.'.png';
        $savePath=PUBLIC_PATH.'/'.$savepath;
        //halt($savePath);  
        if( (empty($finduser['zhuanshuyuming'])) || ($finduser['avatar']!=$finduser['zhuanshutouxiang']) ){
            
            
            if(empty($yuming)){
                halt('您没有专属域名');
            }
            
           
            
             $data=[
                'zhuanshuyuming'=>$host,
                'iszhuanshuqrcodeshixiao'=>'0',
                'zhuanshutouxiang'=>$finduser['avatar'],
             ];    
            $result=db('wolive_service')->where(['service_id'=>$service_id])->update($data);
            //删除文件
            if(file_exists($savePath)){
                //dump(1);
                unlink($savePath);
            }
            
        }else{
            $host=$finduser['zhuanshuyuming'];
        }
        //查找域名
        
        //halt('你的专属域名为:'.$yuming['name']);
        $theme='7571f9';
        $url='/index/index/home?visiter_id=&visiter_name=&avatar=&business_id='.$login['business_id'].'&groupid='.$login['groupid'].'&special='.$login['service_id'].'&theme='.$theme.'&iszhuanshu=1';
        
  
        //erweicode
        //halt($finduser);
       
        $url='http://'.$host.$url;
        
        
        
    
        
        
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
       
        

        
        $this->assign('url',$url);
        $this->assign('service',$finduser);
        $this->assign('qrimgpath',$qrimgpath);
        
        return $this->fetch();
        return $this->fetch();
    }
}