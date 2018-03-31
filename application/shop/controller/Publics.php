<?php


namespace app\shop\controller;

use app\common\controller\Common;
use app\shop\model\User as UserModel;
use think\Hook;

/**
 * 用户公开控制器，不经过权限认证
 * @package app\user\admin
 */
class Publics extends Common
{
    /**
     * 用户登录
     * @author zg
     * @return mixed
     */
    public function signin()
    {
        if ($this->request->isPost()) {
            // 获取post数据
            $data = $this->request->post();
            $rememberme = isset($data['remember-me']) ? true : false;

            // 登录钩子
            $hook_result = Hook::listen('signin', $data);
            if (!empty($hook_result) && true !== $hook_result[0]) {
                $this->error($hook_result[0]);
            }

            // 验证数据
            $result = $this->validate($data, 'User.signin');
            if(true !== $result){
                // 验证失败 输出错误信息
                $this->error($result);
            }

            // 验证码
            if (config('captcha_signin')) {
                $captcha = $this->request->post('captcha', '');
                $captcha == '' && $this->error('请输入验证码');
                if(!captcha_check($captcha, '', config('captcha'))){
                    //验证失败
                    $this->error('验证码错误或失效');
                };
            }

            // 登录
            $UserModel = new UserModel;
            $uid = $UserModel->login($data['username'], $data['password'], $rememberme);
            if ($uid) {
                // 记录行为
                action_shop_log('user_signin', 'shop_user', $uid, $uid);
                $this->success('登录成功', url('shop/index/index'));
            } else {
                $this->error($UserModel->getError());
            }
        } else {
            $hook_result = Hook::listen('signin_sso');
            if (!empty($hook_result) && true !== $hook_result[0]) {
                if (isset($hook_result[0]['url'])) {
                    $this->redirect($hook_result[0]['url']);
                }
                if (isset($hook_result[0]['error'])) {
                    $this->error($hook_result[0]['error']);
                }
            }

            if (is_signin()) {
                $this->redirect('shop/index/index');
            } else {
                return $this->fetch();
            }
        }
    }

    /**
     * 退出登录
     * @author zg
     */
    public function signout()
    {
        $hook_result = Hook::listen('signout_sso');
        if (!empty($hook_result) && true !== $hook_result[0]) {
            if (isset($hook_result[0]['url'])) {
                $this->redirect($hook_result[0]['url']);
            }
            if (isset($hook_result[0]['error'])) {
                $this->error($hook_result[0]['error']);
            }
        }

        session(null);
        cookie('suid', null);
        cookie('shop_signin_token', null);

        $this->redirect('signin');
    }
}
