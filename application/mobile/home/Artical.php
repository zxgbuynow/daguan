<?php
namespace app\mobile\home;
use app\common\controller\Common;
use \think\Request;
use \think\Db;
use think\Model;
use think\helper\Hash;

/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Artical extends Common
{
    public function index()
    {
        return $this->fetch(); // 渲染模板
    }

    public function detail()
    {
        return $this->fetch(); // 渲染模板
    }

    public function cate()
    {
        return $this->fetch(); // 渲染模板
    }

    public function tlist()
    {
        return $this->fetch(); // 渲染模板
    }
    public function volist()
    {
        return $this->fetch(); // 渲染模板
    }

    
}
