<?php
namespace app\counsellor\home;
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
        $timestamp = time();
        $appId = 'wx12ddc64d7e01faaf';
        $appsecret = '8c437a0deffcdfdaa2bbb97220f7fe92';
        $jsapi_ticket = $this->make_ticket($appId,$appsecret);
        $nonceStr = $this->make_nonceStr();
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $signature = $this->make_signature($nonceStr,$timestamp,$jsapi_ticket,$url);

        //值
        $this->assign('url', $url);
        $this->assign('nonceStr', $nonceStr);
        $this->assign('timestamp', $timestamp);
        $this->assign('signature', $signature);
        

        return $this->fetch(); // 渲染模板
    }

    public function msg()
    {
        return $this->fetch(); // 渲染模板
    }
    public function msg_list()
    {
        return $this->fetch(); // 渲染模板
    }
    public function msg_detail()
    {
        return $this->fetch(); // 渲染模板
    }
    public function income()
    {
        return $this->fetch('member/income/index'); // 渲染模板
    }

    public function infomanager()
    {
        return $this->fetch('member/info/infomanager'); // 渲染模板
    }

    public function avar()
    {
        return $this->fetch('member/info/avar'); // 渲染模板
    }

    public function bind()
    {
        return $this->fetch('member/info/bind'); // 渲染模板
    }

    public function identifi()
    {
        return $this->fetch('member/info/identifi'); // 渲染模板
    }

    public function social()
    {
        return $this->fetch('member/info/social'); // 渲染模板
    }

    public function safe()
    {
        return $this->fetch('member/safe/safe'); // 渲染模板
    }

    public function point()
    {
        return $this->fetch('member/point/integral'); // 渲染模板
    }
    
    public function checkpassword()
    {
        return $this->fetch('member/safe/checkpassword'); // 渲染模板
    }
    public function logpassword()
    {
        return $this->fetch('member/safe/logpassword'); // 渲染模板
    }

    /*
    |---
    | 公共方法
    |___
     */
    public function make_nonceStr()
    {
        $codeSet = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i<16; $i++) {
            $codes[$i] = $codeSet[mt_rand(0, strlen($codeSet)-1)];
        }
        $nonceStr = implode($codes);
        return $nonceStr;
    }

    public function make_signature($nonceStr,$timestamp,$jsapi_ticket,$url)
    {
        $tmpArr = array(
        'noncestr' => $nonceStr,
        'timestamp' => $timestamp,
        'jsapi_ticket' => $jsapi_ticket,
        'url' => $url
        );
        ksort($tmpArr, SORT_STRING);
        $string1 = http_build_query( $tmpArr );
        $string1 = urldecode( $string1 );
        $signature = sha1( $string1 );
        return $signature;
    }

    public function make_ticket($appId,$appsecret)
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents(__DIR__."/../../../public/uploads/temp/access_token.json"));
        if (!is_dir(__DIR__.'/../../../public/uploads/temp')) {
            mkdir(__DIR__.'/../../../public/uploads/temp', 0755, true);
        }
        if ($data->expire_time < time()) {
            $TOKEN_URL="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appsecret;
            $json = file_get_contents($TOKEN_URL);
            $result = json_decode($json,true);
            $access_token = $result['access_token'];
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $fp = fopen(__DIR__."/../../../public/uploads/temp/access_token.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        }else{
            $access_token = $data->access_token;
        }

        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents(__DIR__."/../../../public/uploads/temp/jsapi_ticket.json"));
        if ($data->expire_time < time()) {
            $ticket_URL="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
            $json = file_get_contents($ticket_URL);
            $result = json_decode($json,true);
            $ticket = $result['ticket'];
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $fp = fopen(__DIR__."/../../../public/uploads/temp/jsapi_ticket.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        }else{
            $ticket = $data->jsapi_ticket;
        }
        return $ticket;
    }
}
