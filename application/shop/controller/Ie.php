<?php


namespace app\shop\controller;

use app\common\controller\Common;

/**
 * ie提示页面控制器
 * @package app\shop\controller
 */
class Ie extends Common
{
    /**
     * 显示ie提示
     * @author zg
     * @return mixed
     */
    public function index(){
        // ie浏览器判断
        if (get_browser_type() == 'ie') {
            return $this->fetch();
        } else {
            $this->redirect('admin/index/index');
        }
    }
}