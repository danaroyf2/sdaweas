<?php


namespace app\admin\controller;

use app\Common;
use app\platform\model\Admin;
use app\platform\model\Business;
use app\weixin\model\Weixin;
use think\Controller;
use think\Cookie;
use think\Loader;
use think\Response;
use think\Session;
use app\admin\model\Admins;

/**
 * 基础验证是否登录.
 */
class Base extends Controller
{

    protected $base_root = null;
    public $wechat_platform=null;
    public $open_id='';
    /**
     * 验证session.
     *
     * @return void
     */
    public function _initialize0()
    {
		 parent::_initialize();
        if (empty($_SESSION['Msg']) || !isset($_SESSION['Msg'])) {
            
            $token = Cookie::get('service_token');
            if (!$token) {
                $this->redirect('admin/login/index');
            }
            $common = new Common();
            $user_name = $common->cpDecode($token,YMWL_SALT);
            if(!$user_name){
                $this->redirect('admin/login/index');
            }
            $data = Admins::table("wolive_service")
                ->where('user_name', $user_name)
                ->find();
            if ($data) {
                $_SESSION['Msg'] = $data->getData();
                $business = Business::get($_SESSION['Msg']['business_id']);
                $_SESSION['Msg']['business'] = $business->getData();
                $this->open_id= $_SESSION['Msg']['open_id'];
            }
        }else{
            $serviceInfo=db('wolive_service')->field('open_id')->where(['service_id'=>$_SESSION['Msg']['service_id']])->find();
            if($serviceInfo){
                $this->open_id=$serviceInfo['open_id'];
            }

        }
        if (empty($_SESSION['Msg']) || !isset($_SESSION['Msg'])) {
            $this->redirect('admin/login/index');
        }

        $login = $_SESSION['Msg'];
        $res =Admins::table('wolive_business')->where('id',$login['business_id'])->find();
        if ($res['is_recycle'] || $res['is_delete']) {
            session('Msg',null);
            Cookie::delete('service_token');
            $this->error('系统已被回收或封禁', url("/admin/login/index"));
        }
        if ($res['expire_time'] < time() && $res['expire_time'] != 0) {
            session('Msg',null);
            Cookie::delete('service_token');
            $this->error('系统已过期');
        }

        $group =Admins::table('wolive_group')->where('business_id',$login['business_id'])->select();

        $groupjson =json_encode($group);

        $temp = $login;
        $temp['open_id']= $this->open_id;
        unset($temp['copyright']);
        unset($temp['business']['copyright']);
        $data = json_encode($temp);
        $app_key = app_key;

        $arr = parse_url(whost);

        if ($arr['scheme'] == 'ws') {
            $value = 'false';
            $port = 'wsPort';
        } else {
            $value = 'true';
            $port = 'wssPort';
        }
        $this->base_root = BASENAME;
        $this->assign('baseroot',$this->base_root);
//        $service = Weixin::get(['service_id'=>$_SESSION['Msg']['service_id']]);
        $this->assign('referer',session('Platform.referer'));

        $admin = Admin::count();
        if (empty($admin)) {
            $is_we7 = 1;
        } else {
            $is_we7 = 0;
        }
        $this->wechat_platform=db('wolive_wechat_platform')->where(['business_id'=>$login['business_id']])->find();
        $is_bind_wechat=0;
        if(!$this->open_id &&$this->wechat_platform && $this->wechat_platform['app_id'] && $this->wechat_platform['app_secret'] && $this->wechat_platform['visitor_tpl']){
            $is_bind_wechat=1;
        }
        $service = Admins::table('wolive_service')->where('business_id', $login['business_id'])->find();
        $add=$service->getData();
        $add['expire_time']=date("Y-m-d H:i:s",$res['expire_time']);
        
        $title=Admins::table('wolive_shezhi')->where('id',1)->find();
        $this->assign('admin_title',$title['name']);
        
        $this->assign('is_bind_wechat',$is_bind_wechat);
        $this->assign('we7_referer',session('zjhjdql.referer'));
        $this->assign('is_we7',$is_we7);

        $this->assign('seo',$_SESSION['Msg']['business']);
        $this->assign('app_key', $app_key);
        $this->assign('whost',$arr['host']);
        $this->assign('value', $value);
        $this->assign('wport', wport);
        $this->assign('arr', $login);
        $this->assign('data', $data);
        $this->assign('port', $port);
        $this->assign('group',$groupjson);
        $this->assign('service',$add);
        $this->assign('voice',$res['voice_state']);
        $this->assign('voice_address',$res['voice_address']);

    }

}