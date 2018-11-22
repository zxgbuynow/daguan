<?php


namespace app\shop\controller;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\shop\model\Member as MemberModel;
use app\shop\model\Module as ModuleModel;
use app\shop\model\User as UserModel;
use app\shop\model\Agency as AgencyModel;
use app\cms\model\Point as PointModel;
use app\cms\model\Category as CategoryModel;
use app\cms\model\CateAccess as CateAccessModel;
use app\cms\model\Counsellorot as CounsellorotModel;
use util\Tree;
use think\Db;
use think\Hook;

/**
 * 咨询师默认控制器
 * @package app\member\admin
 */
class Counsellor extends Shop
{
    /**
     * 咨询师首页
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();
        $map['type'] = 1;

        $info = UserModel::where('id', UID)->field('password', true)->find();
        if (!$info['shopid']) {
             $this->error('缺少参数');
        }
        $map['shopid'] = $info['shopid'];
        // 数据列表
        $data_list = MemberModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();


        $list_type = AgencyModel::where('status', 1)->column('id,title');

        $btnAdd = ['icon' => 'fa fa-plus', 'title' => '积分列表', 'href' => url('point', ['id' => '__id__'])];

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('咨询师管理') // 设置页面标题
            ->setTableName('member') // 设置数据表名
            ->setSearch(['mobile' => '手机号']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['mobile', '手机号'],
                ['qq', 'QQ'],
                ['weixin', '微信'],
                ['alipay', '支付宝'],
                ['shopid', '机构', 'select', $list_type],
                ['create_time', '创建时间', 'datetime'],
                ['status', '状态', 'switch'],
                ['right_button', '操作', 'btn']
            ])
            // ->addTopButtons('enable,disable,delete') // 批量添加顶部按钮
            ->addRightButtons('edit') // 批量添加右侧按钮
            ->addRightButton('custom', $btnAdd)
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染页面
    }

    /**
     * 新增
     * @author zg
     * @return mixed
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'User');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);

            if ($user = UserModel::create($data)) {
                Hook::listen('user_add', $user);
                // 记录行为
                action_shop_log('user_add', 'shop_user', $user['id'], UID);
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }

        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('新增') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['text', 'username', '用户名', '必填，可由英文字母、数字组成'],
                ['text', 'nickname', '昵称', '可以是中文'],
                ['select', 'role', '角色', '', RoleModel::getTree(null, false)],
                ['text', 'email', '邮箱', ''],
                ['password', 'password', '密码', '必填，6-20位'],
                ['text', 'mobile', '手机号'],
                ['image', 'avatar', '头像'],
                ['radio', 'status', '状态', '', ['禁用', '启用'], 1]
            ])
            ->fetch();
    }

    /**
     * 编辑
     * @param null $id 用户id
     * @author zg
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();

            $save['id'] = $data['id'];
            $save['username'] = $data['username'];
            $save['nickname'] = $data['nickname'];
            $save['password'] = $data['password'];
            $save['mobile'] = $data['mobile'];
            
            $save['qq'] = $data['qq'];
            $save['weixin'] = $data['weixin'];
            $save['alipay'] = $data['alipay'];

            $save['status'] = $data['status'];
            $save['recommond'] = $data['recommond'];

            if (MemberModel::update($save)) {
                $user = MemberModel::get($save['id']);
                //更新属表
                $save1['id'] = $data['bid'];
                $save1['per'] = $data['per'];
                $save1['wordchart'] = $data['wordchart'];
                $save1['speechchart'] = $data['speechchart'];
                $save1['videochart'] = $data['videochart'];
                $save1['facechart'] = $data['facechart'];
                $save1['intro'] = $data['intro'];
                $save1['employment'] = $data['employment'];
                $save1['remark'] = $data['remark'];
                $save1['tags'] = implode(',', $data['tags']);
                //业务类弄
                // $save1['tags'] = CateAccessModel::where('shopid', $data['shopid'])->column('cids')[0];
                CounsellorotModel::update($save1);
                // 记录行为
                action_log('user_edit', 'shop_counsellor', $user['id'], UID, get_shop_nickname($user['id']));
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        // $info = CounsellorModel::where('id', $id)->find();
        $info = MemberModel::getCounsellorList($id);

        $list_type = CategoryModel::where('status', 1)->column('id,title');

        // 使用ZBuilder快速创建表单 
        return ZBuilder::make('form')
            ->setPageTitle('编辑') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['hidden', 'id'],
                ['hidden', 'bid'],
                ['hidden', 'shopid'],
                ['text', 'username', '用户名', '必填，可由英文字母、数字组成'],
                ['text', 'nickname', '昵称', '可以是中文'],
                ['password', 'password', '密码', '必填，6-20位'],
                ['text', 'mobile', '手机号'],
                ['text', 'qq', 'QQ'],
                ['text', 'weixin', '微信'],
                ['text', 'alipay', '支付宝'],
                ['radio', 'status', '状态', '', ['禁用', '启用']],
                ['radio', 'recommond', '推荐', '', ['不推荐', '推荐']],
                // ['date', 'employment', '从业时间'],
                ['number', 'per', '每次单价'],
                ['text', 'wordchart', '文字咨询'],
                ['text', 'speechchart', '语音咨询'],
                ['text', 'videochart', '视频咨询'],
                ['text', 'facechart', '面对面咨询'],
                ['textarea', 'intro', '简介'],
            ])
            ->addDatetime('employment', '从业时间', '', '', 'YYYY-MM-DD')
            ->addSelect('tags', '业务分类', '', $list_type,'','multiple')
            ->addUeditor('remark', '祥细说明')
            ->hideBtn('submit')
            ->setFormData($info) // 设置表单数据
            ->fetch();
    }

       public function point($id = null)
       {
           if ($id === null) $this->error('缺少参数');

            cookie('__forward__', $_SERVER['REQUEST_URI']);

            // 获取查询条件
            $map = $this->getMap();

            $map['memberid'] = $id;
            // 数据列表
            $data_list = PointModel::where($map)->order('id desc')->paginate();

            // 分页数据
            $page = $data_list->render();


            $list_type = MemberModel::where('status', 1)->column('id,username');

            // 使用ZBuilder快速创建数据表格
            return ZBuilder::make('table')
                ->setPageTitle('会员管理') // 设置页面标题
                ->setTableName('member_point') // 设置数据表名
                ->setSearch(['mobile' => '手机号']) // 设置搜索参数
                ->hideCheckbox()
                ->addColumns([ // 批量添加列
                    ['id', 'ID'],
                    ['behavior_type', '行为类型',['获得','消费']],
                    ['behavior', '行为描述'],
                    ['memberid', '会员', 'select', $list_type],
                    ['point', '积分值'],
                    ['create_time', '创建时间', 'datetime'],
                    // ['right_button', '操作', 'btn']
                ])
                ->addTopButton('back', [
                    'title' => '返回咨询师列表',
                    'icon'  => 'fa fa-reply',
                    'href'  => url('counsellor/index')
                ])
                // ->addTopButtons('delete') // 批量添加顶部按钮
                // ->addRightButtons('delete') // 批量添加右侧按钮
                ->setRowList($data_list) // 设置表格数据
                ->setPages($page) // 设置分页数据
                ->fetch(); // 渲染页面
       }

    /**
     * 删除用户
     * @param array $ids 用户id
     * @author zg
     * @return mixed
     */
    public function delete($ids = [])
    {
        // Hook::listen('user_delete', $ids);
        return $this->setStatus('delete');
    }

