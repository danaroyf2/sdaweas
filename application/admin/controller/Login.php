<?php


namespace app\admin\controller;

use app\admin\model\Admins;
use app\platform\enum\apps;
use app\platform\model\Business;
use app\platform\model\Option;
use think\Controller;
use think\captcha\Captcha;
use think\config;
use app\Common;
use app\extra\push\Pusher;
use think\Cookie;


/**
 * 登录控制器.
 */
class Login extends Controller
{
    private $business_id = null;

    public function _initialize()
    {
        $this->business_id = $this->request->param('business_id',Cookie::get('YMWL_APP_FLAG'));

       if( !empty($this->business_id) ){
           Cookie::set('YMWL_APP_FLAG',$this->business_id);
       }
        $this->assign('business_id',$this->business_id);
    }

    /**
     * 登陆首页.
     *
     * @return string
     */
    public function index()
    {
        $token  = Cookie::get('service_token');
        // echo $token;
        // exit;
        if ($token) {
            $this->redirect(url('admin/index/index'));
        }
        // 未登陆，呈现登陆页面.
        $params = [];
        $goto = $this->request->get('goto', '');
        if ($goto) {
            $params['goto'] = urlencode($goto);
        }
        $business=[];
        if($this->business_id){
            $business = Business::get($this->business_id);

        }
        $title=Admins::table('wolive_shezhi')->where('id',1)->find();
        $admin_title=$title->getData();
        $this->assign('admin_title',$admin_title);
        $this->assign('business',$business);
        $option = Option::getList('regist', 0, 'admin');
        $this->assign('regist',$option['regist']);
        $this->assign('submit', url('check', $params));
        return $this->fetch();
    }

    /**
     * 注册页面.
     *
     * @return mixed
     */
    public function sign()
    {
        $business=[];
        if($this->business_id){
            $business = Business::get($this->business_id);

        }
        $this->assign('business',$business);
        return $this->fetch();
    }
    /**
     *  注册用户.
     *
     * @return string
     */
    public function regist()
    {
        $option = Option::getList('regist,regist_expire,regist_crnum', 0, 'admin');
        if (!isset($option['regist']) || $option['regist']== 0) {
            return $this->error("系统禁止注册");
        }
        $post = $this->request->post();
        $result = $this->validate($post, 'Login.regist');
        if (true !== $result) {
            return $this->error($result);
        }
        $mService=Admins::table('wolive_service');
        $res = $mService->where('user_name', $post['user_name'])->find();
        if ($res) {
            return $this->error("用户名已存在！");
        }
        $res = Admins::table('wolive_business')->where('business_name', $post['business_name'])->find();
        if ($res) {
            return $this->error("商家名已存在！");
        }
        //合成新函数
        unset($post['captcha']);
        unset($post['repassword']);
        $exp_time = $option['regist'] ? $option['regist'] *24*60*60: 7*24*60*60;
        $post['admin_id']=1;
        $post['max_count']= $option['regist_crnum'] ? $option['regist_crnum']: 1;
        $post['expire_time']=time() + $exp_time;
        $res = Business::addBusiness($post);
        if($res){
            return $this->success('注册成功');
        }else{
            return $this->error('注册失败');
        }

    }


    /**
     * 验证码.
     *
     * @return \think\Response
     */
    public function captcha()
    {

        $captcha = new Captcha(Config::get('captcha'));
        ob_clean();
        return $captcha->entry('admin_login');
    }

    /**
     * 注册验证码.
     *
     * @return \think\Response
     */
    public function captchaForAdmin()
    {
        $captcha = new Captcha(Config::get('captcha'));
        return $captcha->entry('admin_regist');
    }

