<?php
namespace app\api\home;

use \think\Request;
use \think\Db;
use think\Model;
use think\helper\Hash;
use think\Session;
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
        $ismobile = trim($params['ismobile']);


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
        //
        if ($ismobile) {
             session('user_mobile_auth',$user);
        }
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$user
        ];
        return json($data);
    }
    /**
     * [logout_coustom description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function logout_custom($params)
    {
        $username = trim($params['account']);
        $ismobile = trim($params['ismobile']);
        if ($ismobile) {
             session('user_mobile_auth',null);
        }

        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>1
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


        $data['birthday'] = trim($params['birthday']);
        $data['sex'] = trim($params['sex']);
        $data['edu'] = trim($params['edu']);
        $data['grade'] = trim($params['grade']);
        $data['profession'] = trim($params['profession']);
        $data['marital'] = trim($params['marital']);
        $data['province'] = trim($params['province']);
        $data['city'] = trim($params['city']);
        $data['tel'] = trim($params['tel']);
        $data['isconsolle'] = trim($params['isconsolle']);
        $data['consolletime'] = trim($params['consolletime']);


        if (db('member')->where(['mobile'=>$data['mobile']])->find()) {
            return $this->error('账号已存在！');
        }
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

        //短信
        $code = $this->sendmsg($account);
        if (!$code) {
            return $this->error('发送失败，1小时只能获得3次');
        }
        
        //生成session 
        cache($account.'code',$code);

        //设置过期时间
        cache($account.$code, time() + 1800) ;

        // $code  = rand(1000,9999);

        // //生成session 
        // session($account.$code,1);

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
        
        //检查过期时间
        if (cache($username.$code)&&cache($username.$code)<time()) {
            return $this->error('验证码已过期');
        }
        
        //检查是否正确
        if (cache($username.'code')!=$code) {
            return $this->error('验证码不正确');
        }
        

        //更新状态
        $data['status'] = 1;
        
        $map['username'] = $username;

        if(!db('member')->where($map)->update($data)){
            // return $this->error('服务器忙，请稍后');
        }
        
        //注销session
        // Session.set($username.$code,null);
        // Session.set($username.'code',null);

        
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
        foreach ($article['list'] as $key => $value) {
            unset($article['list'][$key]['content']);
            $article['list'][$key]['author'] = $value['userid']==0?'ADMIN':db('member')->where('status',1)->column('nickname');
            $article['list'][$key]['cover'] = get_file_path($value['cover']);
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
        $ismobile = trim($params['ismobile']);
        $lunbo['pic'] = db('cms_advert')->where($map)->order('id DESC')->limit(10)->select();
        foreach ($lunbo['pic'] as $key => $value) {
            if (strstr($value['link'], 'article')) {//文章
                if ($ismobile) {
                    $lunbo['pic'][$key]['webview'] = "/mobile.php/artical/detail.html";
                    $lunbo['pic'][$key]['webparam'] = explode('.',explode('/', $value['link'])[1])[0];
                }else{
                    $lunbo['pic'][$key]['webview'] = '_www/view/artical/detail.html';
                    $lunbo['pic'][$key]['webparam'] = ['article_id'=>explode('.',explode('/', $value['link'])[1])[0]];
                }
                
                 
            }else if (strstr($value['link'], 'counsellor')) {
                if ($ismobile) {
                    $lunbo['pic'][$key]['webview'] = "/mobile.php/counsellor/detail.html";
                     $lunbo['pic'][$key]['webparam'] = explode('.',explode('/', $value['link'])[1])[0];
                }else{
                    $lunbo['pic'][$key]['webview'] = '_www/view/counsellor/detail.html';
                     $lunbo['pic'][$key]['webparam'] = ['counsellor_id'=>explode('.',explode('/', $value['link'])[1])[0]];
                }
                
               
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
        foreach ($category['list'] as $key => $value) {
            $category['list'][$key]['cover'] = get_file_path($value['cover']);
        }
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
            if (!$value['memberid']) {
                unset($recommend['list'][$key]);
                continue;
            }
            //订单数
            $recommend['list'][$key]['trade'] = db('trade')->where(array('status'=>1,'mid'=>$value['memberid']))->count();
            //标识
            $smap['id'] = array('in',$value['tags']);
            $recommend['list'][$key]['sign'] = implode('|', db('cms_category')->where($smap)->column('title')) ;
            //从业时间
            $recommend['list'][$key]['employment'] = '从业'.ceil(date('Y',time())-date('Y',$value['employment'])).'年';
        }
        $recommend['list'] = array_values($recommend['list']);
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
        //订单数
        $counsellor['trade'] = db('trade')->where(array('status'=>1,'mid'=>$counsellor['memberid']))->count();
        //标识
        $smap['id'] = array('in',$counsellor['tags']);
        $counsellor['sign'] = db('cms_category')->where($smap)->column('title') ;
        //从业时间
        $counsellor['employment'] = '从业'.ceil(date('Y',time())-date('Y',$counsellor['employment'])).'年';
        
        //沟通方式
        $counsellor['chartArr'] = array(
            array(
                'chart'=>'wordchart',
                'status'=>$counsellor['iswordchart'],
                'price'=>$counsellor['wordchart'],
                'show'=>'文字咨询'
            ),
            array(
                'chart'=>'speechchart',
                'status'=>$counsellor['isspeechchart'],
                'price'=>$counsellor['speechchart'],
                'show'=>'语音咨询'

            ),
            array(
                'chart'=>'videochart',
                'status'=>$counsellor['isvideochart'],
                'price'=>$counsellor['videochart'],
                'show'=>'视频咨询'
            ),
            array(
                'chart'=>'facechart',
                'status'=>$counsellor['isfacechart'],
                'price'=>$counsellor['facechart'],
                'show'=>'面对面咨询'
            )
        );
        
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
        $account = trim($params['account']);
        $map['memberid'] = $account;

        $trade['list'] = db('trade')->where($map)->order('id DESC')->select();

       
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
     * [updatepreference_custom 更新preference]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function updatepreference_custom($params)
    {
        //参数
        $account = trim($params['account']);
        $pid = trim($params['pid']);
        $isActive = trim($params['isActive']);
        
        if (!$pid) {
            return $this->error('参数必填');
        }
        
        //获得用户信息
        $user = db('member')->where(['mobile'=>$account])->find();
        if ($user['preference']) {
            $preference = explode(',', $user['preference']);
        }else{
            $preference = array();
        }
        if (!$isActive) {
            foreach ($preference as $key => $value) {
                if ($value==$pid) {
                    unset($preference[$key]);
                }
            }
        }else{
            array_push($preference, $pid);
        }
        

        if ($preference) {
            $data['preference'] = implode(',', $preference);
        }else{
            $data['preference'] = '';
        }

        //更新状态
        // $data['preference'] = implode(',', $preference);
        $map['username'] = $account;
        if(!db('member')->where($map)->update($data)){
            // return $this->error('服务器忙，请稍后');
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
        foreach ($category['list'] as $key => $value) {
            $category['list'][$key]['cover'] = get_file_path($value['cover']);
        }
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
        if (isset($params['cate_id'])) {
            $cate_id = trim($params['cate_id']);
            $map['cid'] = $cate_id;
        }

        $map['title'] = array('like','%'.$keyword.'%');
        $map['status'] = 1;
        
        $article['list'] = db('cms_page')->where($map)->order('view DESC')->select();

        foreach ($article['list'] as $key => $value) {
            unset($article['list'][$key]['content']);
            $article['list'][$key]['author'] = $value['userid']==0?'ADMIN':db('member')->where('status',1)->column('nickname');
            $article['list'][$key]['cover'] = get_file_path($value['cover']);
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

    /**
     * [createTrade_custom 生成订单]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function createTrade_custom($params)
    {
        $counsellor_id = trim($params['counsellor_id']);
        $price = trim($params['price']);
        $chart = trim($params['chart']);
        $account = trim($params['account']);
        $num = trim($params['num']);

        //shopid mid uid tid payment title
        
        $data['mid'] = $counsellor_id;
        $data['memberid'] = $account;
        $data['payment'] = $price;
        $data['created_time'] = time();
        $data['num'] = $num;
        $data['chart'] = $chart;

        //交易标题
        
        $str = '文字咨询';
        switch ($chart) {
            case 'speechchart':
                $str = '语音咨询';
                break;
            case 'videochart':
                $str = '视频咨询';
                break;
            case 'facechart':
                $str = '面对面咨询';
                break;
            
            default:
                break;
        }

        $counsellor = db('member')->where('id',$counsellor_id)->column('nickname');
        $username = db('member')->where('id',$account)->column('nickname');

        $data['title'] = '预约'.$counsellor[0].$str;
        //机构
        $data['shopid'] = db('member')->where('id',$account)->column('shopid');
        //订单号
        $data['tid'] = date('YmdHis',time()).rand(1000,9999);
        
        //插入数据
        $trade = db('trade')->insert($data);
        if (!$trade) {
            return $this->error('生成订单');
        }
        //生成消息
        $msg['type'] = 1;
        $msg['subtitle'] = '预约'.$counsellor[0].$str;
        $msg['title'] = '预约您的'.$str;
        $msg['descrption'] = $username[0].'预约您的'.$str;
        $msg['display'] = $username[0].'预约'.$counsellor[0].'的'.$str;
        $msg['sendid'] = $account;
        $msg['reciveid'] = $counsellor_id;

        $this->create_msg($msg);
        
        $ret = array('tid'=>$data['tid']);
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$ret
        ];
        return json($data);


    }

    public function tradepay_custom($params)
    {
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>1
        ];
        return json($data);
    }
    /**
     * [upavar_custom 头像]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function upavar_custom($params)
    {
        //参数
        $account = trim($params['account']);
        $avar = trim($params['avar']);
        
        if (!$avar) {
            return $this->error('参数必填');
        }
        

        //更新状态
        $data['avar'] =$this->_seve_img($avar);
        if (!$data['avar']) {
            return $this->error('头像上传失败，请稍后重试');
        }
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
     * [getAvatar 头像]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getAvatar_custom($params)
    {
        $users = trim($params['users']);
        if (!$users) {
            return $this->error('参数缺失！');
        }

        $where['mobile'] = array('in',explode(',', $users)) ;
        $rs = db('member')->where($where)->column('mobile,avar');

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$rs
        ];
        return json($data);

    }

    /**
     * [calendatoday_custom 获得当前时间日程数据]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function calendatoday_custom($params)
    {
       $account = trim($params['account']);
       $cstime =  trim($params['day']);

       //当晚24点时间
       $cetime = strtotime(date('Y-m-d',$cstime))+24 * 60 * 60;
       //今天
       $today = strtotime(date('Y-m-d',time()));

       //日程
        $pmap['memberid'] = $account;

        $calendar['list'] = db('calendar')->where($pmap)->whereTime('start_time', 'between', [$cstime, $cetime])->select();

        $times = array('9:00','9:30','10:00','10:30','11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30','19:00');

        $timesarr['list'] = [];
        //过去的时间
        if ($today>strtotime(date('Y-m-d',$cstime))) {
            foreach ($times as $key => $value) {
                //订单记录
                $tpoint = strtotime(date('Y-m-d',$cstime).$value);
                $timesarr['list'][$key]['t'] = $value;
                $timesarr['list'][$key]['s'] = 2;
            }
        }else{
            foreach ($times as $key => $value) {
                //订单记录
                $tpoint = strtotime(date('Y-m-d',$cstime).$value);
                $timesarr['list'][$key]['t'] = $value;
                $timesarr['list'][$key]['s'] = 0;
                foreach ($calendar['list'] as $k => $v) {
                    if ($tpoint>$v['start_time']&&$tpoint<$v['end_time']) {
                        $timesarr['list'][$key]['s'] = 1;
                    }
                }
            }
        }
        
        //咨询师
        $timesarr['user'] = db('member')->where(['id'=>$account])->column('username');

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$timesarr
        ];
        return json($data);
    }

    /**
     * [msg_shop 消息列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function msg_custom($params)
    {

        //查询消息
        $user['list'] =  db('msg')->where(1)->order('id DESC')->limit('20')->select();
        
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$user
        ];
        return json($data);
    }

    /**
     * [usersendSms_custom 发送验证码]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function usersendSms_custom($params)
    {
        //参数
        $account = trim($params['mobile']);

        //是否是会员
        if (!db('member')->where(['mobile'=>$account])->find()) {
            return $this->error('账号不存在');
        }
        
        //短信
        $code = $this->sendmsg($account);
        if (!$code) {
            return $this->error('发送失败，1小时只能获得3次');
        }
        
        //生成session 
        cache($account.'vcode',$code);

        //设置过期时间
        cache($account.$code,time() + 1800);

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>1
        ];
        return json($data);
    }
    /**
     * [findPassword description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function findPassword_custom($params)
    {
        //参数
        $username = trim($params['mobile']);
        $code = trim($params['code']);
        $newpw = trim($params['newpw']);
        $rnewpw = trim($params['rnewpw']);


        //检查过期时间
        if (cache($username.$code)&&cache($username.$code)<time()) {
            return $this->error('验证码已过期');
        }
        
        //检查是否正确
        if (cache($username.'vcode')!=$code) {
            return $this->error('验证码不正确');
        }

        //生成密码
        $data['password'] =  Hash::make((string)trim($params['newpw']));

        //更新
        if(!db('member')->where(['mobile'=>$username])->update($data)){
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
     * [articalcate_custom 文章分类]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function articalcate_custom($params)
    {
        $ret = array();
        // $article = db('cms_page')->where('status',1)->order('view DESC')->limit(10)->select();

        $cates = db('cms_category')->where('status',1)->order('id DESC')->select();

        foreach ($cates as $k => $v) {
            $ret[$k]['name'] = $v['title'];
            $ret[$k]['cid'] = $v['id'];
            //取分类下数据
            $article = db('cms_page')->where(['status'=>1,'cid'=>$v['id']])->order('view DESC')->limit(10)->select();
            foreach ($article as $key => $value) {
                unset($value['content']);
                $ret[$k]['list'][$key] = $value;
                $ret[$k]['list'][$key]['author'] = $value['userid']==0?'ADMIN':db('member')->where('status',1)->column('nickname');
                $ret[$k]['list'][$key]['cover'] = get_file_path($value['cover']);
            }
        }
        $rs['data'] = array_values($ret);
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$rs
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
        $isshop = trim($params['isshop']);

        //是否存在
        $map['username'] = $username;
        $map['status'] = 1;
        $user = db('member')->where($map)->find();
        if (!$user) {
            return $this->error('用户不存在或被禁用！');
        }
        if ($user['shopid']) {
            $user['agency'] = db('shop_agency')->where('id',$user['shopid'])->column('title')[0];
        }else{
            $user['agency'] = '';
        }
        
        //密码是否正确
        if (!Hash::check((string)$password, $user['password'])) {
           return $this->error( '密码错误！');
        }

        if ($isshop) {
             session('user_counsellor_auth',$user);
        }
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$user
        ];
        return json($data);
    }

    /**
     * [logout_shop description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function logout_shop($params)
    {
        $username = trim($params['account']);
        $isshop = trim($params['isshop']);
        if ($isshop) {
             session('user_counsellor_auth',null);
        }

        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>1
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

        if (db('member')->where(['mobile'=>$data['mobile']])->find()) {
            return $this->error('账号已存在！');
        }

        $data['type'] = 1;

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

        //短信
        $code = $this->sendmsg($account);
        if (!$code) {
            return $this->error('发送失败，1小时只能获得3次');
        }
        
        //生成session 
        cache($account.'code',$code);

        //设置过期时间
        cache($account.$code, time() + 1800) ;

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
        
        // if (!session($username.$code)) {
        //     return $this->error('验证码不正确');
        // }
        
        //检查过期时间
        if (cache($username.$code)&&cache($username.$code)<time()) {
            return $this->error('验证码已过期');
        }
        
        //检查是否正确
        if (cache($username.'code')!=$code) {
            return $this->error('验证码不正确');
        }

        //更新状态
        $data['status'] = 1;
        
        $map['username'] = $username;
        if(!db('member')->where($map)->update($data)){
            // return $this->error('服务器忙，请稍后');
        }
        
        //注销session
        // cache($username.$code,null);
        // cache($username.'code',null);

        
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
            $article['list'][$key]['cover'] = get_file_path($value['cover']);
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
        $map['tagname'] = 'shop';
        $map['status'] = 1;
        $isshop = trim($params['isshop']);
        $lunbo['pic'] = db('cms_advert')->where($map)->order('id DESC')->limit(10)->select();
        foreach ($lunbo['pic'] as $key => $value) {
            if (strstr($value['link'], 'article')) {//文章
                if ($isshop) {
                    $lunbo['pic'][$key]['webview'] = "/counsellor.php/artical/detail.html";
                    $lunbo['pic'][$key]['webparam'] = explode('.',explode('/', $value['link'])[1])[0];
                }else{
                    $lunbo['pic'][$key]['webview'] = '_www/view/artical/detail.html';
                    $lunbo['pic'][$key]['webparam'] = ['article_id'=>explode('.',explode('/', $value['link'])[1])[0]];
                }
                
                 
            }else if (strstr($value['link'], 'counsellor')) {
                if ($isshop) {
                    $lunbo['pic'][$key]['webview'] = "/counsellor.php/counsellor/detail.html";
                     $lunbo['pic'][$key]['webparam'] = explode('.',explode('/', $value['link'])[1])[0];
                }else{
                    $lunbo['pic'][$key]['webview'] = '_www/view/counsellor/detail.html';
                     $lunbo['pic'][$key]['webparam'] = ['counsellor_id'=>explode('.',explode('/', $value['link'])[1])[0]];
                }
                
               
            }

            // if (strstr($value['link'], 'article')) {//文章
            //     $lunbo['pic'][$key]['webview'] = '_www/view/article/detail.html';
            //     $lunbo['pic'][$key]['webparam'] = ['id'=>explode('.',explode('/', $value['link'])[1])[0]]; 
            // }else if (strstr($value['link'], 'counsellor')) {
            //     $lunbo['pic'][$key]['webview'] = '_www/view/counsellor/detail.html';
            //     $lunbo['pic'][$key]['webparam'] = ['id'=>explode('.',explode('/', $value['link'])[1])[0]];
            // }

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
        foreach ($category['list'] as $key => $value) {
            $category['list'][$key]['cover'] = get_file_path($value['cover']);
        }
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
     * [articallist_shop 文章列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function articallist_shop($params)
    {

        $keyword = trim($params['search_keywords']);

        $map['title'] = array('like','%'.$keyword.'%');
        $map['status'] = 1;
        
        $article['list'] = db('cms_page')->where($map)->order('view DESC')->select();

        foreach ($article['list'] as $key => $value) {
            unset($article['list'][$key]['content']);
            $article['list'][$key]['author'] = $value['userid']==0?'ADMIN':db('member')->where('status',1)->column('nickname');
            $article['list'][$key]['cover'] = get_file_path($value['cover']);
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
     * [contentinfo_shop 文章祥情]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function contentinfo_shop($params)
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
     * [checkpassword 验证密码]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function checkpassword_shop($params)
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
     * [uppw_shop 更新密码]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function uppw_shop($params)
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
     * [msg_shop 消息列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function msg_shop($params)
    {
        $id = trim($params['account']);

        //查询消息
        $map['reciveid'] = $id;
        $user =  db('msg')->where($map)->select();
        $ret = [];
        foreach ($user as $key => $value) {
            $ret[$value['type']][$key] = $value;
        }
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$ret
        ];
        return json($data);
    }
    /**
     * [msginfo_shop 消息祥情]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function msginfo_shop($params)
    {
        $id = trim($params['id']);

        //查询消息
        $map['id'] = $id;
        $msg =  db('msg')->where($map)->find();
        

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$msg
        ];
        return json($data);
    }

    /**
     * [msgup_shop 消息状态更新]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function msgup_shop($params)
    {
        $id = trim($params['id']);

        //查询消息
        $map['id'] = $id;
        $data['status'] = 1;
        if (!db('msg')->where($map)->update($data)) {
            $this->error('更新失败！');
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
     * [income_shop 收入]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function income_shop($params)
    {
        $account = trim($params['account']);
        $map['mid'] = $account;
        
        $trade['list'] = db('trade')->where($map)->order('id DESC')->select();

       $trade['income'] =db('trade')->where($map)->sum('payment'); 
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$trade
        ];
        return json($data);
    }

    /**
     * [counsellorindex_shop 咨询师信息]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function counsellorindex_shop($params)
    {
        $account = trim($params['account']);

        //收入
        $map['mid'] = $account;
        $user['income'] =db('trade')->where($map)->sum('payment'); 

        //积分
        $pmap['memberid'] = $account;
        $pmap['behavior_type'] = 0;
        $user['points'] = db('member_point')->where($pmap)->sum('point');

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$user
        ];
        return json($data);
    }

    /**
     * [calendatoday_shop 获得当前时间日程数据]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function calendatoday_shop($params)
    {
       $account = trim($params['account']);
       $cstime =  trim($params['day']);

       //当晚24点时间
       $cetime = strtotime(date('Y-m-d',$cstime))+24 * 60 * 60;
       //当天 0点
       $btime = strtotime(date('Y-m-d',$cstime));

       //日程
        $pmap['memberid'] = $account;

        $calendar['list'] = db('calendar')->where($pmap)->whereTime('start_time', 'between', [$btime, $cetime])->select();

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$calendar
        ];
        return json($data);
    }

    /**
     * [calendaall_shop 当月到月底的数据]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function calendaall_shop($params)
    {
       $account = trim($params['account']);
       $cstime =  trim($params['day']);

       

       //日程
        $pmap['memberid'] = $account;

        $calendar['list'] = db('calendar')->where($pmap)->whereTime('start_time', 'm')->select();
        foreach ($calendar['list'] as $key => $value) {
            if ($value['start_time']<$cstime) {
                error_log($value['start_time'].'|||'.$cstime,3,'/home/wwwroot/daguan/time.log');
                unset($calendar['list'][$key]);
            }
        }
        $calendar['list'] = array_values($calendar['list']);
        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$calendar
        ];
        return json($data);
    }
    /**
     * [calendaadd_shop 添加日程]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function calendaadd_shop($params)
    {
        //title des tid createtime start_time memberid
        $account = trim($params['account']);
        $start_time = trim($params['start_time']);
        $end_time = trim($params['end_time']);
        $tid = trim($params['tid']);


        //添加
        $save['create_time'] = time();
        $save['memberid'] = $account;

        $save['start_time'] =strtotime($start_time);
        $save['end_time'] = strtotime($end_time);
        $save['tid'] = $tid;

        $save['title'] = db('trade')->where('id',$tid)->column('title')[0];
        $save['descrption'] = db('trade')->where('id',$tid)->column('title')[0];

        if (!db('calendar')->insert($save)) {
            $this->error('保存失败！');
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
     * [upavar_shop 头像]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function upavar_shop($params)
    {
        //参数
        $account = trim($params['account']);
        $avar = trim($params['avar']);
        
        if (!$avar) {
            return $this->error('参数必填');
        }
        

        //更新状态
        $data['avar'] =$this->_seve_img($avar);
        if (!$data['avar']) {
            return $this->error('头像上传失败，请稍后重试');
        }
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
     * [social_shop 社交信息]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function social_shop($params)
    {
        //参数
        $account = trim($params['account']);
        $weixin = trim($params['weixin']);
        $qq = trim($params['qq']);
        $alipay = trim($params['alipay']);
        
        if (!$weixin||!$qq||!$alipay) {
            return $this->error('参数必填');
        }
        

        //更新状态
        $data['weixin'] = $weixin;
        $data['qq'] = $qq;
        $data['alipay'] = $alipay;
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
     * [identifi_shop description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function identifi_shop($params)
    {
        //参数
        $account = trim($params['account']);
        $identifi = trim($params['identifi']);
        $cerfornt = trim($params['cerfornt']);
        $cerback = trim($params['cerback']);
        
        if (!$identifi||!$cerfornt||!$cerback) {
            return $this->error('参数必填');
        }
        

        //更新状态
        $data['cerfornt'] =$this->_seve_img($cerfornt);
        if (!$data['cerfornt']) {
            return $this->error('身份正面上传失败，请稍后重试');
        }
        $data['cerback'] =$this->_seve_img($cerback);
        if (!$data['cerback']) {
            return $this->error('身份反面上传失败，请稍后重试');
        }
        $data['cerback'] = $identifi;
        $map['id'] = $account;

        if(!db('member')->where($map)->update($data)){
            // return $this->error('服务器忙，请稍后');
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
     * [agency_custom 机构列表]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function agency_shop($params)
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

    /**
     * [getAvatar 头像]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getAvatar_shop($params)
    {
        $users = trim($params['users']);
        if (!$users) {
            return $this->error('参数缺失！');
        }

        $where['mobile'] = array('in',explode(',', $users)) ;
        $rs = db('member')->where($where)->column('mobile,avar');

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$rs
        ];
        return json($data);

    }

    /**
     * [updateonline 更新状态]
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function updateonline_shop($params)
    {
        //参数
        $account = trim($params['account']);
        $isActive = trim($params['isActive']);
        
        if (!$account) {
            return $this->error('参数必填');
        }
        

        //更新状态
        $data['online'] = $isActive;
        $map['memberid'] = $account;
        if(!db('member_counsellor')->where($map)->update($data)){
            // return $this->error('服务器忙，请稍后');
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
     * [usersendSms_custom 发送验证码]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function usersendSms_shop($params)
    {
        //参数
        $account = trim($params['mobile']);

        //是否是会员
        if (!db('member')->where(['mobile'=>$account])->find()) {
            return $this->error('账号不存在');
        }
        
        //短信
        $code = $this->sendmsg($account);
        if (!$code) {
            return $this->error('发送失败，1小时只能获得3次');
        }
        
        //生成session 
        cache($account.'vcode',$code);

        //设置过期时间
        cache($account.$code,time() + 1800);

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>1
        ];
        return json($data);
    }
    /**
     * [findPassword description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function findPassword_shop($params)
    {
        //参数
        $username = trim($params['mobile']);
        $code = trim($params['code']);
        $newpw = trim($params['newpw']);
        $rnewpw = trim($params['rnewpw']);


        //检查过期时间
        if (cache($username.$code)&&cache($username.$code)<time()) {
            return $this->error('验证码已过期');
        }
        
        //检查是否正确
        if (cache($username.'vcode')!=$code) {
            return $this->error('验证码不正确');
        }

        //生成密码
        $data['password'] =  Hash::make((string)trim($params['newpw']));

        //更新
        if(!db('member')->where(['mobile'=>$username])->update($data)){
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
     * [getMemberInfoByTid_shop 获得咨询信息]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function getMemberInfoByTid_shop($params)
    {
        //参数
        $tid = trim($params['tid']);
        $cid = trim($params['cid']);

        //订单信息
        $trade = db('trade')->where(['id'=>$tid])->find();

        //预约信息
        $ondate = db('calendar')->where(['id'=>$cid])->find();
        
        $rs = array();
        if ($trade&&$trade['memberid']) {
            $rs['user'] = db('member')->where(['id'=>$trade['memberid']])->find();
            if ($rs['user']) {
                unset($rs['user']['password']);
            }
        }
        $rs['trade'] = $trade;
        $rs['ondate'] = $ondate;

        //返回信息
        $data = [
            'code'=>'1',
            'msg'=>'',
            'data'=>$rs
        ];
        return json($data);
    }
    /*
    |--------------------------------------------------------------------------
    | 公用方法
    |--------------------------------------------------------------------------
     */ 
    /**
     * [create_msg 创建消息]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function create_msg($data)
    {
        
        //消息类型 0 系统消息  1订单系统
        $data['type'] = $data['type'];
        $data['subtitle'] = trim($data['subtitle']);
        $data['title'] = trim($data['title']);
        $data['descrption'] = trim($data['descrption']);
        $data['sendid'] = trim($data['sendid']);
        $data['reciveid'] = trim($data['reciveid']);
        $data['create_time'] = time();

        //插入数据
        $me = db('msg')->insert($data);

    }
    /**
     * [_seve_img 上传头像]
     * @param  [type] $avar [description]
     * @return [type]       [description]
     */
    public function _seve_img($avar)
    {
        $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';
        

        $path = "public/uploads/images/".date("Ymd",time());
        if (!is_dir($path)){ //判断目录是否存在 不存在就创建
            mkdir($path,0777,true);
        }
        $imageSrc=  $path."/". $imageName;  //图片名字

        $r = file_put_contents(ROOT_PATH ."public/".$imageSrc, base64_decode($avar));//返回的是字节数
        if (!$r) {
            return false;
        }else{
            return $imageSrc;
        }
    }

    /** 
     * 计算两个时间段是否有交集（边界重叠不算） 
     * 
     * @param string $beginTime1 开始时间1 
     * @param string $endTime1 结束时间1 
     * @param string $beginTime2 开始时间2 
     * @param string $endTime2 结束时间2 
     * @return bool 
     * @author blog.snsgou.com 
     */ 

    function is_time_cross($beginTime1 = '', $endTime1 = '', $beginTime2 = '', $endTime2 = '')  
    {  
        $status = $beginTime2 - $beginTime1;  
        if ($status > 0)  
        {  
            $status2 = $beginTime2 - $endTime1;  
            if ($status2 >= 0)  
            {  
                return false;  
            }  
            else  
            {  
                return true;  
            }  
        }  
        else  
        {  
            $status2 = $endTime2 - $beginTime1;  
            if ($status2 > 0)  
            {  
                return true;  
            }  
            else  
            {  
                return false;  
            }  
        }  
    }

    /**
     * [sendmsg description]
     * @param  [type] $mobile [description]
     * @return [type]         [description]
     */
    public function sendmsg($mobile)
    {
        $apikey = "8df6ed7129c50581eecdf1e875edbaa3"; 

        $code  = rand(1000,9999);
        $text="【希望24热线】您的验证码是".$code; 

        error_log($text,3,'/home/wwwroot/daguan/mobile.log');
        $ch = curl_init();
 
         /* 设置验证方式 */
         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8',
             'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
         /* 设置返回结果为流 */
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         
         /* 设置超时时间*/
         curl_setopt($ch, CURLOPT_TIMEOUT, 10);
         
         /* 设置通信方式 */
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         
         // 发送短信
         $data = array('text'=>$text,'apikey'=>$apikey,'mobile'=>$mobile);
         $json_data = $this->send($ch,$data);
         error_log($json_data,3,'/home/wwwroot/daguan/sendmsg.log');
         $array = json_decode($json_data,true);  
         if ($array['code']==0) {
            return $code;
         }else{
            return false;
         }
    }
    /**
     * [send description]
     * @param  [type] $ch   [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    function send($ch,$data){
         curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
         curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
         $result = curl_exec($ch);
         $error = curl_error($ch);
         // checkErr($result,$error);
         return $result;
     }
    
}
