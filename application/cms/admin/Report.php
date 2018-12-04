<?php


namespace app\cms\admin;

use app\admin\controller\Admin;
use app\common\builder\ZBuilder;
use app\cms\model\Coupon as CouponModel;
use app\cms\model\Counsellor as CounsellorModel;
use app\cms\model\Classes as ClassModel;
use app\cms\model\Active as ActiveModel;
use app\cms\model\Classestemp as ClassestempModel;
use app\cms\model\Category as CategoryModel;
use app\cms\model\Agency as AgencyModel;
use app\cms\model\Calendar as CalendarModel;
use util\Tree;
use think\Db;

/**
 * 属性控制器
 * @package app\cms\admin
 */
class Report extends Admin
{
    /**
     * 用户列表
     * @return mixed
     */
    public function index()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        // 数据列表
        $data_list = AgencyModel::where($map)->order('id desc')->paginate();

        // // 分页数据
        $page = $data_list->render();


        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['title' => '标题'])// 设置搜索框
            ->hideCheckbox()
            ->addColumns([ // 批量添加数据列
                ['title', '分中心'],
                ['runum', '注册用户数'],
                ['dunum', '停用用户数'],
                ['onunum', '线上咨询用户数'],
                ['ofunum', '线下咨询用户数'],
                ['clunum', '课程用户数'],
                ['acunum', '活动用户数'],
                // ['right_button', '操作', 'btn']
            ])
            ->raw('runum')
            ->raw('dunum')
            ->raw('onunum')
            ->raw('ofunum')
            ->raw('clunum')
            ->raw('acunum')
            // ->addTopButton('add', ['href' => url('add')])
            // ->addRightButton('edit')
            // ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }

    /**
     * 咨询师列表
     * @return mixed
     */
    public function counsollor()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        // 数据列表
        $data_list = AgencyModel::where($map)->order('id desc')->paginate();

        // // 分页数据
        $page = $data_list->render();


        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['title' => '标题'])// 设置搜索框
            ->hideCheckbox()
            ->addColumns([ // 批量添加数据列
                ['title', '分中心'],
                ['dcnum', '待审核咨询师人数'],
                ['rcnum', '正式咨询师人数'],
                ['bycnum', '正式咨询师咨询数'],
                ['rnum', '推荐人数'],
            ])
            ->raw('dcnum')
            ->raw('rcnum')
            ->raw('bycnum')
            ->raw('rnum')
            // ->addTopButton('add', ['href' => url('add')])
            // ->addRightButton('edit')
            // ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }

    /**
     * 咨询师列表
     * @return mixed
     */
    public function calendar()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        // 数据列表
        $data_list = CalendarModel::where($map)->order('id desc')->paginate();

        // // 分页数据
        $page = $data_list->render();


        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['title' => '预约内容'])// 设置搜索框
            ->hideCheckbox()
            ->addColumns([ // 批量添加数据列
                ['agency', '分中心'],
                ['username', '咨询姓名'],
                ['counsollor', '咨询师'],
                ['start_time', '预约时间','datetime'],
                ['title', '预约种类'],
                ['address', '预约地点'],
            ])
            ->raw('agency')
            ->raw('username')
            ->raw('counsollor')
            ->raw('address')
            // ->addTopButton('add', ['href' => url('add')])
            // ->addRightButton('edit')
            // ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }

    /**
     * 咨询师收入列表
     * @return mixed
     */
    public function cincome()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        // 数据列表
        $data_list = CounsellorModel::where($map)->order('id desc')->paginate();

        // // 分页数据
        $page = $data_list->render();


        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['nickname' => '咨询师'])// 设置搜索框
            ->hideCheckbox()
            ->addColumns([ // 批量添加数据列
                ['agency', '分中心'],
                ['nickname', '咨询师姓名'],
                ['offnum', '线下咨询人次'],
                ['offincome', '线下咨询收入'],
                ['wordnum', '文字咨询人次'],
                ['wordincome', '文字咨询收入'],
                ['voicenum', '语音咨询人次'],
                ['voiceincome', '语音咨询收入'],
                ['talkincome', '咨询总收入'],
                ['classnume', '课程数'],
                ['classincome', '课程收入'],
                ['activenum', '活动数'],
                ['activeincome', '活动收入'],
                ['clacincome', '课程活动收入'],
                ['total', '总收入'],
            ])
            ->raw('agency')
            ->raw('offnum')
            ->raw('offincome')
            ->raw('wordnum')
            ->raw('wordincome')
            ->raw('voicenum')
            ->raw('voiceincome')
            ->raw('talkincome')
            ->raw('classnume')
            ->raw('classincome')
            ->raw('activenum')
            ->raw('activeincome')
            ->raw('clacincome')
            ->raw('total')
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }

    /**
     * 课程列表
     * @return mixed
     */
    public function classes()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        // 数据列表
        $data_list = ClassModel::where($map)->order('id desc')->paginate();

        // // 分页数据
        $page = $data_list->render();

        $btncalendar = [
            // 'class' => 'btn btn-info',
            'title' => '导出',
            'icon'  => 'fa fa-fw fa-file-excel-o',
            'href'  => url('classexport')
        ];

        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['title' => '课程'])// 设置搜索框
            ->hideCheckbox()
            ->addColumns([ // 批量添加数据列
                ['agency', '分中心'],
                ['title', '课程'],
                ['nums', '参与人数'],
                ['counsollor', '导师收入'],
                ['shop', '机构收入'],
                ['admin', '总公司收入'],
                ['counsoller', '导师'],
                ['start_time', '时间','datetime'],
                ['address', '地点'],
            ])
            ->raw('agency')
            ->raw('nums')
            ->raw('counsollor')
            ->raw('shop')
            ->raw('admin')
            ->raw('counsoller')
            ->addTopButton('custom', $btncalendar)
            // ->addRightButton('edit')
            // ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }
    /**
     * [classexport 导出]
     * @return [type] [description]
     */
    public function classexport()
    {
        
        //查询数据
        $data = ClassModel::all();
        foreach ($data as $key => $value) {
            $data[$key]['agency'] = db('shop_agency')->where(['id'=>$value['shopid']])->value('title');
            $data[$key]['nums'] = db('trade')->where(['classid'=>$value['id'],'paytype'=>2,'status'=>1])->count();
            $data[$key]['counsollor'] = ClassModel::getCounsollorAttr(null,$value);
            $data[$key]['shop'] = ClassModel::getShopAttr(null,$value);
            $data[$key]['admin'] = ClassModel::getAdminAttr(null,$value);
            $data[$key]['counsoller'] = ClassModel::getCounsollerAttr(null,$value);
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['agency', 'auto','分中心'],
            ['title','auto', '课程'],
            ['nums', 'auto','参与人数'],
            ['counsollor', 'auto','导师收入'],
            ['shop', 'auto','机构收入'],
            ['admin', 'auto','总公司收入'],
            ['counsoller', 'auto','导师'],
            ['start_time', 'auto','时间','datetime'],
            ['address', 'auto','地点'],
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['课程表', $cellName, $data]);
    }
    /**
     * 课程列表
     * @return mixed
     */
    public function actives()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        // 数据列表
        $data_list = ActiveModel::where($map)->order('id desc')->paginate();

        // // 分页数据
        $page = $data_list->render();

        $btncalendar = [
            // 'class' => 'btn btn-info',
            'title' => '导出',
            'icon'  => 'fa fa-fw fa-file-excel-o',
            'href'  => url('activexport')
        ];
        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['title' => '课程'])// 设置搜索框
            ->hideCheckbox()
            ->addColumns([ // 批量添加数据列
                ['agency', '分中心'],
                ['title', '课程'],
                ['nums', '参与人数'],
                ['acounsollor', '导师收入'],
                ['ashop', '机构收入'],
                ['aadmin', '总公司收入'],
                ['acounsoller', '导师'],
                ['start_time', '时间','datetime'],
                ['address', '地点'],
            ])
            ->raw('agency')
            ->raw('anums')
            ->raw('acounsollor')
            ->raw('ashop')
            ->raw('aadmin')
            ->raw('acounsoller')
            ->addTopButton('custom', $btncalendar)
            // ->addTopButton('add', ['href' => url('add')])
            // ->addRightButton('edit')
            // ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }

    /**
     * [classexport 导出]
     * @return [type] [description]
     */
    public function activexport()
    {
        
        //查询数据
        $data = ActiveModel::all();
        foreach ($data as $key => $value) {
            $data[$key]['agency'] = ActiveModel::getAgencyAttr(null,$value);
            $data[$key]['nums'] = ActiveModel::getAnumsAttr(null,$value);
            $data[$key]['counsollor'] = ActiveModel::getAcounsollorAttr(null,$value);
            $data[$key]['shop'] = ActiveModel::getAshopAttr(null,$value);
            $data[$key]['admin'] = ActiveModel::getAadminAttr(null,$value);
            $data[$key]['counsoller'] = ActiveModel::getAcounsollerAttr(null,$value);
        }
        // 设置表头信息（对应字段名,宽度，显示表头名称）
        $cellName = [
            ['agency', 'auto','分中心'],
            ['title','auto', '课程'],
            ['nums', 'auto','参与人数'],
            ['counsollor', 'auto','导师收入'],
            ['shop', 'auto','机构收入'],
            ['admin', 'auto','总公司收入'],
            ['counsoller', 'auto','导师'],
            ['start_time', 'auto','时间','datetime'],
            ['address', 'auto','地点'],
        ];
        // 调用插件（传入插件名，[导出文件名、表头信息、具体数据]）
        plugin_action('Excel/Excel/export', ['活动表', $cellName, $data]);
    }

    /**
     * 课程列表
     * @return mixed
     */
    public function totalincome()
    {
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        // 获取查询条件
        $map = $this->getMap();

        // 数据列表
        $data_list = AgencyModel::where($map)->order('id desc')->paginate();

        // // 分页数据
        $page = $data_list->render();


        // 使用ZBuilder快速创建数据表格
        return ZBuilder::make('table')
            ->setSearch(['title' => '标题'])// 设置搜索框
            ->hideCheckbox()
            ->addColumns([ // 批量添加数据列
                ['title', '分中心'],
                ['seeknums', '总咨询人次'],
                ['wordnums', '文字咨询人次'],
                ['voicenums', '语音咨询人次'],
                ['videonums', '视频咨询人次'],
                ['wifinums', '网络咨询总人次'],
                ['facenums', '地面咨询人次'],
                ['cincome', '咨询师收入'],
                ['sincome', '咨询机构收入'],
                ['aincome', '咨询平台收入'],
                ['clnums', '课程次数'],
                ['clcnums', '课程咨询师收入'],
                ['clsnums', '课程机构收入'],
                ['clanums', '课程平台收入'],
                ['acnums', '活动次数'],
                ['accincome', '活动咨询师收入'],
                ['acsincome', '活动机构收入'],
                ['acaincome', '活动平台收入'],
                ['totalsincome', '机构总收入'],
                ['totalaincome', '平台总收入'],
                // ['right_button', '操作', 'btn']
            ])
            ->raw('seeknums')
            ->raw('wordnums')
            ->raw('voicenums')
            ->raw('videonums')
            ->raw('wifinums')
            ->raw('facenums')
            ->raw('cincome')
            ->raw('sincome')
            ->raw('aincome')
            ->raw('clnums')
            ->raw('clcnums')
            ->raw('clsnums')
            ->raw('clanums')

            ->raw('acnums')
            ->raw('accincome')
            ->raw('acsincome')
            ->raw('acaincome')
            ->raw('totalsincome')
            ->raw('totalaincome')

            // ->addTopButton('add', ['href' => url('add')])
            // ->addRightButton('edit')
            // ->addRightButton('delete', ['data-tips' => '删除后无法恢复。'])// 批量添加右侧按钮
            ->setRowList($data_list)// 设置表格数据
            ->fetch(); // 渲染模板
    }
    
}