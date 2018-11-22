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
        return db('member')->where(['id'=>$data['memberid']])->value('nickname');

    }

    public  function getTidsAttr($v,$data)
    {
       return db('trade')->where(['id'=>$data['tid']])->value('tid');

    }
    public  function getPlaceAttr($v,$data)
    {
       return db('trade')->where(['id'=>$data['tid']])->value('place');

    }


    public  function getAddressAttr($v,$data)
    {
        $shopid = db('trade')->where(['id'=>$data['tid']])->value('shopid');

        return db('shop_agency')->where(['id'=>$shopid])->value('city');

    }

    public  function getChartAttr($v,$data)
    {
      $data = db('trade')->where(['id'=>$data['tid']])->value('chart');
      switch ($data) {
        case 'wordchart':
          return '文字咨询';
          break;
        case 'speechchart':
          return '语音咨询';
          break;
        case 'facechart':
          return '面对面咨询';
          break;
        
        default:
          return '视频咨询';
          break;
      }
    }


}
