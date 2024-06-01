<?php


namespace app\admin\controller;

use app\admin\iplocation\Ip;

/**
 * 基础验证是否登录.
 */
class Shows extends Ip
{

    protected $base_root = null;
    public $wechat_platform=null;
    public $open_id='';
    /**
     * 验证session.
     *
     * @return void
     */
    public function index($ip='220.167.23.149')
    {
        
         $res=Ip::find($ip);
         echo json_encode($res,JSON_UNESCAPED_UNICODE);
    }

}