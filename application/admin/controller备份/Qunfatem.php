<?php


namespace app\admin\controller;

/**
 *
 * 后台页面控制器.
 */
class Qunfatem extends Base{
    //http://118.193.34.5/admin/Qunfatem/getlist.html
    public function _initialize(){
        parent::_initialize();
        $login = $_SESSION['Msg'];
        $this->MyServiceId=$login['service_id'];
    }
    
    public function getlist(){
        $lis=db('wolive_qunfatem')->where(['server_id'=>$this->MyServiceId])->select();
        
        return json(['code'=>'1','data'=>$lis]);
    }
    //http://118.193.34.5/admin/Qunfatem/getById.html
    public function getById(){
        $id=input('id');
        $id='1';
        $lis=db('wolive_qunfatem')->where(['id'=>$id])->select();
        
        return json(['code'=>'1','data'=>$lis]);
    }
    
    //http://118.193.34.5/admin/Qunfatem/add.html
    public function add(){
        //$content=input('content');
        $post = $this->request->post();
        $content= $this->request->post('content','','\app\Common::clearXSS');
        $type=input('type');
        $data=[
            'server_id'=>$this->MyServiceId,
            'type'=>$type,
            'content'=>$content,
        ];

        $result=db('wolive_qunfatem')->insert($data);
        if(!empty($result)){
             return json(['code'=>'0','data'=>'添加成功!']);
        }else{
            return json(['code'=>'1','data'=>'添加失败!']);
        }
       
    }
    ///http://118.193.34.5/admin/Qunfatem/edit.html
    public function edit(){
        $id=input('id');
         $post = $this->request->post();
        $content= $this->request->post('content','','\app\Common::clearXSS');
        //$content=input('content');
        $type=input('type');
        $data=[
            'type'=>$type,
            'content'=>$content,
        ];
        
        $result=db('wolive_qunfatem')->where(['id'=>$id])->update($data);
       
        if(!empty($result)){
             return json(['code'=>'0','data'=>'修改成功!']);
        }else{
            return json(['code'=>'1','data'=>'修改失败!']);
        }
    }
    ///http://118.193.34.5/admin/Qunfatem/del.html
    public function del(){
        $id=input('id');
        $result=db('wolive_qunfatem')->where(['id'=>$id])->delete();
        if(!empty($result)){
             return json(['code'=>'0','data'=>'删除成功!']);
        }else{
            return json(['code'=>'1','data'=>'删除失败!']);
        }
       
    }
}

?>