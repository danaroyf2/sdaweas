<?php


namespace app\admin\controller;

use app\admin\model\Admins;
use app\admin\model\Chats;
use app\admin\model\CommentSetting;
use app\admin\model\Queue;
use app\admin\model\TplService;
use app\admin\model\Visiter;
use app\admin\model\WechatPlatform;
use app\common\lib\CurlUtils;
use app\common\lib\Lock;
use app\common\lib\Storage;
use app\common\lib\storage\StorageException;
use app\extra\push\Pusher;
use think\Db;
use think\Exception;
use think\Log;
use app\admin\iplocation\Ip;
/**
 *
 * 设置控制器.
 */
class Set extends Base
{
    
    public function websocketSendMsg($visiter_id,$business_id,$msg){
        $arr=[];
        $login = $_SESSION['Msg'];
        $service_id=$login['service_id'];
        
        $app_key = app_key;
        $app_secret = app_secret;
        $app_id = app_id;
        $sarr = parse_url(ahost);
        if ($sarr['scheme'] == 'https') {
            $state = true;
        } else {
            $state = false;
        }
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
        $arr['visiter_id'] = $visiter_id;
        $arr['business_id'] = $business_id;
        $channel = bin2hex($arr['visiter_id'] . '/' . $arr['business_id']);
        
        $pusher->trigger("cu" . $channel, 'my-event', array('message' =>$msg));
    }
    
    
    public function clearchat(){
        $visiter_id=input('visiter_id');
        $login = $_SESSION['Msg'];
        $service_id=$login['service_id'];
        

        $where=[
            'visiter_id'=>$visiter_id,
            'service_id'=>$service_id,
        ];
        $res=db('wolive_chats')->where($where)->delete();
        if(!empty($res)){
            //hook('sendonesubhook',array_merge($wechat,['nick_name'=>$login['nick_name'],'groupid'=>$queue['groupid'],'sendres'=>$sendres,'visiter'=>$visiter,'content'=>$arr["content"]]));
            //hook('sendonesubhook','666');
            $arr['visiter_id'] = $visiter_id;
            $arr['business_id'] = $login['business_id'];
            $msg=[
                'caozuo'=>'clear',
            ];
            $this->websocketSendMsg($arr['visiter_id'],$arr['business_id'],$msg);
            
            
            return json(['code'=>'1','data'=>'清空成功!']);
        }else{
            return json(['code'=>'0','data'=>'清空失败!']);
        }
        //halt($login);
    }
    /**
     * 对话pusher类.
     *
     * @return void
     */

    public function chats()
    {
        $data = $this->request->post();
        $visiterids=explode(',',$data['visiter_id']);
        //halt($visiterids);
        foreach ($visiterids as $id){
            $data['visiter_id']=$id;
            $this->chatsone($data);
        }
    }
    
    public function chatsone($arr)
    {
        $login = $_SESSION['Msg'];
//        $arr = $this->request->post('',null,null);
        //$arr = $this->request->post();
        $arr['content'] = $_POST['content']; //$this->request->post('content','','\app\Common::clearXSS');
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

        $arr['business_id'] = $login['business_id'];
        $arr['service_id'] = $login['service_id'];

        $arr['direction'] = 'to_visiter';
        $arr["timestamp"] = time();
        $channel = bin2hex($arr['visiter_id'] . '/' . $arr['business_id']);
        $visiter = Db::table('wolive_visiter')
            ->where('visiter_id',$arr['visiter_id'])
            ->where('business_id',$login['business_id'])
            ->find();

        $queue = Db::table('wolive_queue')
            ->where('visiter_id',$arr['visiter_id'])
            ->where('business_id',$login['business_id'])
            ->find();

        try {
            $wechat = WechatPlatform::get(['business_id'=>$arr['business_id']]);
            $sendres=[];
            if ($visiter['state'] == 'offline' && trim($wechat['customer_tpl'])!='' && strlen($visiter['visiter_id'])>16) {
                $sendres=TplService::send($arr["business_id"],$visiter['visiter_id'],url('index/index/wechat',['business_id'=>$arr['business_id'],'groupid'=>$queue['groupid']],true,true),$wechat['customer_tpl'],[
                    "first"  => "你有一条新的信息!",
                    "keyword1"   => $arr["content"],
                    "keyword2"  => $login['nick_name'],
                    "remark" => $login['business']['business_name']."提示:客服有新的消息,快去看看吧~",
                ]);
            }
            if(!$wechat){$wechat=[];}else{$wechat=$wechat->toArray();}
            
            hook('sendonesubhook',array_merge($wechat,['nick_name'=>$login['nick_name'],'groupid'=>$queue['groupid'],'sendres'=>$sendres,'visiter'=>$visiter,'content'=>$arr["content"]]));
           
        } catch (\EasyWeChat\Core\Exceptions\HttpException $e) {
        } catch (\EasyWeChat\Core\Exceptions\InvalidArgumentException $exception) {
        }

        //halt(6);
        try {
            //fields not exists:[avatar]
            unset($arr['avatar']);
            $cid = Admins::table('wolive_chats')->insertGetId($arr);
            //$arr['avatar'] = $login['avatar'];
            //halt($login);
            $arr['avatar'] =  db('wolive_service')->where(['service_id' => $login['service_id']])->value('avatar');
            dump($arr);
            $arr['cid'] = $cid;
            $pusher->trigger("cu" . $channel, 'my-event', array('message' => $arr));
            $key = "callback_".$_SESSION['Msg']['business_id']."_".$_SESSION['Msg']['service_id'];
            //针对同一客户端的锁，防止同一客户端多次回调
            $_SESSION[$key] = md5(microtime(true));
            $businessInfo=Admins::table('wolive_business')->where('id', $login['business_id'])->field('push_url')->find();
            if(trim($businessInfo['push_url'])!=''){
                $pusher->trigger('kefu' .  $login['service_id'], 'callbackpusher', array('message' => $arr));
            }
            $data = ['code' => 0, 'msg' => 'success'];
            return $data;

        } catch (Exception $e) {

            $error = $e->getMessage();
            $data = ['code' => 3, 'msg' => $error];
            return $data;
        }

    }

    /**
     * 删除访客类.
     *
     * @return mixed
     */
    public function deletes()
    {
        $login = $_SESSION['Msg'];
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


        
        $post = $this->request->post();
        $result = Admins::table('wolive_queue')->where('visiter_id', $post['visiter_id'])->where('business_id', $login['business_id'])->update(['state' => 'complete']);


        $pusher->trigger("ud" . $login['service_id'], 'on_chat', array('message' => ''));

        $data = ['code' => 0, 'msg' => 'success'];

        return $data;

    }

    public function clear()
    {
        $login = $_SESSION['Msg'];
        $post = $this->request->post();
        $res = Admins::table('wolive_queue')->where('business_id',$login['business_id'])
            ->where('service_id', $post['id'])
            ->update(['state' => 'complete']);
        if ($res) {
            $data = ['code' => 0, 'msg' => 'success'];
        } else {
            $data = ['code' => 1, 'msg' => 'error'];
        }

        return $data;
    }

    /**
     * 转接客服类.
     * @return [type] [description]
     */
    public function getswitch()
    {
        $login = $_SESSION['Msg'];
        $post = $this->request->post();

        $admin = Admins::table('wolive_service')->where('service_id', $post['id'])->find();

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

        $channel = bin2hex($post['visiter_id'] . '/' . $login['business_id']);

unset($admin['password']);
        $pusher->trigger("cu" . $channel, 'getswitch', array('message' => $admin));

        $pusher->trigger('kefu' . $post['id'], 'getswitch', array('message' => $post['name'] . "  转接访客给你"));

        $result = Admins::table('wolive_queue')->where("visiter_id", $post['visiter_id'])->where('business_id', $login['business_id'])->where('state', 'normal')->update(['service_id' => $post['id']]);


        if ($result) {
            $arr = ['code' => 0, 'msg' => '转接成功！'];
            return $arr;
        } else {
            $arr = ['code' => 1, 'msg' => '转接失败！'];
            return $arr;
        }

    }


