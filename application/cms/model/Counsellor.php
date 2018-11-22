<?php


namespace app\cms\model;

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


    public static function getCounsellorList($id)
    {
        $counsellor =  db('member')->alias('a')->field('a.*,b.*,b.id as bid,a.id as aid')->join(' member_counsellor b',' b.memberid = a.id','LEFT')->where(array('a.id'=>$id))->find();

        return $counsellor;
    }

    
    public  function getIncomeAttr($v,$data)
    {
        return number_format(db('trade')->where(['mid'=>$data['id'],'status'=>1])->sum('payment'),1);
    }
    public  function getVerifystatusAttr($v,$data)
    {
        return $data['status'];
    }

    public  function getAgencyAttr($v,$data)
    {
        $map['id'] = $data['shopid'];
        return db('shop_agency')->where($map)->value('title');

    }

    public  function getOffnumAttr($v,$data)
    {
        $map['chart'] = 'facechart';
        $map['status'] = 1;
        $map['mid'] = $data['id'];
        return db('trade')->where($map)->count();

    }

    public  function getOffincomeAttr($v,$data)
    {
        $map['chart'] = 'facechart';
        $map['status'] = 1;
        $map['mid'] = $data['id'];
        return number_format(db('trade')->where($map)->sum('payment'),1);

    }

    public  function getWordnumAttr($v,$data)
    {
        $map['chart'] = 'wordchart';
        $map['status'] = 1;
        $map['mid'] = $data['id'];
        return db('trade')->where($map)->count();

    }

    public  function getWordincomeAttr($v,$data)
    {
        $map['chart'] = 'wordchart';
        $map['status'] = 1;
        $map['mid'] = $data['id'];
        return number_format(db('trade')->where($map)->sum('payment'),1);

    }

    public  function getVoicenumAttr($v,$data)
    {
        $map['chart'] = 'speechchart';
        $map['status'] = 1;
        $map['mid'] = $data['id'];
        return db('trade')->where($map)->count();

    }

    public  function getVoiceincomeAttr($v,$data)
    {
        $map['chart'] = 'speechchart';
        $map['status'] = 1;
        $map['mid'] = $data['id'];
        return number_format(db('trade')->where($map)->sum('payment'),1);

    }

    public  function getTalkincomeAttr($v,$data)
    {
        $map['paytype'] = 0;
        $map['status'] = 1;
        $map['mid'] = $data['id'];
        return number_format(db('trade')->where($map)->sum('payment'),1);

    }

    public  function getClassnumeAttr($v,$data)
    {
        $map['adminid'] = $data['id'];
        return db('shop_classes_allot')->alias('a')->join('cms_classes b',' b.id = a.classid','LEFT')->where($map)->count();

    }

    public  function getClassincomeAttr($v,$data)
    {
        // $map['mid'] = $data['id'];
        // return db('shop_classes_allot')->alias('a')->join('cms_classes b',' b.id = a.classid','LEFT')->where($map)->count();
        return 0;

    }

    public  function getActivenumAttr($v,$data)
    {
        $map['adminid'] = $data['id'];
        return db('shop_classes_allot')->alias('a')->join('cms_active b',' b.id = a.classid','LEFT')->where($map)->count();

    }

    public  function getActiveincomeAttr($v,$data)
    {
        // $map['mid'] = $data['id'];
        // return db('shop_classes_allot')->alias('a')->join('cms_classes b',' b.id = a.classid','LEFT')->where($map)->count();
        return 0;

    }

    public  function getClacincomeAttr($v,$data)
    {
        // $map['mid'] = $data['id'];
        // return db('shop_classes_allot')->alias('a')->join('cms_classes b',' b.id = a.classid','LEFT')->where($map)->count();
        return 0;

    }

    public  function getTotalAttr($v,$data)
    {
        // $map['mid'] = $data['id'];
        // return db('shop_classes_allot')->alias('a')->join('cms_classes b',' b.id = a.classid','LEFT')->where($map)->count();
        return 0;

    }


    public  function getOndatenumsAttr($v,$data)
    {
        $tids = db('trade')->where(['mid'=>$data['id'],'paytype'=>0])->column('id');
        $map['tid'] = array('in',$tids);
        $num =  db('calendar')->where($map)->count();
        if ($num>0) {
            db('member')->where(['id'=>$data['id']])->update(['ondatenum'=>$num]);
        }
        
        return $num;
    }

}
