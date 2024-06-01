<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-01 03:47:21
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-11-01 05:30:13
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/api/controller/Login.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\api\controller;

/***
 * 公共的方法
 * 
 */
class Publictool extends Base{

    //http://ceshi.yusygoe.store/api/Public/upload
    //上传
    public function upload(){
        $host='/uploads/';
        $host='http://ceshi.yusygoe.store'.$host;
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                // 成功上传后 获取上传信息
                //echo $info->getExtension();
                // 输出 jpg
                //echo $info->getSaveName();
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                //echo $info->getFilename();
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                $path=$info->getSaveName();
                $path=$host.$path;
                $this->success('上传成功',['path'=>$path]);
            }else{
                // 上传失败获取错误信息
                $this->error('上传失败');
            }
        }
    }
}