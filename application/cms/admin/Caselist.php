<?php


namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Counsellor as CounsellorModel;
use app\cms\model\Counsellorot as CounsellorotModel;
use app\cms\model\Agency as AgencyModel;
use app\cms\model\Point as PointModel;
use app\cms\model\Category as CategoryModel;
use app\cms\model\CateAccess as CateAccessModel;
use app\cms\model\Trade as TradeModel;
use app\cms\model\Casetab as CasetabModel;
use util\Tree;
use think\Db;
use think\Hook;

/**
 * 咨询师默认控制器
 * @package app\member\admin
 */
class Caselist extends Admin
{
    /**
     * 案例首页
     * @TODO 所属机构
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();
        
        // 数据列表
        $data_list = CasetabModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();
        
        $btnAdd = ['icon' => 'fa fa-fw fa-search', 'title' => '查看', 'href' => url('edit', ['id' => '__id__'])];

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('案例管理') // 设置页面标题
            ->setTableName('member') // 设置数据表名
            ->setSearch(['mobile' => '手机号']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['mobile', '手机号'],
                ['truename', '姓名'],
                ['sex', '性别', '', '', ['女', '男']],
                ['birthday', '年龄'],
                ['grade', '年级'],
                ['marital', '婚姻', '', '', ['否', '是']],
                ['profession', '职业'],
                ['timerange', '预约时间'],
                ['chat', '咨询方式'],
                ['caseStarttime', '开始时间'],
                ['caseEndtime', '结束时间'],
                ['create_time', '创建时间', 'datetime'],
                ['right_button', '操作', 'btn']
            ])
            // ->addTopButtons('enable,disable,delete') // 批量添加顶部按钮
            ->addRightButton('custom', $btnAdd)
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染页面
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

            if (CasetabModel::update($save)) {
                $this->success('编辑成功', cookie('__forward__'));
            } else {
                $this->error('编辑失败');
            }
        }

        // 获取数据
        $info = CasetabModel::where('id', $id)->find();
        //处理数据
        $info['Avl'] = number_format(array_sum(explode(',', $info['Avl']))/count(explode(',', $info['Avl'])),2);
        $info['A2vl'] = number_format(array_sum(explode(',', $info['A2vl']))/count(explode(',', $info['A2vl'])),2);
        $info['Bvl'] = number_format(array_sum(explode(',', $info['Bvl']))/count(explode(',', $info['Bvl'])),2);
        $info['M1vl'] = number_format(array_sum(explode(',', $info['M1vl']))/count(explode(',', $info['M1vl'])),2);
        $info['AMvl'] = number_format(array_sum(explode(',', $info['AMvl']))/count(explode(',', $info['AMvl'])),2);
        $info['M2vl'] = number_format(array_sum(explode(',', $info['M2vl']))/count(explode(',', $info['M2vl'])),2);
        $info['PLAN'] = number_format(array_sum(explode(',', $info['PLAN']))/count(explode(',', $info['PLAN'])),2);

        // 使用ZBuilder快速创建表单 
        return ZBuilder::make('form')
            ->setPageTitle('查看') // 设置页面标题
            ->addFormItems([ // 批量添加表单项
                ['hidden', 'id'],
                ['text', 'truename', '用户名'],
                ['radio', 'sex', '性别', '', ['女', '男']],
                ['text', 'birthday', '年龄'],
                ['text', 'edu', '学历'],
                ['text', 'grade', '年级'],
                ['radio', 'marital', '婚姻', '', ['否', '是']],
                ['text', 'profession', '职业'],
                ['text', 'mobile', '手机号'],
                ['text', 'timerange', '预约时间'],
                ['date', 'caseStarttime', '开始时间'],
                ['date', 'caseEndtime', '结束时间'],

                ['text', 'caseS', 'S'],
                ['text', 'caseR9', 'R9'],
                ['text', 'caseSOR', 'SOR'],
                ['text', 'casePNF', 'PNF'],

                ['text', 'Avl', 'A'],
                ['text', 'A2vl', '2A'],
                ['text', 'Bvl', 'B'],
                ['text', 'M1vl', '1M'],
                ['text', 'AMvl', 'A-M'],
                ['text', 'M2vl', '2M'],
                ['text', 'PLAN', '计划'],

                ['textarea', 'caseMasterQs', '资金投向'],
                ['textarea', 'casefamilyQs', '家属主诉'],
                ['textarea', 'caseSkill', '应用技术'],
                ['textarea', 'caseWork', '作业'],
                ['textarea', 'caseResult', '效果评估'],
                ['textarea', 'caseStreng', '加强项目'],
                ['textarea', 'caseMark', '备注'],

            ])
            // ->addDatetime('caseStarttime', '开始时间', '', '', 'YYYY-MM-DD')
            // ->addDatetime('caseEndtime', '结束时间', '', '', 'YYYY-MM-DD')
            ->setFormData($info) // 设置表单数据
            ->hideBtn('submit')
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


        $list_type = CounsellorModel::where('status', 1)->column('id,username');

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
    * [income 收入列表]
    * @param  [type] $id [description]
    * @return [type]     [description]
    */
   public function income($id = null)
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


        $list_type = CounsellorModel::where('status', 1)->column('id,username');

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('收入管理') // 设置页面标题
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
            ])
            ->addTopButton('back', [
                'title' => '返回咨询师列表',
                'icon'  => 'fa fa-reply',
                'href'  => url('counsellor/index')
            ])
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
        $config  = CounsellorModel::where('id', $id)->value($field);
        $details = '字段(' . $field . ')，原值(' . $config . ')，新值：(' . $value . ')';
        return parent::quickEdit(['member_edit', 'admin_member', $id, UID, $details]);
    }
}