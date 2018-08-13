<?php


namespace app\cms\model;

use think\Model;
use think\helper\Hash;
use think\Db;

/**
 * 机构模型
 * @package app\admin\model
 */
class Agency extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SHOP_AGENCY__';

     // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    public  function getIncomeAttr($v,$data)
    {
       return number_format(db('trade')->where(['shopid'=>$data['id'],'status'=>1])->sum('payment'));
    }

    public  function getRunumAttr($v,$data)
    {   

       return db('member')->where(['shopid'=>$data['id'],'type'=>0])->count();
    }

    public  function getDunumAttr($v,$data)
    {   

       return db('member')->where(['shopid'=>$data['id'],'status'=>'0','type'=>0])->count();
    }

    public  function getOnunumAttr($v,$data)
    {   

       return db('trade')->where(['shopid'=>$data['id'],'chart'=>'facechart'])->group('memberid')->count();
    }

    public  function getOfunumAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['chart'] = array('in','speechchart,videochart,wordchart');
        return db('trade')->where($map)->group('memberid')->count();
    }

    public  function getClunumAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['paytype'] = 2;
        return db('trade')->where($map)->group('memberid')->count();
    }

    public  function getAcunumAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['paytype'] = 3;
        return db('trade')->where($map)->group('memberid')->count();
    }

    public  function getDcnumAttr($v,$data)
    {   

       return db('member')->where(['shopid'=>$data['id'],'type'=>1,'status'=>0])->count();
    }

    public  function getRcnumAttr($v,$data)
    {   

       return db('member')->where(['shopid'=>$data['id'],'type'=>1,'status'=>1])->count();
    }
    
    public  function getBycnumAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['paytype'] = 0;
        return db('trade')->where($map)->group('mid')->count();
    }

    public  function getRnumAttr($v,$data)
    {   

        return 0;
    }

    public  function getSeeknumsAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['paytype'] = 0;
        $map['status'] = 1;
        return db('trade')->where($map)->group('shopid')->count();
    }

    public  function getWordnumsAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['paytype'] = 0;
        $map['status'] = 1;
        $map['chart'] = 'wordchart';
        return db('trade')->where($map)->group('shopid')->count();
    }
    public  function getVoicenumsAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['paytype'] = 0;
        $map['status'] = 1;
        $map['chart'] = 'speechchart';
        return db('trade')->where($map)->group('shopid')->count();
    }
    public  function getVideonumsAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['paytype'] = 0;
        $map['status'] = 1;
        $map['chart'] = 'videochart';
        return db('trade')->where($map)->group('shopid')->count();
    }
    public  function getFacenumsAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['paytype'] = 0;
        $map['status'] = 1;
        $map['chart'] = 'facechart';
        return db('trade')->where($map)->group('shopid')->count();
    }
    public  function getWifinumsAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['paytype'] = 0;
        $map['status'] = 1;
        $map['chart'] = array('in','wordchart,speechchart,videochart');
        return db('trade')->where($map)->group('shopid')->count();
    }

    public  function getCincomeAttr($v,$data)
    {   

        $map['shopid'] = $data['id'];
        $map['paytype'] = 0;
        $map['status'] = 1;
        return number_format(db('trade')->where($map)->group('shopid')->sum('payment'),1);
    }

    public  function getSincomeAttr($v,$data)
    {   

        // $map['shopid'] = $data['id'];
        // $map['paytype'] = 0;
        // $map['status'] = 1;
        // return number_format(db('trade')->where($map)->group('shopid')->sum('payment'),1);
        return 0;
    }

    public  function getAincomeAttr($v,$data)
    {   

        // $map['shopid'] = $data['id'];
        // $map['paytype'] = 0;
        // $map['status'] = 1;
        // return number_format(db('trade')->where($map)->group('shopid')->sum('payment'),1);
        return 0;
    }
    public  function getClnumsAttr($v,$data)
    {   
        $map['shopid'] = $data['id'];
        $map['paytype'] = 2;
        $map['status'] = 1;
        return db('trade')->where($map)->group('shopid')->count();
    }

    public  function getClcnumsAttr($v,$data)
    {   
        $allot = db('shop_classes_allot')->where(['shopid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }

        $s['scale'] = 100 - (floatval($allot['sscale'])+floatval($allot['mscale']));//比例

        $map['paytype'] = 2;
        $map['status'] = 1;
        $map['shopid'] = $data['id'];
        $total = db('trade')->where($map)->sum('payment');

        return number_format((db('trade')->where($map)->sum('payment'))*floatval($s['scale'])/100,1);
    }

    public  function getClsnumsAttr($v,$data)
    {   
        $allot = db('shop_classes_allot')->where(['shopid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }

        $map['paytype'] = 2;
        $map['status'] = 1;
        $map['shopid'] = $data['id'];
        $total = db('trade')->where($map)->sum('payment');

        return number_format((db('trade')->where($map)->sum('payment'))*floatval($allot['sscale'])/100,1);
    }

    public  function getClanumsAttr($v,$data)
    {   
        $allot = db('shop_classes_allot')->where(['shopid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }

        $map['paytype'] = 2;
        $map['status'] = 1;
        $map['shopid'] = $data['id'];
        $total = db('trade')->where($map)->sum('payment');

        return number_format((db('trade')->where($map)->sum('payment'))*floatval($allot['mscale'])/100,1);
    }

    //活动
    public  function getAcnumsAttr($v,$data)
    {   
        $map['shopid'] = $data['id'];
        $map['paytype'] = 3;
        $map['status'] = 1;
        return db('trade')->where($map)->group('shopid')->count();
    }

    public  function getAccincomeAttr($v,$data)
    {   
        $allot = db('shop_acitve_allot')->where(['shopid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }

        $s['scale'] = 100 - (floatval($allot['sscale'])+floatval($allot['mscale']));//比例

        $map['paytype'] = 3;
        $map['status'] = 1;
        $map['shopid'] = $data['id'];
        $total = db('trade')->where($map)->sum('payment');

        return number_format((db('trade')->where($map)->sum('payment'))*floatval($s['scale'])/100,1);
    }

    public  function getAcsincomeAttr($v,$data)
    {   
        $allot = db('shop_acitve_allot')->where(['shopid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }

        $map['paytype'] = 3;
        $map['status'] = 1;
        $map['shopid'] = $data['id'];
        $total = db('trade')->where($map)->sum('payment');

        return number_format((db('trade')->where($map)->sum('payment'))*floatval($allot['sscale'])/100,1);
    }

    public  function getAcaincomeAttr($v,$data)
    {   
        $allot = db('shop_acitve_allot')->where(['shopid'=>$data['id']])->find();

        if (!$allot) {
            return 0;
        }

        $map['paytype'] = 3;
        $map['status'] = 1;
        $map['shopid'] = $data['id'];
        $total = db('trade')->where($map)->sum('payment');

        return number_format((db('trade')->where($map)->sum('payment'))*floatval($allot['mscale'])/100,1);
    }

    public  function getTotalsincomeAttr($v,$data)
    {   
        
        $map['paytype'] = array('in','0,2,3');
        $map['status'] = 1;
        $map['shopid'] = $data['id'];
        $total = db('trade')->where($map)->sum('payment');

        return number_format($total,1);
    }

    public  function getTotalaincomeAttr($v,$data)
    {   
        $map['paytype'] = array('in','0,2,3');
        $map['status'] = 1;
        $map['shopid'] = $data['id'];
        $total = db('trade')->where($map)->sum('payment');

        return number_format($total,1);
    }



}
