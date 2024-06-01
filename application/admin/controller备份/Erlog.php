<?php


namespace app\admin\controller;

use app\admin\model\Admins;
use app\admin\model\WechatPlatform;
use app\admin\model\WechatService;
use think\Db;
use think\Paginator;
use app\Common;

class Erlog extends Base{
    
    public function index(){
        //halt('index');
        $login = $_SESSION['Msg'];
        $service_id=$login['service_id'];
        
        $lister=db('wolive_servererlog')
        ->where(['service_id'=>$service_id])
        ->order('id desc')
        ->paginate(9);
        //halt($lister);
        $page = $lister->render();
        
        $this->assign('lister',$lister);
        $this->assign('part', "操作日志");
        $this->assign('page', $page);
        
        return $this->fetch();
    }
}