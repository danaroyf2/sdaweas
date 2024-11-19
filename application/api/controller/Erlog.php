<?php

namespace app\api\controller;
/**
 * 操作日志
 * 
 */

class Erlog extends CRUD{
    
    
    public $table='wolive_servererlog';

    //private $DbModel=db('wolive_qunfatem');

    public function controller_init(){
        //dump($this->serverUser);
    }
    //http://ceshi.yusygoe.store/api/Massmailing/list
    public function list(){
        $pagesize=input('pagesize','10');
        $page=input('page','1');
        $whrer=[
            'service_id'=>$this->serverUser['service_id']
        ];
        
        $lis=$this->DbModel->where($whrer)->paginate($pagesize,false,['page' =>$page,]);
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
    /**
     * 发送群发
     */
    public function send(){

    }
}