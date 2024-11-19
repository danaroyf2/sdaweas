<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-06 06:24:50
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-12-13 11:19:25
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/api/controller/Chat.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\api\controller;
use app\admin\model\Admins;
use app\admin\model\Visiter;
use app\admin\model\Chats;
use app\extra\push\Pusher;
use think\Db;
use app\admin\model\WechatPlatform;
use app\admin\iplocation\Ip;
use app\admin\model\Vgroup;
use app\admin\model\Queue;

class Chat extends CRUD{


    

    /**
     * 添加用户到黑名单
     */
    public function addblacklist(){
        $post = $this->request->post();
        $result = Admins::table('wolive_queue')->where('visiter_id', $post['visiter_id'])->where('business_id', $this->serverUser['business_id'])->update(['state' => 'in_black_list']);
        if(!empty($result)){
            $this->success('添加黑名单成功!');
        }else{
            $this->error('添加黑名单失败!');
        }
    }

    public function moreblack(){
        $vids = $this->request->post('vid/a', []);
        if (empty($vids)) {
            $this->error('参数不正确!');
        }
        $result = Db::table('wolive_queue')->where('visiter_id', 'in',$vids)->where('business_id', $this->login['business_id'])->update(['state' => 'in_black_list']);
        if(!empty($result)){
            $this->success('添加黑名单成功!');
        }else{
            $this->error('添加黑名单失败!');
        }
    }

    

     /**
     * 移出黑名单
     */
    public function removeblacklist(){
        $post = $this->request->post();
        $result = Admins::table('wolive_queue')->where('visiter_id', $post['visiter_id'])->where('business_id', $this->serverUser['business_id'])->update(['state' => 'normal']);
        if(!empty($result)){
            $this->success('移出黑名单成功!');
        }else{
            $this->error('移出黑名单失败!');
        }
    }

    
    /**
     * 获取黑名单列表
     */
    public function getblacklist(){
        $lis=Admins::table('wolive_queue')
        ->field('wolive_queue.*,wolive_visiter.visiter_name')
        ->join('wolive_visiter','wolive_visiter.visiter_id=wolive_queue.visiter_id','LEFT')
        ->where(['wolive_queue.business_id'=>$this->serverUser['business_id'],'wolive_visiter.business_id'=>$this->serverUser['business_id'],'wolive_queue.state' => 'in_black_list'])
        ->select();
        //halt(Admins::getLastSql());
          
        $this->success('获取成功!',$lis);
    }

