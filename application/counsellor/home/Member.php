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
class Member extends Home
{
    public function member()
    {
        return $this->fetch(); // 渲染模板
    }

    public function setting()
    {
        return $this->fetch(); // 渲染模板
    }

    public function msg()
    {
        return $this->fetch(); // 渲染模板
    }
    public function msg_list()
    {
        return $this->fetch(); // 渲染模板
    }
    public function income()
    {
        return $this->fetch('member/income/index'); // 渲染模板
    }

    public function safe()
    {
        return $this->fetch('member/safe/safe'); // 渲染模板
    }

    public function point()
    {
        return $this->fetch('member/point/integral'); // 渲染模板
    }
    
    public function checkpassword()
    {
        return $this->fetch('member/safe/checkpassword'); // 渲染模板
    }
    public function logpassword()
    {
        return $this->fetch('member/safe/logpassword'); // 渲染模板
    }
}
