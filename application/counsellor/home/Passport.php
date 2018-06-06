<?php
namespace app\counsellor\home;
use app\common\controller\Common;
use \think\Request;
use \think\Db;
use think\Model;
use think\helper\Hash;

/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Passport extends Common
{
    public function login()
    {
        return $this->fetch(); // 渲染模板
    }

    public function register()
    {
        return $this->fetch(); // 渲染模板
    }

    public function verificationsms()
    {
        return $this->fetch(); // 渲染模板
    }

    public function forgetPassword()
    {
        return $this->fetch(); // 渲染模板
    }

    
}
