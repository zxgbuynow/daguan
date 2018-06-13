<?php


namespace app\shop\controller;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Counsellor as CounsellorModel;
use app\cms\model\Trade as TradeModel;
use app\cms\model\Agency as AgencyModel;
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
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();


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
                // ['right_button', '操作', 'btn']
            ])
            ->raw('income')
            // ->addTopButtons('enable,disable,delete') // 批量添加顶部按钮
            ->addRightButtons('edit') // 批量添加右侧按钮
            // ->addRightButton('custom', $incomeBtn)
            ->setRowList($data_list) // 设置表格数据
            ->setPages($page) // 设置分页数据
            ->fetch(); // 渲染页面
    }

    
}
