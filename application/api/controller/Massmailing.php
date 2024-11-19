<?php

namespace app\api\controller;
/**
 * 群发
 * 
 */

class Massmailing extends CRUD{
    
    
    public $table='wolive_qunfatem';

    //private $DbModel=db('wolive_qunfatem');

    public function controller_init(){
        //dump($this->serverUser);
    }
    //http://ceshi.yusygoe.store/api/Massmailing/list
    public function list(){
        $pagesize=input('pagesize','10');
        $page=input('page','1');
        $whrer=[
            'server_id'=>$this->serverUser['service_id']
        ];
        $pagesize='10000000';

        $lis=$this->DbModel->where($whrer)
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
                'id'  => $data['id'],
            ],
            [
                'id'  => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        $this->find(['id'=>$data['id']]); 
    }
    //http://ceshi.yusygoe.store/api/Massmailing/add
    //添加
    public function add(){
        $data=input();
        //验证数据
        $result = $this->validate(
            [
                'content'  => $data['content'],
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
        $data['server_id']=$this->serverUser['service_id'];
        $data['creattime']=time();
        $lastid=$this->DbModel->where(['server_id'=>$this->serverUser['service_id']])->order('weigh desc')->value('id');
        if(empty($lastid)){
            $lastid=1;
        }

        $data['weigh']=time().$lastid;
        //halt($data['weigh']);
        $this->insert($data);
    }
    //修改
    public function edit(){
        $data=input();
        //验证数据
        $result = $this->validate(
            [
                'id'  => $data['id'],
                'content'  => $data['content'],
                'type'  => $data['type'],
                //'email' => '',
            ],
            [
                'id'  => 'require',
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
        $data['server_id']=$this->serverUser['service_id'];

        $this->update(['id'=>$data['id']],$data);
    }
    //http://ceshi.yusygoe.store/api/Massmailing/del
    //删除
    public function del(){
        $data=input();
        //验证数据
        $result = $this->validate(
            [
                'id'  => $data['id'],
            ],
            [
                'id'  => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        $this->delete(['id'=>$data['id']]); 
    }
    //交换两个id的权重
    public function exchangeWeigh(){
        $data=input();
        $weigh1=$this->DbModel->where(['id'=>$data['id1']])->value('weigh');
        $weigh2=$this->DbModel->where(['id'=>$data['id2']])->value('weigh');
        //交换
        $this->DbModel->where(['id'=>$data['id1']])->update(['weigh'=>$weigh2]);
        $this->DbModel->where(['id'=>$data['id2']])->update(['weigh'=>$weigh1]);
        //
        $this->success('交换成功!');
    }

    /**
     * 发送群发
     */
    public function send(){

    }
}