    /**
     * 认领访客类.
     *
     * @return mixed
     */
    public function get()
    {
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

        $login = $_SESSION['Msg'];
        $post = $this->request->post();
        // 避免重复认领
        if ($login['groupid'] == 0) {
            $res = Admins::table("wolive_queue")->where("visiter_id", $post['visiter_id'])->where('business_id', $login['business_id'])->where('state', 'normal')->find();
        } else {
            $res = Admins::table("wolive_queue")->where("visiter_id", $post['visiter_id'])->where('business_id', $login['business_id'])->where('state', 'normal')->where('groupid', $login['groupid'])->find();
        }


        if ($res['service_id'] == 0) {

            $result = Admins::table('wolive_queue')->where(['qid' => $res['qid'], 'state' => 'normal'])->update(['service_id' => $login['service_id']]);


            $channel = bin2hex($post['visiter_id'] . '/' . $login['business_id']);

            $resdata = Admins::table('wolive_chats')->where(['visiter_id' => $post['visiter_id'], 'business_id' => $login['business_id']])->update(['service_id' => $login['service_id']]);


            // 获取默认的常用语
            $words = Admins::table('wolive_sentence')->where('service_id', $login['service_id'])->where('state', 'using')->find();
            if ($words['content'] == "") {
                $words['content'] = "你好!";
            }
            $chats = array(
                'avatar' => $login['avatar'],
                'content' => $words['content']
            );

            $arr = ['msg' => '访客被认领', 'groupid' => $login['groupid']];
            $pusher->trigger("all" . $login['business_id'], 'on_notice', array('message' => $arr));
            $pusher->trigger("cu" . $channel, 'first_word', array('message' => $chats));
            $pusher->trigger("cu" . $channel, 'cu_notice', array('message' => $login));

            $data = ['code' => 0, 'msg' => 'success'];
            return $data;

        } else {

            $res = Admins::table('wolive_queue')->where(['visiter_id' => $post['visiter_id'], 'business_id' => $login['business_id'], 'service_id' => 0, 'state' => "normal"])->delete();

            $arr = ['msg' => '该客服已经被认领', 'groupid' => $login['groupid']];
            $pusher->trigger("all" . $login['business_id'], 'on_notice', array('message' => $arr));
        }
    }

    /**
     * 排队列表类.
     *
     * @return mixed
     */
    public function getwait()
    {
        $login = $_SESSION['Msg'];

        if ($login['groupid'] == 0) {
            $visiters = Admins::table('wolive_queue')->where('service_id', 0)->where('business_id', $login['business_id'])->where('state', 'normal')->select();
        } else {
            $visiters = Admins::table('wolive_queue')->where('service_id', 0)->where('business_id', $login['business_id'])->where('state', 'normal')->where('groupid', $login['groupid'])->select();
        }


        $waiters = [];

        foreach ($visiters as $v) {
            $data = Admins::table('wolive_visiter')->where('visiter_id', $v['visiter_id'])->where('business_id', $login['business_id'])->find();

            $class = Admins::table('wolive_group')->where('id', $v['groupid'])->find();

            if ($data) {
                $waiters[] = $data;
            }

            if ($class) {
                $data['groupname'] = $class['groupname'];
            } else {
                $data['groupname'] = '普通咨询';
            }

            if (!empty($data['timestamp'])) {
                $data['timestamp'] = $this->formatTime(strtotime($data['timestamp']));
            }
        }
        $count = count($waiters);

        $data = ['code' => 0, 'data' => $waiters, 'num' => $count];
        return $data;
    }

    /**
     * 对话列表类.
     *
     * @return mixed
     */
    public function getchats()
    {

        $login = $_SESSION['Msg'];
//  business_id一个客服后台就是一个商户
        $visiters = Admins::table('wolive_queue')->distinct(true)->field('visiter_id')->where(['service_id' => $login['service_id'], 'business_id' => $login['business_id']])->where('state', 'normal')->order('timestamp desc')->select();
        //halt($visiters);

        if (empty($visiters)) {
            $data = ['code' => 1, 'msg' => '暂时没有数据！'];
            return $data;
        }else{
            $visiters = array_column(collection($visiters)->toArray(),'visiter_id');
        }

        function extract_attrib($tag)
        {
            preg_match_all('/(id|alt|title|src)=("[^"]*")/i', $tag, $matches);
            $ret = array();
            foreach ($matches[1] as $i => $v) {
                $ret[$v] = $matches[2][$i];
            }
            return $ret;
        }

        $chatonlinearr = [];
        $chatonlineunread = [];
        $chatofflinearr = [];
        $chatofflineunread = [];
//wolive_visiter 访客表
        $data = Visiter::all(['business_id'=>$login['business_id'],'visiter_id'=>['in',$visiters]]);
//wolive_chats 消息表
        $chatids = Chats::field('max(cid) as cid')
            ->where('business_id',$login['business_id'])
            ->where('visiter_id','in',$visiters)
            ->where('service_id',$login['service_id'])
            ->group('visiter_id')
            ->order('timestamp desc')
            ->select();

        $cids = array_column(collection($chatids)->toArray(),'cid');
        if (empty($cids)) {
            $chatsList = [];
        } else {
            $chats = Chats::where('cid','in',$cids)
                ->select();
            $chatsList = array_column(collection($chats)->toArray(),null,'visiter_id');
        }


        $result = Chats::where('business_id',$login['business_id'])
            ->where('state','unread')
            ->where('direction', 'to_service')
            ->field('visiter_id,count(visiter_id) as count')
            ->group('visiter_id')
            ->select();

        $resultList = array_column(collection($result)->toArray(),null,'visiter_id');

        foreach ($data as $v) {
            $chats2['content'] = isset($chatsList[$v['visiter_id']]['content']) ? $chatsList[$v['visiter_id']]['content'] : '' ;
            $chats2['timestamp'] = isset($chatsList[$v['visiter_id']]['timestamp']) ? $chatsList[$v['visiter_id']]['timestamp']:0;

            $values = preg_match_all('/<img.*\>/isU', $chats2['content'], $out);
            if ($values) {

                $img = $out[0];

                if ($img) {

                    $chats = "";
                    foreach ($img as $value) {
                        $attr = extract_attrib($value);

                        if ($attr) {
                            $src = $attr["src"];
                            if ($src) {
                                if (strpos($src, "emo_")) {
                                    $newimg = "<img src={$src}>";
                                } else {
                                    $newimg = "[图片]";
                                }
                            }
                        } else {
                            $newimg = '[图片]';
                        }
                        $chats .= $newimg;
                    }
                }
                $newstr = preg_replace('/<img.*\>/isU', "", $chats2['content']);
                $newcontent = $chats . $newstr;

            } else {

                if (strpos($chats2['content'], '</i>') !== false) {

                    $newcontent = '[文件]';
                } elseif (strpos($chats2['content'], '</audio>') !== false) {

                    $newcontent = '[音频]';

                } elseif (strpos($chats2['content'], '</a>') !== false) {
                    $newcontent = '[超链接]';
                } else {

                    if ($chats2['content'] == null) {
                        $newcontent = '';
                    } else {
                        $newcontent = $chats2['content'];
                    }
                }
            }
            $v['content'] = strip_tags($newcontent);
            if (isset($resultList[$v['visiter_id']]['count'])) {
                $v['count'] = $resultList[$v['visiter_id']]['count'];
            } else {
                $v['count'] = 0;
            }

            if (!empty($chats2['timestamp'])) {
                $time = $chats2['timestamp'];
                $v['order'] = $chats2['timestamp'];
            } else {
                $time = strtotime($v['timestamp']);
                $v['order'] = 0;
            }
            //halt($v['timestamp']);
            //$v['timestamp'] = $this->formatTime($time);
            //halt($v['timestamp']);
            $url = url('mobile/admin/talk',null,true,true);
            $v['mobile_route_url'] = $url."?channel=".$v['channel']."&avatar=".urlencode($v['avatar'])."&visiter_id=".$v['visiter_id'];
            if ($v['count'] > 0) {
                if ($v['state'] == 'online') {
                    $v['sort']=50;
                    $chatonlineunread[] = $v;
                } else {
                    $v['sort']=30;
                    $chatofflineunread[] = $v;
                }
            } else {
                if ($v['state'] == 'online') {
                    $v['sort']=20;
                    $chatonlinearr[] = $v;
                } else {
                    $v['sort']=10;
                    $chatofflinearr[] = $v;
                }
            }

        }

       /* reset($chatonlineunread);
        reset($chatofflineunread);
        reset($chatonlinearr);
        reset($chatofflinearr);*/
     /*   array_multisort(array_column(collection($chatonlineunread)->toArray(),'order'),SORT_DESC,$chatonlineunread);
        array_multisort(array_column(collection($chatofflineunread)->toArray(),'order'),SORT_DESC,$chatofflineunread);
        array_multisort(array_column(collection($chatonlinearr)->toArray(),'order'),SORT_DESC,array_column(collection($chatonlinearr)->toArray(),'vid'),SORT_DESC,$chatonlinearr);
        array_multisort(array_column(collection($chatofflinearr)->toArray(),'order'),SORT_DESC,array_column(collection($chatofflinearr)->toArray(),'vid'),SORT_DESC,$chatofflinearr);*/
        $chatarr = array_merge($chatonlineunread, $chatofflineunread,$chatonlinearr,$chatofflinearr);
//        var_dump(array_column($chatarr,'istop'),array_column($chatarr,'sort'),array_column($chatarr,'order'),array_column($chatarr,'vid'));exit();
        array_multisort(array_column($chatarr,'istop'),SORT_DESC,array_column($chatarr,'sort'),SORT_DESC,array_column($chatarr,'order'),SORT_DESC,array_column($chatarr,'vid'),SORT_DESC,$chatarr);
        $result = Admins::table('wolive_chats')->where('service_id', $login['service_id'])->where('business_id', $login['business_id'])->where('state', 'unread')->where('direction', 'to_service')->count();
        if ($chatarr) {
            $data = ['code' => 0, 'data' => $chatarr,'all_unread_count'=>$result];
            return $data;
        } else {
            $data = ['code' => 1, 'msg' => '暂时没有数据！'];
            return $data;
        }
    }

