<?php


namespace app\counsellor\home;

use app\common\controller\Common;

/**
 * mobile公共控制器
 * @package app\index\controller
 */
class Home extends Common
{
    /**
     * 初始化方法
     * @author zg
     */
    protected function _initialize()
    {
        // 判断是否登录，并定义用户ID常量
        $this->is_counsellor_signin();
        //赋值会员信息
        $this->assign('__Cuser', $this->is_counsellor_signin());
    }
    
    /**
     * [is_mobile_signin 登录检测]
     * @return boolean [description]
     */
    function is_counsellor_signin()
    {
        $user = session('user_counsellor_auth');
        if (empty($user)) {
            $this->redirect('passport/login');
        }else{
            return $user;
        }
    }
}
