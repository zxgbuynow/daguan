<?php
namespace app\mobile\home;
use app\common\controller\Common;
use \think\Request;
use \think\Db;
use think\Model;
use think\helper\Hash;

/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Member extends Home
{
    public function member()
    {
        return $this->fetch(); // 渲染模板
    }

    public function setting()
    {
        return $this->fetch(); // 渲染模板
    }

    public function trade()
    {
        return $this->fetch(); // 渲染模板
    }
    
    public function safe()
    {
        return $this->fetch('member/safe/safe'); // 渲染模板
    }

    public function point()
    {
        return $this->fetch('member/point/integral'); // 渲染模板
    }
    public function coupon()
    {
        return $this->fetch('member/point/coupon'); // 渲染模板
    }
    public function nickname()
    {
        return $this->fetch('member/userinfo/nickname'); // 渲染模板
    }
    public function avar()
    {
        return $this->fetch('member/userinfo/avar'); // 渲染模板
    }
    public function gender()
    {
        return $this->fetch('member/userinfo/gender'); // 渲染模板
    }
    public function checkpassword()
    {
        return $this->fetch('member/safe/checkpassword'); // 渲染模板
    }
    public function logpassword()
    {
        return $this->fetch('member/safe/logpassword'); // 渲染模板
    }
    public function clac()
    {
        return $this->fetch('member/clac/index'); // 渲染模板
    }
    public function claceval()
    {
        return $this->fetch('member/clac/claceval'); // 渲染模板
    }
    
    public function fav()
    {
        return $this->fetch('member/fav/index'); // 渲染模板
    }
    public function favarticle()
    {
        return $this->fetch('member/fav/article'); // 渲染模板
    }
    public function favclac()
    {
        return $this->fetch('member/fav/clac'); // 渲染模板
    }
    public function complaint()
    {
        return $this->fetch('member/complaint/complaint'); // 渲染模板
    }
    public function updatelv()
    {
        return $this->fetch('apply'); // 渲染模板
    }
    public function refundlist()
    {
        return $this->fetch(); // 渲染模板
    }
    public function refundform()
    {
        return $this->fetch(); // 渲染模板
    }
    public function cards()
    {
        return $this->fetch(); // 渲染模板
    }
    public function cardsget()
    {
        return $this->fetch(); // 渲染模板
    }
    public function cardsex()
    {
        return $this->fetch(); // 渲染模板
    }
    public function share()
    {
        return $this->fetch(); // 渲染模板
    }
    public function uppay()
    {
        return $this->fetch(); // 渲染模板
    }
    public function record()
    {
        return $this->fetch(); // 渲染模板
    }
    public function evaluate()
    {
        return $this->fetch('member/ondate/evaluate'); // 渲染模板
    }
    public function ondate()
    {
        return $this->fetch('member/ondate/index'); // 渲染模板
    }
    public function hour()
    {
        return $this->fetch('member/ondate/hour'); // 渲染模板
    }
}
