<?php
/**
 * Handler File Class
 *
 * @author liliang <liliang@wolive.cc>
 * @email liliang@wolive.cc
 * @date 2017/06/01
 */

namespace app\admin\controller;

use app\admin\model\Admins;
use app\platform\enum\apps;
use app\platform\model\Business;
use think\Controller;
use think\captcha\Captcha;
use think\config;
use app\Common;
use app\extra\push\Pusher;
use think\Cookie;
use app\admin\iplocation\Ip;

/**
 * 登录控制器.
 */
class Apilogin extends Controller
{
    private $business_id = null;

    public function _initialize()
    {
        
    }

    /**
     * 登陆首页.
     *
     * @return string
     */
    public function index()
    {
        echo '访问页面错误';
    }

    /**
     * 检查.
     *
     * @return void
     */
    public function checkLoginToken()
    {
        session('zjhjdql.referer',null);
        $get = $this->request->get();
        
        $get['userToken'] =htmlspecialchars($get['token']);
        
        if(empty($get['userToken'])){
            
            $this->error('验证码参数错误', url("/admin/login/index"));
        }
        
        
        $user_res = Admins::table('wolive_service')->where('token', $get['userToken'])->find();
        
        if(!$user_res){
            
            $this->error('未找到用户', url("/admin/login/index"));
        }
        

        $res = Admins::table('wolive_service')->where('service_id', $user_res['service_id'])->update(['state' => 'online']);

        $data = Admins::table('wolive_service')->where('service_id', $user_res['service_id'])->find();


        $_SESSION['Msg'] = $data->getData();
        $business = Business::get($_SESSION['Msg']['business_id']);
        $_SESSION['Msg']['business'] = $business->getData();

        $common =new Common();

        $service_token = $common->encrypt($_SESSION['Msg']['service_id'],'E','dianqilai_service');
        Cookie::set('service_token', $service_token, 7*24*60*60);

        $ismoblie =$common->isMobile();

        if($ismoblie){
          
          $this->success('登录成功', url("mobile/admin/index"));
        }else{

          $this->success('登录成功', url("admin/Index/index"));
        }
        
    }

    /**
     *  用户登录获取信息
     *
     * @return string
     */
   public function checkLogin(){
       
        $post =$this->request->post();

        $post['user_name'] =htmlspecialchars($post['user_name']);
        $post['password'] =htmlspecialchars($post['password']);
        $post['business_id'] =htmlspecialchars($post['business_id']);
        $post['userToken'] =htmlspecialchars($post['userToken']);
        
        if(empty($post['user_name'])){
            
            //输出数据
    		$resarr = array('state'=>0, 'code'=> 2008, 'text'=>'用户名不能是空');
    		return $this->json_req($resarr);
        }
        
        if(empty($post['password'])){
            
            //输出数据
    		$resarr = array('state'=>0, 'code'=> 2008, 'text'=>'密码不能是空');
    		return $this->json_req($resarr);
        }
        
        // 获取信息 根据$post['username'] 的数据 来做条件 获取整条信息
        $admin = Admins::table("wolive_service")
            ->where('user_name', $post['user_name'])
            ->where('business_id',$post['business_id'])
            ->find();
        if (!$admin) {
            
            //输出数据
    		$resarr = array('state'=>0, 'code'=> 2008, 'text'=>'用户不存在');
    		return $this->json_req($resarr);
        }
        // 密码检查

        $pass = md5($post['user_name'] . "hjkj" . $post['password']);

        $password = Admins::table("wolive_service")
            ->where('user_name', $post['user_name'])
            ->where('password', $pass)
            ->find();

        if (!$password) {

            //输出数据
    		$resarr = array('state'=>0, 'code'=> 2008, 'text'=>'密码错误');
    		return $this->json_req($resarr);
        }
        
        $service_res = Admins::table('wolive_service')->where('user_name', $post['user_name'])->where('password', $pass)->find();
        
        $app_key = app_key;
        $whost = whost;
        $arr = parse_url($whost);
        if ($arr['scheme'] == 'ws') {
            $port = 'wsPort';
            $value = 'false';
        } else {
            $value = 'true';
            $port = 'wssPort';
        }
        
        $data['app_key'] = $app_key;
        $data['whost'] = $arr['host'];
        $data['wport'] = wport;
        $data['value'] = $value;
        $data['business_id'] = $post['business_id'];
        $data['service_id'] = $service_res['service_id'];
        
        //token
        $res = Admins::table('wolive_service')->where('service_id', $service_res['service_id'])->update(['token' => $post['userToken']]);
        
        if($res){
            //输出数据
		    $resarr = array('state'=>1, 'code'=> 2008, 'text'=>'登录成功！', 'data'=>$data);
        }else{
            
            $resarr = array('state'=>0, 'code'=> 2008, 'text'=>'登录错误'); 
        }
        
		return $this->json_req($resarr);

    }


    /**
     *  用户登录获取信息
     *
     * @return string
     */
   public function checkLogin_yz(){
       
        $post =$this->request->post();

        $post['token'] =htmlspecialchars($post['token']);
        
        $res = Admins::table('wolive_service')->where('token', $post['token'])->find();
        
        if($res){
            //输出数据
		    $resarr = array('state'=>1, 'code'=> 2008, 'text'=>'验证成功！');
        }else{
            
            $resarr = array('state'=>0, 'code'=> 2008, 'text'=>'验证错误'); 
        }
        
		return $this->json_req($resarr);

    }


  

    /**
     * 退出登陆 并清除session.
     *
     * @return void
     */
    public function logout()
    {
        $post =$this->request->post();

        $post['token'] =htmlspecialchars($post['token']);
        /*
        Cookie::delete('service_token');
        if(isset($_SESSION['Msg'])){
               $login = $_SESSION['Msg'];
            // 更改状态

          Cookie::delete('service_token');
          setCookie("cu_com", "", time() - 60);
          $_SESSION['Msg'] = null;
        }
        */
        $res = Admins::table('wolive_service')->where('token', $post['token'])->update(['state' => 'offline', 'token' => '']);

        if($res){
            //输出数据
		    $resarr = array('state'=>1, 'code'=> 2008, 'text'=>'退出成功！');
        }else{
            
            $resarr = array('state'=>0, 'code'=> 2008, 'text'=>'退出失败'); 
        }
        
		return $this->json_req($resarr);
           
    }



    //返回数组转json数据
    private function json_req($res){
    	
    	$json_res = json_encode($res);
    	return $json_res;
    	
    }
  
}
