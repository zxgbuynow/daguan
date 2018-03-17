<?php
namespace app\api\home;

use \think\Request;
use \think\Db;
/**
 * 前台首页控制器
 * @package app\index\controller
 */
class Index
{
    public function index()
    {
        $request = Request::instance();
        $params = $request->param();
error_log(json_encode($params),'3','/data/httpd/daguan/test.log');
        //获得定义接口
        $api =  array_flip(config('api'));
        if (isset($params['method'])) {
            //判断是否存在该方法
            $func = $api[$params['method']];
            if (method_exists($this,$func)) {
                return $this->$func($params);
            }else{
                return $this->error($params['method'].'方法不存在');
            }
            
        }else{
            return $this->error('method参数缺失');
        }
    }

    public function error($msg)
    {
        $data = [
            'code'=>'1',
            'msg'=>$msg,
            'data'=>null
        ];
        return json($data);
    }
    /**
     * login
     * @param string $value [description]
     */
    public function login($params)
    {
        $data = [
            'code'=>'1',
            'msg'=>$params,
            'data'=>null
        ];
        return json($data);
    }
    /**
     * 注册
     * @return [type] [description]
     */
    public function register($params)
    {
        $data = [
            'code'=>'1',
            'msg'=>$params,
            'data'=>null
        ];
        return json($data);
    }
}
