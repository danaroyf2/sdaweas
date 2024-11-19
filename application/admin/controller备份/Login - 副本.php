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

        $post['user_name'] =htmlspecialchars($post['username']);

            unset($post['username']);

            $admin = Admins::table("wolive_service")
                ->where('kami', $post['user_name'])
                ->find();
            $kami = Admins::table("wolive_kami")
                ->where('kami', $post['user_name'])
                ->find();

            if (!$admin) {
               
                if(!$kami){
                     $this->error('卡密不正确');
                }
                 $mi = $kami->getData();
                if($mi['dqtime']!='0000-00-00 00:00:00')
                {
                     $this->error('当前秘钥已使用');
                }
                $d=$mi['shichang'];
                $rqi=date('Y-m-d',strtotime("+$d day")).' 23:59:59';
                $k = Admins::table('wolive_kami')->where('id', $mi['id'])->update(['jihuo'=>date('Y-m-d H:i:s',time()),'dqtime' => $rqi]);
                $b=['business_name'=>$post['user_name'],'expire_time'=>strtotime($rqi),'max_count'=>0,'admin_id'=>1];
                $bid = Admins::table('wolive_business')->insertGetId($b);
                $arr=['user_name'=>$post['user_name'],'nick_name'=>$post['user_name'],'password'=>$post['user_name'],'business_id'=>$bid,'level'=>'super_manager','kami'=>$post['user_name']];
                $res = Admins::table('wolive_service')->insert($arr);
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
                      
                      $this->success('登录成功', url("mobile/admin/index"));
                    }else{
            
                      $this->success('登录成功', url("admin/Index/index"));
                    }
                }else{
                    return $this->error('激活失败');
                }
            }
            else
            {
                  $login = $admin->getData();
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
                      
                      $this->success('登录成功', url("mobile/admin/index"));
                    }else{
            
                      $this->success('登录成功', url("admin/Index/index"));
                    }
            }

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