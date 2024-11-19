<?php
/*
 * @Author: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @Date: 2023-11-01 05:08:16
 * @LastEditors: error: error: git config user.name & please set dead value or install git && error: git config user.email & please set dead value or install git & please set dead value or install git
 * @LastEditTime: 2023-12-14 16:30:26
 * @FilePath: /coder/wwwroot/ceshi.yusygoe.store/application/api/controller/Serveruser.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace app\api\controller;
/**
 * 客服信息
 * 
 */
use app\admin\model\Admins;
use think\Db;
use app\platform\model\Business;
use app\admin\model\Queue;

class Serveruser extends CRUD{

    //http://ceshi.yusygoe.store/api/Serveruser/getserveruserBytoken
    //通过token获取用户信息
    public function getserveruserBytoken(){
        
        $this->serverUser['kamidata']=db('wolive_kami')->where(['kami'=>$this->serverUser['kami']])->find();
        $this->success('获取成功',$this->serverUser);
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
        return json($arr);
    }

    public function user_group_list(){
        $this->serverUser['business_id']=$this->serverUser['service_id'];
        $this->login = $this->serverUser;
        $vid=$this->request->param('vid','');
        if(!$vid){
            $this->error('参数请求非法！');
        }
        $vgInfo=db('wolive_visiter_vgroup')->field('GROUP_CONCAT(group_id) as group_id')->where(['vid'=>$vid])->group('vid')
            ->find();
        $user_groups=[];
        if($vgInfo && $vgInfo['group_id']!=''){
            $user_groups=explode(',',$vgInfo['group_id']);
        }
        $group = \app\admin\model\Vgroup::where('status',1)
            ->where('business_id',$this->login['business_id'])
            ->where('service_id',$this->login['service_id'])->select();
        $this->assign('group', $group);
        $this->assign('user_groups', $user_groups);
        return $this->fetch();
    }

     /**
     * 重新打开访客
     * @return \think\response\Json
     */
    public function openCs()
    {
        if ($this->request->isPost()) {
            $visiter_id = $this->request->post('visiter_id');
            Queue::where('visiter_id', $visiter_id)
                ->where('business_id', $this->login['business_id'])
                ->where('service_id',$this->login['service_id'])
                ->update(['state' => 'normal']);
            return json(['code'=>0,'msg'=>'success']);
        }
    }
    
    /**
     * 批量操作客户分组
     *  group_id[]:8
     *  group_id[]:7
     *  vid[]:1
     *  vid[]:2
     *  vid[]:3
     * @return \think\response\Json
     * @throws \Exception
     */
    public function visiterGroup()
    {
        if ($this->request->isPost()) {
            $vids = $this->request->post('vid/a',[]);
            $gid = $this->request->post('group_id/a',[]);
            if (empty($gid)) {
                VisiterGroup::where('business_id',$this->login['business_id'])
                    ->where('service_id',$this->login['service_id'])
                    ->where('vid','in',$vids)
                    ->delete();
                return json(['code'=>0,'msg'=>'操作成功']);
            }

            $groups = Vgroup::where('id','in',$gid)
                ->where('service_id',$this->login['service_id'])
                ->where('business_id',$this->login['business_id'])
                ->count();
            $visiter = Queue::alias('q')
                ->join('wolive_visiter v','q.visiter_id = v.visiter_id','left')
                ->where('v.vid','in',$vids)
                ->where('q.business_id',$this->login['business_id'])
                ->count();
            if ($groups != count($gid) || $visiter != count($vids)) {
                return json(['code'=>1,'msg'=>'参数错误']);
            }
            $vgmodel = new VisiterGroup();
            $vgmodel->where('business_id',$this->login['business_id'])
                ->where('service_id',$this->login['service_id'])
                ->where('vid','in',$vids)
                ->delete();
            $data = [];
            foreach ($vids as $v) {
                $temp['vid'] = $v;
                $temp['business_id'] = $this->login['business_id'];
                $temp['service_id'] = $this->login['service_id'];
                foreach ($gid as $g) {
                    $temp['group_id'] = $g;
                    $data[] = $temp;
                }
            }
            $res = $vgmodel->saveAll($data);
            if ($res !== false) {
                $data = ['code'=>0,'msg'=>'操作成功'];
            } else {
                $data = ['code'=>1,'msg'=>'操作失败'];
            }
            return json($data);
        }
    }

