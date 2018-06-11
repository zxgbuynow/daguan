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
       //发短信
       $data['msg'] = 'crontab'.time();
       db('test')->insert($data);   
    }
}
