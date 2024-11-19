<?php
namespace app\mobile\controller;

use app\admin\model\Admins;
use app\admin\model\Vgroup;
use app\platform\model\Business;
use think\Controller;
use app\mobile\model\User;
use app\Common;
use think\Cookie;
use think\Paginator;


class Mapp extends Mbase
{
    /**
     * 登陆首页.
     *
     * @return string
     */
    public function login()
    {
        $token  = Cookie::get('service_token');
        if ($token) {
            $this->redirect(url('Mobile/mapp/index'));
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
        $this->assign('submit', url('check', $params));
        return $this->fetch();
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


        $this->redirect(url('Mobile/mapp/login',['business_id'=>$this->request->param('business_id')]));

    }

    /**
     * 检查.
     *
     * @return void
     */
    public function check()
    {
        $post = $this->request->post();
        if(!isset($post['username']) || !isset($post['password'])){
            $this->error('参数不完整!', url("/Mobile/mapp/login"));
        }

        $post['user_name'] =htmlspecialchars($post['username']);

        $post["password"] =htmlspecialchars($post['password']);
        unset($post['username']);

        $result = $this->validate($post, 'Login');
        if ($result !== true) {
            $this->error($result);
        }
        // 密码检查

        $pass = md5($post['user_name'] . "hjkj" . $post['password']);

        $admin = Admins::table("wolive_service")
            ->where('user_name', $post['user_name'])
            ->where('password', $pass)
            ->find();

        if (!$admin) {

            $this->error('登录用户名或密码错误');
        }

        // 获取登陆数据

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

        $this->success('登录成功', url("mobile/mapp/index"));

    }
    /**
     * .主页
     * [index description]
     * @return [type] [description]
     */
	public function index(){

        $login = $_SESSION['Msg'];
        /*if($login['level'] == 'service'){
            $this->redirect('admin/index/index');
        }*/
        $res = User::table("wolive_business")->where('id',$login['business_id'])->find();
        $groups = User::table('wolive_group')->where('business_id',$login['business_id'])->select();
        $group_id = 0;
        $group_name = '通用客服';
        foreach ($groups as $group_info) {
            if ($group_info['id'] === $login['groupid']) {
                $group_id = $login['groupid'];
                $group_name = $group_info['groupname'];
                break;
            }
        }

        $this->assign('voice_address',$res['voice_address']);
        $this->assign('voice',$res['voice_state']);
        $this->assign('method',$res['distribution_rule']);
        $this->assign('group_id', $group_id);
        $this->assign('group_name', $group_name);
        $this->assign('mine', $login);

		return $this->fetch();
	}

  public function group(){

        $login = $_SESSION['Msg'];
        /*if($login['level'] == 'service'){
            $this->redirect('admin/index/index');
        }*/
        $res = User::table("wolive_business")->where('id',$login['business_id'])->find();
        $groups = User::table('wolive_group')->where('business_id',$login['business_id'])->select();
        $group_id = 0;
        $group_name = '通用客服';
        foreach ($groups as $group_info) {
            if ($group_info['id'] === $login['groupid']) {
                $group_id = $login['groupid'];
                $group_name = $group_info['groupname'];
                break;
            }
        }

        $this->assign('voice_address',$res['voice_address']);
        $this->assign('voice',$res['voice_state']);
        $this->assign('method',$res['distribution_rule']);
        $this->assign('group_id', $group_id);
        $this->assign('group_name', $group_name);
        $this->assign('mine', $login);

    return $this->fetch('index');
  }

  public function book(){

        $login = $_SESSION['Msg'];
        /*if($login['level'] == 'service'){
            $this->redirect('admin/index/index');
        }*/
        $res = User::table("wolive_business")->where('id',$login['business_id'])->find();
        $groups = User::table('wolive_group')->where('business_id',$login['business_id'])->select();
        $group_id = 0;
        $group_name = '通用客服';
        foreach ($groups as $group_info) {
            if ($group_info['id'] === $login['groupid']) {
                $group_id = $login['groupid'];
                $group_name = $group_info['groupname'];
                break;
            }
        }

        $this->assign('voice_address',$res['voice_address']);
        $this->assign('voice',$res['voice_state']);
        $this->assign('method',$res['distribution_rule']);
        $this->assign('group_id', $group_id);
        $this->assign('group_name', $group_name);
        $this->assign('mine', $login);

    return $this->fetch('index');
  }


  public function setting(){

        $login = $_SESSION['Msg'];
        /*if($login['level'] == 'service'){
            $this->redirect('admin/index/index');
        }*/
        $res = User::table("wolive_business")->where('id',$login['business_id'])->find();
        $groups = User::table('wolive_group')->where('business_id',$login['business_id'])->select();
        $group_id = 0;
        $group_name = '通用客服';
        foreach ($groups as $group_info) {
            if ($group_info['id'] === $login['groupid']) {
                $group_id = $login['groupid'];
                $group_name = $group_info['groupname'];
                break;
            }
        }

        $this->assign('voice_address',$res['voice_address']);
        $this->assign('voice',$res['voice_state']);
        $this->assign('method',$res['distribution_rule']);
        $this->assign('group_id', $group_id);
        $this->assign('group_name', $group_name);
        $this->assign('mine', $login);

    return $this->fetch('index');
  }

  public function my(){

        $login = $_SESSION['Msg'];
        /*if($login['level'] == 'service'){
            $this->redirect('admin/index/index');
        }*/
        $res = User::table("wolive_business")->where('id',$login['business_id'])->find();
        $groups = User::table('wolive_group')->where('business_id',$login['business_id'])->select();
        $group_id = 0;
        $group_name = '通用客服';
        foreach ($groups as $group_info) {
            if ($group_info['id'] === $login['groupid']) {
                $group_id = $login['groupid'];
                $group_name = $group_info['groupname'];
                break;
            }
        }

        $this->assign('voice_address',$res['voice_address']);
        $this->assign('voice',$res['voice_state']);
        $this->assign('method',$res['distribution_rule']);
        $this->assign('group_id', $group_id);
        $this->assign('group_name', $group_name);
        $this->assign('mine', $login);

    return $this->fetch('index');
  }

    /**
     * .对话页面
     * [chat description]
     * @return [type] [description]
     */
	public function chat()
	{
      
      return $this->fetch();
	}

  /**
   * 
   * [talk description]
   * @return [type] [description]
   */
	public function talk()
	{ 

	  $login =$_SESSION['Msg'];
      $get =$this->request->param();
      $channel=htmlspecialchars($get['channel']);
      $avatar =htmlspecialchars($get['avatar']);
      $data =User::table('wolive_visiter')->where("channel",$channel)->find();
      
      $business =User::table('wolive_business')->where('id',$login['business_id'])->find();
      
      $this->assign("atype",$business['audio_state']);
      $this->assign("data",$data);
      $this->assign("avatar",$avatar);
      $this->assign('se',$login);
      $this->assign("img",$login['avatar']);	
	  return $this->fetch();
	}
  
  /**
   * .留言页面
   * [message description]
   * @return [type] [description]
   */
  public function message()
  { 

     $login = $_SESSION['Msg'];
     $get = $this->request->get();
     $userAdmin = User::table('wolive_message');
     $pageParam = ['query' => []];
     unset($get['page']);
     if ($get) {
            $pushtime = $get['pushtime'];

            if ($pushtime) {
                if ($pushtime == 1) {
                    $timetoday = date("Y-m-d", time());
                    $userAdmin->where('timestamp', 'like', $timetoday . "%");
                    $this->assign('pushtime', $pushtime);
                    $pageParam['query']['timestamp'] = $pushtime;
                } elseif ($pushtime == 7) {
                    $timechou = strtotime("-1 week");
                    $times = date("Y-m-d", $timechou);
                    $userAdmin->where('timestamp', ">", $times);
                    $this->assign('pushtime', $pushtime);
                    $pageParam['query']['timestamp'] = $pushtime;
                }
            }
        }

    $data = $userAdmin->where('business_id', $login['business_id'])->paginate(3, false, $pageParam);
    if($data->count() == 0){
    
       $this->assign('content','暂时没有留言数据');
    }else{
       $this->assign('content',"");
    }
    $page = $data->render();
 
    $this->assign('page', $page);
    $this->assign('msgdata', $data);
    return $this->fetch();
  }

  /**
   * 
   * [show description]
   * @return [type] [description]
   */
  public function show()
  { 
   
    $post =$this->request->post();

    $res =User::table('wolive_message')->where('mid', $post['mid'])->find();

    $data =['code'=>0,'data'=>$res];

    return $data;

  }

  public function user()
  {
      $id = $this->request->param('id');
      $group = Vgroup::get($id);
      $this->assign('group',$group);
      $this->assign('id',$id);
      return $this->fetch();
  }

}