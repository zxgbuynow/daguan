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
        $data['shopid'] = trim($params['agency']);
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
        if (!$user) {
            return $this->error('用户不存在');
        }
        //用户积分
        $pmap['memberid'] = $user['id'];
        $pmap['behavior_type'] = 0;
        $user['point'] = db('member_point')->where($pmap)->sum('point');

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

        $article['list'] = db('cms_page')->where('status',1)->order('view DESC')->limit(10)->select();

        foreach ($article['list'] as $key => $value) {
            unset($article['list'][$key]['content']);
            $article['list'][$key]['author'] = $value['userid']==0?'ADMIN':db('member')->where('status',1)->column('nickname');
        }
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
        $map['tagname'] = 'custom';
        $map['status'] = 1;
        $lunbo['pic'] = db('cms_advert')->where($map)->order('id DESC')->limit(10)->select();
        foreach ($lunbo['pic'] as $key => $value) {
            if (strstr($value['link'], 'article')) {//文章
                $lunbo['pic'][$key]['webview'] = '_www/view/article/detail.html';
                $lunbo['pic'][$key]['webparam'] = ['article_id'=>explode('.',explode('/', $value['link'])[1])[0]]; 
            }else if (strstr($value['link'], 'counsellor')) {
                $lunbo['pic'][$key]['webview'] = '_www/view/counsellor/detail.html';
                $lunbo['pic'][$key]['webparam'] = ['counsellor_id'=>explode('.',explode('/', $value['link'])[1])[0]];
            }
        }
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
        $category['list'] = db('cms_category')->where('status',1)->order('id DESC')->limit(8)->select();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$category
        ];
        return json($data);
    }
    /**
     * [recommend_custom 推荐咨询师]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function recommend_custom($params)
    {
        $recommend['list'] = db('member')->alias('a')->field('a.*,b.*')->join(' member_counsellor b',' b.memberid = a.id','LEFT')->where(array('a.status'=>1,'a.type'=>1))->order('a.recommond DESC')->select();

        foreach ($recommend['list'] as $key => $value) {
            //订单数
            $recommend['list'][$key]['trade'] = db('trade')->where(array('status'=>1,'mid'=>$value['memberid']))->count();
            //标识
            $smap['id'] = array('in',$value['tags']);
            $recommend['list'][$key]['sign'] = implode('|', db('cms_category')->where($smap)->column('title')) ;
            //从业时间
            $recommend['list'][$key]['employment'] = '从业'.ceil(date('Y',time())-date('Y',$value['employment'])).'年';
        }

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$recommend
        ];
        return json($data);
    }
    /**
     * [counsellor_custom 咨询师]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function counsellor_custom($params)
    {

        if (!trim($params['id'])) {
            return $this->error('参数缺失！');
        }
        $counsellor =  db('member')->alias('a')->field('a.*,b.*')->join(' member_counsellor b',' b.memberid = a.id','LEFT')->where(array('a.id'=>$params['id']))->find();

        if (!$counsellor) {
            return $this->error('咨询师不存在或是已注销');
        }
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$counsellor
        ];
        return json($data);
    }
    /**
     * [point 积分明细]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function point_custom($params)
    {
       if (!trim($params['account'])) {
            return $this->error('参数缺失！');
        }
        $member['list'] =  db('member')->alias('a')->field('a.*,b.*')->join(' member_point b',' b.memberid = a.id','LEFT')->where(array('a.username'=>$params['account']))->select();

        if (!$member) {
            return $this->error('用户不存在');
        }
        $pmap['memberid'] = $member['list'][0]['memberid'];
        $pmap['behavior_type'] = 0;
        $member['points'] = db('member_point')->where($pmap)->sum('point');
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$member
        ];
        return json($data);
    }

    /**
     * [trade 订单]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function trade_custom($params)
    {
        $trade['list'] = db('trade')->where(1)->order('id DESC')->select();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$trade
        ];
        return json($data);
    }

     /**
     * [checkpassword 验证密码]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function checkpassword_custom($params)
    {
        $password = trim($params['password']);
        $account = trim($params['account']);

        //设置密码
        // $data['password'] =  Hash::make((string)$password);


        //更新
        $map['id'] = $account;
        $user =  db('member')->where($map)->find();
        if (!Hash::check((string)$password, $user['password'])) {
           return $this->error( '密码错误！');
        }

        
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>1
        ];
        return json($data);

    }

    /**
     * [uppw_custom 更新密码]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function uppw_custom($params)
    {
        $password = trim($params['password']);
        $password_confirmation = trim($params['password_confirmation']);
        $account = trim($params['account']);
        if ($password!=$password_confirmation) {
            return $this->error('二次密码不一致');
        }
        //设置密码
        $data['password'] =  Hash::make((string)trim($password));

        //更新
        $map['id'] = $account;
        if(!db('member')->where($map)->update($data)){
            return $this->error('服务器忙，请稍后');
        }
        
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>1
        ];
        return json($data);
    }
    /**
     * [updatenickname 更新Nickname]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function updatenickname_custom($params)
    {
        //参数
        $account = trim($params['account']);
        $nickname = trim($params['nickname']);
        
        if (!$nickname) {
            return $this->error('参数必填');
        }
        

        //更新状态
        $data['nickname'] = $nickname;
        $map['username'] = $account;
        if(!db('member')->where($map)->update($data)){
            return $this->error('服务器忙，请稍后');
        }
        
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>1
        ];
        return json($data);
    }
    /**
     * [updategender_custom 更新性别]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function updategender_custom($params)
    {
        //参数
        $account = trim($params['account']);
        $sex = trim($params['sex']);
        
        // if (!$sex) {
        //     return $this->error('参数必填');
        // }
        

        //更新状态
        $data['sex'] = $sex;
        $map['username'] = $account;
        if(!db('member')->where($map)->update($data)){
            return $this->error('服务器忙，请稍后');
        }
        
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>1
        ];
        return json($data);
    }

    /**
     * [contentinfo_custom 文章祥情]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function contentinfo_custom($params)
    {

        $id = $params['id'];
        if (!$id) {
            return $this->error('参数必填');
        }
        $article = db('cms_page')->where('id',$id)->find();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$article
        ];
        return json($data);

    }
    /**
     * [category_custom 咨询分类]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function allcategory_custom($params)
    {
        $category['list'] = db('cms_category')->where('status',1)->order('id DESC')->select();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$category
        ];
        return json($data);
    }
    /**
     * [counsellorlist_custom 咨询师列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function counsellorlist_custom($params)
    {

        //参数
        $category = trim($params['cat_id']);
        $keyword = trim($params['search_keywords']);

        $map['a.nickname'] = array('like','%'.$keyword.'%');
        $map['a.status'] = 1;

        $counsellor['list'] =  db('member')->alias('a')->field('a.*,b.*')->join(' member_counsellor b',' b.memberid = a.id','LEFT')->where($map)->select();
        
        if ($category) {
            foreach ($counsellor['list'] as $key => $value) {
                if (!in_array($category, explode(',', $value['tags']))) {
                    unset($counsellor['list'][$key]);
                    continue;
                }
            }    
        }
        foreach ($counsellor['list'] as $key => $value) {
            //订单数
            $counsellor['list'][$key]['trade'] = db('trade')->where(array('status'=>1,'mid'=>$value['memberid']))->count();
            //标识
            $smap['id'] = array('in',$value['tags']);
            $counsellor['list'][$key]['sign'] = implode('|', db('cms_category')->where($smap)->column('title')) ;
            //从业时间
            $counsellor['list'][$key]['employment'] = '从业'.ceil(date('Y',time())-date('Y',$value['employment'])).'年';
        }
        
        // if (!$counsellor) {
        //     return $this->error('咨询师不存在或是已注销');
        // }
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$counsellor
        ];
        return json($data);
    }
    /**
     * [articallist_custom 文章列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function articallist_custom($params)
    {

        $keyword = trim($params['search_keywords']);

        $map['title'] = array('like','%'.$keyword.'%');
        $map['status'] = 1;
        
        $article['list'] = db('cms_page')->where($map)->order('view DESC')->select();

        foreach ($article['list'] as $key => $value) {
            unset($article['list'][$key]['content']);
            $article['list'][$key]['author'] = $value['userid']==0?'ADMIN':db('member')->where('status',1)->column('nickname');
        }

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$article
        ];
        return json($data);
    }
    
    /**
     * [agency_custom 机构列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function agency_custom($params)
    {
        $agency['list'] = db('shop_agency')->where('status',1)->select();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$agency
        ];
        return json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | 商家版API
    |--------------------------------------------------------------------------
     */ 

    /**
     * login 用户端
     * @param string $value [description]
     */
    public function login_shop($params)
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
    public function register_shop($params)
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
    public function vcode_shop($params)
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
    public function confirm_shop($params)
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
    public function userinfo_shop($params)
    {
        //params
        $username = trim($params['account']);

        //是否存在
        $map['username'] = $username;
        $map['status'] = 1;
        //用户信息
        $user = db('member')->where($map)->find();
        if (!$user) {
            return $this->error('用户不存在');
        }
        //用户积分
        $pmap['memberid'] = $user['id'];
        $pmap['behavior_type'] = 0;
        $user['point'] = db('member_point')->where($pmap)->sum('point');

         //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$user
        ];
        return json($data);

    }

    /**
     * [article_shop 首页好文章]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function article_shop($params)
    {

        $article['list'] = db('cms_page')->where('status',1)->order('view DESC')->limit(10)->select();

        foreach ($article['list'] as $key => $value) {
            unset($article['list'][$key]['content']);
            $article['list'][$key]['author'] = $value['userid']==0?'ADMIN':db('member')->where('status',1)->column('nickname');
        }
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
    public function lunbo_shop($params)
    {
        $map['tagname'] = 'custom';
        $map['status'] = 1;
        $lunbo['pic'] = db('cms_advert')->where($map)->order('id DESC')->limit(10)->select();
        foreach ($lunbo['pic'] as $key => $value) {
            if (strstr($value['link'], 'article')) {//文章
                $lunbo['pic'][$key]['webview'] = '_www/view/article/detail.html';
                $lunbo['pic'][$key]['webparam'] = ['id'=>explode('.',explode('/', $value['link'])[1])[0]]; 
            }else if (strstr($value['link'], 'counsellor')) {
                $lunbo['pic'][$key]['webview'] = '_www/view/counsellor/detail.html';
                $lunbo['pic'][$key]['webparam'] = ['id'=>explode('.',explode('/', $value['link'])[1])[0]];
            }

        }
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$lunbo
        ];
        return json($data);
    }
    /**
     * [category_shop 咨询分类]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function category_shop($params)
    {
        $category['list'] = db('cms_category')->where('status',1)->order('id DESC')->limit(8)->select();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$category
        ];
        return json($data);
    }
    /**
     * [recommend_shop 推荐咨询师]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function recommend_shop($params)
    {
        $recommend['list'] = db('member')->alias('a')->field('a.*,b.*')->join(' member_counsellor b',' b.memberid = a.id','LEFT')->where(array('a.status'=>1,'a.type'=>1))->order('a.recommond DESC')->select();

        foreach ($recommend['list'] as $key => $value) {
            //订单数
            $recommend['list'][$key]['trade'] = db('trade')->where(array('status'=>1,'mid'=>$value['memberid']))->count();
            //标识
            $smap['id'] = array('in',$value['tags']);
            $recommend['list'][$key]['sign'] = implode('|', db('cms_category')->where($smap)->column('title')) ;
            //从业时间
            $recommend['list'][$key]['employment'] = '从业'.ceil(date('Y',time())-date('Y',$value['employment'])).'年';
        }
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$recommend
        ];
        return json($data);
    }
    /**
     * [counsellor_custom 咨询师]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function counsellor_shop($params)
    {

        if (!trim($params['id'])) {
            return $this->error('参数缺失！');
        }
        $counsellor =  db('member')->alias('a')->field('a.*,b.*')->join(' member_counsellor b',' b.memberid = a.id','LEFT')->where(array('a.id'=>$params['id']))->find();

        if (!$counsellor) {
            return $this->error('咨询师不存在或是已注销');
        }
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$counsellor
        ];
        return json($data);
    }
    /**
     * [point 积分明细]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function point_shop($params)
    {
       if (!trim($params['account'])) {
            return $this->error('参数缺失！');
        }
        $member['list'] =  db('member')->alias('a')->field('a.*,b.*')->join(' member_point b',' b.memberid = a.id','LEFT')->where(array('a.username'=>$params['account']))->select();

        if (!$member) {
            return $this->error('用户不存在');
        }
        $pmap['memberid'] = $member['memberid'];
        $pmap['behavior_type'] = 0;
        $member['points'] = db('member_point')->where($pmap)->sum('point');
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$member
        ];
        return json($data);
    }

    /**
     * [trade 订单]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function trade_shop($params)
    {
        $trade = db('trade')->where(1)->order('id DESC')->select();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$trade
        ];
        return json($data);
    }
}