    /**
     * 启用用户
     * @param array $ids 用户id
     * @author zg
     * @return mixed
     */
    public function enable($ids = [])
    {
        // Hook::listen('user_enable', $ids);
        return $this->setStatus('enable');
    }

    /**
     * 禁用用户
     * @param array $ids 用户id
     * @author zg
     * @return mixed
     */
    public function disable($ids = [])
    {
        // Hook::listen('user_disable', $ids);
        return $this->setStatus('disable');
    }

    /**
     * 设置用户状态：删除、禁用、启用
     * @param string $type 类型：delete/enable/disable
     * @param array $record
     * @author zg
     * @return mixed
     */
    public function setStatus($type = '', $record = [])
    {
        $ids        = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $menu_title = MemberModel::where('id', 'in', $ids)->column('mobile');
        return parent::setStatus($type, ['member_'.$type, 'member', 0, UID, implode('、', $menu_title)]);
    }

    /**
     * 快速编辑
     * @param array $record 行为日志
     * @author zg
     * @return mixed
     */
    public function quickEdit($record = [])
    {
        $id      = input('post.pk', '');
        $id      == UID && $this->error('禁止操作当前账号');
        $field   = input('post.name', '');
        $value   = input('post.value', '');
        $config  = UserModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $config . ')，新值：(' . $value . ')';
        return parent::quickEdit(['user_edit', 'shop_user', $id, UID, $details]);
    }
}
