<?php


namespace app\index\controller;

use \think\Request;
use \think\Db;
use think\Model;

/**
 * crontab控制器
 * @package app\index\controller
 */
class crontab
{
    public function index()
    {
       //发
        
       error_log('crontab',3,'/data/httpd/daguan/c.log');
       // return $this->fetch(); // 渲染模板
       
    }
}
