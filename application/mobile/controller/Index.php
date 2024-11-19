<?php


namespace app\mobile\controller;

use app\admin\model\RestSetting;
use app\extra\push\Pusher;
use think\Controller;
use app\mobile\model\User;
use app\Common;


/**
 *
 * 前台手机端控制器.
 * Class Index
 * @package app\mobile\controller
 */
class Index extends Controller
{
    public function _initialize()
    {
        $this->assign('basename',BASENAME);
    }
    /**
     * 唯一随机数方法
     * [rand description]
     * @param  [type] $len [description]
     * @return [type]      [description]
     */
    public function rand($len)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $string = substr(time(), -3);
        for (; $len >= 1; $len--) {
            $position = rand() % strlen($chars);
            $position2 = rand() % strlen($string);
            $string = substr_replace($string, substr($chars, $position, 1), $position2, 0);
        }
        return $string;
    }


    /**
     *
     * [home description]
     * @return [type] [description]
     */
    public function home()
    {
        //halt(2);
        $data = $this->request->param();
        //halt($data);
        $data['product'] = empty($data['product']) ? '' : $data['product'];
        $data['special'] = isset($data['special']) ? $data['special']:null;
        $data['theme'] = isset($data['theme']) ? $data['theme']:'7571f9';
        $str = 'theme='.$data['theme']."&visiter_id=" . $data['visiter_id'] . "&visiter_name=" . $data['visiter_name'] . "&avatar=" . $data['avatar'] . "&business_id=" . $data['business_id'] . "&groupid=" . $data['groupid'] . "&product=" . $data['product'] . "&special=".$data['special'];
        //用session存起来
        session('vurl'.$data['business_id'],$str);
        //session('vurl'.$data['business_id'],$data);
        $common = new Common();

        $newstr = $common->encrypt($str, 'E', 'QQ727647930');

        $a = urlencode($newstr);

        $this->redirect(request()->root().'/mobile/index?bid=' . $data['business_id']);

    }
     public function report(){
         return $this->fetch(); 
     }
     
    /**
     *
     * 手机端首页.
     *
     * @return mixed
     */
     
     public function mycaptcha_check(){
         if(empty($_GET['yanzhengma'])){
            //dump($_GET['code']);
            //$randomNumber = rand(1, 9); 
            //$this->assign('num',rand(1, 9));
            $this->assign('num','1');
            $this->assign('bid',$_GET['bid']);
            echo($this->fetch('index/yanzhengma'));
            die();
        }else{
            //$_GET['yanzhengma']=md5('AdminSystem'.$_GET['yanzhengma']);
            //$captcha = \think\captcha\Captcha([]);
            if(!captcha_check($_GET['yanzhengma'])){
             //验证失败
             $this->error('验证码错误');
             //halt($_GET['yanzhengma'].'错误');
             
            }else{
                //session('')
            }
            //halt('正确');
        }
     }
     
    public function index()
    {
        $serviceUser=db('wolive_service')->where(['business_id'=>$_GET['bid']])->find();
        if($serviceUser['yanzhengma']=='on'){
          $this->mycaptcha_check();
        }
        
        
        
        
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $ip.$_SERVER['HTTP_USER_AGENT'];
        $userAgent=formatstr($userAgent);
        
        //$userAgent=$ip;

        $url = domain;
        $arr = $this->request->get();

        $common = new Common();
        if(!isset($arr['code'])){
            $arr['code']=$this->request->param('code','');
            $arr['code']=urldecode($arr['code']);
        }
       
        $data = $common->encrypt($arr['code'], 'D', 'QQ727647930');
        
        $data=session('vurl'.$arr['bid']);
        if (!$data) {
            $this->redirect(request()->root().'/index/index/errors');
        }
        
$code=$arr['code'];
        
        parse_str($data, $arr2);
        
        
        //halt($arr2);
        //halt($arr2);
        $special = isset($arr2['special']) ? $arr2['special']:null;
        
        $theme=isset($arr2['theme'])?$arr2['theme']:'#07c160';
        hook('mobileindexhook',array_merge($arr,$arr2));
        if (!isset($arr2['visiter_id']) || !isset($arr2['visiter_name']) || !isset($arr2['product']) || !isset($arr2['groupid']) || !isset($arr2['business_id']) || !isset($arr2['avatar'])) {
            $this->redirect(request()->root().'/index/index/errors');
        }

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

        $business_id = $arr2['business_id'];
        /*
        $visiter_id = $arr2['visiter_id'];
   
        if(!empty(cookie('visiter_id'))){
            $visiter_id=cookie('visiter_id');
        }
        */
        
        //
        if(empty($visiter_id)){
            $findvisiter=db('wolive_visiter')->where(['ipuseragent'=>$userAgent])->find();
            //halt($findvisiter);
            $visiter_id=$findvisiter['visiter_id'];
        }else{
            //halt($visiter_id);
            
        }
        //dump($userAgent);
        //die();
        //dump($userAgent);
        //die();
        
        if (trim($visiter_id) == '') {
            $visiter_id=cookie('visiter_id');
            if (!$visiter_id) {
                $visiter_id = bin2hex(pack('N', time())).strtolower($common->rand(8));
                //采用浏览器保存更持久
                cookie('visiter_id', $visiter_id, 63072000);
            }
        }else{
            cookie('visiter_id', $visiter_id, 63072000);
        }
        
        /*
        dump($_SERVER);
        dump($arr2);
        dump(config('cookie'));
        halt($visiter_id);
        */
        if ($visiter_id) {

            if (!isset($_COOKIE['product_id'])) {
                // 没有product_id
                if ($arr2['product']) {
                    $product = $arr2['product'];
                    $content = json_decode(htmlspecialchars_decode($arr2['product']), true);
                    if (isset($content['pid']) && isset($content['url']) && isset($content['img']) && isset($content['title']) && isset($content['info']) && isset($content['price'])) {
                        setcookie("product_id", $content['pid'], time() + 3600 * 12);
                        $arr2['timestamp'] = time();

                        $service = User::table('wolive_queue')->where(['visiter_id' => $visiter_id, 'business_id' => $business_id])->find();

                        if ($service) {
                            $service_id = $service['service_id'];
                        } else {
                            $service_id = 0;
                        }

                        $str = '<a href="' . $content['url'] . '" target="_blank" class="wolive_product">';
                        $str .= '<div class="wolive_img"><img src="' . $content['img'] . '" width="100px"></div>';
                        $str .= '<div class="wolive_head"><p class="wolive_info">' . $content['title'] . '</p><p class="wolive_price">' . $content['price'] . '</p>';
                        $str .= '<p class="wolive_info">' . $content['info'] . '</p>';
                        $str .= '</div></a>';


                        $mydata = ['service_id' => $service_id, 'visiter_id' => $visiter_id, 'content' => $str, 'timestamp' => time(), 'business_id' => $business_id, 'direction' => 'to_service'];
                        $pusher->trigger('kefu' . $service_id, 'cu-event', array('message' => $mydata));

                        $chats = User::table('wolive_chats')->insert($mydata);
                    }
                }
            } else {
                //新用户
                 $touxiang=db('wolive_randtouxiang')->orderRaw("RAND()")->find();
                 $arr2['avatar']=$touxiang['url'];
                 //
                $pid = $_COOKIE['product_id'];


                if ($arr2['product']) {
                    $product = $arr2['product'];
                    $content = json_decode(htmlspecialchars_decode($arr2['product']), true);

                    if (isset($content['pid']) && isset($content['url']) && isset($content['img']) && isset($content['title']) && isset($content['info']) && isset($content['price']) && $content['pid'] != $pid) {

                        $service = User::table('wolive_queue')->where(['visiter_id' => $visiter_id, 'business_id' => $business_id])->find();

                        if ($service) {
                            $service_id = $service['service_id'];
                        } else {
                            $service_id = 0;
                        }

                        $str = '<a href="' . $content['url'] . '" target="_blank" class="wolive_product">';
                        $str .= '<div class="wolive_img"><img src="' . $content['img'] . '" width="100px"></div>';
                        $str .= '<div class="wolive_head"><p class="wolive_info">' . $content['title'] . '</p><p class="wolive_price">' . $content['price'] . '</p>';
                        $str .= '<p class="wolive_info">' . $content['info'] . '</p>';
                        $str .= '</div></a>';

                        $mydata = ['service_id' => $service_id, 'visiter_id' => $visiter_id, 'content' => $str, 'timestamp' => time(), 'business_id' => $business_id, 'direction' => 'to_service'];
                        $pusher->trigger('kefu' . $service_id, 'cu-event', array('message' => $mydata));

                        $chats = User::table('wolive_chats')->insert($mydata);

                    }
                }
            }

        } else {

            if (!isset($_COOKIE['product_id'])) {
                // 没有product_id
                if ($arr2['product']) {
                    $product = $arr2['product'];
                    $content = json_decode(htmlspecialchars_decode($arr2['product']), true);
                    if (isset($content['pid']) && isset($content['url']) && isset($content['img']) && isset($content['title']) && isset($content['info']) && isset($content['price'])) {
                        setcookie("product_id", $content['pid'], time() + 3600 * 12);
                        $arr2['timestamp'] = time();

                        $service = User::table('wolive_queue')->where(['visiter_id' => $visiter_id, 'business_id' => $business_id])->find();

                        if ($service) {
                            $service_id = $service['service_id'];
                        } else {
                            $service_id = 0;
                        }

                        $str = '<a href="' . $content['url'] . '" target="_blank" class="wolive_product">';
                        $str .= '<div class="wolive_img"><img src="' . $content['img'] . '" width="100px"></div>';
                        $str .= '<div class="wolive_head"><p class="wolive_info">' . $content['title'] . '</p><p class="wolive_price">' . $content['price'] . '</p>';
                        $str .= '<p class="wolive_info">' . $content['info'] . '</p>';
                        $str .= '</div></a>';


                        $mydata = ['service_id' => $service_id, 'visiter_id' => $visiter_id, 'content' => $str, 'timestamp' => time(), 'business_id' => $business_id, 'direction' => 'to_service'];
                        $pusher->trigger('kefu' . $service_id, 'cu-event', array('message' => $mydata));

                        $chats = User::table('wolive_chats')->insert($mydata);
                    }
                }
            } else {

                $pid = $_COOKIE['product_id'];

                if ($arr2['product']) {
                    $product = $arr2['product'];
                    $content = json_decode(htmlspecialchars_decode($arr2['product']), true);

                    if (isset($content['pid']) && isset($content['url']) && isset($content['img']) && isset($content['title']) && isset($content['info']) && isset($content['price']) && $content['pid'] != $pid) {

                        $service = User::table('wolive_queue')->where(['visiter_id' => $visiter_id, 'business_id' => $business_id])->find();

                        if ($service) {
                            $service_id = $service['service_id'];
                        } else {
                            $service_id = 0;
                        }

                        $str = '<a href="' . $content['url'] . '" target="_blank" class="wolive_product">';
                        $str .= '<div class="wolive_img"><img src="' . $content['img'] . '" width="100px"></div>';
                        $str .= '<div class="wolive_head"><p class="wolive_info">' . $content['title'] . '</p><p class="wolive_price">' . $content['price'] . '</p>';
                        $str .= '<p class="wolive_info">' . $content['info'] . '</p>';
                        $str .= '</div></a>';


                        $mydata = ['service_id' => $service_id, 'visiter_id' => $visiter_id, 'content' => $str, 'timestamp' => time(), 'business_id' => $business_id, 'direction' => 'to_service'];
                        $pusher->trigger('kefu' . $service_id, 'cu-event', array('message' => $mydata));

                        $chats = User::table('wolive_chats')->insert($mydata);
                    }
                }
            }
        }


        $channel = bin2hex($visiter_id . '/' . $business_id);
        $visiter_name = htmlspecialchars($arr2['visiter_name']);
        $from_url=session('from_url');
        if(!$from_url){
            if (isset($_SERVER['HTTP_REFERER'])) {
                $from_url = $_SERVER['HTTP_REFERER'];
            } else {
                $from_url = '';
            }
        }
        
        //$avatar = htmlspecialchars($arr2['avatar']);
        $avatar= db('wolive_visiter')->where(['visiter_id'=>$visiter_id])->value('avatar');
        
        $groupid = htmlspecialchars($arr2['groupid']);
        //halt($avatar);
         if ($visiter_name == '') {
             //设置随机名字
            //$visiter_name = '游客' . $visiter_id; 
            $touxiang=db('wolive_randusername')->orderRaw("RAND()")->find();
            $visiter_name=$touxiang['name']; 
            
        }
        //halt($avatar);
          //修改头像
        if(empty($avatar)){
            //dump($visiter);
            $touxiang=db('wolive_randtouxiang')->orderRaw("RAND()")->find();
            $avatar=$touxiang['url'];
        }
        
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

        $business = User::table('wolive_business')->where('id', $business_id)->find();

        $rest = RestSetting::get(['business_id'=>$business_id]);
        $state = empty($rest) ? false : $rest->isOpen($business_id,$visiter_id) ;
        session('from_url',null);
        if(!$avatar || !$visiter_name){
            $visiterInfo=db('wolive_visiter')->field('visiter_name,avatar')->where(['visiter_id'=>$visiter_id,'business_id'=>$business_id])->find();
            if($visiterInfo){
                $avatar=$avatar==''?$visiterInfo['avatar']:'';
                $visiter_name=$visiter_name==''?$visiterInfo['visiter_name']:'';
            }
        }
        $is_bind_wechat=0;

        $this->wechat_platform=db('wolive_wechat_platform')->where(['business_id'=>$business_id])->find();
        if($this->wechat_platform && $this->wechat_platform['app_id'] && $this->wechat_platform['app_secret'] && $this->wechat_platform['isscribe']){
            $wxInfo=db('wolive_weixin')->field('subscribe')->where(['business_id'=>$business_id,'open_id'=>$visiter_id])->find();

            if(!$wxInfo || $wxInfo['subscribe']==0){
                $is_bind_wechat=1;
            }
        }
        $serverUser=db('wolive_service')->where(['business_id'=>$business_id])->find();
        $this->assign('serveruserstate', $serverUser['state']);
        $this->assign('nickname', $serverUser['nick_name']);
        $this->assign('is_bind_wechat', $is_bind_wechat);
        $this->assign('reststate', $state);
        $this->assign('code', $code);
        $this->assign('restsetting',$rest);
        $this->assign('business_name',$business['business_name']);
        $this->assign("atype", $business['audio_state']);
        $this->assign('groupid', $groupid);
        $this->assign('app_key', $app_key);
        $this->assign('whost', $arr['host']);
        $this->assign('value', $value);
        $this->assign('wport', wport);
        $this->assign('port', $port);
        $this->assign('url', $url);
        $this->assign('visiter', $visiter_name);
        $this->assign('business_id', $business_id);
        $this->assign('from_url', $from_url);
        $this->assign('channel', $channel);
        $this->assign('visiter_id', $visiter_id);
        $this->assign('avatar', $avatar);
        $this->assign('special',$special);
        $this->assign('theme',$theme);
        
        //halt($avatar);
        return $this->fetch();
    }

}