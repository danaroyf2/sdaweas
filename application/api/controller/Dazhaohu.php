<?php

namespace app\api\controller;
/**
 * 打招呼
 * 
 */

class Dazhaohu extends CRUD{
    
    
    public $table='wolive_sentence';

    //private $DbModel=db('wolive_qunfatem');

    public function controller_init(){
        //dump($this->serverUser);
    }
    //http://ceshi.yusygoe.store/api/Dazhaohu/list
    public function list(){
        $pagesize=input('pagesize','10');
        $page=input('page','1');
        $whrer=[
            'service_id'=>$this->serverUser['service_id']
        ];
        $pagesize='10000000';
        $lis=$this->DbModel
        ->where($whrer)
        ->order('weigh')
        ->paginate($pagesize,false,['page' =>$page,]);
        //->select();
        if(!empty($lis)){
            return json(['code'=>'1','msg'=>'获取成功!','data'=>$lis]);
        }else{
            return json(['code'=>'0','msg'=>'暂无数据!']);
        }
    }
    /**
     * 通过id获取单条数据
     */
    public function getbyid(){
        $data=input();
        //验证数据
        $result = $this->validate(
            [
                'sid'  => $data['id'],
            ],
            [
                'sid'  => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        $this->find(['sid'=>$data['id']]); 
    }
    //http://ceshi.yusygoe.store/api/Massmailing/add
    //添加
    public function add(){
        $data=input();
        //验证数据
        $result = $this->validate(
            [
                'content'  => $data['content'],
                'content_src'  => $data['content'],
                'type'  => $data['type'],
                //'email' => '',
            ],
            [
                'content'  => 'require',
               
                'type'   => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        } 
        //添加
        unset($data['token']);
        $data['content']=htmlspecialchars_decode($data['content'],ENT_QUOTES);
        $data['content_src']=htmlspecialchars_decode($data['content'],ENT_QUOTES);  
        $data['service_id']=$this->serverUser['service_id'];
        $data['state']='using';
        $lastid=$this->DbModel->where(['service_id'=>$this->serverUser['service_id']])->order('weigh desc')->value('sid');
        $data['weigh']=time().$lastid;
        

        $this->insert($data);
    }
    //修改
    public function edit(){
        $data=input();
        //验证数据
        $result = $this->validate(
            [
                'sid'  => $data['id'],
                'content'  => $data['content'],
                'type'  => $data['type'],
                //'email' => '',
            ],
            [
                'sid'  => 'require',
                'content'  => 'require',
                'type'   => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        } 
        //添加
        unset($data['token']);
        $sid=$data['id'];
        unset($data['id']);
        $data['content']=htmlspecialchars_decode($data['content'],ENT_QUOTES); 
        
        //getfuwenbUrl($data['content']);
        //halt($data['content']);
        $data['content_src']=htmlspecialchars_decode($data['content'],ENT_QUOTES);
        $data['service_id']=$this->serverUser['service_id'];   


        $this->update(['sid'=>$sid],$data);
    }
    //http://ceshi.yusygoe.store/api/Massmailing/del
    //删除
    public function del(){
        $data=input();
        //验证数据
        $result = $this->validate(
            [
                'sid'  => $data['id'],
            ],
            [
                'sid'  => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        $this->delete(['sid'=>$data['id']]); 
    }

    //交换两个id的权重
    public function exchangeWeigh(){
        $data=input();
        $weigh1=$this->DbModel->where(['sid'=>$data['id1']])->value('weigh');
        $weigh2=$this->DbModel->where(['sid'=>$data['id2']])->value('weigh');
        //交换
        $this->DbModel->where(['sid'=>$data['id1']])->update(['weigh'=>$weigh2]);
        $this->DbModel->where(['sid'=>$data['id2']])->update(['weigh'=>$weigh1]);
        //
        $this->success('交换成功!');
    }
   
}