    /**
     * 编辑分组
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function editGroup()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $post['business_id'] = $this->login['business_id'];
            $post['service_id'] = $this->login['service_id'];
            if (empty($post['group_name'])) {
                return json(['code'=>1,'msg'=>'分组名不能为空']);
            }
            if(mb_strlen($post['group_name'],'UTF8') > 20){
                $data = ['code'=>1,'msg'=>'分组名不能多于12个字符！'];
                return json($data);
            }
            if (isset($post['id'])) {
                $group = Vgroup::get($post['id']);
                if (empty($group)) {
                    return json(['code'=>1,'msg'=>'该分组不存在']);
                }
                $where=$post;
                $where['id']=['<>',$post['id']];
                unset($where['bgcolor']);
                $res = Vgroup::get($where);

                if ($res['group_name'] == $post['group_name']) {
                    return json(['code'=>1,'msg'=>'该组名称已存在']);
                }
                $data = $group->save($post);
                return json(['code'=>0,'msg'=>'编辑成功','data'=>$data]);
            } else {
                $group = Vgroup::get($post);
                if ($group) {
                    return json(['code'=>1,'msg'=>'该组名称已存在']);
                }
                $data = Vgroup::create($post);
                $sdata = ['code'=>0,'msg'=>'添加成功','data'=>$data->getData()];
                return json($sdata);
            }
        }
    }

     /**
     * 删除分组
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function delGroup()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $post['business_id'] = $this->login['business_id'];
            $post['service_id'] = $this->login['service_id'];
            $group = Vgroup::get($post['id']);
            if (empty($group)) {
                return json(['code'=>1,'msg'=>'该分组不存在']);
            }
            $res = $group->where('id',$post['id'])->delete();
            $post['group_id'] = $post['id'];
            unset($post['id']);
            VisiterGroup::where('group_id',$post['group_id'])->delete();
            if ($res !== false) {
                return json(['code'=>0,'msg'=>'删除成功']);
            } else {
                return json(['code'=>1,'msg'=>'删除失败']);
            }
        }
    }

    /**
     * 查找分组下的客户
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function visiter()
    {
        $group = $this->request->get('group_id','0');
        if (empty($group)) {
            $vids = Queue::where('q.business_id',$this->login['business_id'])
                ->alias('q')
                ->where('q.service_id',$this->login['service_id'])
                ->where('q.state','neq','in_black_list')
                ->field('v.vid')
                ->where('v.business_id',$this->login['business_id'])
                ->join('wolive_visiter v','v.visiter_id = q.visiter_id','left')
                ->order('v.timestamp','desc')
                ->paginate(20);
        } elseif ($group == -1) {
            $visiter = Queue::alias('q')
                ->field('v.*')
                ->join('wolive_visiter v','q.visiter_id = v.visiter_id','left')
                ->where('q.service_id',$this->login['service_id'])
                ->where('v.business_id',$this->login['business_id'])
                ->where('q.state','in_black_list')
                ->order('v.timestamp','desc')
                ->paginate(20);
            $page = $visiter->toArray();
            $data=$page['data'];
            unset($page['data']);
            if (!empty($data)) {
                foreach ($data as &$v) {
                    $url = url('mobile/admin/talk',null,true,true);
                    $v['mobile_route_url'] = $url."?channel=".$v['channel']."&avatar=".urlencode($v['avatar'])."&visiter_id=".$v['visiter_id'];
                    $v['group_name_array'] = ['黑名单'];
                }
            } else {
                $data = [];
            }
            unset($v);

            return json(['code'=>0,'msg'=>'success','data'=>$data,'page'=>$page]);
        } elseif ($group == -2) {
            $vids = Queue::where('q.business_id',$this->login['business_id'])
                ->alias('q')
                ->where('q.service_id',$this->login['service_id'])
                ->where('q.state','neq','in_black_list')
                ->field('v.vid')
                ->where('v.business_id',$this->login['business_id'])
                ->where('v.name|v.tel','neq','')
                ->join('wolive_visiter v','v.visiter_id = q.visiter_id')
                ->order('v.timestamp','desc')
                ->paginate(20);
        } else {
            $vids = VisiterGroup::alias('vg')->where('group_id',$group)
                ->join('wolive_visiter v','vg.vid = v.vid','left')
                ->join('wolive_queue q','q.visiter_id = v.visiter_id','left')
                ->where('vg.business_id',$this->login['business_id'])
                ->where('vg.service_id',$this->login['service_id'])
                ->where('q.state','neq','in_black_list')
                ->distinct(true)
                ->field('vg.vid')
                ->order('v.timestamp','desc')
                ->paginate(20);
        }
        $newdata = [];
        $page = $vids->toArray();
        unset($page['data']);
        $ids = $vids->getCollection()->toArray();
        if (empty($ids)) {
            $newdata = [];
        } else {
            $visiter = Visiter::alias('v')
                ->field('v.*,g.group_name')
                ->where('v.vid','in',array_column($ids,'vid'))
                ->join('wolive_visiter_vgroup vg',"v.vid = vg.vid and vg.service_id = {$this->login['service_id']}",'left')
                ->join('wolive_vgroup g',"g.id = vg.group_id and g.service_id = {$this->login['service_id']}",'left')
                ->order('v.timestamp','desc')
                ->select();
            foreach($visiter as $k=>$v) {
                if (!isset($newdata[$v['vid']])) {
                    $url = url('mobile/admin/talk',null,true,true);
                    $v['mobile_route_url'] = $url."?channel=".$v['channel']."&avatar=".urlencode($v['avatar'])."&visiter_id=".$v['visiter_id'];
                    $newdata[$v['vid']] = $v;
                } else {
                    $newdata[$v['vid']]['group_name'] .= ",".$v['group_name'];
                }
            }

            foreach ($newdata as &$item) {
                $item['group_name_array'] = explode(',',$item['group_name']);
            }
            unset($item);
        }

        return json(['code'=>0,'msg'=>'success','data'=>array_values($newdata),'page'=>$page]);
    }



     /**
     * 查找客服系统分组
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function group()
    {
        $group = Vgroup::where('status',1)
            ->where('business_id',$this->login['business_id'])
            ->where('service_id',$this->login['service_id'])
            ->paginate();
        foreach ($group as &$item) {
            $item['count'] = VisiterGroup::alias('vg')
                ->join('wolive_visiter v','v.vid = vg.vid','left')
                ->join('wolive_queue q','q.visiter_id = v.visiter_id','left')
                ->where('vg.business_id',$item['business_id'])
                ->where('vg.group_id',$item['id'])
                ->where('q.state','neq','in_black_list')
                ->count();
        }
        unset($item);

        $allcount = Queue::where('business_id',$this->login['business_id'])
            ->where('service_id',$this->login['service_id'])
            ->where('state','neq','in_black_list')
            ->count();

        $blackcount = Queue::where('service_id',$this->login['service_id'])
            ->where('business_id',$this->login['business_id'])
            ->where('state','in_black_list')
            ->count();

        $this->assign('allcount',$allcount);
        $this->assign('blackcount',$blackcount);

        return json(['code'=>0,'msg'=>'success','data'=>$group,'allcount'=>$allcount,'blackcount'=>$blackcount]);
    }
    
    /**
     * 标记已看信息.
     *
     * @return mixed
     */
    public function getwatch()
    {
        $login = $this->serverUser;
        $business_id = $login['business_id'];
        $post = $this->request->post();
        $result = Admins::table('wolive_chats')
        ->where('visiter_id', $post['visiter_id'])
        ->where('business_id', $business_id)
        ->where(['direction'=>'to_service'])
        ->update(['state' => 1]);
        $arr = ['code' => 0, 'msg' => 'success', 'data' => ''];
        return json($arr);
    }
    
    
    public function chatdata()
    {


        $login = $this->serverUser;
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

    

    public function chats()
    {
        $data = $this->request->post();
        $visiterids=explode(',',$data['visiter_id']);
        //halt($visiterids);
        foreach ($visiterids as $id){
            $data['visiter_id']=$id;
            $this->sendchat($id);
        }
        $this->success('发送成功!');
    }
     /**
     * 删除聊天记录。
     * [history description]
     * @return [type] [description]
     */
    public function truncates()
    {
        $post = $this->request->post();
        $login = $this->serverUser;
        $business_id =$login['business_id'];
        
        $talk_time =isset($post['talk_time'])?$post['talk_time']:0;
        $map = ['business_id' => $business_id];
        switch ($talk_time) {
            case 0:
                $map = ['business_id' => $business_id];
                $result = Admins::table('wolive_queue')->where('business_id', $login['business_id'])->update(['state' => 'complete']);
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
                $result = Admins::table('wolive_queue')->where('business_id', $login['business_id'])->update(['state' => 'complete']);
                break;
                
            case 6:
                $map['timestamp'] = ['<', strtotime("-3 day")];
                $result = Admins::table('wolive_queue')->where('business_id', $login['business_id'])->update(['state' => 'complete']);
                break;
        }
        $res=Admins::table('wolive_chats')->where($map)->delete();
         if($res){
              $arr = ['code' => 0, 'msg' => '删除成功'];
              return json($arr);
         } 
         else{
              $arr = ['code' => 0, 'msg' => '删除成功'];
              return json($arr);
         }


    }
    public function sendchat($visiter_id){
        $arr['content'] = $_POST['content'];
        $arr['visiter_id']=$visiter_id;
        //halt($arr);
        $sarr = parse_url(ahost);

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
        $arr['business_id'] = $this->serverUser['business_id'];
        $arr['service_id'] = $this->serverUser['service_id'];

        $arr['direction'] = 'to_visiter';
        $arr["timestamp"] = time();
        $channel = bin2hex($arr['visiter_id'] . '/' . $arr['business_id']);
        $visiter = Db::table('wolive_visiter')
            ->where('visiter_id',$arr['visiter_id'])
            ->where('business_id',$this->serverUser['business_id'])
            ->find();

        $queue = Db::table('wolive_queue')
            ->where('visiter_id',$arr['visiter_id'])
            ->where('business_id',$this->serverUser['business_id'])
            ->find();

        try {
            $wechat = WechatPlatform::get(['business_id'=>$arr['business_id']]);
            $sendres=[];
            if ($visiter['state'] == 'offline' && trim($wechat['customer_tpl'])!='' && strlen($visiter['visiter_id'])>16) {
                $sendres=TplService::send($arr["business_id"],$visiter['visiter_id'],url('index/index/wechat',['business_id'=>$arr['business_id'],'groupid'=>$queue['groupid']],true,true),$wechat['customer_tpl'],[
                    "first"  => "你有一条新的信息!",
                    "keyword1"   => $arr["content"],
                    "keyword2"  => $this->serverUser['nick_name'],
                    "remark" => $this->serverUser['business']['business_name']."提示:客服有新的消息,快去看看吧~",
                ]);
            }
            if(!$wechat){$wechat=[];}else{$wechat=$wechat->toArray();}
            
            hook('sendonesubhook',array_merge($wechat,['nick_name'=>$this->serverUser['nick_name'],'groupid'=>$queue['groupid'],'sendres'=>$sendres,'visiter'=>$visiter,'content'=>$arr["content"]]));
           
        } catch (\EasyWeChat\Core\Exceptions\HttpException $e) {
        } catch (\EasyWeChat\Core\Exceptions\InvalidArgumentException $exception) {
        }

        //halt(6);
        try {
            //fields not exists:[avatar]
            unset($arr['avatar']);
            if($visiter['state']=='online'){
                $arr['state']='1';
            }
            //halt($arr);
            $cid = Admins::table('wolive_chats')->insertGetId($arr);
            //$arr['avatar'] = $login['avatar'];
            //halt($login);
            $arr['avatar'] =  db('wolive_service')->where(['service_id' => $this->serverUser['service_id']])->value('avatar');
            //dump($arr);
            $arr['cid'] = $cid;
            $pusher->trigger("cu" . $channel, 'my-event', array('message' => $arr));
            $key = "callback_".$this->serverUser['business_id']."_".$this->serverUser['service_id'];
            //针对同一客户端的锁，防止同一客户端多次回调
            $_SESSION[$key] = md5(microtime(true));
            $businessInfo=Admins::table('wolive_business')->where('id',$this->serverUser['business_id'])->field('push_url')->find();
            if(trim($businessInfo['push_url'])!=''){
                $pusher->trigger('kefu' .  $this->serverUser['service_id'], 'callbackpusher', array('message' => $arr));
            }
            //$this->success('发送成功!');

        } catch (Exception $e) {

            $error = $e->getMessage();
            $data = ['code' => 3, 'msg' => $error];
            return $data;
        }

    }

    public function websocketSendMsg($visiter_id,$business_id,$msg){
        $arr=[];
        $login =$this->serverUser;
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
    

    /**
     * 清空聊天
     */
    public function clearchat(){
        $visiter_id=input('visiter_id');
        $login = $this->serverUser;
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
     * 获取该business_id下所用的黑名单信息.
     *
     * @return bool|string
     */
    public function getblackdata()
    {
        $this->serverUser['business_id']=$this->serverUser['service_id'];
        $login = $this->serverUser;

        $visiters = Admins::table('wolive_queue')->where('state', 'in_black_list')->where('business_id', $login['business_id'])->select();

        $blackers = [];
        foreach ($visiters as $v) {
            $data = Admins::table('wolive_visiter')->where('visiter_id', $v['visiter_id'])->where('business_id', $login['business_id'])->find();
            $blackers [] = $data;
        }

        $data = ['code' => 0, 'data' => $blackers];

        return json($data);
    }


    
    /**
     * 获取当前对话信息
     *
     */
    public function getchatnow()
    {
        $post = $this->request->post();
        $this->serverUser['business_id']=$this->serverUser['service_id'];
        $login = $this->serverUser;
        $visiter_id = $post['sdata']['visiter_id'];
        $res = Admins::table('wolive_queue')->where('visiter_id', $visiter_id)->where('business_id', $login['business_id'])->where('service_id', $login['service_id'])->find();

        // var_dump($res['state']);exit;

        $sdata = Admins::table('wolive_visiter')->where('visiter_id', $visiter_id)->where('business_id', $login['business_id'])->find();

        $chats = Admins::table('wolive_chats')->where(['visiter_id' => $visiter_id, 'service_id' => $login['service_id'], 'direction' => 'to_service'])->group('cid desc')->find();

        if ($res['state'] == 'complete') {

            $res = Admins::table('wolive_queue')->where('visiter_id', $visiter_id)->where('business_id', $login['business_id'])->update(['state' => 'normal']);
        }

        $data = ['code' => 0, 'msg' => 'success'];
        return json($data);
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
            return json($data);
        }
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
        return json($data);
    }


    /**
     * 排队列表类.
     *
     * @return mixed
     */
    public function getwait()
    {   
        $this->serverUser['business_id']=$this->serverUser['service_id'];
        $login = $this->serverUser;

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
        return json($data);
    }

     /**
     * 删除访客类.
     *
     * @return mixed
     */
    public function deletes()
    {
        $login = $this->serverUser;
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

        return json($data);

    }

    //获取聊天人员
    public function getchats(){
        $login = $this->serverUser;
        //dump($login['service_id']);
       
        //  business_id一个客服后台就是一个商户
                //$visiters = Admins::table('wolive_queue')->distinct(true)->field('visiter_id')->where(['service_id' => $login['service_id']])->where('state', 'normal')->order('timestamp desc')->select();
                $visiters = Admins::table('wolive_queue')
                ->distinct(true)
                ->field('visiter_id')
                ->where(['service_id' => $login['service_id'],'state'=>'normal'])
                ->order('timestamp desc')
                ->select();
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
                //dump(Visiter::getLastSql());
                
                //dump($login['business_id']);dump($visiters);dump($data);
                //halt($data);
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
                /*
                reset($chatonlineunread);
                reset($chatofflineunread);
                reset($chatonlinearr);
                reset($chatofflinearr);
                */
                array_multisort(array_column(collection($chatonlineunread)->toArray(),'order'),SORT_DESC,$chatonlineunread);
                array_multisort(array_column(collection($chatofflineunread)->toArray(),'order'),SORT_DESC,$chatofflineunread);
                array_multisort(array_column(collection($chatonlinearr)->toArray(),'order'),SORT_DESC,array_column(collection($chatonlinearr)->toArray(),'vid'),SORT_DESC,$chatonlinearr);
                array_multisort(array_column(collection($chatofflinearr)->toArray(),'order'),SORT_DESC,array_column(collection($chatofflinearr)->toArray(),'vid'),SORT_DESC,$chatofflinearr);
                //$chatarr = array_merge($chatonlineunread, $chatofflineunread,$chatonlinearr,$chatofflinearr);
                
                $chatarr = array_merge($chatonlinearr,$chatofflinearr,$chatonlineunread, $chatofflineunread);
        //        var_dump(array_column($chatarr,'istop'),array_column($chatarr,'sort'),array_column($chatarr,'order'),array_column($chatarr,'vid'));exit();
                //array_multisort(array_column($chatarr,'istop'),SORT_DESC,array_column($chatarr,'sort'),SORT_DESC,array_column($chatarr,'order'),SORT_DESC,array_column($chatarr,'vid'),SORT_DESC,$chatarr);
                //halt($chatarr);
                $result = Admins::table('wolive_chats')->where('service_id', $login['service_id'])->where('business_id', $login['business_id'])->where('state', 'unread')->where('direction', 'to_service')->count();
                if ($chatarr) {
                    $data = ['code' => 1, 'data' => $chatarr,'all_unread_count'=>$result];
                    return json($data);
                } else {
                    $data = ['code' => 0, 'msg' => '暂时没有数据！'];
                    return $data;
                }
    }

}