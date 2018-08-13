<?php


namespace app\shop\controller;


use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Active as ActiveModel;
use app\shop\model\Activeallot as ActiveallotModel;
use app\cms\model\Category as CategoryModel;
use app\cms\model\Agency as AgencyModel;
use app\shop\model\User as UserModel;
use util\Tree;
use think\Db;

/**
 * 属性控制器
 * @package app\cms\admin
 */
class Active extends Shop
{
    /**
     * 菜单列表
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        $info = UserModel::where('id', UID)->field('password', true)->find();
        if (!$info['shopid']) {
             $this->error('缺少参数');
        }
        $map['shopid'] = $info['shopid'];
        // 数据列表
        $data_list = ActiveModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();
        $btnAdd = ['icon' => 'fa fa-fw fa-eye', 'title' => '查看', 'href' => url('look', ['id' => '__id__'])];

        $list_module = CategoryModel::where(1)->order('id desc')->column('id as cateid,title');
        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['title' => '标题'])// 设置搜索框
            ->hideCheckbox()
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['title', '标题'],
                ['cateid', '分类名','','',$list_module],
                ['price', '金额'],
                ['num', '限定人数'],
                ['start_time', '开始时间','datetime'],
                ['endtime', '结束时间','datetime'],
                ['statustext', '状态'],
                ['right_button', '操作', 'btn']
            ])
            ->raw('statustext')
            ->addRightButton('custom', $btnAdd)
            // ->addTopButton('add', ['href' => url('add')])
            // ->addRightButton('edit')
            // ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }

    /**
     * 查看
     * @return mixed
     */
    public function look($id = null)
    {
        if ($id === null) $this->error('缺少参数');
        $info = ActiveModel::get($id);

        $shopname = AgencyModel::where('id', $info['shopid'])->value('title');
        $catetitle = CategoryModel::where('id', $info['cateid'])->value('title');

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['static', 'catetitle', '分类','',$catetitle],
                ['static', 'shopname', '分中心','',$shopname],
                ['static', 'title', '标题','',$info['title']],
                ['static', 'num', '限定人数','',$info['num']],
                ['static', 'price', '定价','',$info['price']],
                ['static', 'st', '开始时间','',date('Y-m-d H:i:s',$info['start_time'])],
                ['static', 'et', '结束时间','',date('Y-m-d H:i:s',$info['endtime'])],
                ['image', 'pics', '课程封面','',$info['pic']],
            ])
            ->addWangeditor('describe', '内容')
            ->hideBtn('submit')
            ->setFormData(ActiveModel::get($id))
            ->fetch();
    }

    /**
     * [allot 分配]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function allot($id = null)
    {
        if ($id === null) $this->error('缺少参数');

        $u = UserModel::where('id', UID)->field('password', true)->find();
        if (!$u['shopid']) {
             $this->error('缺少参数');
        }
        $map['shopid'] = $u['shopid'];

        $users = UserModel::where('status', 1)->column('id,nickname');

        //获取分配ID
        $map['acitveid'] = $id;
        $info =  ActiveallotModel::where($map)->find();

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            // $result = $this->validate($data, 'Classes');
            // if(true !== $result) $this->error($result);
            $data['tearchid'] = implode(',', $data['tearchid']);
            $data['coachid'] = implode(',', $data['coachid']);
            if ($info) {//编辑
                if (ActiveallotModel::update($data)) {
                    // 记录行为
                    $this->success('编辑成功', url('index'));
                } else {
                    $this->error('编辑失败');
                }

            }else{//新增
                $data['classid'] = $id;
                $data['shopid'] = $u['shopid'];
                if (ActiveallotModel::create($data)) {
                // 记录行为
                    $this->success('新增成功', url('index'));
                } else {
                    $this->error('新增失败');
                }
            }
            
        }
        

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                // ['select', 'adminid', '管理员', '', $users],
                // ['text', 'ascale', '管理员分配比例',['%']],

                // ['select', 'tearchid', '讲师员', '<code>可多选</code>', $users,'multiple'],
                // ['text', 'tscale', '讲师员分配比例','<code>多人时以逗号分隔（10,20）</code>',['', '%']],

                // ['select', 'coachid', '辅导员', '<code>可多选</code>', $users,'multiple'],
                // ['text', 'cscale', '辅导员分配比例','<code>多人时以逗号分隔（10,20）</code>',['', '%']],

            ])
            ->addSelect('adminid', '管理员', '', $users,'')
            ->addSelect('tearchid', '讲师员', '<code>可多选</code>', $users,'','multiple')
            ->addSelect('coachid', '辅导员', '<code>可多选</code>', $users,'','multiple')
            ->addText('ascale', '管理员分配比例', '', '', ['', '%'])
            ->addText('tscale', '讲师员分配比例', '<code>多人时以逗号分隔比如10%,20%（写成10,20）</code>', '', ['', '%'])
            ->addText('cscale', '辅导员分配比例', '<code>多人时以逗号分隔比如10%,20%（写成10,20）</code>', '', ['', '%'])
            ->addText('sscale', '本机构分配比例', '', '', ['', '%'])
            ->addText('mscale', '平台分配比例', '', '', ['', '%'])
            ->setFormData($info)
            ->fetch();
    }

    /**
     * 新增
     * @return mixed
     */
    public function add()
    {
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Classes');
            if(true !== $result) $this->error($result);
            $data['created_time'] = time();
            $data['start_time'] = strtotime($data['start_time']);
            $data['endtime'] = strtotime($data['endtime']);
            if ($props = ActiveModel::create($data)) {
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }

        $list_type = AgencyModel::where('status', 1)->column('id,title');

        $catelist = CategoryModel::where('status', 1)->column('id,title');

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['select', 'cateid', '分类', '<code>必选</code>', $catelist],
                ['select', 'shopid', '分中心', '<code>必选</code>', $list_type],
                ['text', 'title', '标题'],
                ['number', 'num', '限定人数'],
                ['text', 'price', '定价'],
                ['datetime', 'start_time', '开始时间'],
                ['datetime', 'endtime', '结束时间'],
                ['image', 'pic', '活动封面'],
            ])
            ->addWangeditor('describe', '内容')
            ->fetch();
    }

    /**
     * 编辑
     * @param null $id 菜单id
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($id === null) $this->error('缺少参数');
        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
            $result = $this->validate($data, 'Classes');
            if(true !== $result) $this->error($result);
            $data['start_time'] = strtotime($data['start_time']);
            $data['endtime'] = strtotime($data['endtime']);
            if (ActiveModel::update($data)) {
                // 记录行为
                $this->success('编辑成功', url('index'));
            } else {
                $this->error('编辑失败');
            }
        }
        $list_type = AgencyModel::where('status', 1)->column('id,title');

        $catelist = CategoryModel::where('status', 1)->column('id,title');

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],

                ['select', 'cateid', '分类', '<code>必选</code>', $catelist],
                ['select', 'shopid', '分中心', '<code>必选</code>', $list_type],
                ['text', 'title', '标题'],
                ['number', 'num', '限定人数'],
                ['text', 'price', '定价'],
                ['datetime', 'start_time', '开始时间'],
                ['datetime', 'endtime', '结束时间'],
                ['image', 'pic', '活动封面'],
            ])
             ->addWangeditor('describe', '内容')
            ->setFormData(ActiveModel::get($id))
            ->fetch();
    }

    /**
     * 删除菜单
     * @param null $ids 菜单id
     * @author zg
     * @return mixed
     */
    public function delete($ids = null)
    {
        
        return $this->setStatus('delete');
    }

    /**
     * 启用菜单
     * @param array $record 行为日志
     * @author zg
     * @return mixed
     */
    public function enable($record = [])
    {
        return $this->setStatus('enable');
    }

    /**
     * 禁用菜单
     * @param array $record 行为日志
     * @author zg
     * @return mixed
     */
    public function disable($record = [])
    {
        return $this->setStatus('disable');
    }

    /**
     * 设置菜单状态：删除、禁用、启用
     * @param string $type 类型：delete/enable/disable
     * @param array $record
     * @author zg
     * @return mixed
     */
    public function setStatus($type = '', $record = [])
    {
        $ids        = $this->request->isPost() ? input('post.ids/a') : input('param.ids');
        $menu_title = ActiveModel::where('id', 'in', $ids)->column('title');
        return parent::setStatus($type, ['acitve_'.$type, 'active', 0, UID, implode('、', $menu_title)]);
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
        $field   = input('post.name', '');
        $value   = input('post.value', '');
        $menu    = ActiveModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $menu . ')，新值：(' . $value . ')';
        return parent::quickEdit(['Acitve_edit', 'class', $id, UID, $details]);
    }
}