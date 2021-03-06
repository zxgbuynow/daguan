<?php


namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Counsellor as CounsellorModel;
use app\cms\model\Category as CategoryModel;
use app\cms\model\CateAccess as CateAccessModel;
use app\cms\model\Agency as AgencyModel;
use app\shop\model\User as UserModel;
use util\Tree;
use think\Db;
use think\Hook;

/**
 * 咨询师默认控制器
 * @package app\member\admin
 */
class Agency extends Admin
{
    /**
     * 咨询师首页
     * @TODO 所属机构
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();
        
        // 数据列表
        $data_list = AgencyModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();

        $btnAdd = ['icon' => 'fa fa-plus', 'title' => '添加管理员', 'href' => url('admin', ['id' => '__id__'])];
        $cateAdd = ['icon' => 'fa fa-list', 'title' => '设置业务分类', 'href' => url('cate', ['id' => '__id__'])];

        $list_type = UserModel::where('status', 1)->column('id,username');
        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('咨询机构管理') // 设置页面标题
            ->setTableName('shop_agency') // 设置数据表名
            ->setSearch(['title' => '分机构名']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['title', '分机构名'],
                ['city', '地区', 'text.edit'],
                ['description', '描述'],
                ['adminid', '管理员', 'select', $list_type],
                ['create_time', '创建时间', 'datetime'],
                ['status', '状态', 'switch'],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons('enable,disable,delete,add') // 批量添加顶部按钮
            ->addRightButtons('delete,edit') // 批量添加右侧按钮
            ->addRightButton('custom', $btnAdd)
            ->replaceRightButton(['adminid' => ['<>', 0]], '', ['custom'])
            ->addRightButton('custom1', $cateAdd)
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
            if ($data['map_address']) {
                $data['city'] = mb_substr($data['map_address'],0,5,'utf-8');
            }
            // 验证
            $result = $this->validate($data, 'Agency');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);
            $data['create_time'] = time();
            if ($data = AgencyModel::create($data)) {
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }
        $map['status'] = 1;
        $map['shopid'] = 0;
        $list = UserModel::where($map)->column('id,username');
        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('新增') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['text', 'title', '分机构名'],
                // ['text', 'city', '地区','<code>最合适四个字</code>'],
                ['text', 'description', '描述'],
                ['image', 'lincence', '营业执照'],
                // ['text', 'address', '地址'],
                ['text', 'manager', '负责人'],
                ['text', 'tel', '电话'],
                ['text', 'webchat', '微信'],
                ['text', 'aptiude', '资质'],
                ['text', 'longvity', '质历'],
                ['text', 'bail', '保证金'],
                ['datetime', 'setup', '创办时间'],
                ['select', 'adminid', '分机构管理员', '', $list],
                ['radio', 'status', '状态', '', ['冻结', '审核','认证'], 1]
            ])
            ->addBmap('map', '地图', 'fDNzsAsd9KpOfOR1GEvvYo1ysQMcTwIM', '', '', '')
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
            if ($data['map_address']) {
                $data['city'] = mb_substr($data['map_address'],0,5,'utf-8');
            }
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
                // ['text', 'city', '地区','<code>最合适四个字</code>'],
                ['text', 'description', '描述'],
                ['image', 'lincence', '营业执照'],
                // ['text', 'address', '地址'],
                ['text', 'manager', '负责人'],
                ['text', 'tel', '电话'],
                ['text', 'webchat', '微信'],
                ['text', 'aptiude', '资质'],
                ['text', 'longvity', '质历'],
                ['text', 'bail', '保证金'],
                ['datetime', 'setup', '创办时间'],
                ['select', 'adminid', '分机构管理员', '', $list],
                ['radio', 'status', '状态', '', ['冻结', '审核','认证'], 1]
            ])
            ->addBmap('map', '地图', 'fDNzsAsd9KpOfOR1GEvvYo1ysQMcTwIM')
            ->setFormData($info) // 设置表单数据
            ->fetch();
    }


   public function adminlist()
    {

        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        // 数据列表
        $data_list = UserModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();


        $list_type = AgencyModel::where('status', 1)->column('id,title');

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('分中心管理员维护') // 设置页面标题
            ->setTableName('shop_user') // 设置数据表名
            ->setSearch(['nickname' => '名称','username'=>'用户名']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['username', '用户名'],
                ['nickname', '名称'],
                ['shopid', '所属机构', 'select', $list_type],
                ['create_time', '创建时间', 'datetime']
            ])
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染页面

    }
   public function admin($id = null)
   {
       if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $data['role'] = 1;
            $data['access'] = 1;
            $data['shopid'] = $id;
            $result = $this->validate($data, 'User');
            // 验证失败 输出错误信息
            if(true !== $result) $this->error($result);
            
            if ($user = UserModel::create($data)) {
                //更新字段
                $adata['adminid'] = $user;
                $adata['id'] = $id;
                AgencyModel::update($adata);
                
                // 记录行为
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
                ['text', 'email', '邮箱', ''],
                ['password', 'password', '密码', '必填，6-20位'],
                ['text', 'mobile', '手机号'],
                ['radio', 'status', '状态', '', ['禁用', '启用'], 1]
            ])
            ->fetch();
   }

   public function cate($id = null)
   {
        if ($id === null) $this->error('缺少参数');

        // 保存数据
        if ($this->request->isPost()) {
            $data = $this->request->post();
            
            if (CateAccessModel::where('shopid',$id)->find()) {//编辑
                $map['shopid'] = $id;
                $save['cids'] = implode(',', $data['cids']);
                if (db('shop_cate_access')->where($map)->update($save)) {
                    
                    // 记录行为
                    $this->success('编辑成功', url('index'));
                } else {
                    $this->error('编辑失败');
                }  
                
            }else{//添加

                $save['cids'] = implode(',', $data['cids']);
                $save['shopid'] = $id;
                if (db('shop_cate_access')->insert($save)) {
                    
                    // 记录行为
                    $this->success('新增成功', url('index'));
                } else {
                    $this->error('新增失败');
                }
            }
            
            
        }

        $info = CateAccessModel::where('shopid',$id)->find();

        $list_type = CategoryModel::where('status', 1)->column('id,title');

        // 使用ZBuilder快速创建表单
        return ZBuilder::make('form')
            ->setPageTitle('业务分类') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                
            ])
            ->addSelect('cids', '业务分类', '', $list_type,'','multiple')
            ->setFormData($info) // 设置表单数据
            ->fetch();
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
        $menu_title = CounsellorModel::where('id', 'in', $ids)->column('mobile');
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
        $config  = AgencyModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $config . ')，新值：(' . $value . ')';
        return parent::quickEdit(['user_edit', 'shop_user', $id, UID, $details]);
    }
}
