<?php
namespace app\api\home;

use \think\Request;
use \think\Db;
use think\Model;
use think\helper\Hash;

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
        //获得定义接口
        $api =  array_flip(config('api'));
        if (isset($params['method'])) {
            //判断是否存在该方法
            $func = $api[$params['method']].'_'.$params['source'];
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
            'code'=>'0',
            'msg'=>$msg,
            'data'=>null
        ];
        return json($data);
    }
    /**
     * login 用户端
     * @param string $value [description]
     */
    public function login_custom($params)
    {   
        //参数手机号，密码
        
        $username = trim($params['account']);
        $password = trim($params['password']);

        //是否存在
        $map['username'] = $username;
        $map['status'] = 1;
        $user = db('member')->where($map)->find();
        if (!$user) {
            return $this->error('用户不存在或被禁用！');
        }
        //密码是否正确
        if (!Hash::check((string)$password, $user['password'])) {
           return $this->error( '密码错误！');
        }

        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$user
        ];
        return json($data);
    }
    /**
     * 注册
     * @return [type] [description]
     */
    public function register_custom($params)
    {
        //参数
        $data['username'] = trim($params['mobile']);
        $data['nickname'] = trim($params['account']);
        $data['email'] = trim($params['email']);
        $data['mobile'] = trim($params['mobile']);
        $data['create_time'] = time();

        //生成密码
        $data['password'] =  Hash::make((string)trim($params['password']));

        //插入数据
        $me = db('member')->insert($data);
        if (!$me) {
            return $this->error('注册失败！请稍后重试');
        }
        
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$me
        ];
        return json($data);
    }

    /**
     * 验证码 暂没
     * @return [type] [description]
     */
    public function vcode_custom($params)
    {
        //参数
        $account = trim($params['mobile']);
        $code  = rand(1000,9999);

        //生成session 
        session($account.$code,1);

        $map['username'] = $account;
        $user = db('member')->where($map)->find();
        if (!$user) {
            return $this->error('手机号不存在');
        }

        
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$code
        ];
        return json($data);
    }

    /**
     * 注册 验证
     * @return [type] [description]
     */
    public function confirm_custom($params)
    {
        //参数
        $username = trim($params['mobile']);
        $code = trim($params['code']);
        
        if (!session($username.$code)) {
            return $this->error('验证码不正确');
        }
        

        //更新状态
        $data['status'] = 1;
        
        $map['username'] = $username;
        if(!db('member')->where($map)->update($data)){
            return $this->error('服务器忙，请稍后');
        }
        
        //注销session
        session($username.$code,null);

        
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>1
        ];
        return json($data);
    }

    /**
     * [用户信息]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function userinfo_custom($params)
    {
        //params
        $username = trim($params['account']);

        //是否存在
        $map['username'] = $username;
        $map['status'] = 1;
        //用户信息
        $user = db('member')->where($map)->find();
        //用户积分
        $pmap['memberid'] = $username;
        $pmap['behavior_type'] = 0;
        $user['point'] = db('member')->where($pmap)->sum('point');

         //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$user
        ];
        return json($data);

    }

    /**
     * [article_custom 首页好文章]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function article_custom($params)
    {

        $article = db('cms_page')->where('status',1)->order('view DESC')->limit(10)->select();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$article
        ];
        return json($data);

    }

    /**
     * [lunbo 首页轮播]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function lunbo_custom($params)
    {
        $lunbo = db('cms_advert')->where('status',1)->order('id DESC')->limit(10)->select();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$lunbo
        ];
        return json($data);
    }
    /**
     * [category_custom 咨询分类]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function category_custom($params)
    {
        $category = db('cms_category')->where('status',1)->order('id DESC')->select();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$category
        ];
        return json($data);
    }
}
