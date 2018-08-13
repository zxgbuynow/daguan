<?php


namespace app\cms\model;

use think\Model;
use think\helper\Hash;
use think\Db;

/**
 * 机构模型
 * @package app\admin\model
 */
class Calendar extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CALENDAR__';

     // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    public  function getAgencyAttr($v,$data)
    {
       $shopid = db('trade')->where(['id'=>$data['tid']])->value('shopid');

       return db('shop_agency')->where(['id'=>$shopid])->value('title');
    }

    public  function getUsernameAttr($v,$data)
    {
       $memberid = db('trade')->where(['id'=>$data['tid']])->value('memberid');
       return db('member')->where(['id'=>$memberid])->value('nickname');

    }

    public  function getCounsollorAttr($v,$data)
    {
        $mid = db('trade')->where(['id'=>$data['tid']])->value('mid');

        return db('member')->where(['id'=>$mid])->value('nickname');

    }

    public  function getAddressAttr($v,$data)
    {
        $shopid = db('trade')->where(['id'=>$data['tid']])->value('shopid');

        return db('shop_agency')->where(['id'=>$shopid])->value('city');

    }


}
