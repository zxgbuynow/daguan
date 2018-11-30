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
class Counsellor extends Common
{
    public function index()
    {
        return $this->fetch(); // 渲染模板
    }

    public function counsellor()
    {
        return $this->fetch(); // 渲染模板
    }
    public function evaluate()
    {
        return $this->fetch('eve_form'); // 渲染模板
    }

    public function detail()
    {
        return $this->fetch(); // 渲染模板
    }
}