    /**
     * 修改用户
     */
    public function editserveruser(){

    }
    //卡密续费
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
        $login = $this->serverUser;
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
            return json($arr);
         }
         else{
            $arr = ['code' => 1, 'msg' => '续费失败！'];
            return json($arr);
            
         }
    }
    //更新数据
    public function updateuserinfo(){
        $post = $this->request->post();
        //改id
        $post['id']=$this->serverUser['service_id'];

        $file = $this->request->file('img_head333');
       
        $login = $this->serverUser;
        
        
        
        if(empty($post['yanzhengma'])){
            $post['yanzhengma']='off';
        }
        //halt($post);
        $result = Admins::table('wolive_service')->where(['service_id'=>$post['id']])->update(['nick_name' => $post['nickname'],'avatar' => $post['img_head333'],'yanzhengma'=>$post['yanzhengma']]);
        //dump(Admins::getLastSql());
        //halt($result);
     

        if ($result == 0) {
            $data =['code'=>1,'msg'=>'未被修改'];
            return $data;
        } else {
            if($post['id'] == $login['service_id']){
                $newdata =Admins::table('wolive_service')->where('service_id',$post['id'])->find();
                $_SESSION['Msg'] =$newdata->getData();
                $business = Business::get($_SESSION['Msg']['business_id']);
                $_SESSION['Msg']['business'] = $business->getData();
            }
            $data =['code'=>0,'msg'=>'修改成功'];
            return json($data);
        }
    }

     //添加专属域名
    public function addzhuanshuyuming(){
        $name=input('name');

        $service_id=$this->serverUser['service_id'];
        //查询域名
        $yuming=db('wolive_yuming')->where(['name'=>$name,'leixing'=>'2','type'=>'0'])->find();
        if(empty($yuming)){
            $this->error('专属域名不正确!');
        }
        $zhuanshuidArr=explode(',',$yuming['suoshuid']);
        //dump($zhuanshuidArr);
        if(!in_array($service_id,$zhuanshuidArr)){
           
            $zhuanshuidArr[]=$service_id;
            $zhuanshuid=implode(',',$zhuanshuidArr);
            $zhuanshuid.=',';

            $res=db('wolive_yuming')->where(['id'=>$yuming['id']])->update(['suoshuid'=>$zhuanshuid]);
            if(!empty($res)){
                $this->success('添加成功!');
            }else{
                $this->error('添加失败!');
            }
        }else{
            $this->error('域名已存在!');

        }
        //halt($yuming);
    }
    /**
     * 查询专属域名
     */
    public function findyumhuiyuan(){
        $name=input('name');
       //查询域名
        $yuming=db('wolive_yuming')->where(['name'=>$name,'leixing'=>'2','type'=>'0'])->find();
        if(empty($yuming)){
            $this->error('专属域名不正确!');
        }
        $zhuanshuidArr=explode(',',$yuming['suoshuid']);
        //halt($zhuanshuidArr);
        //通过专属id查找用户
        $where['service_id']=['in',$zhuanshuidArr];
        $serviceArr=db('wolive_service')->where($where)->select();
        //halt($serviceArr);
        if(!empty($serviceArr)){
            $this->success('获取成功!',$serviceArr);
        }else{
            $this->error('没有查询到匹配数据!');
        }
        
    }
    /**
     * 统计数据
     */
    public function statistics(){
        $this->serverUser['business_id']=$this->serverUser['service_id'];
        $login = $this->serverUser;
        $jt=date("Y-m-d",time());
        $zt=date("Y-m-d",strtotime("-1 days"));
        
         
        //今日ip数
        $jip_sql="select count(distinct(v.ip)) as total from wolive_visiter as v left join wolive_queue as q on v.visiter_id=q.visiter_id where v.business_id={$login['business_id']} and to_days(now())-to_days(q.timestamp)=0";
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
        $jinrinumber=\GlobleTongbu::getUserContent($login['business_id'],$jt);
        $j_arr=array(
                'ip'=>$jinrinumber,
                'message'=>$jm_row[0]['total'],
                'logon'=>$jlogon,
                'logonx'=>$jlogonx,
            );
            
        $zip_sql="select count(distinct(v.ip)) as total from wolive_visiter as v left join wolive_queue as q on v.visiter_id=q.visiter_id where v.business_id={$login['business_id']} and to_days(now())-to_days(q.timestamp)=1";
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
            $sql="select count(distinct(v.ip)) as total from wolive_visiter as v left join wolive_queue as q on v.visiter_id=q.visiter_id where v.business_id={$login['business_id']} and to_days(now())-to_days(q.timestamp)=$j";
            //echo $sql;
            
            $row=Db::query($sql);
            $number=\GlobleTongbu::getUserContent($login['business_id'],$d);
            $arr[]=array(
                    'time'=>$d,
                    'number'=>$number
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
     * 退出登录
     */
    public function outloginserveruser(){

    }

    
}