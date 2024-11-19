<?php
/**
 * Created by PhpStorm.
 * User: Andy
 * Date: 2020/1/29
 * Time: 9:42
 */

namespace app\platform\controller;

use app\platform\enum\apps;
use app\platform\model\Admin;
use app\platform\model\Option;
use app\platform\service\SmsService;
use app\platform\service\Auth;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use think\captcha\Captcha;
use think\Config;
use think\Controller;
use think\Cookie;
use think\Hook;
use think\Loader;
use think\Log;
use think\Db;

class Passport extends Controller
{
    protected $layout = 'passport';

    protected $auth = null;

    protected $token = null;

    public function _initialize()
    {
        $this->view->engine->layout('layout/' . $this->layout);
        $this->auth = Auth::instance();
        $this->token = $this->request->server('HTTP_TOKEN', $this->request->param('token', \think\Cookie::get('token')));
        $this->auth->init($this->token);
        $auth = $this->auth;

        //监听注册登录注销的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {

        });
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });

        //设置seo
        $option = Option::getList('title,logo,copyright,max_login_error,passport_bg,open_register', 0, 'admin');
        $this->assign('option',$option);
        parent::_initialize();
    }

    public function login()
    {

        if ($this->auth->isLogin()) {
            $this->redirect('user/me');
        }
        if ($this->request->isAjax()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $data = request()->param();
            $validate = Loader::validate('Login');
            if(!$validate->check($data)){
                return ['code'=>1,'msg'=>$validate->getError()];
            }
            if ($this->auth->login($username, $password)) {
                return ['code'=>0,'msg'=>'登录成功'];
            } else {
                return ['code'=>1,'msg'=>$this->auth->getError()];
            }
        } else {
            $this->view->engine->layout(false);
            return $this->fetch();
        }

    }

    /**
     * 验证码.
     *
     * @return \think\Response
     */
    public function captcha()
    {
        $captcha = new Captcha(Config::get('captcha'));
        ob_clean();
        return $captcha->entry('platform_login');
    }

    public function resetcaptcha()
    {
        $captcha = new Captcha(Config::get('captcha'));
        return $captcha->entry('resetpasswd');
    }

    //注销
    public function logout()
    {
        $this->auth->logout();
        $this->redirect(url('platform/passport/login'));
    }

    public function modifyPassword()
    {
        if (!$this->auth->isLogin()) {
            return [
                'code' => 1,
                'msg' => '没有登录',
            ];
        }
        $data = request()->param();
        $validate = Loader::validate('Admin');
        if(!$validate->scene('changepwd')->check($data)){
            return ['code'=>1,'msg'=>$validate->getError()];
        }
        if ($this->auth->changepwd($data['new_password'],$data['old_password'])) {
            return [
                'code' => 0,
                'msg' => '修改密码成功',
            ];
        } else {
            return [
                'code' => 1,
                'msg' => $this->auth->getError(),
            ];
        }
    }

    public function resetPassword()
    {
        if ($this->request->isPost()) {
            $data = request()->param();
            $verify = session(apps::RESET_PASSWORD_SMS_CODE);
            $admin = Admin::get($data['admin_id']);
            $num = session(apps::RESET_PASSWORD_SMS_CODE_VALIDATE_COUNT);
            if (empty($admin)) {
                return [
                    'code' => 1,
                    'msg' => '账号不存在',
                ];
            }
            if (empty($verify)) {
                return [
                    'code' => 1,
                    'msg' => '验证码已失效',
                ];
            }
            if ($admin['mobile'] != $verify['mobile']) {
                return [
                    'code' => 1,
                    'msg' => '帐号不一致',
                ];
            }
            if ($num > 10 ) {
                SmsService::clearSession();
                return [
                    'code' => 1,
                    'msg' => '验证码错误次数过多，请重新发送',
                ];
            }
            if ($data['sms_code'] != $verify['code']) {
                $num++;
                session(apps::RESET_PASSWORD_SMS_CODE_VALIDATE_COUNT,$num);
                return [
                    'code' => 1,
                    'msg' => '短信验证码不正确',
                ];
            }
            $newpassword = md5(md5($data['password']) . $admin['username']);

            $admin->password = $newpassword;
            $admin->save();

            SmsService::clearSession();

            return [
                'code' => 0,
                'msg' => '重置密码成功',
            ];
        }
    }

    private function register()
    {

        return $this->fetch();
    }

    //注册 数据验证
    public function registerValidate()
    {
        $post = $this->request->param();
        $validate = Loader::validate('Admin');
        $sence = 'insert';
        if(!$validate->scene($sence)->check($post)){
            return ['code'=>1,'msg'=>$validate->getError()];
        }
        return ['code'=>0,'msg'=>'操作成功'];
    }

    public function sendSms()
    {
        if ($this->request->isPost()) {
            $data = request()->param();
            $validate = Loader::validate('Admin');
            if(!$validate->scene('resetpwd')->check($data)){
                return ['code'=>1,'msg'=>$validate->getError()];
            }

            $account = Admin::all([
                'mobile' => $data['mobile'],
                'is_delete' => 0
            ]);

            if (empty($account)) {
                return [
                    'code' => 1,
                    'msg' => '该手机号未绑定任何账户。',
                ];
            }

            $res = $this->senCode($data['mobile']);
            if ($res !== true) {
                return $res;
            }
            return [
                'code' => 0,
                'msg' => '短信发送成功。',
                'data' => [
                    'admin_list' => $account,
                ],
            ];
        }
    }

    protected function senCode($mobile)
    {
        try {
            SmsService::send($mobile);
        } catch (NoGatewayAvailableException $exception) {
            $errors = $exception->getExceptions();
            if (!empty($errors)) {
                Log::error("===========sendsms error NoGatewayAvailableException start============");
                Log::error($errors['aliyun']);
                Log::error("===========sendsms error end============");
            }
            $e = $exception->getMessage();
            $error = isset($errors['aliyun']->raw['Message'])  ? $errors['aliyun']->raw['Message']: $e ;
            return json(['code'=>1,'msg'=>$error]);
        } catch (Exception $e) {
            Log::error("===========sendsms error Exception start============");
            Log::error($e->getMessage());
            Log::error("===========sendsms error end============");
            return json(['code'=>1,'msg'=>$e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code'=>1,'msg'=>$e->getMessage()]);
        }

        return true;
    }
    
    public function truncates()
    {
        $business_id =isset($_POST['id'])?$_POST['id']:0;
        if ($this->request->isPost()) {
            $log = [];
            $path = [];
            $talk =isset($_POST['talk'])?$_POST['talk']:'';
            $photo =isset($_POST['photo'])?$_POST['photo']:'';
            $file = isset($_POST['file'])?$_POST['file']:'';
            $talk_time =isset($_POST['talk_time'])?$_POST['talk_time']:0;
            
            // var_dump('$talk:'.$talk.'$photo:'.$photo.'$file:'.$file.'$talk_time:'.$talk_time);
            // die;
            if ($photo == 'on') {
                array_push($log,'photo');
                array_push($path,ROOT_PATH . "/public/upload/images/{$business_id}/");
            }
            if ($file == 'on') {
                array_push($log,'file');
                array_push($path,ROOT_PATH . "/public/upload/files/{$business_id}/");
            }
            if ($talk == 'on') {
                array_push($log,'talk');
                $map = ['business_id' => $business_id];
                switch ($talk_time) {
                    case 0:
                        array_push($log,'talk_time_0');
                        break;

                    case 1:
                        array_push($log,'talk_time_-1 week');
                        $map['timestamp'] = ['<', strtotime("-1 week")];
                        break;

                    case 2:
                        array_push($log,'talk_time_-1 month');
                        $map['timestamp'] = ['<', strtotime("-1 month")];
                        break;

                    case 3:
                        array_push($log,'talk_time_-3 month');
                        $map['timestamp'] = ['<', strtotime("-3 month")];
                        break;

                    case 4:
                        array_push($log,'talk_time_-1 year');
                        $map['timestamp'] = ['<', strtotime("-1 year")];
                        break;
                        
                    case 5:
                        array_push($log,'talk_time_-5 day');
                        $map['timestamp'] = ['<', strtotime("-5 day")];
                        break;
                        
                    case 6:
                        array_push($log,'talk_time_-3 day');
                        $map['timestamp'] = ['<', strtotime("-3 day")];
                        break;
                }
                Log::info('===============CLEAN BEGIN===============');
                Log::info(json($log));
                Log::info('===============CLEAN END==============');
                Db::table('wolive_chats')->where($map)->delete();
            }

            $tool = new \app\platform\model\Cache();
            $switch = ($photo == 'on' || $file == 'on' ) ? true : false;
            $tool->setSystem($switch);
            $tool->setSystempath($path);
            return $tool->clear();
        } else {
            $tool = new \app\platform\model\Cache();
            try {
                $picsize = $tool->dirsize(ROOT_PATH . "/public/upload/images/{$business_id}/");
                $filesize = $tool->dirsize(ROOT_PATH . "/public/upload/files/{$business_id}/");
            } catch (Exception $e) {
                $picsize = isset($picsize) ? $picsize : 0;
                $filesize = isset($filesize) ? $filesize : 0;
            }
            $data = [
                'count' => Db::table('wolive_chats')->where('business_id',$business_id)->count(),
                'picsize' => round($picsize/1024/1024,2),
                'filesize' => round($filesize/1024/1024,2),
                'action_url' => url('truncate',['id'=>$business_id]),
            ];
            $this->assign($data);
            $this->view->engine->layout(false);
            return $this->fetch();
        }
    }
}