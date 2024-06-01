<?php


namespace app\index\controller;

use app\admin\model\RestSetting;
use app\admin\model\WechatPlatform;
use app\admin\model\WechatService;
use EasyWeChat\Factory;
use think\Controller;
use app\extra\push\Pusher;
use app\index\model\User;
use app\Common;
use think\Cookie;
use think\Exception;

/**
 *
 * 前台Pc端对话窗口.
 */
class Index extends Controller
{

    public function _initialize()
    {
        /*
        $request = request();
        $ip=$request->ip();
        //$ip='165.154.72.236';
        getCityByIP($ip);
        */
        
        /*
        require 'lib/gitip/index.php';
        $a=get_ip_location('125.70.98.43');
        halt($a);
        */
        
        $this->assign('basename',BASENAME);
    }
    //轮询检查域名
    //http://118.193.34.5/index.php/index/index/checkym
    public function checkym(){
        $yuminglist=db('wolive_yuming')->where(['leixing'=>['in',['2','3'] ],'type'=>'0'])->select();
        foreach ($yuminglist as $ym){
            $issurvival=wxfengjincheck($ym['name']);
            dump($ym['name'].':'.$issurvival);
            if(!$issurvival){
                db('wolive_yuming')->where(['id'=>$ym['id']])->update(['type'=>'1']);
            }
        }
        //wxfengjincheck
    }
    
    //http://118.193.34.5/index.php/index/index/checkjump
    public function checkjump(){
        //halt(1);
        //获取普通域名
        $yzm=input('yzm');
        $serverid=input('service_id');
        
        //halt($serverid);
        
        $survival_host=db('wolive_survival_host')->where(['service_id'=>$serverid,'erweicode'=>$yzm])->find();
        //halt($findservice);
        
        if(empty($survival_host)){
            halt('二维码链接已失效');
        }
        
        $findservice=db('wolive_service')->where(['service_id'=>$serverid])->find();
        
        $yuming=db('wolive_yuming')->where(['leixing'=>'1','type'=>'0'])->select();
        
        foreach ($yuming as $ym){
            if(wxfengjincheck($ym['name'])){
                //halt($ym['name']);
                //halt('可用域名:'.$ym['name']);
                $url='http://'.$ym['name'].$findservice['qrurl'];
                //$url='http://'.'118.193.34.5'.$findservice['qrurl'];
                //halt($url);
                header('Location:'.$url);
                exit();
            }else{
                db('wolive_yuming')->where(['id'=>$ym['id']])->update(['type'=>'1']);
            }
        }
        
        halt('无可用域名！');
        
        
    }
    
    
     public function zscheckjump(){
        //获取普通域名
        $yzm=input('yzm');
        //halt($yzm);
        $findservice=db('wolive_service')->where(['zhuanshuerweicode'=>$yzm])->find();
        if(empty($findservice)){
            halt('链接不正确或已失效!');
        }
        if($findservice['iszhuanshuqrcodeshixiao']=='1'){
            halt('二维码链接已失效');
        }
        
        $yuming=db('wolive_yuming')->where(['leixing'=>'1','type'=>'0'])->select();
        
        foreach ($yuming as $ym){
            if(wxfengjincheck($ym['name'])){
                //halt('可用域名:'.$ym['name']);
                $url='http://'.$ym['name'].$findservice['qrurl'];
                header('Location:'.$url);
                
            }else{
                db('wolive_yuming')->where(['id'=>$ym['id']])->update(['type'=>'1']);
            }
        }
        
        halt('无可用域名！');
        
        
    }
    
    
    
    public function checkzhuanshuyuming(){
        $host = $_SERVER['HTTP_HOST'];
        $serverid=$_GET['special'];
        //查找用户
        $findservice=db('wolive_service')->where(['service_id'=>$serverid])->find();
        if($findservice['iszhuanshuqrcodeshixiao']=='1'){
            halt('专属域名已失效');
        }
        
        if($findservice['zhuanshuyuming']!=$host){
            halt('专属域名不正确');
        }

    }
    