    /**
     * 登录检查.
     *
     * @return void
     */
     public function check()
    {
        
        $post = $this->request->post();
//        if(!isset($post['username']) || !isset($post['password']) || !isset($post['business_id'])){
        if(!isset($post['username']) || $post['username']==''){
          $this->error('参数不完整!', url("/admin/login/index"));
        }
        if($post['checkbox']==''){
          $this->error('请阅读并同意用户协议!', url("/admin/login/index"));
        }
        $post['user_name'] =htmlspecialchars($post['username']);

            unset($post['username']);
            $kami = Admins::table("wolive_kami")
                ->where('kami', $post['user_name'])
                ->find();
            if(!$kami){
                 $this->error('卡密不正确', url("/admin/login/index"));
            }
            $admin = Admins::table("wolive_business")
                ->where('business_name', $post['user_name'])
                ->find();
            
            if (!$admin) {
                $mi = $kami->getData();
                if($mi['business_id']!=0)
                {
                     $this->error('当前秘钥不是登录秘钥', url("/admin/login/index"));
                }
                
                $time=time();//当前时间
                $d=$mi['shichang'];//卡密时长
                $interval = $d * 24 * 3600;//计算卡密时长的时间戳
                $rqi=date('Y-m-d H:i:s',$time + $interval);//当前时间+时长=到期时间
                $b=['business_name'=>$post['user_name'],'expire_time'=>strtotime($rqi),'max_count'=>0,'admin_id'=>1];
                $bid = Admins::table('wolive_business')->insertGetId($b);
                
                $arr=['user_name'=>$post['user_name'],'nick_name'=>'在线客服','password'=>$post['user_name'],'business_id'=>$bid,'level'=>'super_manager','kami'=>$post['user_name']];
                $checkservice=Admins::table('wolive_service')->where(['kami'=>$post['user_name']])->find();
                if(empty($checkservice)){
                     $res = Admins::table('wolive_service')->insert($arr);
                }else{
                    $res='1';
                }
               
                $k = Admins::table('wolive_kami')->where('id', $mi['id'])->update(['jihuo'=>date('Y-m-d H:i:s',time()),'dqtime' => $rqi,'business_id'=>$bid]);
                if($res){
                    $show = Admins::table("wolive_service")
                        ->where('kami', $post['user_name'])
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
            
                    if($ismoblie){
                      
                      $this->redirect("admin/Index/chats");
                    }else{
            
                      $this->redirect("admin/Index/chats");
                    }
                }else{
                    return $this->error('激活失败', url("/admin/login/index"));
                }
            }
            else
            {
                
                  $time = $admin->getData();
                  $dtime=time();
                  if($time['expire_time']<$dtime)
                  {
                       $this->error('当前卡密已过期', url("/admin/login/index"));
                  }
                  $service=Admins::table("wolive_service")
                        ->where('kami', $post['user_name'])
                        ->find();
                  $login=$service->getData();
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
            
                    if($ismoblie){
                       $this->redirect("admin/Index/chats");
                    }else{
                       $this->redirect("admin/Index/chats");
                    }
            }

    }
    
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
        $login = $_SESSION['Msg'];
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
            return $arr;
         }
         else{
            $arr = ['code' => 1, 'msg' => '续费失败！'];
            return $arr;
            
         }
    }
    
     public function loginrenew(){
         $post = $this->request->post();
        
         $kami=isset($_POST['kami'])?$_POST['kami']:'QFgYFgM1MTIoVB7O1M57wOv';
         $loginkami=isset($post['loginkami'])?$post['loginkami']:'';
            $admin = Admins::table("wolive_business")
            ->where('business_name', $loginkami)
            ->find();
        $login = $admin;    
            
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
        
        //dump($login);
       
        $expire_time=$admin['expire_time'];
        $time=time();
        $d=$mi['shichang'];
        $interval = $d * 24 * 3600;
        $rqi=date('Y-m-d H:i:s',$time + $interval);
        if($expire_time>$time){
            $rqi=date("Y-m-d H:i:s", $expire_time + $interval);
        }
        $bu = Admins::table('wolive_business')->where('id', $login['id'])->update(['expire_time'=>strtotime($rqi)]);
        $res= Admins::table('wolive_kami')->where('id', $mi['id'])->update(['jihuo'=>date('Y-m-d H:i:s',time()),'dqtime' => $rqi,'business_id'=>$login['id'],'ytime'=>$expire_time]);
        $res2= Admins::table('wolive_kami')->where('kami', $loginkami)->update(['dqtime' => $rqi]);
         if($res){
               return $this->success('续费成功！');
         }
         else{
           return $this->error('续费失败！');
            
         }
         die();
         
         
        
    }
   
    /**
     * 退出登陆 并清除session.
     *
     * @return void
     */
    public function logout()
    {
        Cookie::delete('service_token');
      if(isset($_SESSION['Msg'])){
               $login = $_SESSION['Msg'];
            // 更改状态

          Cookie::delete('service_token');
          setCookie("cu_com", "", time() - 60);
          $_SESSION['Msg'] = null;
        }
        $this->redirect(url('admin/login/index'));
           
    }

    /**
     * socket_auth 验证
     * [auth description]
     * @return [type] [description]
     */
     public function auth(){

        $sarr = parse_url(ahost);
        if ($sarr['scheme'] == 'https') {
            $state = true;
        } else {
            $state = false;
        }

        $app_key = app_key;
        $app_secret = app_secret;
        $app_id = app_id;
        $options = array(
            'encrypted' => $state
        );
        $host = ahost;
        $port = aport;

        $pusher = new Pusher(
            $app_key,
            $app_secret,
            $app_id,
            $options,
            $host,
            $port
        );
        

        $data= $pusher->socket_auth($_POST['channel_name'], $_POST['socket_id']);
        
        return $data;  
    }

}