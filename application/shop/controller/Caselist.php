<?php


namespace app\shop\controller;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Counsellor as CounsellorModel;
use app\cms\model\Trade as TradeModel;
use app\cms\model\Agency as AgencyModel;
use app\cms\model\Casetab as CasetabModel;
use app\shop\model\User as UserModel;
use util\Tree;
use think\Db;
use think\Hook;

/**
 * 咨询师收入默认控制器
 * @package app\member\admin
 */
class Caselist extends Shop
{
    /**
     * 咨询师首页
     * @return mixed
     */
    public function index($id=null)
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();
        if ($id) {
            $map['memberid'] = $id;
        }

        $info = UserModel::where('id', UID)->field('password', true)->find();
        if (!$info['shopid']) {
             $this->error('缺少参数');
        }
        $map['b.shopid'] = $info['shopid'];
        // 数据列表
        $data_list = db('case')->alias('a')->field('a.*')->join('calendar c',' a.cid = c.id','LEFT')->join('trade b',' b.id = c.tid','LEFT')->where($map)->order('a.id DESC')->paginate();
        // $data_list = CounsellorModel::where($map)->order('id desc')->paginate();

        // 分页数据
        $page = $data_list->render();

        
        $incomeBtn = ['icon' => 'fa fa-fw fa-cny', 'title' => '收入明细', 'href' => url('income', ['id' => '__id__'])];
        $btnAdd = ['icon' => 'fa fa-fw fa-eye', 'title' => '查看', 'href' => url('look', ['id' => '__id__'])];
        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setPageTitle('咨询文案管理') // 设置页面标题
            ->setTableName('member') // 设置数据表名
            ->hideCheckbox()
            ->setSearch(['mobile' => '手机号']) // 设置搜索参数
            ->addColumns([ // 批量添加列
                ['id', 'ID'],
                ['truename', '姓名'], 
                ['sex', '性别','text','',['女', '男']],
                ['birthday', '年龄'],
                ['edu', '学历'],
                ['grade', '年级'],
                ['marital', '婚姻','text','',['否', '是']],
                ['profession', '职业'],
                ['mobile', '手机号'],
                ['timerange', '预约时间'],
                ['chat', '咨询方式'],
                ['caseStarttime', '开始时间'],
                ['caseEndtime', '结束时间'],
                ['right_button', '操作', 'btn']
            ])
            ->raw('income')
            // ->addTopButtons('enable,disable,delete') // 批量添加顶部按钮
            // ->addRightButtons('edit') // 批量添加右侧按钮
            ->addRightButton('custom', $btnAdd)
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染页面
    }

    public function look($id = null)
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
            ->hideBtn('submit')
            ->setFormData($info)
            ->fetch();
    }


    
}