    public function access(){
        $login=$_SESSION['Msg'];
         $data = Visiter::all(['business_id'=>$login['business_id']]);
         echo count($data);
         print_r($data);
    }

    /**
     * 获取当前聊天信息类.
     *
     * @return string
     */
    public function chatdata()
    {

        $login = $_SESSION['Msg'];
        $service_id = $login['service_id'];
        $post = $this->request->post();

        if ($post["hid"] == '') {

            $data = Admins::table('wolive_chats')->where(['service_id' => $service_id, 'visiter_id' => $post['visiter_id'], 'business_id' => $login['business_id']])->order('timestamp desc,cid desc')->limit(10)->select();

            $vdata = Admins::table('wolive_visiter')->where('visiter_id', $post['visiter_id'])->where('business_id', $login['business_id'])->find();

            $sdata = Admins::table('wolive_service')->where('service_id', $service_id)->find();

            foreach ($data as $v) {

                if ($v['direction'] == 'to_service') {
                    $v['avatar'] = $vdata['avatar'];
                } else {

                    $v['avatar'] = $sdata['avatar'];
                }

            }

            reset($data);

        } else {


            $data = Admins::table('wolive_chats')->where(['service_id' => $service_id, 'visiter_id' => $post['visiter_id'], 'business_id' => $login['business_id']])->where('cid', '<', $post['hid'])->order('timestamp desc,cid desc')->limit(10)->select();

            $vdata = Admins::table('wolive_visiter')->where('visiter_id', $post['visiter_id'])->where('business_id', $login['business_id'])->find();

            $sdata = Admins::table('wolive_service')->where('service_id', $service_id)->find();


            foreach ($data as $v) {

                if ($v['direction'] == 'to_service') {
                    $v['avatar'] = $vdata['avatar'];
                } else {
                    $v['avatar'] = $sdata['avatar'];
                }

            }

            reset($data);

        }

        $result = array_reverse($data);
        $user = Admins::table('wolive_visiter')
            ->field('v.*,GROUP_CONCAT(g.group_name) as group_name,GROUP_CONCAT(g.bgcolor) as bgcolor')
            ->alias('v')
            ->where('v.visiter_id', $post['visiter_id'])
            ->where('v.business_id', $login['business_id'])
            ->join('wolive_visiter_vgroup vg',"v.vid = vg.vid and vg.service_id = {$login['service_id']}",'LEFT')
            ->join('wolive_vgroup g',"g.id = vg.group_id and g.service_id = {$login['service_id']}",'LEFT')
            ->group('v.vid')
            ->find();
        if (!empty($user['group_name'])) {
            $user['group_name_array'] = explode(',',$user['group_name']);
            $user['bgcolor_array'] = explode(',',$user['bgcolor']);
        } else {
            $user['group_name_array'] = [];
            $user['bgcolor_array']=[];
        }
        $data = ['code' => 0, 'data' => $result,'user'=>$user];
        return $data;
    }

    /**
     * 获取所有该用户的聊天记录。
     * [history description]
     * @return [type] [description]
     */
    public function history()
    {
        $post = $this->request->post();
        $login = $_SESSION['Msg'];

        if ($post["hid"] == '') {
            $data = Admins::table('wolive_chats')->where('visiter_id', $post['visiter_id'])->where('business_id', $login['business_id'])->order('timestamp desc,cid asc')->limit(10)->select();
            $vdata = Admins::table('wolive_visiter')->where('visiter_id', $post['visiter_id'])->where('business_id', $login['business_id'])->find();

            foreach ($data as $v) {

                if ($v['direction'] == 'to_service') {
                    $v['avatar'] = $vdata['avatar'];
                    $v['name'] = $vdata['visiter_name'];
                } else {
                    $sdata = Admins::table('wolive_service')->where('service_id', $v['service_id'])->find();
                    $v['avatar'] = $sdata['avatar'];
                    $v['name'] = $sdata['nick_name'];
                }
            }
            reset($data);
        } else {

            $data = Admins::table('wolive_chats')->where('visiter_id', $post['visiter_id'])->where('business_id', $login['business_id'])->where('cid', '<', $post['hid'])->order('timestamp desc,cid asc')->limit(10)->select();
            $vdata = Admins::table('wolive_visiter')->where('visiter_id', $post['visiter_id'])->where('business_id', $login['business_id'])->find();

            foreach ($data as $v) {

                if ($v['direction'] == 'to_service') {
                    $v['avatar'] = $vdata['avatar'];
                    $v['name'] = $vdata['visiter_name'];
                } else {
                    $sdata = Admins::table('wolive_service')->where('service_id', $v['service_id'])->find();
                    $v['avatar'] = $sdata['avatar'];
                    $v['name'] = $sdata['nick_name'];
                }
            }
            reset($data);
        }

        $result = array_reverse($data);
        $data = ['code' => 0, 'data' => $result];
        return $data;
    }

     /**
     * 删除聊天记录。
     * [history description]
     * @return [type] [description]
     */
    public function truncates()
    {
        $post = $this->request->post();
        $login = $_SESSION['Msg'];
        $business_id =$login['business_id'];
        $talk_time =isset($post['talk_time'])?$post['talk_time']:0;
        $map = ['business_id' => $business_id];
        switch ($talk_time) {
            case 0:
                 $map = ['business_id' => $business_id];
                break;

            case 1:
                $map['timestamp'] = ['<', strtotime("-1 week")];
                break;

            case 2:
                $map['timestamp'] = ['<', strtotime("-1 month")];
                break;

            case 3:
                $map['timestamp'] = ['<', strtotime("-3 month")];
                break;

            case 4:
                
                $map['timestamp'] = ['<', strtotime("-1 year")];
                break;
                
            case 5:
                $map['timestamp'] = ['<', strtotime("-5 day")];
                break;
                
            case 6:
                $map['timestamp'] = ['<', strtotime("-3 day")];
                break;
        }
        $res=Admins::table('wolive_chats')->where($map)->delete();
         if($res){
              $arr = ['code' => 0, 'msg' => '删除成功'];
              return $arr;
         } 
         else{
              $arr = ['code' => 0, 'msg' => '删除成功'];
              return $arr;
         }


    }
    /**
     * 获取ip地址类.
     *
     * @return string
     */
    public function getipinfo()
    {

        $post = $this->request->get();
        $ip = $post['ip'];
        $data = Ip::find($ip);
        $arr = ['code' => 0, 'data' => $data];
        return $arr;
    }

    /**
     * 标记已看信息.
     *
     * @return mixed
     */
    public function getwatch()
    {
        $login = $_SESSION['Msg'];
        $business_id = $login['business_id'];
        $post = $this->request->post();
        $result = Admins::table('wolive_chats')->where('visiter_id', $post['visiter_id'])->where('business_id', $business_id)->update(['state' => 1]);
        $arr = ['code' => 0, 'msg' => 'success', 'data' => ''];
        return $arr;
    }

    /**
     * 获取未看信息条数类.
     *
     * @return mixed
     */
    public function getmessage()
    {
        $login = $_SESSION['Msg'];
        $business_id = $login['business_id'];
        $post = $this->request->post();

        $channel = $post['channel'];

        $str = pack('H*', $channel);

        $arr = explode("/", $str);

        $visiter_id = $arr[0];

        $result = Admins::table('wolive_chats')->where('visiter_id', $visiter_id)->where('business_id', $business_id)->where('state', 'unread')->count();
        $data = ['code' => 0, 'data' => $result];
        return $data;
    }

    /**
     * 拖到黑名单.
     *
     * @return mixed
     */
    public function blacklist()
    {
        $post = $this->request->post();
        $login = $_SESSION['Msg'];

        $result = Admins::table('wolive_queue')->where('visiter_id', $post['visiter_id'])->where('business_id', $login['business_id'])->update(['state' => 'in_black_list']);

        $arr = ['code' => 0, 'msg' => 'success', 'data' => $result];
        return $arr;

    }

    /**
     * 移出黑名单
     *
     * @return array
     */
    public function removeblacklist()
    {
        $post = $this->request->post();
        $login = $_SESSION['Msg'];

        $result = Admins::table('wolive_queue')->where('visiter_id', $post['visiter_id'])->where('business_id', $login['business_id'])->update(['state' => 'normal']);

        $arr = ['code' => 0, 'msg' => 'success', 'data' => $result];
        return $arr;
    }

    /**
     * 获取该business_id下所用的黑名单信息.
     *
     * @return bool|string
     */
    public function getblackdata()
    {
        $login = $_SESSION['Msg'];

        $visiters = Admins::table('wolive_queue')->where('state', 'in_black_list')->where('business_id', $login['business_id'])->select();

        $blackers = [];
        foreach ($visiters as $v) {
            $data = Admins::table('wolive_visiter')->where('visiter_id', $v['visiter_id'])->where('business_id', $login['business_id'])->find();
            $blackers [] = $data;
        }

        $data = ['code' => 0, 'data' => $blackers];

        return $data;
    }


