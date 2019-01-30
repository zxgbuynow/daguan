<?php


namespace app\cms\validate;

use think\Validate;

/**
 * 菜单验证器
 * @package app\cms\validate
 */
class Addr extends Validate
{
    //定义验证规则
    protected $rule = [
        'shotnm|简称'      => 'require',
        'fullnm|详细地址'      => 'require',
    ];

    //定义验证提示
    protected $message = [
        'shotnm.require' => '简称不能为空',
        'fullnm.require' => '详细地址不能为空',
    ];
}