    /**
     *
     * [home description]
     * @return [type] [description]
     */
    public function home()
    {
        
        
//        $data = $this->request->request('','');
        $data = $this->request->param();
        //halt($data);
        if((!empty($data['iszhuanshu'])) && $data['iszhuanshu']=='1'){
            $this->checkzhuanshuyuming();
        }
        
        $data['theme'] = $this->request->param('theme','7571f9');
        if (isset($data['code']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            try{
//                var_dump($data);exit();
//                cookie('visiter_id',null);
//                Cookie::delete('product_id');
                $wechat = WechatPlatform::get(['business_id' => $data['business_id']]);
                $appid = $wechat['app_id'];
                $appsecret = $wechat['app_secret'];
//                $access_token=cache('oauth_access_token');
                $weixin = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code={$data['code']}&grant_type=authorization_code");//通过code换取网页授权access_token
                $array = json_decode($weixin,true); //对JSON格式的字符串进行编码
                //{"errcode":40029,"errmsg":"invalid code"}
                if(!isset($array['access_token'])){
                    //说明没有获取到
                    $this->error($array['errmsg'],$url = null, $data = '', $wait = 999999999);
                }
                cache('oauth_access_token',$array['access_token'],7000);

                $info = file_get_contents("https://api.weixin.qq.com/sns/userinfo?access_token={$array['access_token']}&openid={$array['openid']}&lang=zh_CN");
                $infoarray = json_decode($info,true);
                if(!isset($infoarray['openid'])){
                    //说明没有获取到
                    $this->error('当前会员信息获取失败',$url = null, $data = '', $wait = 999999999);
                }
                $data['visiter_id'] = $infoarray['openid'];
                $common = new Common();
                $data['visiter_name'] = $common->remove_emoji($infoarray['nickname']);
                $data['avatar'] = $infoarray['headimgurl'];
                if (!isset($data['groupid'])) {
                    $data['groupid'] = 0;
                }
                $this->wechat_platform=db('wolive_wechat_platform')->where(['business_id'=> $data['business_id']])->find();
                if($this->wechat_platform && $this->wechat_platform['app_id'] && $this->wechat_platform['app_secret'] && $this->wechat_platform['isscribe']){
                //https://api.weixin.qq.com/cgi-bin/user/info?access_token={$this->access_token}&openid={$openid}
                //https://api.weixin.qq.com/cgi-bin/user/info?access_token=
                    $options=[
                        'app_id' => $this->wechat_platform['app_id'],
                        'secret' => $this->wechat_platform['app_secret'],
                        'aes_key' => $this->wechat_platform['wx_aeskey'],
                        'token'  => $this->wechat_platform['wx_token'],
                    ];
                    $app = Factory::officialAccount($options);
                    $user = $app->user->get($data['visiter_id']);
                    $wxInfo=db('wolive_weixin')->field('subscribe')->where(['business_id'=>$data['business_id'],'open_id'=>$data['visiter_id']])->find();
                    $subscribe=$user['subscribe'];
                    if(isset($wxInfo['subscribe'])){
                        if($wxInfo['subscribe']!=$subscribe){
//                        不相等则更新
                            db('wolive_weixin')->where(['business_id'=>$data['business_id'],'open_id'=>$data['visiter_id']])->update(['subscribe' => $subscribe]);
                        }
                    }else{
                        db('wolive_weixin')->insert(['subscribe' => $subscribe,'business_id'=>$data['business_id'],'open_id'=>$data['visiter_id'],'subscribe_time'=>0]);
                    }
                }
            }catch (Throwable $t)
            {
                $this->error($t->getMessage(),$url = null, $data = '', $wait = 999999999);
            }
            catch (Exception $e)
            {
                $this->error($e->getMessage(),$url = null, $data = '', $wait = 999999999);
            }

        }else{
            session('from_url',isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'');
        }

        if (!isset($data['product'])) {
            $data['product'] = "";
        }

        if (!isset($data['special'])) {
            $data['special'] = "";
        }

        $str = 'theme='.$data['theme']."&visiter_id=" . $data['visiter_id'] . "&visiter_name=" . $data['visiter_name'] . "&avatar=" . $data['avatar'] . "&business_id=" . $data['business_id'] . "&groupid=" . $data['groupid'] . "&product=" . $data['product']."&special=" . $data['special'];


        $common = new Common();

        $newstr = $common->encrypt($str, 'E', 'QQ727647930');

        $a = urlencode($newstr);

        hook('homejumpbeforehook',array_merge($data,['code'=>$a]));
        //halt($a);
        $this->redirect(request()->root().'/index/index?code=' . $a);

    }


    /**
     * 对话窗口页面.
     *
     * @return mixed
     */
    public function index()
    {
        $arr = $this->request->get();
        $common = new Common();

        $is_mobile = $common->isMobile();
        if(!isset($arr['code'])){
            exit('非法访问');
        }
        $data = $common->encrypt($arr['code'], 'D', 'QQ727647930');
        //halt($data);
        if (!$data) {
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



        $url = domain;
        $from_url=session('from_url');
        if(!$from_url){
            if (isset($_SERVER['HTTP_REFERER'])) {
                $from_url = $_SERVER['HTTP_REFERER'];
            } else {
                $from_url = '';
            }
        }
        parse_str($data, $arr2);
        //dump($arr2);
       
        $special = isset($arr2['special']) ? $arr2['special']:null;

        if (!isset($arr2['visiter_id']) || !isset($arr2['visiter_name']) || !isset($arr2['product']) || !isset($arr2['groupid']) || !isset($arr2['business_id']) || !isset($arr2['avatar'])) {
            $this->redirect(request()->root().'/index/index/errors');
        }

        $theme=isset($arr2['theme'])?$arr2['theme']:'7571f9';
        if ($is_mobile) {
            $this->redirect(request()->root().'/mobile/index/home?theme=' . $theme . '&visiter_id=' . $arr2['visiter_id'] . '&visiter_name=' . $arr2['visiter_name'] . '&avatar=' . $arr2['avatar'] . '&business_id=' . $arr2['business_id'] . '&product=' . $arr2['product'] . '&groupid=' . $arr2['groupid']."&special=".$special);
        }


        $content = json_decode($arr2['product'], true);
        if (!$content) {
            $arr2['product'] = NULL;

        }
        $business_id = htmlspecialchars($arr2['business_id']);
        $visiter_id = htmlspecialchars($arr2['visiter_id']);
        if ($visiter_id === '') {
            $visiter_id=cookie('visiter_id');
            if (!$visiter_id) {
                $visiter_id = bin2hex(pack('N', time())).strtolower($common->rand(8));
                //采用浏览器保存更持久
                cookie('visiter_id', $visiter_id, 63072000);
            }
        }

        // 判断是否访问过
        if ($visiter_id) {
            //dump('访问过');
             $visiter = db('wolive_visiter')->where(['business_id' => $arr2['business_id']])->find();
             //$arr2['avatar']=$visiter['avatar'];
            if (!isset($_COOKIE['product_id'])) {

                if ($arr2['product'] != NULL) {
                    $content = json_decode($arr2['product'], true);
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
               
                 //$visiter = db('wolive_visiter')->where(['business_id' => $arr2['business_id']])->find();

                //$arr2=$visiter;
               
                
                
                $pid = isset($_COOKIE['product_id']) ? $_COOKIE['product_id'] : '';
                if ($arr2['product'] != NULL) {
                    $content = json_decode($arr2['product'], true);
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
             //没被访问过
            if (!isset($_COOKIE['product_id'])) {

                if ($arr2['product'] != NULL) {
                    $content = json_decode($arr2['product'], true);
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
                if ($arr2['product'] != NULL) {
                    if ($arr2['visiter_id'] != cookie('visiter_id')) {
                        $content = json_decode($arr2['product'], true);
                        if (isset($content['pid']) && isset($content['url']) && isset($content['img']) && isset($content['title']) && isset($content['info']) && isset($content['price'])) {
                            $service = User::table('wolive_queue')->where(['visiter_id' => $visiter_id, 'business_id' => $business_id])->find();
                            if ($service) {
                                $service_id = $service['service_id'];
                            } else {
                                $service_id = 0;
                            }
                            $str = '<a href="' . $content['url'] . '" target="_blank" class="wolive_product">';
                            $str .= '<div class="wolive_img"><img src="' . $content['img'] . '" width="100px"></div>';
                            $str .= '<div class="wolive_head"><p class="wolive_info">' . $content['title'] . '</p><p class="wolive_price">' . $content['price'] . '</p><p>';
                            $str .= '<p class="wolive_info">' . $content['info'] . '</p>';
                            $str .= '</div></a>';
                            $mydata = ['service_id' => $service_id, 'visiter_id' => $visiter_id, 'content' => $str, 'timestamp' => time(), 'business_id' => $business_id, 'direction' => 'to_service'];
                            $pusher->trigger('kefu' . $service_id, 'cu-event', array('message' => $mydata));
                            $chats = User::table('wolive_chats')->insert($mydata);
                        }
                    } else {
                        $pid = $_COOKIE['product_id'];
                        $product = $arr2['product'];
                        $content = json_decode($arr2['product'], true);
                        // 判断是否是同个商品
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
        }

        $channel = bin2hex($visiter_id . '/' . $business_id);
        $visiter_name = htmlspecialchars($arr2['visiter_name']);
        //halt($arr2);
        
        
        //$avatar = htmlspecialchars($arr2['avatar']);
        $avatar= db('wolive_visiter')->where(['visiter_id'=>$visiter_id])->value('avatar');
        //halt($arr2);
        if ($visiter_name == '') {
            if(!empty($visiter)){
                $visiter_name=$visiter['visiter_name'];
            }else{
                $touxiang=db('wolive_randusername')->orderRaw("RAND()")->find();
                $visiter_name=$touxiang['name']; 
            }
            
            //$visiter_name = '游客' . $visiter_id;
        }
        //修改头像
        if(empty($avatar)){
            //dump($visiter);
            $touxiang=db('wolive_randtouxiang')->orderRaw("RAND()")->find();
            $avatar=$touxiang['url'];
        }
        
        $groupid = htmlspecialchars($arr2['groupid']);
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
        session('from_url',null);
        $business = User::table('wolive_business')->where('id', $business_id)->find();
        $rest = RestSetting::get(['business_id'=>$business_id]);
        $state = empty($rest) ? false : $rest->isOpen($business_id,$visiter_id) ;
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

        $title=User::table('wolive_shezhi')->where('id',1)->find();
        $this->assign('admin_title',$title['name']);

        $this->assign('reststate', $state);
        $this->assign('restsetting',$rest);
        $this->assign('business_name',$business['business_name']);
        $this->assign("type", $business['video_state']);
        $this->assign("atype", $business['audio_state']);
        $this->assign('app_key', $app_key);
        $this->assign('whost', $arr['host']);
        $this->assign('value', $value);
        $this->assign('wport', wport);;
        $this->assign('port', $port);
        $this->assign('url', $url);
        $this->assign('groupid', $groupid);
        $this->assign('visiter', $visiter_name);
        $this->assign('business_id', $business_id);
        $this->assign('from_url', $from_url);
        $this->assign('channel', $channel);
        $this->assign('visiter_id', $visiter_id);
        $this->assign('avatar', $avatar);
        $this->assign('theme', $theme);
        $this->assign('is_bind_wechat', $is_bind_wechat);
        $this->assign('special',$special);
        return $this->fetch();
    }

    public function qrcode()
    {

        $visiter_id=$this->request->post('visiter_id','');
        $business_id=$this->request->post('business_id','');
        if($visiter_id && $business_id){
            $qrcode = WechatService::get($business_id)->qrcode;
            $result = $qrcode->temporary('fangke_'.$visiter_id, 6 * 24 * 3600);
            $ticket = $result['ticket'];
            $url = $qrcode->url($ticket);
            return json(['code'=>1,'data'=>$url]);
        }else{
            return json(['code'=>0,'msg'=>'访客信息没有获取到！']);
        }
    }

    public function bind_user()
    {
        $visiter_id=$this->request->post('visiter_id','');
        $open_id=$this->request->post('open_id','');
        if(!$visiter_id || !$open_id){
            return json(['code'=>0,'msg'=>'绑定失败']);
        }
        $business_id=$this->request->post('business_id','');
        $code=$this->request->post('code','');
        $parameter=$this->request->post('parameter','',null);
        $wechat = WechatPlatform::get(['business_id' => $business_id]);
        // config配置
        $options=[
            'app_id' => $wechat['app_id'],
            'secret' => $wechat['app_secret'],
            'aes_key' => $wechat['wx_aeskey'],
            'token'  => $wechat['wx_token'],
        ];
        $app = Factory::officialAccount($options);
        $user = $app->user->get($open_id);
        if(!$user['subscribe']){
            return json(['code'=>0,'msg'=>'请先关注微信公众号']);
        }
        $url='';
        if($code){
            $common = new Common();
            $data1 = $common->encrypt($code, 'D', 'QQ727647930');
            parse_str($data1, $data);
            $data['visiter_id']=$open_id;
            $data['avatar']=$user['headimgurl'];
            $data['visiter_name']=$user['nickname'];
            $str = http_build_query($data);
            $newstr = $common->encrypt($str, 'E', 'QQ727647930');
            $a = urlencode($newstr);
            $url=request()->root().'/index/index?code=' . $a;
        }elseif($parameter){
            $data=json_decode($parameter,true);
            $data['visiter_id']=$open_id;
            $data['avatar']=$user['headimgurl'];
            $data['visiter_name']=$user['nickname'];
            $url=request()->root().'/layer?' . http_build_query($data);
        }

        $res=db('wolive_visiter')->field('vid')->where(['visiter_id'=>$open_id,'business_id'=>$business_id])->find();
        if(!$res){
            db('wolive_visiter')->where(['visiter_id'=>$visiter_id,'business_id'=>$business_id])->update(['visiter_id' =>$open_id,'visiter_name'=>$user['nickname'],'avatar'=>$user['headimgurl'],'channel'=>bin2hex($open_id . '/' . $business_id)]);
        }
        cookie('visiter_id', $open_id, 63072000);
        db('wolive_chats')->where(['visiter_id'=>$visiter_id,'business_id'=>$business_id])->update(['visiter_id' =>$open_id]);
        return json(['code'=>1,'msg'=>'绑定成功','url'=>$url]);
    }
    /**
     * 404页面
     */

    public function errors()
    {
        return $this->fetch();
    }

    /**
     * 获取排队数量.
     *
     * @return mixed
     */
    public function getwaitnum()
    {
        $post = $this->request->post();
        $num = User::table('wolive_queue')->where('visiter_id', $post['visiter_id'])->where("service_id", 0)->count();
        return $num;
    }

    public function wechat()
    {
        $business_id = $this->request->param('business_id', '');
        $group_id = $this->request->param('groupid',0);
        $special = $this->request->param('special','');
        $theme = $this->request->param('theme','7571f9');
        if(empty($business_id)){
            abort(500);
        }
        session('from_url',isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'');
        $wechat = WechatPlatform::get(['business_id' => $business_id]);
        $APPID = $wechat['app_id'];
        $REDIRECT_URI = url('index/index/home',['business_id'=>$business_id,'groupid'=>$group_id,'special'=>$special,'theme'=>$theme],true,true);
        $scope = 'snsapi_userinfo';
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $APPID . '&redirect_uri=' . urlencode($REDIRECT_URI) . '&response_type=code&scope=' . $scope . '&state=123#wechat_redirect';
        $this->redirect($url);
    }
    public function test(){
        $wechat = WechatPlatform::get(['business_id' => 1]);
        // config配置
        $options=[
            'app_id' => $wechat['app_id'],
            'secret' => $wechat['app_secret'],
            'aes_key' => $wechat['wx_aeskey'],
            'token'  => $wechat['wx_token'],
        ];
        $app = Factory::officialAccount($options);
        $user = $app->user->get('o1PDR5mfR4GftZBpzzoI9n2gWGtE');
//        {{productType.DATA}}：{{name.DATA}}
//购买数量：{{number.DATA}}
//有效期：{{expDate.DATA}}
//{{remark.DATA}}
        $res=$app->template_message->send([
            'touser' => 'o1PDR5mfR4GftZBpzzoI9n2gWGtE',
            'template_id' => 'gBAFRzcRkQYwlRLncSPSPuyPSlxg-JXxt2Dfk3DvoNk',
            'url' => 'https://easywechat.org',
            'data' => [
                'productType' => '软件',
                'name' => '来客客服系统',
                'number' => '1套',
                'expDate' => '1年内',
                'remark' => '请及时提取'
            ],
        ]);
        var_dump($res);
    }
}