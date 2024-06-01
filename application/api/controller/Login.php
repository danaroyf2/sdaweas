<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-01 03:47:21
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-11-23 05:27:17
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/api/controller/Login.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\api\controller;
use app\admin\model\Admins;
/***
 * 登录和退出登录控制器
 * 
 */
class Login extends Base{

    public function init(){

    }
    //http://ceshi.yusygoe.store/api/Login/ServerLogin
    /**
     * 用户通过卡密登录
     */
    public function ServerLogin(){

        $kami=input('kami');
        //验证数据
        $result = $this->validate(
            [
                'kami'  => $kami,
            ],
            [
                'kami'  => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        } 
        //登录检查
        $kamiData = Admins::table("wolive_kami")
        ->where('kami',$kami)
        ->find();
        if(!$kamiData){
            $this->error('卡密不正确');
        }
        if($kamiData['ytime']!='0'){
            $this->error('被续费卡密无法登录');
        }
       

        
        $serverUser=db('wolive_service')->where(['kami'=>$kami])->find();
        $mi = $kamiData->getData();
        //没有用户激活卡密
        if(empty($serverUser)){
            //--
            $time=time();//当前时间
                $d=$mi['shichang'];//卡密时长
                $interval = $d * 24 * 3600;//计算卡密时长的时间戳
                $rqi=date('Y-m-d H:i:s',$time + $interval);//当前时间+时长=到期时间
                $b=['business_name'=>$kami,'expire_time'=>strtotime($rqi),'max_count'=>0,'admin_id'=>1];
                $bid = Admins::table('wolive_business')->insertGetId($b);
                $arr=['user_name'=>$kami,'nick_name'=>'在线客服','password'=>$kami,'business_id'=>$bid,'level'=>'super_manager','kami'=>$kami];
               //
               $checkservice=Admins::table('wolive_service')->where(['kami'=>$kami])->find();
               if(empty($checkservice)){
                    $res = Admins::table('wolive_service')->insert($arr);
               }else{
                   $res='1';
               }
               $k = Admins::table('wolive_kami')->where('id', $mi['id'])->update(['jihuo'=>date('Y-m-d H:i:s',time()),'dqtime' => $rqi,'business_id'=>$bid]);
               $serverUser=db('wolive_service')->where(['kami'=>$kami])->find();
        }
        /*
        dump($kami);
        dump($b);
        halt($serverUser);
        */
        if(!empty($serverUser)){
            //更新token
            if(empty($serverUser['token'])){
                $token=md5($kami.time());
                db('wolive_service')->where(['kami'=>$kami])->update(['token'=>$token]);
            }else{
                $token=$serverUser['token'];
            }
           
            //激活
            
           
            $business = Admins::table("wolive_business")
            ->where('business_name',$kami)
            ->find();
            if (!$business) {
                
                if($mi['business_id']!=0)
                {
                     $this->error('当前秘钥不是登录秘钥');
                }
                
               
               
               
                $k = Admins::table('wolive_kami')->where('id', $mi['id'])->update(['jihuo'=>date('Y-m-d H:i:s',time()),'dqtime' => $rqi,'business_id'=>$bid]);
                if($res){
                    $show = Admins::table("wolive_service")
                        ->where('kami', $kami)
                        ->find();
                    $login = $show->getData();
                    // 删掉登录用户的敏感信息
                    unset($login['password']);
        
                    $res = Admins::table('wolive_service')->where('service_id', $login['service_id'])->update(['state' => 'online']);
                    $_SESSION['Msg'] = $login;
                    $business = Business::get($_SESSION['Msg']['business_id']);
                    $_SESSION['Msg']['business'] = $business->getData();
            
                    $common =new Common();
                    $expire=7*24*60*60;
                    $service_token = $common->cpEncode($login['user_name'],YMWL_SALT,$expire);
                    Cookie::set('service_token', $service_token, $expire);
            
                    $ismoblie =$common->isMobile();
            
                    
                }else{
                    return $this->error('激活失败');
                }
            }
            $title=Admins::table('wolive_shezhi')->where('id',1)->find();
            $admin_title=$title->getData();
             //到期检查
             $checkres=checkendtime($kami);
             if($checkres['code']!='1'){
                return $this->error($checkres['msg']);
             }
             //dump($a);
             //
            $this->success('登录成功!',['token'=>$token,'user'=>$serverUser,'admin_title'=>$admin_title]);
        }else{
            //卡密不正确
            $this->error('登录失败!');
        }
    }
    
    //卡密续费
    public function renew(){
        $kami=isset($_POST['kami'])?$_POST['kami']:'QFgYFgM1MTIoVB7O1M57wOv';
        $kami = Admins::table("wolive_kami")
            ->where('kami', $kami)
            ->find();
        if(!$kami){
             $this->error('卡密不正确');
        }
        $mi = $kami->getData();
        if($mi['business_id']!=0)
        {
             $this->error('当前秘钥已使用');
        }
        $login = $admin = Admins::table("wolive_service")
            ->where('business_id', $login['business_id'])
            ->find();
        $admin = Admins::table("wolive_business")
            ->where('id', $login['business_id'])
            ->find();
        $expire_time=$admin['expire_time'];
        $time=time();
        $d=$mi['shichang'];
        $interval = $d * 24 * 3600;
        $rqi=date('Y-m-d H:i:s',$time + $interval);
        if($expire_time>$time){
            $rqi=date("Y-m-d H:i:s", $expire_time + $interval);
        }
        $bu = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['expire_time'=>strtotime($rqi)]);
        $res= Admins::table('wolive_kami')->where('id', $mi['id'])->update(['jihuo'=>date('Y-m-d H:i:s',time()),'dqtime' => $rqi,'business_id'=>$login['business_id'],'ytime'=>$expire_time]);
         if($res){
              $arr = ['code' => 0, 'msg' => '续费成功！', 'end_time' => $rqi];
            return json($arr);
         }
         else{
            $arr = ['code' => 1, 'msg' => '续费失败！'];
            return json($arr);
            
         }
    }

     //卡密续费
     public function renew0(){
        $kami=$_POST['kami'];
        
        $kami = Admins::table("wolive_kami")
            ->where('kami', $kami)
            ->find();
        if(!$kami){
             $this->error('卡密不正确');
        }
        $mi = $kami->getData();
        if($mi['business_id']!=0)
        {
             $this->error('当前秘钥已使用');
        }
        $login = $this->serverUser;
        $admin = Admins::table("wolive_business")
            ->where('id', $login['business_id'])
            ->find();
        $expire_time=$admin['expire_time'];
        $time=time();
        $d=$mi['shichang'];
        $interval = $d * 24 * 3600;
        $rqi=date('Y-m-d H:i:s',$time + $interval);
        //halt($rqi);
        if($expire_time>$time){
            $rqi=date("Y-m-d H:i:s", $expire_time + $interval);
        }
        $bu = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['expire_time'=>strtotime($rqi)]);
        $res= Admins::table('wolive_kami')->where('id', $kami['id'])->update(['jihuo'=>date('Y-m-d H:i:s',time()),'dqtime' => $rqi,'business_id'=>$login['business_id'],'ytime'=>$expire_time]);
         if($res){
              $arr = ['code' => 0, 'msg' => '续费成功！', 'end_time' => $rqi];
            return json($arr);
         }
         else{
            $arr = ['code' => 1, 'msg' => '续费失败！'];
            return json($arr);
            
         }
    }
}