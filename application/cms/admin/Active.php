<?php


namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Active as ActiveModel;
use app\cms\model\Counsellor as CounsellorModel;
use app\shop\model\Activeallot as ActiveallotModel;
use app\cms\model\Classestemp as ClassestempModel;
use app\cms\model\Clacategory as ClacategoryModel;
use app\cms\model\Category as CategoryModel;
use app\cms\model\Agency as AgencyModel;
use util\Tree;
use think\Db;

/**
 * 属性控制器
 * @package app\cms\admin
 */
class Active extends Admin
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

        // 数据列表
        $data_list = ActiveModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();

        $allotAdd = ['icon' => 'fa fa-plus', 'title' => '分配', 'href' => url('allot', ['id' => '__id__'])];
        $list_module = ClacategoryModel::where(1)->order('id desc')->column('id as cateid,title');
        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['title' => '标题'])// 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['title', '标题'],
                ['cateid', '分类名','','',$list_module],
                ['price', '金额'],
                ['limitnum', '限定人数'],
                ['start_time', '开始时间','datetime'],
                ['endtime', '结束时间','datetime'],
                ['statustext', '状态'],
                ['right_button', '操作', 'btn']
            ])
            ->raw('statustext')
            ->addTopButton('add', ['href' => url('add')])
            ->addRightButton('edit')
            ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->addRightButton('custom', $allotAdd)
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }

    /**
     * [allot 分配]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function allot($id = null)
    {
        if ($id === null) $this->error('缺少参数');

        $users = CounsellorModel::where('status', 1)->column('id,nickname');

        //获取分配ID
        $map['activeid'] = $id;
        $info =  ActiveallotModel::where($map)->find();

        // 保存数据
        if ($this->request->isPost()) {
            // 表单数据
            $data = $this->request->post();

            // 验证
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
                $data['activeid'] = $id;
                $data['shopid'] = 0;
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

            ])
            ->addSelect('adminid', '管理员', '', $users,'')
            ->addSelect('tearchid', '讲师员', '<code>可多选</code>', $users,'','multiple')
            ->addSelect('coachid', '辅导员', '<code>可多选</code>', $users,'','multiple')
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
                $data['classid'] = $props['id'];
                $data['type'] = 1;
                ClassestempModel::create($data);
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }

        $list_type = AgencyModel::where('status', 1)->column('id,title');

        $catelist = ClacategoryModel::where('status', 1)->column('id,title');

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['select', 'cateid', '分类', '<code>必选</code>', $catelist],
                ['select', 'shopid', '分中心', '<code>必选</code>', $list_type],
                ['text', 'title', '标题'],
                ['number', 'limitnum', '限定人数'],
                ['text', 'price', '定价'],
                ['text', 'address', '地址'],
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
                $data['classid'] = $data['id'];
                $data['type'] = 1;
                unset($data['id']);

                $map['type'] = $data['type'];
                $map['classid'] = $data['classid'];
                db('cms_clac_temp')->where($map)->update($data);
                // 记录行为
                $this->success('编辑成功', url('index'));
            } else {
                $this->error('编辑失败');
            }
        }
        $list_type = AgencyModel::where('status', 1)->column('id,title');

        $catelist = ClacategoryModel::where('status', 1)->column('id,title');

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],

                ['select', 'cateid', '分类', '<code>必选</code>', $catelist],
                ['select', 'shopid', '分中心', '<code>必选</code>', $list_type],
                ['text', 'title', '标题'],
                ['number', 'limitnum', '限定人数'],
                ['text', 'price', '定价'],
                ['text', 'address', '地址'],
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
        //关联删除
        
        if ($ids) {
            db('cms_clac_temp')->where(['classid'=>$ids,'type'=>1])->delete();
        }
        
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