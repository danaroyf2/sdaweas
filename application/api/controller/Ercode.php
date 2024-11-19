<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-06 02:27:21
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-11-06 02:50:27
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/api/controller/Ercode.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%
 */

namespace app\api\controller;
/**
 * 二维码
 * 
 */

class Ercode extends CRUD{
    /**
     * 获取普通二维码信息
     * 
     */
    public function ptercoe(){
      
        //dump($this->serverUser);
       // errorR('789');
        $a=\Foxercode::getercode($this->serverUser['service_id']);
        $this->success('获取成功',$a);
    } 
    /**
     * 失效普通二维码
     */
    public function shixiaoptercode(){
        //dump($this->serverUser);
        $a=\Foxercode::shixiaoqrcode($this->serverUser['service_id']);
        if($a){
            //\Foxercode::regetercode($this->serverUser['service_id']);
            $this->success('失效成功!');
        }else{
            $this->success('失效失败!');
        }
        
    }   
    /**
     * 重新生成普通二维码
     */
    public function regetercode(){
        //dump($this->serverUser);
        $a=\Foxercode::regetercode($this->serverUser['service_id']);
        if($a){
            $this->success('重新生成成功!');
        }else{
            $this->success('重新生成失败!');
        }
        
    }   
    //--------------------------------------------------------------
    /***
     * 
     * 获得专属二维码
     */
    public function zuanshuercode(){
        $a=\Foxercode::zuanshuercode($this->serverUser['service_id']);
        $this->success('获取成功',$a);
        try {
            $a=\Foxercode::zuanshuercode($this->serverUser['service_id']);
            $this->success('获取成功',$a);
            //dump($a);
            return(json(['code'=>'1','data'=>$a]));
        } catch (\Exception $e) {
            $this->error($e->getMessage());
           //halt();
        }
       
    }
     /**
     * 失效专属二维码
     */
    public function zuanshushixiaoptercode(){
        //dump($this->serverUser);
        $a=\Foxercode::shixiaozuanshuercode($this->serverUser['service_id']);
        if($a){
             //\Foxercode::zsrecreatqrcode($this->serverUser['service_id']);
            $this->success('失效成功!');
        }else{
            $this->success('失效失败!');
        }
        
    }
    
     /**
     * 重新生成专属二维码
     */
    public function rezuanshuercode(){
        $a=\Foxercode::zsrecreatqrcode($this->serverUser['service_id']);
            if($a){
                $this->success('重新生成成功!');
            }else{
                $this->success('重新生成失败!');
            }
        try {
            $a=\Foxercode::zsrecreatqrcode($this->serverUser['service_id']);
            if($a){
                $this->success('重新生成成功!');
            }else{
                $this->success('重新生成失败!');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
           //halt();
        }
       
        
    }   
    
}