<?php


namespace app\shop\model;

use think\Model;
use think\helper\Hash;
use think\Db;

/**
 * 后台用户模型
 * @package app\admin\model
 */
class Member extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__MEMBER__';

     // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    public static function getCounsellorList($id)
    {
        $counsellor =  db('member')->alias('a')->field('a.*,b.*,b.id as bid')->join(' member_counsellor b',' b.memberid = a.id','LEFT')->where(array('a.id'=>$id))->find();

        return $counsellor;
    }

}