    /**
     * 设置常用语默认值.
     *
     * @return mixed
     */
    public function settalkdefa()
    {
        $login = $_SESSION['Msg'];
        $post = $this->request->post();

        //unser using
        $find=Admins::table('wolive_sentence')->where('sid', $post['tid'])->find();
        //dump($find);die();
        $chenglis=[
            'unuse'=>'using',
            'using'=>'unuse',
        ];
        $result = Admins::table('wolive_sentence')->where('sid', $post['tid'])->update(['state' =>$chenglis[$find['state']]]);
        //$res = Admins::table("wolive_sentence")->where('sid', '<>', $post['tid'])->where('service_id',$login['service_id'])->update(['state' => 'unuse']);

        $arr = ['code' => 0, 'msg' => 'success', 'data' => $result];
        return $arr;
    }

    /**
     * 设置客服离线优先
     *
     * @return array
     */
    public function setofflinefirst()
    {
        $post = $this->request->post();
        $type = $post['type']==1 ? 1:0;
        $result = Admins::table('wolive_service')->where('service_id', $post['id'])->update(['offline_first' => $type]);

        $arr = ['code' => 0, 'msg' => 'success', 'data' => $result];
        return $arr;
    }
    /**
     * 常用语删除.
     *
     * @return bool
     */
    public function tdelete()
    {
        $post = $this->request->post();
       // var_dump($post);die;
        
        $result = Admins::table('wolive_sentence')->where('sid', $post['tid'])->delete();


        $arr = ['code' => 0, 'msg' => 'success', 'data' => $result];

        return $arr;
    }

    /**
     * 查看历史记录类.
     *
     * @return mixed
     */
    public function getviews()
    {

        $login = $_SESSION['Msg'];
        $post = $this->request->post();
        $vid = $post["visiter_id"];
        $service_id = $post['service_id'];

        $business_id = $login['business_id'];
        $time = $post['puttime'];
        if ($time == 1) {
            $puttime = strtotime("-1 month");
            $result = Admins::table('wolive_chats')->where(['visiter_id' => $vid, 'business_id' => $business_id])->where('timestamp', '>', $puttime)->order('timestamp')->select();

            $vdata = Admins::table('wolive_visiter')->where('visiter_id', $vid)->where('business_id', $login['business_id'])->find();
            $sdata = Admins::table('wolive_service')->where('service_id', $service_id)->find();

            foreach ($result as $v) {

                if ($v['direction'] == 'to_service') {

                    $v['avatar'] = $vdata['avatar'];
                } else {

                    $v['avatar'] = $sdata['avatar'];
                }

            }

            reset($result);


        } else if ($time == 7) {
            $puttime = strtotime("-1 week");
            $result = Admins::table('wolive_chats')->where(['visiter_id' => $vid, 'business_id' => $business_id])->where('timestamp', '>', $puttime)->order('timestamp')->select();
            $vdata = Admins::table('wolive_visiter')->where('visiter_id', $vid)->where('business_id', $login['business_id'])->find();
            $sdata = Admins::table('wolive_service')->where('service_id', $service_id)->find();

            foreach ($result as $v) {
                if ($v['direction'] == 'to_service') {
                    $v['avatar'] = $vdata['avatar'];
                } else {
                    $v['avatar'] = $sdata['avatar'];
                }
            }

            reset($result);


        }

        $data = ['code' => 0, 'data' => $result];
        return $data;
    }

    /**
     * 自定义查看历史对话记录类.
     *
     * @return mixed
     */
    public function getdesignForViews()
    {
        $login = $_SESSION['Msg'];
        $post = $this->request->post();
        $cha = $post["channel"];

        $s_time = strtotime($post['start']);
        $e_time = strtotime($post['end']) + 24 * 60 * 60;

        $result = Admins::table('wolive_chats')->where('visiter_id', $cha)->where('timestamp', '>=', $s_time)->where('timestamp', '<=', $e_time)->select();

        foreach ($result as $v) {
            if ($v['direction'] == 'to_service') {
                $data = Admins::table('wolive_visiter')->where('visiter_id', $v['visiter_id'])->where('business_id', $login['business_id'])->find();
                $v['avatar'] = $data['avatar'];
            } else {

                $data = Admins::table('wolive_service')->where('service_id', $v['service_id'])->find();
                $v['avatar'] = $data['avatar'];
            }
        }

        reset($result);


        $data = ['code' => 0, 'data' => $result];
        return $data;
    }

    /**
     * 获取该客服交谈过的所有客服.
     *
     * @return mixed
     */
    public function getvisiters()
    {
        $login = $_SESSION['Msg'];
        $post = $this->request->post();
        $id = $post["service"];

        $visiters = Admins::table('wolive_chats')->field('visiter_id,max(cid) as cid')->where(['service_id' => $id, 'business_id' => $login['business_id']])->group('visiter_id')->order('cid','desc')->select();

        $visiterdata = [];
        foreach ($visiters as $v) {
            $visiterdata[] = $v['visiter_id'];
        }

        if ($visiterdata) {
            $data = Admins::table('wolive_visiter')->where('business_id', $login['business_id'])->where('visiter_id', 'in', $visiterdata)->select();

            $data = array_column(collection($data)->toArray(),null,'visiter_id');
            $datas = [];
            foreach ($visiterdata as $v){
                if(!isset($data[$v]))continue;
                $datas[] = $data[$v];
            }
        } else {
            $datas = '';
        }


        $data = ['code' => 0, 'data' => $datas];
        return $data;
    }

    /**
     * 获取排队人数.
     *
     * @return mixed
     */
    public function getwaitnum()
    {
        $login = $_SESSION['Msg'];

        if ($login['groupid'] == 0) {
            $result = Admins::table('wolive_queue')->where(['service_id' => 0, 'business_id' => $login['business_id']])->where('state', 'normal')->count();

        } else {
            $result = Admins::table('wolive_queue')->where(['service_id' => 0, 'business_id' => $login['business_id'], 'groupid' => $login['groupid']])->where('state', 'normal')->count();

        }


        $data = ['code' => 0, 'data' => $result];
        return $data;
    }

    /**
     * 获取访客的状态
     *
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getstatus()
    {
        $post = $this->request->post();
        $res = Admins::table('wolive_visiter')->where('channel', $post['channel'])->find();
        if($res['extends']){
            $res['extends']=json_decode($res['extends'],true);
        }
        $res['area']='';
        if($res['ip']){
            $res['area']=Ip::find($res['ip']);
        }
        $data = ['code' => 0, 'data' => $res];
        return $data;
    }

    /**
     * 获取当前对话信息
     *
     */
    public function getchatnow()
    {
        $post = $this->request->post();
        $login = $_SESSION['Msg'];
        $visiter_id = $post['sdata']['visiter_id'];
        $res = Admins::table('wolive_queue')->where('visiter_id', $visiter_id)->where('business_id', $login['business_id'])->where('service_id', $login['service_id'])->find();

        // var_dump($res['state']);exit;

        $sdata = Admins::table('wolive_visiter')->where('visiter_id', $visiter_id)->where('business_id', $login['business_id'])->find();

        $chats = Admins::table('wolive_chats')->where(['visiter_id' => $visiter_id, 'service_id' => $login['service_id'], 'direction' => 'to_service'])->group('cid desc')->find();

        if ($res['state'] == 'complete') {

            $res = Admins::table('wolive_queue')->where('visiter_id', $visiter_id)->where('business_id', $login['business_id'])->update(['state' => 'normal']);
        }

        $data = ['code' => 0, 'msg' => 'success'];
        return $data;
    }


    public function apply()
    {
        $login = $_SESSION['Msg'];
        $post = $this->request->post();

        $visiter = Admins::table('wolive_visiter')->where('visiter_id', $post['id'])->where('business_id', $login['business_id'])->find();

        if (!$visiter) {

            $data = ['code' => 1, 'msg' => '该访客数据已被清理！'];

            return $data;
        }

        $type = $visiter['state'];

        if ($type == -1) {

            $data = ['code' => 1, 'msg' => '对方不在线'];
            return $data;
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

        $pusher->trigger("cu" . $visiter['channel'], "video", array("message" => "申请视频连接", "channel" => $post['channel'], "avatar" => $post['avatar'], 'username' => $post['name']));
        $data = ['code' => 0, 'msg' => 'success'];

        return $data;

    }

    /**
     * 拒绝视屏类方法
     *
     */
    public function refuse()
    {
        $post = $this->request->post();

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

        $pusher->trigger("cu" . $post['channel'], "video-refuse", array("message" => "对方拒绝视频连接！"));
    }


    public function templateswitch()
    {
        $login = $_SESSION['Msg'];
        if($login['level'] == 'service'){
            return [
                'code' => 1,
                'data' => '没有权限',
            ];
        }
        $post = $this->request->post();
        $type = $post['type'];
        if ($type == 'open') {
            $res = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['template_state' => 'open']);

        } else {
            $res = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['template_state' => 'close']);
        }

