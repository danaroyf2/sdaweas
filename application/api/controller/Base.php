<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-03 04:42:55
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-11-12 07:48:39
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/api/controller/Base.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2020/1/14
 * Time: 9:44
 */

namespace app\api\controller;

use app\common\exception\ApiException;
use think\Controller;
//use think\Validate;
class Base extends Controller
{
    public $table;
    public $DbModel;
    /**
     * 初始化的方法
     */
    public function _initialize() {
        $this->checkRequestAuth();
        $this->init();
    }

    public function init(){
        /*
        $u=model('User');
        $u->a();
        */
        $this->checkdata();
        $token=input('token');
        $this->serverUser=$this->getServerByToken($token);
        if(empty($this->serverUser)){
            $this->error('token错误或已失效');
        }
        //修改赋值
        //$this->serverUser['business_id']=$this->serverUser['service_id'];
        if(!empty($this->table)){
            $this->DbModel=db($this->table);
        }
       
        $this->controller_init();
        
    }

    //检查数据
    public function checkdata(){
        $token=input('token');
        $result = $this->validate(
            [
                'token'  => $token,
                //'email' => '',
            ],
            [
                'token'  => 'require',
                //'email'   => 'email',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
            //dump($result);die();
        }
    }

    /**
     * 每个控制器单单独的初始化方法可以继承修改
     */
    public function controller_init(){

    }

    

    public function getServerByToken($token){
        $serverUser=db('wolive_service')->where(['token'=>$token])->find();
        return $serverUser;
    }

    /**
     * 检查每次app请求的数据是否合法
     */
    public function checkRequestAuth() {
        // 首先需要获取headers
        $headers = request()->header();

        // 基础参数校验
//        if(empty($headers['sign'])) {
//            throw new ApiException([
//                'msg' => 'sign不正确',
//                'errorCode' => 10001
//            ]);
//        }
//        // 需要sign
//        if(!IAuth::checkSignPass($headers)) {
//            throw new ApiException([
//                'msg'=>'sign验证失败',
//                'errorCode'=>10003
//            ]);
//        }
        // 1、文件  2、mysql 3、redis
        $this->headers = $headers;
    }
}