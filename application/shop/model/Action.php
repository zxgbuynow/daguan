<?php


namespace app\shop\model;

use think\Model;

/**
 * 日志模型
 * @package app\shop\model
 */
class Action extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SHOP_ACTION__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
}