        if ($res) {
            $arr = [
                "code" => 0,
                "data" => "修改成功"
            ];
            return $arr;
        } else {
            $arr = [
                "code" => 0,
                "data" => "修改失败"
            ];
            return $arr;
        }
    }

    /**
     * 更改 video
     * @return array
     */
    private function videoswitch()
    {
        $login = $_SESSION['Msg'];
        if($login['level'] == 'service'){
            return [
                'code' => 1,
                'data' => '没有权限',
            ];
        }
        $post = $this->request->post();
        $type = $post['type'];
        if ($type == 'open') {
            $res = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['video_state' => 'open']);

        } else {
            $res = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['video_state' => 'close']);

        }

        if ($res) {
            $arr = [
                "code" => 0,
                "data" => "修改成功"
            ];
            return $arr;
        } else {
            $arr = [
                "code" => 0,
                "data" => "修改失败"
            ];
            return $arr;
        }
    }

    /**
     * 更改 audio
     * @return array
     */
    private function audioswitch()
    {
        $login = $_SESSION['Msg'];
        if($login['level'] == 'service'){
            return [
                'code' => 1,
                'data' => '没有权限',
            ];
        }
        $post = $this->request->post();
        $type = $post['type'];
        if ($type == 'open') {
            $res = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['audio_state' => 'open']);

        } else {
            $res = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['audio_state' => 'close']);

        }

        if ($res) {
            $arr = [
                "code" => 0,
                "data" => "修改成功"
            ];
            return $arr;
        } else {
            $arr = [
                "code" => 0,
                "data" => "修改失败"
            ];
            return $arr;
        }
    }

    /**
     * 更改 voice
     * [voiceswitch description]
     * @return [type] [description]
     */
    public function voiceswitch()
    {
        $login = $_SESSION['Msg'];
        if($login['level'] == 'service'){
            return [
                'code' => 1,
                'data' => '没有权限',
            ];
        }
        $post = $this->request->post();
        $type = $post['type'];
        $res = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['voice_state' => $type]);

        if ($res) {
            $arr = [
                "code" => 0,
                "data" => "修改成功"
            ];
            return $arr;
        } else {
            $arr = [
                "code" => 0,
                "data" => "修改失败"
            ];
            return $arr;
        }
    }

    /**
     * 更改 分配模式
     * [getswitchmodel description]
     * @return [type] [description]
     */
    public function getswitchmodel()
    {
        $login = $_SESSION['Msg'];
        if($login['level'] == 'service'){
            return [
                'code' => 1,
                'data' => '没有权限',
            ];
        }
        $post = $this->request->post();
        $type = $post['type'];
        $res = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['distribution_rule' => $type]);
        if ($res) {
            $arr = [
                "code" => 0,
                "data" => "修改成功"
            ];
            return $arr;
        } else {
            $arr = [
                "code" => 0,
                "data" => "修改失败"
            ];
            return $arr;
        }

    }

    /**
     * 添加或修改tab面版内容
     * [gettab description]
     * @return [type] [description]
     */
    public function gettab()
    {

        $post = $this->request->post();
        $post['business_id'] = $_SESSION['Msg']['business_id'];
        $post['content'] =$this->request->post('content','','\app\Common::clearXSS');
        $post['content_read'] = $this->request->post('content_read','','\app\Common::clearXSS');

        if (isset($post['tid'])) {

            $res = Admins::table('wolive_tablist')->where('tid', $post['tid'])->update(['title' => $post['title'], 'content' => $post['content'], 'content_read' => $post['content_read']]);
            $flag = '编辑';
        } else {
            $num = Admins::table('wolive_tablist')->where('business_id',$post['business_id'])->count();
            if ($num>10) {
                return ['code'=>1,'msg'=>'tab已达到上限'];
            }
            $res = Admins::table('wolive_tablist')->insert($post);
            $flag = '添加';
        }

        $arr = ['code' => 0, 'msg' => $flag.'成功'];
        return $arr;
    }
    //修改权重
    public function changeweigh()
    {
        $post = $this->request->post();
        $result=Admins::table('wolive_sentence')->where('sid', $post['sid'])->update(['weigh'=>$post['weigh']]);
        if ($result) {

            $data =['code'=>0,'msg'=>'修改成功'];
            return $data;
        }else{
            $arr = ['code' => 1, 'msg' => '修改失败'];
            return $arr;
        }
    }

    public function setcustom()
    {
        $post = $this->request->post();
        $post['business_id'] = $_SESSION['Msg']['business_id'];
        $post['content'] = $this->request->post('content','','\app\Common::clearXSS');
        $post['content_src'] = $this->request->post('content_src','','\app\Common::clearXSS');
        if (isset($post['sid']) && $post['sid']>0) {
            $res = Admins::table('wolive_sentence')->where('sid', $post['sid'])->update(['sid' => $post['sid'],'type'=>$post['type'],'content_type'=>$post['type'],'content_src' => $post['content_src'], 'content' => $post['content']]);
            $arr = ['code' => 0, 'msg' => '编辑成功'];
            return $arr;
        } else {
//            content	text	内容
            $re=Admins::table('wolive_sentence')->where('service_id',$_SESSION['Msg']['service_id'])->order('weigh desc')->find();
//service_id
            $result=Admins::table('wolive_sentence')->insert(['content'=>$post['content'],'weigh'=>$re['weigh']+1,'service_id'=>$_SESSION['Msg']['service_id'],'type'=>$post['type'],'content_type'=>$post['type'],"content_src"=>$post['content_src'],'state'=>'using']
            );
            if ($result) {

                $data =['code'=>0,'msg'=>'添加成功'];
                return $data;
            }else{
                $arr = ['code' => 1, 'msg' => '添加失败'];
                return $arr;
            }

        }
    }

    /**
     * 删除tab面版
     * [getdeleteTab description]
     * @return [type] [description]
     */
    public function getdeleteTab()
    {

        $post = $this->request->post();
        $id = $post['tid'];
        $result = Admins::table('wolive_tablist')->where('tid', $id)->delete();

        if ($result) {
            $arr = ['code' => 0, 'msg' => '删除成功'];
            return $arr;
        }
    }

    /**
     *
     * [getshowTab description]
     * @return [type] [description]
     */
    public function getshowTab()
    {
        $post = $this->request->post();
        $id = $post['tid'];
        $res = Admins::table('wolive_tablist')->where('tid', $id)->find();

        if ($res) {
            $arr = ['code' => 0, 'msg' => 'success', 'data' => $res];
            return $arr;
        }

    }

    /**
     * 删除常见问题
     * [getdeleteQuestion description]
     * @return [type] [description]
     */
    public function getdeleteQuestion()
    {
        $post = $this->request->post();
        $id = $post['qid'];

        $result = Admins::table("wolive_question")->where('qid', $id)->delete();

        if ($result) {
            $arr = ['code' => 0, 'msg' => '删除成功'];
            return $arr;
        }
    }

    /**
     *
     * [uploadVoice description]
     * @return [type] [description]
     */
    public function uploadVoice()
    {

        $file = $this->request->file('file');

        if ($file) {
            $newpath = ROOT_PATH . "/public/assets/upload/voices/";
            $info = $file->move($newpath, time() . ".wav");

            if ($info) {
                $imgname = $info->getFilename();

                $imgpath = $this->base_root . "/assets/upload/voices/" . $imgname;
                $arr = [
                    'data' => [
                        'src' => $imgpath
                    ]
                ];
                return json_encode($arr);
            } else {
                return false;
            }
        }

    }

    public function myupload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('upload');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $savePath= 'uploads/'.date('Ymd',time());
            $info = $file->move(ROOT_PATH .'public' . DS .$savePath);
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                //echo $info->getExtension();
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                //echo $info->getSaveName();
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                //echo $info->getFilename();
                $returnPath=$info->getSaveName();
                $returnPath='/'.$savePath.'/'.$returnPath;

                return [
                    "code" => 0,
                    "msg" => "",
                    "data" => $returnPath,
                    "info"=>pathinfo($info->getFilename()),
                ];
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
        else{
            return [
                    "code" => 1,
                    "msg" => $file,
                    
                    
                ];
        }
    }

    /**
     * 图片上传.
     *
     * @return [type] [description]
     */
    public function upload()
    {
        //halt($_FILES);
        try {
            Storage::$variable = 'upload';
            $url = Storage::put();
            $data = [
                "code" => 0,
                "msg" => "",
                "data" => $url['url']
            ];
        } catch (StorageException $exception) {
            $data = ['code'=> -1,'msg'=>$exception->getMessage(),'data'=>''];
        } catch (\Exception $e) {
            $data = ['code'=> -1,'msg'=>'请检查存储介质配置信息','data'=>$e];
        }
        return $data;
    }


    /**
     * 文件上传.
     *
     * @return [type] [description]
     */
    public function uploadfile()
    {
        try {
            Storage::$variable = 'folder';
            $url = Storage::put();
            $data = [
                "code" => 0,
                "msg" => "",
                "data" => $url['url']
            ];
        } catch (StorageException $exception) {
            $data = ['code'=> -1,'msg'=>$exception->getMessage(),'data'=>''];
        } catch (\Exception $e) {
            $data = ['code'=> -1,'msg'=>'请检查存储介质配置信息'];
        }
        return $data;
    }

    public function createHtml(){

        if(!$this->request->isPost()){
            exit('非法请求');
        }
        $get=$this->request->post();
        $xfbg='';
        if(isset($get['m_kfbtbg']) && trim($get['m_kfbtbg'])){
            $xfbg="style='background-color:{$get['m_kfbtbg']}'";
        }
        $theme='';
        if(isset($get['theme']) && trim($get['theme'])){
            $get['theme']=trim($get['theme'],'#');
            $theme="theme={$get['theme']}&";
        }
        $http_type = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

        $web = $http_type . $_SERVER['HTTP_HOST'];
        $action = $web.request()->root();
        $login = $_SESSION['Msg'];
        $class = Admins::table('wolive_group')->where('business_id', $login['business_id'])->select();
        $html = "";
        foreach ($class as $v) {
            $html .= "\r\n\t\t".' <form class="ymwl-item" action="' . $action . '/index/index/home?'.$theme.'visiter_id=&visiter_name=&avatar=&business_id=' . $login['business_id'] . '&groupid=' . $v['id'] . '" method="post" target="_blank" >';
            $html .= ' <input type="hidden" name="product" value="">';
            $html .= ' <input type="submit" value="' . $v['groupname'] . '">'."\r\n\t\t".'</form>';
        }

//D:\web\kfsystem\public\assets\css\index\ymwl_online.css
        $str = '<link rel="stylesheet" href="' . $action . '/assets/css/index/ymwl_online.css">'."\r\n";
        $str .= "\t".'<div class="ymwl-form"  id="ymwl-kefu" '.$xfbg.'>'."\r\n\t\t".'<i class="ymwl-icon"></i> ';
        $str .= "\r\n\t\t".'<form class="ymwl-item" action="' . $action . '/index/index/home?'.$theme.'visiter_id=&visiter_name=&avatar=&business_id=' . $login['business_id'] . '&groupid=0" method="post" target="_blank" >';
        $str .= '<input type="hidden" name="product"  value="">';
        $str .= '<input type="submit" value="在线咨询">'."\r\n\t\t".'</form>' . $html."\r\n";
        $str .= '</div>';
        $data = ['code' => 0, 'msg' => '生成html成功!', 'data' => $str];
        return $data;
    }
