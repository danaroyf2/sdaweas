<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-08 04:37:54
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-11-23 05:49:00
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/api/controller/Question.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\api\controller;
/**
 * 智能回答
 * 
 */

class Question extends CRUD{
    public $table='wolive_question';

    public function controller_init(){
        //dump($this->serverUser);
    }

    
    /**
     * [getquestion description]
     * @return [type] [description]
     */
    public function getquestion()
    {
        $post = $this->request->post();
        $business_id = $post['business_id'];

        $result = Admins::table('wolive_question')
            ->where('business_id', $business_id)
            ->where('status','eq', 1)
            ->order('sort desc')
            ->select();
        $business = Business::get($business_id);
        $keyword = Admins::table('wolive_question')
            ->where('business_id', $business_id)
            ->where('status','eq', 1)
            ->where('keyword','neq','')
            //->order('')
            ->count();
        if ($result) {
            $arr = ['code' => 1, 'msg' => 'success', 'data' => $result,'keyword'=>$keyword,'logo'=>$business['logo']];
            return json($arr);
        }else{
            $this->error('暂无数据');
        }
    }
    

    
    //http://ceshi.yusygoe.store/api/Massmailing/list
    public function list(){
        $pagesize=input('pagesize','10');
        $page=input('page','1');
        $whrer=[
            'business_id'=>$this->serverUser['business_id']
        ];
        $pagesize='10000000';
        $lis=$this->DbModel
        ->where($whrer)
        ->order('sort')
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
        $this->find(['qid'=>$data['id']]); 
    }
    //http://ceshi.yusygoe.store/api/Massmailing/add
    //添加
    public function add(){
        $data=input();
        //验证数据
        $result = $this->validate(
            [
                'answer_src'  => $data['answer_src'],
                'question'  => $data['question'],
                //'email' => '',
            ],
            [
                'answer_src'  => 'require',
                'question'   => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        } 
        //添加
        unset($data['token']);
        $data['answer']=htmlspecialchars_decode($data['answer'],ENT_QUOTES);  
        $data['answer_src']=htmlspecialchars_decode($data['answer_src'],ENT_QUOTES);  
        $data['business_id']=$this->serverUser['business_id'];
        $lastid=$this->DbModel->where(['business_id'=>$this->serverUser['business_id']])->order('sort desc')->value('qid');
        $data['sort']=time().$lastid;
        

        $this->insert($data);
    }
    //修改
    public function edit(){
        $data=input();
        //验证数据
        $result = $this->validate(
            [
                'answer_src'  => $data['answer_src'],
                'question'  => $data['question'],
                //'email' => '',
            ],
            [
                'answer_src'  => 'require',
                'question'   => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        } 
        //添加
        $qid=$data['id'];
        unset($data['token']);
        unset($data['id']);
        
        $data['answer']=htmlspecialchars_decode($data['answer'],ENT_QUOTES);  
        $data['answer_src']=htmlspecialchars_decode($data['answer_src'],ENT_QUOTES);  
        $data['business_id']=$this->serverUser['business_id'];

        //halt($qid);    
        $this->update(['qid'=>$qid],$data);
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
        $this->delete(['qid'=>$data['id']]); 
    }

     //交换两个id的权重
     public function exchangeWeigh(){
        $data=input();
        $weigh1=$this->DbModel->where(['qid'=>$data['id1']])->value('sort');
        $weigh2=$this->DbModel->where(['qid'=>$data['id2']])->value('sort');
        //交换
        $this->DbModel->where(['qid'=>$data['id1']])->update(['sort'=>$weigh2]);
        $this->DbModel->where(['qid'=>$data['id2']])->update(['sort'=>$weigh1]);
        //
        $this->success('交换成功!');
    }
}