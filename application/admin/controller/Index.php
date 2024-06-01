<?php


namespace app\admin\controller;

use app\admin\model\Admins;
use app\admin\model\WechatPlatform;
use app\admin\model\WechatService;
use think\Db;
use think\Paginator;
use app\Common;
/**
 *
 * 后台页面控制器.
 * 路路发
 * www.dsxty.shop 
 */
class Index extends Base
{
    
    //选择地图位置页面
    public function selectmappage(){
        //halt(1);
        require 'lib/gitip/index.php';
        $request = request();
        $ip=$request->ip();
        $point=get_ip_location($ip);
        //halt($point);
        $this->assign('longitude', $point['longitude']);
        $this->assign('latitude', $point['latitude']);
        return $this->fetch();
    }

    /**
     * 后台首页.
     *
     * @return mixed
     */
    public function index()
    {
        $common = new Common();
        if ($common->isMobile()) {
            $this->redirect('mobile/admin/index');
        }
        $login = $_SESSION['Msg'];
        $time = date('Y-m-d', time());
        $t = strtotime(date('Y-m-d'));
        $times = date('Y-m-d H:i', time());
        $ftime = date('Y-m-d', time());
        $frtime = strtotime($ftime);

        if ($login['level'] != 'super_manager') {
        // 接入总量
            $sql = "select count(distinct(visiter_id)) as total from wolive_chats where business_id={$login['business_id']}";
            $sql .= " and service_id={$login['service_id']}";
            $res = Db::query($sql);
            $getinall = isset($res[0]['total'])?$res[0]['total']:0;
        } else {
            // 接入用户量
            $sql = "select count(distinct(visiter_id)) as total from wolive_chats where business_id={$login['business_id']}";
            $res = Db::query($sql);
            $getinall = isset($res[0]['total'])?$res[0]['total']:0;
        }
        if ($login['level'] != 'super_manager') {
            // 获取总会话量
            $chatsall = Admins::table("wolive_chats")->where('business_id', $login['business_id'])->where('service_id', $login['service_id'])->count();
        } else {
        // 获取总会话量
        $chatsall = Admins::table("wolive_chats")->where('business_id', $login['business_id'])->count();
        }
        // 正在排队人数
        $waiter = Admins::table("wolive_queue")->where(['business_id' => $login['business_id'], 'state' => 'normal'])->where("service_id", 0)->count();
        if ($login['level'] != 'super_manager') {
            // 正在咨询的人
            $talking = Admins::table('wolive_queue')->where(['business_id' => $login['business_id']])->where('service_id', $login['service_id'])->where('state', 'normal')->where("service_id", '<>', 0)->count();
        } else {
        // 正在咨询的人
        $talking = Admins::table('wolive_queue')->where(['business_id' => $login['business_id']])->where('state', 'normal')->where("service_id", '<>', 0)->count();
        }
        if ($login['level'] != 'super_manager') {
            //在线用户数
            $sql = "select count(distinct(a.visiter_id)) as total from wolive_visiter a left join wolive_chats b";
            $sql .= " on a.visiter_id=b.visiter_id";
            $sql .= " WHERE a.business_id={$login['business_id']} and b.service_id={$login['service_id']}";
            $sql .= " and a.state='online'";
            $res = Db::query($sql);
            $visiter_online = isset($res[0]['total'])?$res[0]['total']:0;;
        } else {
            //在线用户数
            $visiter_online = Admins::table('wolive_visiter')->where(['business_id' => $login['business_id']])->where('state', 'online')->count();
        }
        // 在线客服人数
        $services = Admins::table("wolive_service")->where(['business_id' => $login['business_id'], 'state' => 'online'])->count();
        if ($login['level'] != 'super_manager') {
        // 今日会话量
            $nowchats = Admins::table("wolive_chats")->where('business_id', $login['business_id'])->where('service_id', $login['service_id'])->where('timestamp', '>', "{$t}")->where('timestamp', '<=', time())->count();
           
        } else {
            // 今日会话量
            $nowchats = Admins::table("wolive_chats")->where('business_id', $login['business_id'])->where('timestamp',
                '>', "{$t}")->where('timestamp', '<=', time())->count();
        }
        if ($login['level'] != 'super_manager') {
            //今日评价人数
            $nowcomments = Admins::table("wolive_comment")->where('business_id', $login['business_id'])->where('service_id', $login['service_id'])->where('add_time', '>', "{$time}")->where('add_time', '<=', $times)->count();
        } else {
        //今日评价人数
            $nowcomments = Admins::table("wolive_comment")->where('business_id',
                $login['business_id'])->where('add_time', '>', "{$time}")->where('add_time', '<=', $times)->count();
        }

        if ($login['level'] != 'super_manager') {
            //评价总数
            $allcomments = Admins::table("wolive_comment")->where('business_id', $login['business_id'])->where('service_id', $login['service_id'])->count();
        } else {
        //评价总数
        $allcomments = Admins::table("wolive_comment")->where('business_id', $login['business_id'])->count();
        }

        // 今日留言量
        $message = Admins::table('wolive_message')->where('business_id', $login['business_id'])->where('timestamp', '>', $time)->where('timestamp', '<=', $times)->count();
        // 留言总量
        $messageall = Admins::table('wolive_message')->where('business_id', $login['business_id'])->count();


        if ($times > $cutime = $time . " 08:00") {
            $time8 = strtotime($cutime);
            $chats8 = Admins::table('wolive_chats')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $chats8 = $chats8->where('service_id', $login['service_id']);
            }
            $chats8 = $chats8->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time8}%")->count();
            $chatsdata[] = $chats8;