//动态创建微信客户访问端url
    public function createWechatUrl(){

        if(!$this->request->isPost()){
            exit('非法请求');
        }
        $get=$this->request->post();
        $theme='7571f9';
        if(isset($get['theme']) && trim($get['theme'])){
            $get['theme']=trim($get['theme'],'#');
            $theme=$get['theme'];
        }
        $login = $_SESSION['Msg'];
        $groups = Db::table('wolive_group')
            ->where('business_id',$login['business_id'])
            ->select();
        $wechat = [];
        $speciallink= ['group_name'=>'个人专属','url'=> url('index/index/wechat',['theme'=>$theme,'business_id'=>$login['business_id'],'groupid'=>0,'special'=>$login['service_id']], true, true)];
        foreach ($groups as $v) {
            $temp['group_name'] = $v['groupname'];
            $temp['url'] = url('index/index/wechat',['theme'=>$theme,'business_id'=>$login['business_id'],'groupid'=>$v['id']] ,true, true);
            $wechat[] = $temp;
        }
        $wechat[] = ['group_name'=>'通用分组','url'=> url('index/index/wechat',['theme'=>$theme,'business_id'=>$login['business_id'],'groupid'=>0], true, true)];

        $this->assign('wechat', $wechat);
        $this->assign('speciallink', $speciallink);
        $this->assign('login', $login);
        $html=$this->fetch();
        $data = ['code' => 0, 'msg' => '生成微信公众号链接成功', 'data' => $html];
        return $data;
    }
    /**
     *
     * [createFrontjs description]
     * @return [type] [description]
     */
    public function createFrontjs()
    {


        $http_type = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

        $login = $_SESSION['Msg'];
        $get=$this->request->get();
        $xfbg='';
        if(isset($get['m_kfbtbg']) && trim($get['m_kfbtbg'])){
            $xfbg="div.style.backgroundColor='{$get['m_kfbtbg']}'";
        }
        $theme='';
        if(isset($get['theme']) && trim($get['theme'])){
            $get['theme']=trim($get['theme'],'#');
            $theme="theme={$get['theme']}&";
        }
        $business_id = $login['business_id'];
        $url = $http_type . $_SERVER['HTTP_HOST'];
        $js = @file_get_contents($url.$this->base_root . "/assets/front/ymwl_" . $login['business_id'] . ".js");


        $group = Admins::table('wolive_group')->where('business_id', $login['business_id'])->order('sort desc')->select();

        $html = '';
        foreach ($group as $v) {

            $html .= '<p class="ymwl-item" onclick="blzx.blank(' . $v['id'] . ')">' . $v['groupname'] . '</p>';
        }

        try {

            $file = file_put_contents(ROOT_PATH . "/public/assets/front/ymwl_" . $login['business_id'] . ".js", "
             
             /**
              *
              * wolive 标准窗口,js代码
              * 
              */
             
            var head = document.getElementsByTagName('head')[0];
            var link = document.createElement('link');
                link.type='text/css';
                link.rel = 'stylesheet';
                link.href ='{$url}{$this->base_root}/assets/css/index/ymwl_online.css';
                head.appendChild(link);

            var blzx ={
                visiter_id:(typeof ymwl=='undefined' || typeof ymwl.visiter_id == 'undefined')?'':ymwl.visiter_id,
                 visiter_name:(typeof ymwl=='undefined' || typeof ymwl.visiter_name == 'undefined')?'':ymwl.visiter_name,
                 avatar:(typeof ymwl=='undefined' || typeof ymwl.avatar == 'undefined')?'':ymwl.avatar,
                 product:(typeof ymwl=='undefined' || typeof ymwl.product == 'undefined')?'{}':ymwl.product,
                 open:function(){
                    var d =document.getElementById('wolive-box');
                    if(!d){
                      var div =document.createElement('div');
                      div.id ='ymwl-kefu';
                      div.className +='ymwl-form';
                      {$xfbg}
                      document.body.appendChild(div);
                      var w =document.getElementById('ymwl-kefu');
                      w.innerHTML=' <i class=\"ymwl-icon\"></i><p class=\"ymwl-item\" onclick=\"blzx.blank(0)\" >在线咨询</p>" . $html . "';
                    }

                 },
                 blank:function(groupid){
                  var web =encodeURI('{$url}{$this->base_root}/index/index/home?{$theme}visiter_id='+blzx.visiter_id+'&visiter_name='+blzx.visiter_name+'&avatar='+blzx.avatar+'&business_id={$business_id}&groupid='+groupid+'&product='+blzx.product);
                      
                  var moblieweb = encodeURI('{$url}{$this->base_root}/mobile/index/home?{$theme}visiter_id='+blzx.visiter_id+'&visiter_name='+blzx.visiter_name+'&avatar='+blzx.avatar+'&business_id={$business_id}&groupid='+groupid+'&product='+blzx.product);

                   if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))) {
                     window.open(moblieweb); 
                   }else{
                     window.open(web); 
                   }
                 },
            }

            window.onload = blzx.open();
        ");

            if ($file) {
                if ($js) {
                    $path = $url.$this->base_root . "/assets/front/ymwl_" . $login['business_id'] . ".js?v=" . time();
                    $data = ['code' => 0, 'msg' => '新生成js成功!', 'data' => $path];
                    return $data;
                } else {
                    $path = $url.$this->base_root . "/assets/front/ymwl_" . $login['business_id'] . ".js";
                    $data = ['code' => 0, 'msg' => '生成js成功!', 'data' => $path];
                    return $data;
                }
            }
        } catch (\Exception $e) {

            $error = $e->getMessage();
            $data = ['code' => 1, 'msg' => $error, 'data' => ''];
            return $data;
        }
    }

    /**
     *
     * [createMinjs description]
     * @return [type] [description]
     */
    public function createMinjs()
    {

        if(!$this->request->isPost()){
            exit('非法请求');
        }
        $get=$this->request->post();
        $xfbg='';
        if(isset($get['m_kfbtbg']) && trim($get['m_kfbtbg'])){
            $xfbg="div.style.backgroundColor='{$get['m_kfbtbg']}'";
        }
        $theme='';
        if(isset($get['theme']) && trim($get['theme'])){
            $get['theme']=trim($get['theme'],'#');
            $theme="theme={$get['theme']}&";
        }
        if($get['m_height']){
            $mheight="div.style.height='".$get['m_height']."px';";
        }else{
            $mheight='';
        }
        $http_type = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $login = $_SESSION['Msg'];
        $business_id = $login['business_id'];
        $url = $http_type . $_SERVER['HTTP_HOST'];

        $js = @file_get_contents($url.$this->base_root . "/assets/layer/ymwl_mini" . $login['business_id'] . ".js");

        $group = Admins::table('wolive_group')->where('business_id', $login['business_id'])->select();

        $html = '';
        foreach ($group as $v) {
            $html .= '<p class="ymwl-item" onclick="blzx.connenct(' . $v['id'] . ')">' . $v['groupname'] . '</p>';
        }

        try {

            $file = file_put_contents(ROOT_PATH . "/public/assets/layer/ymwl_mini" . $login['business_id'] . ".js", <<<"EOT"
            /**
             *
             * 浮层版 客服咨询js
             * @return {[type]} [description]
             */
                var head = document.getElementsByTagName('head')[0];
                var link = document.createElement('link');
                    link.type='text/css';
                    link.rel = 'stylesheet';
                    link.href ='{$url}{$this->base_root}/assets/css/index/ymwl_online.css';
                    head.appendChild(link);
                var script = document.createElement('script');
                    script.type='text/javascript';
                    script.src ='{$url}{$this->base_root}/assets/js/js.cookie.min.js';
                    head.appendChild(script); 
                     var blzx={};
window.addEventListener('load', function(){
                blzx ={
                    visiter_id:(typeof ymwl=='undefined' || typeof ymwl.visiter_id == 'undefined')?'':ymwl.visiter_id,
                     visiter_name:(typeof ymwl=='undefined' || typeof ymwl.visiter_name == 'undefined')?'':ymwl.visiter_name,
                     avatar:(typeof ymwl=='undefined' || typeof ymwl.avatar == 'undefined')?'':ymwl.avatar,
                     product:(typeof ymwl=='undefined' || typeof ymwl.product == 'undefined')?'{}':ymwl.product,
                     open:function(){
                        var d =document.getElementById('wolive-box');
                        if(!d){
                            var div =document.createElement('div');
                            div.id ="ymwl-kefu";
                            div.className +='ymwl-form';
                            {$xfbg}
                            document.body.appendChild(div);
                            var w =document.getElementById('ymwl-kefu');
                            w.innerHTML='<i class="ymwl-icon"></i><p class="ymwl-item zidong" onclick="blzx.connenct(0)">在线咨询</p>{$html}';
                        }
                     },
                     connenct:function(groupid){
                      var id =groupid;
                      var web =encodeURI('{$url}{$this->base_root}/layer?{$theme}visiter_id='+blzx.visiter_id+'&visiter_name='+blzx.visiter_name+'&avatar='+blzx.avatar+'&business_id={$business_id}&groupid='+groupid+'&product='+blzx.product);
                      var moblieweb = encodeURI('{$url}{$this->base_root}/mobile/index/home?{$theme}visiter_id='+blzx.visiter_id+'&visiter_name='+blzx.visiter_name+'&avatar='+blzx.avatar+'&business_id={$business_id}&groupid='+groupid+'&product='+blzx.product);
                       var s =document.getElementById('wolive-talk');
                       if(!s){

                            var div = document.createElement('div');
                            div.id ="wolive-talk";
                            div.name=id;
                            if(blzx.isMobile()){
                               div.style.width='100%';
                               div.style.bottom=0;
                               div.style.right=0;
                               div.style.left=0;
                               {$mheight}
                           }
                            {$xfbg}
                            document.body.appendChild(div);
                            div.innerHTML='<i class="ymwl-close" onclick="blzx.narrow()"></i><iframe id="wolive-iframe" src="'+web+'"></iframe>';
                        }else{
                            var title =s.name;
                            if(title == groupid){
                                s.style.display ='block';
                            }else{
                                s.parentNode.removeChild(s);
                                blzx.connenct(groupid); 
                            }
                        }
                     },
                     narrow:function(){
                        document.getElementById('wolive-talk').style.display="none";
                     },isMobile:function(){
                        if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))) {
                            return true;
                        }else{
                            return false;
                        }
                    }
                }
                blzx.open();
});
EOT
);

            if ($file) {
                if ($js) {
                    $path = $url.$this->base_root . "/assets/layer/ymwl_mini" . $login['business_id'] . ".js?v=" . time();
                    $data = ['code' => 0, 'msg' => '新生成js成功!', 'data' => $path];
                    return $data;
                } else {
                    $path = $url.$this->base_root . "/assets/layer/ymwl_mini" . $login['business_id'] . ".js";
                    $data = ['code' => 0, 'msg' => '生成js成功!', 'data' => $path];
                    return $data;
                }
            }

        } catch (\Exception $e) {
            $error = $e->getMessage();
            $data = ['code' => 1, 'msg' => $error, 'data' => ''];
            return $data;
        }

    }

    /**
     *
     * [自定义弹窗脚本 description]
     * @return [type] [description]
     */
    public function createMinDiyjs()
    {

        if(!$this->request->isPost()){
            exit('非法请求');
        }
        $http_type = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $get=$this->request->post();
        if(isset($get['isopen']) && $get['isopen']=='on'){
            $get['open_delay']=floatval($get['open_delay']);
            $isopen='setTimeout(function () {
                    blzx.connenct(0);
                },'.($get['open_delay']*1000).');';
        }else{
            $isopen='';
        }
        if($get['m_height']){
            $mheight="div.style.height='".$get['m_height']."px';";
        }else{
            $mheight='';
        }
        $xfbg='#eeeeee';
        if(isset($get['m_kfbtbg']) && trim($get['m_kfbtbg'])){
            $xfbg=$get['m_kfbtbg'];
        }
        $theme='';
        if(isset($get['theme']) && trim($get['theme'])){
            $get['theme']=trim($get['theme'],'#');
            $theme="theme={$get['theme']}&";
        }

        $login = $_SESSION['Msg'];
        $business_id = $login['business_id'];
        $url = $http_type . $_SERVER['HTTP_HOST'];

        $js = @file_get_contents($url.$this->base_root . "/assets/layer/ymwl_diy_" . $login['business_id'] . ".js");

        try {

            $file = file_put_contents(ROOT_PATH . "/public/assets/layer/ymwl_diy_" . $login['business_id'] . ".js", "
            /**
             *
             * 自定义版 客服咨询js
             * @return {[type]} [description]
             */
                var head = document.getElementsByTagName('head')[0];
                var link = document.createElement('link');
                    link.type='text/css';
                    link.rel = 'stylesheet';
                    link.href ='{$url}{$this->base_root}/assets/style1/css/chatStyle.css';
                    head.appendChild(link);

                var blzx ={
                visiter_id:(typeof ymwl=='undefined' || typeof ymwl.visiter_id == 'undefined')?'':ymwl.visiter_id,
                     visiter_name:(typeof ymwl=='undefined' || typeof ymwl.visiter_name == 'undefined')?'':ymwl.visiter_name,
                     avatar:(typeof ymwl=='undefined' || typeof ymwl.avatar == 'undefined')?'':ymwl.avatar,
                     product:(typeof ymwl=='undefined' || typeof ymwl.product == 'undefined')?'{}':ymwl.product,
                     open:function(){
                        var d =document.getElementById('blzxMinChatWindowDiv');
                        if(!d){
                            var div =document.createElement('div');
                            div.id =\"blzxMinChatWindowDiv\";
                            document.body.appendChild(div);
                            var w =document.getElementById('blzxMinChatWindowDiv');
                            w.innerHTML='<div id=\"minblzxmsgtitlecontainer\"><img id=\"minblzxWinlogo\" src=\"{$url}{$this->base_root}/assets/style1/img/wechatLogo.png\"><div id=\"minblzxmsgtitlecontainerlabel\" onclick=\"blzx.connenct(0)\">在线咨询</div><img id=\"minblzxmsgtitlecontainerclosebutton\" onclick=\"blzx.closeMinChatWindow(\'blzxMinChatWindowDiv\');\" src=\"{$url}{$this->base_root}/assets/style1/img/closewin.png\"><img id=\"minblzxNewBigWin\" onclick=\"blzx.connenct(0)\" src=\"{$url}{$this->base_root}/assets/style1/img/up_arrow.png\"></div>';
                            document.getElementById('minblzxmsgtitlecontainer').style.backgroundColor='{$xfbg}';
                        }
                     },
                     connenct:function(groupid){
                     document.getElementById('blzxMinChatWindowDiv').style.display=\"none\";
                      var id =groupid;
                      var web =encodeURI('{$url}{$this->base_root}/layer?{$theme}visiter_id='+blzx.visiter_id+'&visiter_name='+blzx.visiter_name+'&avatar='+blzx.avatar+'&business_id={$business_id}&groupid='+groupid+'&product='+blzx.product);
                      
                      var moblieweb = encodeURI('{$url}{$this->base_root}/mobile/index/home?{$theme}visiter_id='+blzx.visiter_id+'&visiter_name='+blzx.visiter_name+'&avatar='+blzx.avatar+'&business_id={$business_id}&groupid='+groupid+'&product='+blzx.product);
                       var s =document.getElementById('wolive-talk');
                        
                       if(!s){

                            var div = document.createElement('div');
                            div.id =\"wolive-talk\";
                            div.name=id;
                            if(blzx.isMobile()){
                               div.style.width='100%';
                               {$mheight}
                           }
                            document.body.appendChild(div);
                            div.innerHTML='<i class=\"blzx-close\" onclick=\"blzx.closeMinChatWindow(\'wolive-talk\')\"></i><iframe id=\"wolive-iframe\" src=\"'+web+'\"></iframe>'
                          
                        }else{
                           
                            var title =s.name;
                            if(title == groupid){
                                s.style.display ='block';
                            }else{
                                s.parentNode.removeChild(s);
                                blzx.connenct(groupid); 
                            }
                        }
                      
                     },closeMinChatWindow:function(id){
                        document.getElementById(id).style.display=\"none\";
                        if(id==='wolive-talk'){
                            document.getElementById('blzxMinChatWindowDiv').style.display=\"block\";
                        }
                    },isMobile:function(){
                        if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))) {
                            return true;
                        }else{
                            return false;
                        }
                    }
                }

                window.onload =blzx.open();
                {$isopen}

        ");

            if ($file) {
                if ($js) {
                    $path = $url.$this->base_root . "/assets/layer/ymwl_diy_" . $login['business_id'] . ".js?v=" . time();
                    $data = ['code' => 0, 'msg' => '新生成js成功!', 'data' => $path];
                    return $data;
                } else {
                    $path = $url.$this->base_root . "/assets/layer/ymwl_diy_" . $login['business_id'] . ".js";
                    $data = ['code' => 0, 'msg' => '生成js成功!', 'data' => $path];
                    return $data;
                }
            }

        } catch (\Exception $e) {
            $error = $e->getMessage();
            $data = ['code' => 1, 'msg' => $error, 'data' => ''];
            return $data;
        }

    }

    protected function formatTime($time)
    {
        $rtime = date("m-d H:i", $time);
        $htime = date("H:i", $time);
        $time = time() - $time;
        if ($time < 60) {
            $str = '刚刚';
        } elseif ($time < 60 * 60) {
            $min = floor($time / 60);
            $str = $min . '分钟前';
        } elseif ($time < 60 * 60 * 24) {
            $h = floor($time / (60 * 60));
            $str = $h . '小时前 ';
        } elseif ($time < 60 * 60 * 24 * 3) {
            $d = floor($time / (60 * 60 * 24));
            if ($d == 1) {
                $str = '昨天 ' . $htime;
            } else {
                $str = '前天 ' . $htime;
            }
        } else {
            $str = $rtime;
        }
        return $str;
    }

    public function callback()
    {
//        $http_type = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
//        $web = $http_type . $_SERVER['HTTP_HOST'];
//        $action = $web.request()->root();
        $url = $_SESSION['Msg']['business']['push_url'];
        if(trim($url)==''){
            exit('推送url为空，退出');
        }
        $data = $this->request->param();
        $message = $data['message'];
        $group = Db::name('wolive_queue')->where('visiter_id',$message['visiter_id'])->where('business_id',$message['business_id'])->value('groupid');
        $visiter = Db::name('wolive_visiter')->where('visiter_id',$message['visiter_id'])->where('business_id',$message['business_id'])->find();
        $message['group'] = $group;
        $message['state'] = $visiter['state'];
        $message['service_name'] = $_SESSION['Msg']['nick_name'];
//        $query = http_build_query();
//        $returnurl = "{$action}/index/index/home?$query";
        $returnurl=url('/index/index/home',[
            "visiter_id" => $message['visiter_id'],
            "visiter_name" => $visiter['visiter_name'],
            "business_id" => $message['business_id'],
            "avatar" => '',
            "groupid" => $group
        ],true,true);
        $message['service_url'] = urlencode($returnurl);

        $key = "callback_".$_SESSION['Msg']['business_id']."_".$_SESSION['Msg']['service_id'];
        if (isset($_SESSION[$key])) {
            $curl = new CurlUtils($url);
            $res = $curl->post($message);
            if ($res !== false) {
                echo 'success';
            } else {
                echo 'error';
            }
            unset($_SESSION[$key]);
        } else {
            echo 'request lock~';
        }
    }

    public function setpushurl()
    {
        $pushurl = $this->request->param('push_url','',null);
        $pattern="#(http|https)://(.*\.)?.*\..*#i";
        $login = $_SESSION['Msg'];
        if(preg_match($pattern,$pushurl)){
            if($login['level'] == 'service'){
                return [
                    'code' => 1,
                    'data' => '没有权限',
                ];
            }
            $res = Admins::table('wolive_business')->where('id', $login['business_id'])->update(['push_url' => $pushurl]);
            if ($res !== false) {
                return json(['code'=>0,'msg'=>'保存成功']);
            } else {
                return json(['code'=>1,'msg'=>'保存失败']);
            }
        } elseif(empty($pushurl)){
            Admins::table('wolive_business')->where('id', $login['business_id'])->update(['push_url' => $pushurl]);
            return json(['code'=>0,'msg'=>'修改成功']);
        } else{
            return json(['code'=>1,'msg'=>'url格式不正确']);
        }
    }

    public function testtpl(){
        Log::info("===============Test Tpl Message Begin===============");
        $login = $_SESSION['Msg'];
        Log::info($login);
        $open_id = $this->request->post('open_id');
        Log::info('open_id:'.$open_id);
        $service_id = $this->request->post('service_id');
        Log::info('service_id:'.$service_id);
        $type = $this->request->post('type');
        Log::info('type:'.$type);
        $wechat = WechatPlatform::get(['business_id'=>$login['business_id']]);
        Log::info($wechat);
        if ($login['business']['template_state'] == 'close') {
            Log::error('Tpl is closed');
            Log::info("===============Test Tpl Message End===============");
            return json(['code'=>1,'msg'=>'请先打开模板消息开关(设置-通用设置)']);
        }
        try {
            if ($type == 'visitor_tpl') {
                TplService::send($login['business_id'],$open_id,url('weixin/login/callback',['business_id'=>$login['business_id'],'service_id'=>$service_id],true,true),$wechat['visitor_tpl'],[
                    "first"  => "您有新访客！",
                    "keyword1"   => "测试发送新访客提醒模板消息",
                    "keyword2"  => date('Y-m-d H:i:s',time()),
                    "remark" => $login['business']['business_name']."提示:有新客户啦,快去撩一把~",
                ]);
            } else {
                TplService::send($login['business_id'],$open_id,url('weixin/login/callback',['business_id'=>$login['business_id'],'service_id'=>$service_id],true,true),$wechat['msg_tpl'],[
                    "first"  => "你有一条新的信息!",
                    "keyword1"   => "测试",
                    "keyword2"  => "测试发送新消息提醒模板消息",
                    "keyword3"  => "测试",
                    "remark" => $login['business']['business_name']."提示:客户等不及啦,快去回复吧~",
                ]);
            }
        }catch (\Exception $exception) {
            Log::error($exception->getMessage());
            Log::info("===============Test Tpl Message End===============");
            return json(['code'=>1,'msg'=>$exception->getMessage()]);
        }
        Log::info("===============Test Tpl Message End===============");
        return json(['code'=>0,'msg'=>'success']);
    }

    /**
     * 推送客服评价
     */
    public function pushComment()
    {
        $login = $_SESSION['Msg'];
        $arr = $this->request->post();

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

        $visiter = Visiter::get(['business_id'=>$login['business_id'],'visiter_id'=>$arr['visiter_id']]);

        if ($visiter['state'] == 'offline') {
            return json([
                'code' => 1,
                'msg' => '访客已离线,无法推送评价'
            ]);
        }

        $queue = Queue::get(['business_id'=>$login['business_id'],'visiter_id'=>$arr['visiter_id']]);
        if (!$queue['remind_comment']) {
            $data = CommentSetting::get(['business_id'=>$login['business_id']]);
            if (!empty($data)) {
                $data['comments'] = json_decode($data['comments'],true);
            } else {
                return json([
                    'code' => 1,
                    'msg' => '客服评价尚未设置'
                ]);
            }
            $queue->save(['remind_comment'=>1]);
            $arr['business_id'] = $login['business_id'];
            $channel = bin2hex($arr['visiter_id'] . '/' . $arr['business_id']);
            $pusher->trigger("cu" . $channel, 'push-comment', array('message' => $data));
            return json([
                'code' => 0,
                'msg' => '已推送'
            ]);
        } else {
            return json([
                'code' => 1,
                'msg' => '您已经推送过评价了'
            ]);
        }
    }

    //后台客服撤销内容
    public function revokemsg(){
        $id = $this->request->post('id','','trim');
        $type = $this->request->post('type','','trim');

        if($id=='' || $type==''){
           return ['code' =>1, 'msg' => '参数缺失！'];
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
        if($type==1){
            $arr=Admins::table('wolive_chats')->where('cid',$id)->find();
        }else{
            $arr=Admins::table('wolive_chats')->where('unstr',$id)->find();
        }
        if(!$arr){
            return ['code' =>1, 'msg' => '您所需撤销的信息不存在！'];
        }
if($arr['service_id']!=$_SESSION['Msg']['service_id']){
    return ['code' =>1, 'msg' => '只能撤销自己发布的信息！'];
}
        $channel = bin2hex($arr['visiter_id'] . '/' . $arr['business_id']);
        $pusher->trigger("cu" . $channel, 'my-chexiao', array('message' => $arr));


        $res=Admins::table('wolive_chats')->where('cid',$arr['cid'])->delete();
        if($res){
            $data = ['code' =>0, 'id' =>$arr['cid'], 'msg' => '撤销成功！'];
            return $data;
        }
    }
}