<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-01 04:45:17
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-11-20 03:49:07
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/api/controller/CRUD.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 * 
 * 
 */

namespace app\api\controller;
class CRUD extends Base{

    public function find($where){
        $res=$this->DbModel->where($where)->find();
        if(!empty($res)){
             $this->success('获取成功',$res);   
        }else{
            $this->success('获取失败');   
        }
    }


    public function insert($data){
        $res=$this->DbModel->insert($data);
        if(!empty($res)){
             $this->success('新增成功');   
        }else{
            $this->success('新增失败');   
        }
    }

    
    

    public function update($where,$data){
        $res=$this->DbModel->where($where)->update($data);
        if(!empty($res)){
             $this->success('修改成功');   
        }else{
            $this->success('修改失败');   
        }
    }



    public function delete($where){
        $res=$this->DbModel->where($where)->delete();
        if(!empty($res)){
             $this->success('删除成功');   
        }else{
            $this->success('删除失败');   
        }
    }

    
}