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
class Clac extends Home
{
    public function index()
    {
        return $this->fetch(); // 渲染模板
    }

    public function detail()
    {
        return $this->fetch(); // 渲染模板
    }

    public function manager()
    {
        return $this->fetch(); // 渲染模板
    }

    public function userlist()
    {
        return $this->fetch(); // 渲染模板
    }

    public function ulist()
    {
        return $this->fetch(); // 渲染模板
    }

    public function userd()
    {
        return $this->fetch(); // 渲染模板
    }
   
}