            $message8 = Admins::table("wolive_message")->where('business_id', $login['business_id'])->where('timestamp', '>', "{$ftime}")->where('timestamp', '<', "{$cutime}")->count();
            $messagedata[] = $message8;

            $getin8 = Admins::table("wolive_chats")->distinct(true)->field('visiter_id')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $getin8 = $getin8->where('service_id', $login['service_id']);
            }
            $getin8 = $getin8->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time8}%")->count();

            $getindata[] = $getin8;

        } else {
            $chatsdata[] = "";
            $messagedata[] = "";
            $getindata[] = "";

        }

        if ($times > $cutime = $time . " 10:00") {

            $time10 = strtotime($cutime);

            $chats10 = Admins::table('wolive_chats')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $chats10 = $chats10->where('service_id', $login['service_id']);
            }
            $chats10 = $chats10->where('timestamp', '>', "{$frtime}")->where('timestamp', '<', "{$time10}")->count();
            $chatsdata[] = $chats10;

            $message10 = Admins::table("wolive_message")->where('business_id', $login['business_id'])->where('timestamp', '>', "{$ftime}")->where('timestamp', '<', "{$cutime}")->count();

            $messagedata[] = $message10;

            $getin10 = Admins::table("wolive_chats")->distinct(true)->field('visiter_id')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $getin10 = $getin10->where('service_id', $login['service_id']);
            }
            $getin10 = $getin10->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time10}%")->count();


            $getindata[] = $getin10;

        } else {
            $chatsdata[] = "";
            $messagedata[] = "";
            $getindata[] = "";

        }

        if ($times > $cutime = $time . " 12:00") {
            $time12 = strtotime($cutime);
            $chats12 = Admins::table('wolive_chats')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $chats12 = $chats12->where('service_id', $login['service_id']);
            }
            $chats12 = $chats12->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time12}%")->count();
            $chatsdata[] = $chats12;

            $message12 = Admins::table("wolive_message")->where('business_id', $login['business_id'])->where('timestamp', '>', "{$ftime}")->where('timestamp', '<', "{$cutime}")->count();

            $messagedata[] = $message12;

            $getin12 = Admins::table("wolive_chats")->distinct(true)->field('visiter_id')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $getin12 = $getin12->where('service_id', $login['service_id']);
            }
            $getin12 = $getin12->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time12}%")->count();


            $getindata[] = $getin12;

        } else {
            $chatsdata[] = "";
            $messagedata[] = "";
            $getindata[] = "";

        }


        if ($times > $cutime = $time . " 14:00") {
            $time14 = strtotime($cutime);

            $chats14 = Admins::table('wolive_chats')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $chats14 = $chats14->where('service_id', $login['service_id']);
            }
            $chats14 = $chats14->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time14}%")->count();
            $chatsdata[] = $chats14;

            $message14 = Admins::table("wolive_message")->where('business_id', $login['business_id'])->where('timestamp', '>', "{$ftime}")->where('timestamp', '<', "{$cutime}")->count();

            $messagedata[] = $message14;

            $getin14 = Admins::table("wolive_chats")->distinct(true)->field('visiter_id')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $getin14 = $getin14->where('service_id', $login['service_id']);
            }
            $getin14 = $getin14->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time14}%")->count();

            $getindata[] = $getin14;

        } else {
            $chatsdata[] = "";
            $messagedata[] = "";
            $getindata[] = "";

        }

        if ($times > $cutime = $time . " 16:00") {
            $time16 = strtotime($cutime);

            $chats16 = Admins::table('wolive_chats')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $chats16 = $chats16->where('service_id', $login['service_id']);
            }
            $chats16 = $chats16->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time16}%")->count();
            $chatsdata[] = $chats16;

            $message16 = Admins::table("wolive_message")->where('business_id', $login['business_id'])->where('timestamp', '>', "{$ftime}")->where('timestamp', '<', "{$cutime}")->count();

            $messagedata[] = $message16;

            $getin16 = Admins::table("wolive_chats")->distinct(true)->field('visiter_id')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $getin16 = $getin16->where('service_id', $login['service_id']);
            }
            $getin16 = $getin16->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time16}%")->count();


            $getindata[] = $getin16;

        } else {
            $chatsdata[] = "";
            $messagedata[] = "";
            $getindata[] = "";

        }

        if ($times > $cutime = $time . " 18:00") {
            $time18 = strtotime($cutime);

            $chats18 = Admins::table('wolive_chats')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $chats18 = $chats18->where('service_id', $login['service_id']);
            }
            $chats18 = $chats18->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time18}%")->count();
            $chatsdata[] = $chats18;

            $message18 = Admins::table("wolive_message")->where('business_id', $login['business_id'])->where('timestamp', '>', "{$ftime}")->where('timestamp', '<', "{$cutime}")->count();

            $messagedata[] = $message18;

            $getin18 = Admins::table("wolive_chats")->distinct(true)->field('visiter_id')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $getin18 = $getin18->where('service_id', $login['service_id']);
            }
            $getin18 = $getin18->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time18}%")->count();


            $getindata[] = $getin18;

        } else {
            $chatsdata[] = "";
            $messagedata[] = "";
            $getindata[] = "";
        }

        if ($times > $cutime = $time . " 20:00") {
            $time20 = strtotime($cutime);
            $chats20 = Admins::table('wolive_chats')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $chats20 = $chats20->where('service_id', $login['service_id']);
            }
            $chats20 = $chats20->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time20}%")->count();
            $chatsdata[] = $chats20;

            $message20 = Admins::table("wolive_message")->where('business_id', $login['business_id'])->where('timestamp', '>', "{$ftime}")->where('timestamp', '<', "{$cutime}")->count();

            $messagedata[] = $message20;

            $getin20 = Admins::table("wolive_chats")->distinct(true)->field('visiter_id')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $getin20 = $getin20->where('service_id', $login['service_id']);
            }
            $getin20 = $getin20->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time20}%")->count();


            $getindata[] = $getin20;

        } else {
            $chatsdata[] = "";
            $messagedata[] = "";
            $getindata[] = "";

        }

        if ($times > $cutime = $time . " 22:00") {
            $time22 = strtotime($cutime);
            $chats22 = Admins::table('wolive_chats')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $chats22 = $chats22->where('service_id', $login['service_id']);
            }
            $chats22 = $chats22->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time22}%")->count();
            $chatsdata[] = $chats22;

            $message22 = Admins::table("wolive_message")->where('business_id', $login['business_id'])->where('timestamp', '>', "{$ftime}%")->where('timestamp', '<', "{$cutime}")->count();

            $messagedata[] = $message22;

            $getin22 = Admins::table("wolive_chats")->distinct(true)->field('visiter_id')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $getin22 = $getin22->where('service_id', $login['service_id']);
            }
            $getin22 = $getin22->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time22}%")->count();


            $getindata[] = $getin22;

        } else {
            $chatsdata[] = "";
            $messagedata[] = "";
            $getindata[] = "";

        }

        if ($times > $cutime = $time . " 00:00") {
            $time00 = strtotime($cutime);
            $chats00 = Admins::table('wolive_chats')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $chats00 = $chats00->where('service_id', $login['service_id']);
            }
            $chats00 = $chats00->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time00}%")->count();
            $chatsdata[] = $chats00;

            $message00 = Admins::table("wolive_message")->where('business_id', $login['business_id'])->where('timestamp', '>', "{$ftime}%")->where('timestamp', '<', "{$cutime}")->count();

            $messagedata[] = $message00;

            $getin00 = Admins::table("wolive_chats")->distinct(true)->field('visiter_id')->where('business_id', $login['business_id']);
            if ($login['level'] != 'super_manager') {
                $getin00 = $getin00->where('service_id', $login['service_id']);
            }
            $getin00 = $getin00->where('timestamp', '>', "{$frtime}%")->where('timestamp', '<', "{$time00}%")->count();


            $getindata[] = $getin00;

        } else {
            $chatsdata[] = "";
            $messagedata[] = "";
            $getindata[] = "";

        }

        $this->assign('nowcomments',$nowcomments);
        $this->assign('allcomments',$allcomments);

        $this->assign('chatsdata', $chatsdata);
        $this->assign('messagedata', $messagedata);
        $this->assign('getindata', $getindata);

        $this->assign('getinall', $getinall);
        $this->assign('waiter', $waiter);
        $this->assign('chatsall', $chatsall);
        $this->assign('talking', $talking);
        $this->assign('visiter_online', $visiter_online);
        $this->assign('services', $services);
        $this->assign('nowchats', $nowchats);
        $this->assign('message', $message);
        $this->assign('messageall', $messageall);
        $this->assign('admins', $login);
        $this->assign("part", "首页");
        $this->assign('title', '首页');

        $this->redirect("admin/Index/chats");
    }
    //添加专属域名
    public function addzhuanshuyuming(){
        $name=input('name');
        $login = $_SESSION['Msg'];
        $service_id=$login['service_id'];
        //查询域名
        $yuming=db('wolive_yuming')->where(['name'=>$name,'leixing'=>'2','type'=>'0'])->find();
        if(empty($yuming)){
            return json(['code'=>'0','data'=>'专属域名不正确!']);
        }
        $zhuanshuidArr=explode(',',$yuming['suoshuid']);
        //dump($zhuanshuidArr);
        if(!in_array($service_id,$zhuanshuidArr)){
           
            $zhuanshuidArr[]=$service_id;
            $zhuanshuid=implode(',',$zhuanshuidArr);
            $zhuanshuid.=',';
            //dump($zhuanshuid);
            //halt($zhuanshuid);
            $res=db('wolive_yuming')->where(['id'=>$yuming['id']])->update(['suoshuid'=>$zhuanshuid]);
            if(!empty($res)){
                return json(['code'=>'1','data'=>'添加成功!']);
            }else{
                return json(['code'=>'0','data'=>'添加失败!']);
            }
        }else{
            return json(['code'=>'0','data'=>'域名已存在!']);
        }
        //halt($yuming);
    }
    
    public function findyumhuiyuan(){
        $name=input('name');
       //查询域名
        $yuming=db('wolive_yuming')->where(['name'=>$name,'leixing'=>'2','type'=>'0'])->find();
        if(empty($yuming)){
            return json(['code'=>'0','data'=>'专属域名不正确!']);
        }
        $zhuanshuidArr=explode(',',$yuming['suoshuid']);
        //halt($zhuanshuidArr);
        //通过专属id查找用户
        $where['service_id']=['in',$zhuanshuidArr];
        $serviceArr=db('wolive_service')->where($where)->select();
        //halt($serviceArr);
        if(!empty($serviceArr)){
            return json(['code'=>'1','data'=>$serviceArr]);
        }else{
            return json(['code'=>'0','data'=>'没有查询到匹配数据']);
        }
        
    }
    
    public function userinformation(){
        $login = $_SESSION['Msg'];
        $res = Admins::table('wolive_business')->where('id', $login['business_id'])->find();
        $service = Admins::table('wolive_service')->where('business_id', $login['business_id'])->find();
        $add=$service->getData();
        $add['expire_time']=date("Y-m-d H:i:s",$res['expire_time']);
        return $add;
    }
    public function statisticsss(){
        $login = $_SESSION['Msg'];
        $jt=date("Y-m-d",time());
        $zt=date("Y-m-d",strtotime("-1 days"));
        $jip_sql="select count(distinct(visiter_id)) as total from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$jt}'";
        $jip_row= Db::query($jip_sql);
        
        $jm_sql="select count(*) as total from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$jt}'";
        $jm_row=Db::query($jm_sql);
        $j_arr=array(
                'ip'=>$jip_row[0]['total'],
                'message'=>$jm_row[0]['total'],
                'logon'=>$jip_row[0]['total'],
                'logonx'=>0,
            );
            
        $zip_sql="select count(distinct(visiter_id)) as total from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$zt}'";
        $zip_row=Db::query($zip_sql);
        
        $zm_sql="select count(*) as total from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$zt}'";
        $zm_row=Db::query($zm_sql);
        $z_arr=array(
                'ip'=>$zip_row[0]['total'],
                'message'=>$zm_row[0]['total'],
                'logon'=>$zip_row[0]['total'],
                'logonx'=>0,
            );
            
       $arr=array();
        for($i=1;$i<=7;$i++){
            $time=strtotime("-$i days");
            $d=date('Y-m-d',$time);
            $sql="select count(distinct(visiter_id)) as total from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$d}'";
            $row=Db::query($sql);
            $arr[]=array(
                    'time'=>$d,
                    'number'=>$row[0]['total']
                );
        }
         $data=array(
            'today'=>$j_arr,
            'yesterday'=>$z_arr,
            'list'=>$arr
        );
        return json_encode($data,JSON_UNESCAPED_UNICODE);
        
    }
    public function statistics(){
        $login = $_SESSION['Msg'];
        $jt=date("Y-m-d",time());
        $zt=date("Y-m-d",strtotime("-1 days"));
        
         
        //今日ip数
        $jip_sql="select count(distinct(v.visiter_id)) as total from wolive_visiter as v left join wolive_queue as q on v.visiter_id=q.visiter_id where v.business_id={$login['business_id']} and to_days(now())-to_days(q.timestamp)=0";
        //FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$jt}'";
       // echo $jip_sql;
        $jip_row= Db::query($jip_sql);
        
        //今天消息总数
        $jm_sql="select count(*) as total from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$jt}'";
        $jm_row=Db::query($jm_sql);
        
        //今天登录数量
        $jd_sql="select distinct(visiter_id) as visiter_id from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$jt}'";
        $jd_row=Db::query($jd_sql);
        $jlogonx=0;
        $jlogon=0;
        foreach ($jd_row as $jd){
            
            $v_sql="select * from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$jt}' and visiter_id='".$jd['visiter_id']."' order by cid asc ";
            $v_row=Db::query($v_sql);
            
            $l_sql="select * from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$jt}' and visiter_id='".$jd['visiter_id']."' order by cid desc ";
            $l_row=Db::query($l_sql);
            if($v_row[0]['timestamp']=='' || $l_row[0]['timestamp']==''){
                $jlogonx=$jlogonx+1;
            }
            else{
                $miaoshu=$l_row[0]['timestamp']-$v_row[0]['timestamp'];
                if($miaoshu>5){
                    $jlogon=$jlogon+1;
                }
                if($miaoshu<5){
                    $jlogonx=$jlogonx+1;
                }
            }
           
        }
        
        $j_arr=array(
                'ip'=>$jip_row[0]['total'],
                'message'=>$jm_row[0]['total'],
                'logon'=>$jlogon,
                'logonx'=>$jlogonx,
            );
            
        $zip_sql="select count(distinct(v.visiter_id)) as total from wolive_visiter as v left join wolive_queue as q on v.visiter_id=q.visiter_id where v.business_id={$login['business_id']} and to_days(now())-to_days(q.timestamp)=1";
        $zip_row=Db::query($zip_sql);
        
        $zm_sql="select count(*) as total from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$zt}'";
        $zm_row=Db::query($zm_sql);
        
       //昨天登录数量
        $zd_sql="select distinct(visiter_id) as visiter_id from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$zt}'";
        $zd_row=Db::query($zd_sql);
        $zlogonx=0;
        $zlogon=0;
        foreach ($zd_row as $zd){
            
            $zv_sql="select * from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$zt}' and visiter_id='".$zd['visiter_id']."' order by cid asc ";
            $zv_row=Db::query($zv_sql);
            
            $zl_sql="select * from wolive_chats where business_id={$login['business_id']} and FROM_UNIXTIME(timestamp, '%Y-%m-%d')='{$zt}' and visiter_id='".$zd['visiter_id']."' order by cid desc ";
            $zl_row=Db::query($zl_sql);
            if($zv_row[0]['timestamp']=='' || $zl_row[0]['timestamp']==''){
                $zlogonx=$zlogonx+1;
            }
            else{
                $zmiaoshu=$zl_row[0]['timestamp']-$zv_row[0]['timestamp'];
                if($zmiaoshu>5){
                    $zlogon=$zlogon+1;
                }
                if($zmiaoshu<5){
                    $zlogonx=$zlogonx+1;
                }
            }
           
        }
        $z_arr=array(
                'ip'=>$zip_row[0]['total'],
                'message'=>$zm_row[0]['total'],
                'logon'=>$zlogon,
                'logonx'=>$zlogonx,
            );
            
       $arr=array();
       $j=0;
        for($i=1;$i<=7;$i++){
             $time=strtotime("-$i days");
             $d=date('Y-m-d',$time);
             $j=$j+1;
            $sql="select count(distinct(v.visiter_id)) as total from wolive_visiter as v left join wolive_queue as q on v.visiter_id=q.visiter_id where v.business_id={$login['business_id']} and to_days(now())-to_days(q.timestamp)=$j";
            //echo $sql;
            
            $row=Db::query($sql);
            $arr[]=array(
                    'time'=>$d,
                    'number'=>$row[0]['total']
                );
        }
         $data=array(
            'today'=>$j_arr,
            'yesterday'=>$z_arr,
            'list'=>$arr
        );
        return json_encode($data,JSON_UNESCAPED_UNICODE);
        
    }
    /**
     * 后台对话页面.
     *
     * @return mixed
     */
    public function chats()
    {
        $login = $_SESSION['Msg'];
        $res = Admins::table('wolive_business')->where('id', $login['business_id'])->find();
        $this->assign("type", $res['video_state']);
        $this->assign('atype', $res['audio_state']);
        $this->assign("user",$login);
        $this->assign("title", "客户咨询");
        $this->assign('part', '客户咨询');
        return $this->fetch();
    }


    /**
     * 常用语页面.
     *
     * @return mixed
     */
    public function custom()
    {
        $login = $_SESSION['Msg'];
        $data = db("wolive_sentence")->where('service_id', $login['service_id'])
        ->order('weigh')
        
        ->paginate(10);
        //dump($login['service_id']);
        //halt($data);
        
        $page = $data->render();
        $this->assign('page', $page);
        $this->assign('lister', $data);
        $this->assign('title', "问候语设置");
        $this->assign('part', "设置");

        return $this->fetch();
    }

    /**
     * 常见问题设置.
     *
     * @return mixed
     */
    public function question()
    {
        $login = $_SESSION['Msg'];
        if ($login['level'] == 'service') {
            $this->redirect('admin/index/index');
        }
        $data = Admins::table("wolive_question")
            ->where('business_id', $login['business_id'])
            ->paginate();
           // ->order('sort desc')
        $page = $data->render();
        $this->assign('page', $page);
        $this->assign('lister', $data);
        $this->assign('title', "常见问题设置");
        $this->assign('part', "设置");
        return $this->fetch();
    }


    /**
     * 生成前台文件页面.
     *
     * @return mixed
     */
    public function front()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

        $web = $http_type . $_SERVER['HTTP_HOST'];
        $action = $web.request()->root();

        $login = $_SESSION['Msg'];
        $class = Admins::table('wolive_group')->where('business_id', $login['business_id'])->select();

        $this->assign('class', $class);
        $this->assign('business', $login['business_id']);
        $this->assign('web', $web);
        $this->assign('login', $login);
        $this->assign('action', $action);
        $this->assign("title", "接入方法");
        $this->assign("part", "接入方法");

        return $this->fetch();
    }


    /**
     * 所有聊天记录页面。
     * [history description]
     * @return [type] [description]
     */
    public function history()
    {
        $visiter_id = $this->request->param('visiter_id');
        $this->assign('visiter_id',$visiter_id);
        return $this->fetch();
    }

    /**
     * 留言页面.
     *
     * @return mixed
     */
    public function message()
    {
        $login = $_SESSION['Msg'];
        $post = $this->request->get();
        $userAdmin = Admins::table('wolive_message');
        $pageParam = ['query' => []];
        unset($post['page']);
        if ($post) {
            $pushtime = $post['pushtime'];

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

        $data = $userAdmin->where('business_id', $login['business_id'])->paginate(8, false, $pageParam);
        $page = $data->render();
        $this->assign('page', $page);
        $this->assign('msgdata', $data);
        $this->assign('title', "留言查看");
        $this->assign('part', "留言查看");

        return $this->fetch();
    }

    /**
     * 转接客服页面
     * @return [type] [description]
     */
    public function service()
    {

        $get = $_GET;

        $visiter_id = $_GET['visiter_id'];

        $login = $_SESSION['Msg'];

        $business_id = $login['business_id'];

        $res = Admins::table('wolive_service')->where('business_id', "{$business_id}")->where('service_id', '<>', $login['service_id'])->select();

        $this->assign('service', $res);
        $this->assign('visiter_id', $visiter_id);
        $this->assign('name', $get['name']);

        return $this->fetch();
    }

    public function servicejson()
    {
        $get = $_GET;

        $visiter_id = $_GET['visiter_id'];

        $login = $_SESSION['Msg'];

        $business_id = $login['business_id'];

        $res = Admins::table('wolive_service')->where('business_id', "{$business_id}")->where('service_id', '<>', $login['service_id'])->select();
unset($res['password']);
        return json(['code'=>0,'data'=>['visiter_id'=>$visiter_id,'name'=>$get['name'],'service'=>$res]]);
    }

    /**
     * 常见问题编辑页面
     * [editer description]
     * @return [type] [description]
     */
    public function editer()
    {
        $login = $_SESSION['Msg'];
        if ($login['level'] == 'service') {
            $this->redirect('admin/index/index');
        }

        $get = $this->request->get();

        $res = Admins::table('wolive_question')
            ->where('qid', $get['qid'])
            ->order('sort desc')
            ->find();

        $this->assign('question', $res['question']);
        $this->assign('keyword',$res['keyword']);
        
        $this->assign('answer', $res['answer']);
        $this->assign('qid', $get['qid']);
        $this->assign('sort', $res['sort']);
        $this->assign('status', $res['status']);

        return $this->fetch();
    }
    /**
     * 常见问题编辑页面
     * [editer description]
     * @return [type] [description]
     */
    public function custom_editer()
    {
        $login = $_SESSION['Msg'];
        if ($login['level'] == 'service') {
            $this->redirect('admin/index/index');
        }

        $get = $this->request->get();
        $id=isset($get['id'])?$get['id']:0;

        $res = Admins::table('wolive_question')
            ->where('id', $get['id'])
            ->order('sort desc')
            ->find();

        $this->assign('question', $res['question']);
        $this->assign('keyword',$res['keyword']);
        $this->assign('answer', $res['answer_read']);
        $this->assign('qid', $get['qid']);
        $this->assign('sort', $res['sort']);
        $this->assign('status', $res['status']);

        return $this->fetch();
    }


    /**
     * 编辑tab页面
     * [editertab description]
     * @return [type] [description]
     */
    public function editertab()
    {

        $login = $_SESSION['Msg'];
        if ($login['level'] == 'service') {
            $this->redirect('admin/index/index');
        }

        $get = $this->request->get();

        $res = Admins::table('wolive_tablist')->where('tid', $get['tid'])->find();

        $this->assign('title', $res['title']);
        $this->assign('content', $res['content_read']);
        $this->assign('tid', $get['tid']);

        return $this->fetch();
    }

    public function editercustom()
    {
        $login = $_SESSION['Msg'];
        $get = $this->request->get();
$content='';
        $sid=0;
        if($get['sid']>0){
            $res = Admins::table('wolive_sentence')
                ->where('sid', $get['sid'])
                ->where('service_id',$login['service_id'])
                ->find();
            $content=$res['content'];
            $sid=$res['sid'];
        }
        $this->assign('content', $content);
        $this->assign('sid', $sid);

        return $this->fetch();
    }

    /**
     * 设置页面
     * [set description]
     */
    public function set()
    {

        $this->assign('user', $_SESSION['Msg']);
        $this->assign('title', '系统设置');
        $this->assign('part', '系统设置');
        return $this->fetch();
    }
    
    /**
     * 设置页面
     * [set description]
     */
    public function setting()
    {
         $login = $_SESSION['Msg'];
        if ($login['level'] == 'service') {
            $this->redirect('admin/index/index');
        }
        $res = Admins::table("wolive_business")->where('id', $login['business_id'])->find();
        $service = Admins::table("wolive_service")->where('business_id', $login['business_id'])->find();
        $this->assign('user', $_SESSION['Msg']);
        $this->assign('business', $res);
        $this->assign('u', $service);
        $this->assign('title', '设置');
        $this->assign('part', '设置');
        return $this->fetch();
    }


    public function setup()
    {

        $login = $_SESSION['Msg'];
        if ($login['level'] == 'service') {
            $this->redirect('admin/index/index');
        }
        $res = Admins::table("wolive_business")->where('id', $login['business_id'])->find();

        $this->assign('video', $res['video_state']);
        $this->assign('audio', $res['audio_state']);
        $this->assign('voice', $res['voice_state']);
        $this->assign('voice_addr', $res['voice_address']);
        $this->assign('template', $res['template_state']);
        $this->assign('method', $res['distribution_rule']);
        $this->assign('push_url',$res['push_url']);
        $this->assign('title', '通用设置');
        $this->assign('part', '设置');

        return $this->fetch();
    }
    
    /**
     * tab面版页面。
     * [tablist description]
     * @return [type] [description]
     */
    public function tablist()
    {


        if ($_SESSION['Msg']['level'] == 'service') {
            $this->redirect('admin/index/index');
        }

        $business_id = $_SESSION['Msg']['business_id'];

        $res = Admins::table('wolive_tablist')->where('business_id', $business_id)->select();

        $this->assign('tablist', $res);

        $this->assign('title', '编辑前端tab面版');
        $this->assign('part', '设置');

        return $this->fetch();
    }


    /**
     *
     * [replylist description]
     * @return [type] [description]
     */
    public function replylist()
    {

        $id = $_SESSION['Msg']['service_id'];
        $res = Admins::table('wolive_reply')->where('service_id', $id)->paginate(8);
        $page = $res->render();
        $this->assign('page', $page);
        $this->assign('replyword', $res);

        return $this->fetch();
    }

    public function template()
    {
        $common = new Common();
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $post['business_id'] = $_SESSION['Msg']['business_id'];
            $post=$common->deep_array_map_trim($post);
            $res = WechatPlatform::edit($post);

            $arr = $res!== false ? ['code' => 0, 'msg' => '成功']: ['code' => 1, 'msg' => '失败'];
            return $arr;
        } else {
            $template = WechatPlatform::get(['business_id'=>$_SESSION['Msg']['business_id']]);

            $protocol=$common->isHTTPS()?'https://':'http://';
            $this->assign('template',$template);
            $this->assign('business_id',$_SESSION['Msg']['business_id']);
            $this->assign('protocol',$protocol);
            $this->assign('title', '公众号与模板消息设置');
            $this->assign('part', "设置");
            return $this->fetch();
        }
    }

    public function qrcode()
    {
        $qrcode = WechatService::get()->qrcode;
//        fangke
        $result = $qrcode->temporary('kefu_'.$_SESSION['Msg']['service_id'], 6 * 24 * 3600);
        $ticket = $result['ticket'];// 或者 $result['ticket']
        $url = $qrcode->url($ticket);
        return json(['code'=>0,'data'=>$url]);
    }

    public function test(){
        var_dump(\app\Common::clearXSS('121313&&&&156479'));
    }
}