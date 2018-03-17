<?php


namespace app\shop\validate;

use think\Validate;

/**
 * 钩子验证器
 * @package app\admin\validate
 * @author 蔡伟明 <314013107@qq.com>
 */
class Hook extends Validate
{
    //定义验证规则
    protected $rule = [
        'name|钩子名称'  => 'require|regex:^[a-zA-Z]\w{0,39}$|unique:shop_hook'
    ];

    //定义验证提示
    protected $message = [
        'name.regex' => '钩子名称由字母和下划线组成',
    ];
}
