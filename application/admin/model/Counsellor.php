<?php


namespace app\admin\model;

use think\Model;
use think\helper\Hash;
use think\Db;

/**
 * 后台用户模型
 * @package app\admin\model
 */
class Counsellor extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__MEMBER__';

     // 自动写入时间戳
    protected $autoWriteTimestamp = true;

}
