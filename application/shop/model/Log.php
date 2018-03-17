<?php


namespace app\shop\model;

use think\Model;

/**
 * 日志记录模型
 * @package app\shop\model
 */
class Log extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SHOP_LOG__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取所有日志
     * @param array $map 条件
     * @param string $order 排序
     * @author 蔡伟明 <314013107@qq.com>
     * @return mixed
     */
    public static function getAll($map = [], $order = '')
    {
        $data_list = self::view('shop_log', true)
            ->view('shop_action', 'title,module', 'shop_action.id=shop_log.action_id', 'left')
            ->view('shop_user', 'username', 'shop_user.id=shop_log.user_id', 'left')
            ->view('shop_module', ['title' => 'module_title'], 'shop_module.name=shop_action.module')
            ->where($map)
            ->order($order)
            ->paginate();
        return $data_list;
    }
}