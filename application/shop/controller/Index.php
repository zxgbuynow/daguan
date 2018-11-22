<?php


namespace app\shop\controller;

use think\Cache;
use think\helper\Hash;
use think\Db;
use app\common\builder\ZBuilder;
use app\shop\model\User as UserModel;
use app\cms\model\Agency as AgencyModel;

/**
 * 后台默认控制器
 * @package app\shop\controller
 */
class Index extends Shop
{
    /**
     * 后台首页
     * @author zg
     * @return string
     */
    public function index()
    {
        $this->redirect('Log/index');exit;
        $shop_pass = Db::name('shop_user')->where('id', 1)->value('password');

        if (UID == 1 && $shop_pass && Hash::check('admin', $shop_pass)) {
            $this->assign('default_pass', 1);
        }
        return $this->fetch();
    }

    /**
     * 清空系统缓存
     * @author zg
     */
    public function wipeCache()
    {
        if (!empty(config('wipe_cache_type'))) {
            foreach (config('wipe_cache_type') as $item) {
                if ($item == 'LOG_PATH') {
                    $dirs = (array) glob(constant($item) . '*');
                    foreach ($dirs as $dir) {
                        array_map('unlink', glob($dir . '/*.log'));
                    }
                    array_map('rmdir', $dirs);
                } else {
                    array_map('unlink', glob(constant($item) . '/*.*'));
                }
            }
            Cache::clear();
            $this->success('清空成功');
        } else {
            $this->error('请在系统设置中选择需要清除的缓存类型');
        }
    }

    /**
     * 个人设置
     * @author zg
     */
    public function profile()
    {
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $data['nickname'] == '' && $this->error('昵称不能为空');
            $data['id'] = UID;

            // 如果没有填写密码，则不更新密码
            if ($data['password'] == '') {
                unset($data['password']);
            }

            $UserModel = new UserModel();
            if ($user = $UserModel->allowField(['nickname', 'email', 'password', 'mobile', 'avatar','alipay','weixin','unioncard'])->update($data)) {
                // 记录行为
                action_shop_log('user_edit', 'shop_user', UID, UID, get_shop_nickname(UID));
                $this->success('编辑成功');
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = UserModel::where('id', UID)->field('password', true)->find();
        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->addFormItems([ // 批量添加表单项
                ['static', 'username', '用户名', '不可更改'],
                ['text', 'nickname', '昵称', '可以是中文'],
                ['text', 'email', '邮箱', ''],
                ['text', 'alipay', '收款支付宝', ''],
                ['text', 'weixin', '收款微信', ''],
                ['text', 'unioncard', '收款银行卡', ''],
                ['password', 'password', '密码', '必填，6-20位'],
                ['text', 'mobile', '手机号'],
                ['image', 'avatar', '头像']
            ])
            ->setFormData($info) // 设置表单数据
            ->fetch();
    }
    /**
     * [shopinfo 配置分中心信息]
     * @return [type] [description]
     */
    public function shopinfo()
    {
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Agency');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            if (AgencyModel::update($data)) {
                
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        $info = UserModel::where('id', UID)->field('password', true)->find();
        if (!$info['shopid']) {
             $this->error('缺少参数');
        }
        // $map['shopid'] = $info['shopid'];

        $id = $info['shopid'];
        // 获取数据
        $info = AgencyModel::where('id', $id)->find();
        $map['status'] = 1;
        $list = UserModel::where($map)->column('id,username');
        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('编辑') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['hidden', 'id'],
                ['text', 'title', '分机构名'],
                ['text', 'city', '地区','<code>最合适四个字</code>'],
                ['text', 'description', '描述'],
                ['image', 'lincence', '营业执照'],
                ['text', 'address', '地址'],
                ['text', 'manager', '负责人'],
                ['text', 'tel', '电话'],
                ['text', 'webchat', '微信'],
                ['text', 'aptiude', '资质'],
                ['text', 'longvity', '质历'],
                ['text', 'bail', '保证金'],
                ['datetime', 'setup', '创办时间'],
                ['select', 'adminid', '分机构管理员', '', $list],
                // ['radio', 'status', '状态', '', ['冻结', '审核','认证'], 1]
            ])
            ->setFormData($info) // 设置表单数据
            ->fetch();
    }
    /**
     * 检查版本更新
     * @author zg
     * @return \think\response\Json
     */
    public function checkUpdate()
    {
        $params = config('dolphin');
        $params['domain']  = request()->domain();
        $params['website'] = config('web_site_title');
        $params['ip']      = $_SERVER['SERVER_ADDR'];
        $params['php_os']  = PHP_OS;
        $params['php_version'] = PHP_VERSION;
        $params['mysql_version'] = db()->query('select version() as version')[0]['version'];
        $params['server_software'] = $_SERVER['SERVER_SOFTWARE'];
        $params = http_build_query($params);

        $opts = [
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => config('dolphin.product_update'),
            CURLOPT_USERAGENT      => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => $params
        ];

        // 初始化并执行curl请求
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($data, true);

        if ($result['code'] == 1) {
            return json([
                'update' => '<a class="badge badge-primary" href="http://www.dolphinphp.com/download" target="_blank">有新版本：'.$result["version"].'</a>',
                'auth'   => $result['auth']
            ]);
        } else {
            return json([
                'update' => '',
                'auth'   => $result['auth']
            ]);
        }
    }

}