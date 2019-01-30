<?php


namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Addr as AddrModel;
use util\Tree;
use think\Db;

/**
 * 属性控制器
 * @package app\cms\admin
 */
class Addr extends Admin
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
        $data_list = AddrModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['title' => '标题'])// 设置搜索框
            ->addColumns([ // 批量添加数据列
                ['id', 'ID'],
                ['shotnm', '简称'],
                ['fullnm', '详细地址'],
                ['status', '状态', 'status', '', ['0' => '禁用:danger', '1' => '启用:success']],
                ['right_button', '操作', 'btn']
            ])
            ->addTopButtons('add,enable,disable') // 批量添加顶部按钮
            ->addRightButtons('edit,delete')
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
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

            if ($Cards = AddrModel::create($data)) {
                
                $this->success('新增成功', url('index'));
            } else {
                $this->error('新增失败');
            }
        }

        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['text', 'shotnm', '简称','<code>eg:上海徐汇区</code>'],
                ['text', 'fullnm', '详细地址','<code>eg:上海徐汇区 桂林路xxx号</code>'],
                ['radio', 'status', '状态', '', ['禁用', '启用'], 1],
            ])
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
            if (AddrModel::update($data)) {
                $this->success('编辑成功', url('index'));
            } else {
                $this->error('编辑失败');
            }
        }
        
        // 显示添加页面
        return ZBuilder::make('form')
            ->addFormItems([
                ['hidden', 'id'],
                ['text', 'shotnm', '简称'],
                ['text', 'fullnm', '详细地址'],
                ['radio', 'status', '状态', '', ['禁用', '启用'], 1],
            ])
            ->setFormData(AddrModel::get($id))
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
        $menu_title = AddrModel::where('id', 'in', $ids)->column('shotnm');
        return parent::setStatus($type, ['AddrModel_'.$type, 'class', 0, UID, implode('、', $menu_title)]);
    }

    
}