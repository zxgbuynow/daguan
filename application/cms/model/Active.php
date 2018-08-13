<?php


namespace app\cms\model;

use think\Model as ThinkModel;

/**
 * 菜单模型
 * @package app\cms\model
 */
class Active extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__CMS_ACTIVE__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    public  function getStatustextAttr($v,$data)
    {
        $now = time();
        $end = $data['endtime'];
        if ($end<$now) {
            return '已结束';
        }else{
            $start = $data['start_time'];
            if ($start<$now) {
                return '进行中';
            }else{
                return '未开始';
            }
            
        }
    }

    public  function getAgencyAttr($v,$data)
    {
        $map['id'] = $data['shopid'];
        return db('shop_agency')->where($map)->value('title');

    }


    public  function getNumsAttr($v,$data)
    {
        $map['classid'] = $data['id'];
        $map['paytype'] = 3;
        $map['status'] = 1;
        return db('trade')->where($map)->count();
    }

    public  function getCounsollorAttr($v,$data)
    {
        $allot = db('shop_acitve_allot')->where(['classid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }
        $s['scale'] = 100 - (floatval($allot['sscale'])+floatval($allot['mscale']));//比例

        $map['paytype'] = 3;
        $map['status'] = 1;
        $map['classid'] = $data['id'];
        $total = db('trade')->where($map)->sum('payment');
        return  number_format($total*$s['scale']/100,1);
    }

    public  function getShopAttr($v,$data)
    {   
        $allot = db('shop_acitve_allot')->where(['classid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }
        $map['classid'] = $data['id'];
        $map['paytype'] = 3;
        $map['status'] = 1;
        return number_format((db('trade')->where($map)->sum('payment'))*floatval($allot['sscale'])/100,1);
    }

    public  function getAdminAttr($v,$data)
    {
        $allot = db('shop_acitve_allot')->where(['classid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }
        $map['classid'] = $data['id'];
        $map['paytype'] = 3;
        $map['status'] = 1;
        return number_format((db('trade')->where($map)->sum('payment'))*floatval($allot['sscale'])/100,1);
    } 

    public  function getCounsollerAttr($v,$data)
    {
        $allot = db('shop_acitve_allot')->where(['classid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }

        $s = $allot['adminid']+','+$allot['tearchid']+','+$allot['coachid'];
        $s = array_unique(explode(',', $s));

        $map['id'] = array('in',implode(',', $s));

        return db('member')->where($map)->value('nickname');
    }


    public  function getAnumsAttr($v,$data)
    {
        $map['classid'] = $data['id'];
        $map['paytype'] = 3;
        $map['status'] = 1;
        return db('trade')->where($map)->count();
    }

    public  function getAcounsollorAttr($v,$data)
    {
        $allot = db('shop_acitve_allot')->where(['classid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }
        $s['scale'] = 100 - (floatval($allot['sscale'])+floatval($allot['mscale']));//比例

        $map['paytype'] = 3;
        $map['status'] = 1;
        $map['classid'] = $data['id'];
        $total = db('trade')->where($map)->sum('payment');
        return  number_format($total*$s['scale']/100,1);
    }

    public  function getAshopAttr($v,$data)
    {   
        $allot = db('shop_acitve_allot')->where(['classid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }
        $map['classid'] = $data['id'];
        $map['paytype'] = 3;
        $map['status'] = 1;
        return number_format((db('trade')->where($map)->sum('payment'))*floatval($allot['sscale'])/100,1);
    }

    public  function getAadminAttr($v,$data)
    {
        $allot = db('shop_acitve_allot')->where(['classid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }
        $map['classid'] = $data['id'];
        $map['paytype'] = 3;
        $map['status'] = 1;
        return number_format((db('trade')->where($map)->sum('payment'))*floatval($allot['sscale'])/100,1);
    } 

    public  function getAcounsollerAttr($v,$data)
    {
        $allot = db('shop_acitve_allot')->where(['classid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }

        $s = $allot['adminid']+','+$allot['tearchid']+','+$allot['coachid'];
        $s = array_unique(explode(',', $s));

        $map['id'] = array('in',implode(',', $s));

        return db('member')->where($map)->value('nickname');
    }
}