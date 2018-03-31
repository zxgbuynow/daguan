<?php


use think\Db;
use think\View;
use app\shop\model\User;

// 应用公共文件

if (!function_exists('is_shop_signin')) {
    /**
     * 判断是否登录
     * @author zg
     * @return mixed
     */
    function is_shop_signin()
    {
        $user = session('shop_auth');
        if (empty($user)) {
            // 判断是否记住登录
            if (cookie('?suid') && cookie('?shop_signin_token')) {
                $UserModel = new User();
                $user = $UserModel::get(cookie('suid'));
                if ($user) {
                    $signin_token = shop_data_auth_sign($user['username'].$user['id'].$user['last_login_time']);
                    if (cookie('shop_signin_token') == $signin_token) {
                        // 自动登录
                        $UserModel->autoLogin($user);
                        return $user['id'];
                    }
                }
            };
            return 0;
        }else{
            return session('shop_auth_sign') == shop_data_auth_sign($user) ? $user['uid'] : 0;
        }
    }
}

if (!function_exists('shop_data_auth_sign')) {
    /**
     * 数据签名认证
     * @param array $data 被认证的数据
     * @author zg
     * @return string
     */
    function shop_data_auth_sign($data = [])
    {
        // 数据类型检测
        if(!is_array($data)){
            $data = (array)$data;
        }

        // 排序
        ksort($data);
        // url编码并生成query字符串
        $code = http_build_query($data);
        // 生成签名
        $sign = sha1($code);
        return $sign;
    }
}

if (!function_exists('shop_role_auth')) {
    /**
     * 读取当前用户权限
     * @author zg
     */
    function shop_role_auth() {
        session('shop_role_menu_auth', model('shop/role')->roleAuth());
    }
}

if (!function_exists('get_shop_file_path')) {
    /**
     * 获取附件路径
     * @param int $id 附件id
     * @author zg
     * @return string
     */
    function get_shop_file_path($id = 0)
    {
        $path = model('shop/attachment')->getFilePath($id);
        if (!$path) {
            return config('public_static_path').'admin/img/none.png';
        }
        return $path;
    }
}

if (!function_exists('get_shop_files_path')) {
    /**
     * 批量获取附件路径
     * @param array $ids 附件id
     * @author zg
     * @return array
     */
    function get_shop_files_path($ids = [])
    {
        $paths = model('shop/attachment')->getFilePath($ids);
        return !$paths ? [] : $paths;
    }
}

if (!function_exists('get_shop_thumb')) {
    /**
     * 获取图片缩略图路径
     * @param int $id 附件id
     * @author zg
     * @return string
     */
    function get_shop_thumb($id = 0)
    {
        $path = model('shop/attachment')->getThumbPath($id);
        if (!$path) {
            return config('public_static_path').'admin/img/none.png';
        }
        return $path;
    }
}

if (!function_exists('get_shop_avatar')) {
    /**
     * 获取用户头像路径
     * @param int $uid 用户id
     * @author zg
     * @alter 小乌 <82950492@qq.com>
     * @return string
     */
    function get_shop_avatar($uid = 0)
    {
        $avatar = Db::name('shop_user')->where('id', $uid)->value('avatar');
        $path = model('shop/attachment')->getFilePath($avatar);
        if (!$path) {
            return config('public_static_path').'admin/img/avatar.jpg';
        }
        return $path;
    }
}

if (!function_exists('get_shop_file_name')) {
    /**
     * 根据附件id获取文件名
     * @param string $id 附件id
     * @author zg
     * @return string
     */
    function get_shop_file_name($id = '')
    {
        $name = model('shop/attachment')->getFileName($id);
        if (!$name) {
            return '没有找到文件';
        }
        return $name;
    }
}

if (!function_exists('get_shop_auth_node')) {
    /**
     * 获取用户授权节点
     * @param int $uid 用户id
     * @param string $group 权限分组，可以以点分开模型名称和分组名称，如user.group
     * @author zg
     * @return array|bool
     */
    function get_shop_auth_node($uid = 0, $group = '')
    {
        return model('shop/access')->getAuthNode($uid, $group);
    }
}

if (!function_exists('check_shop_auth_node')) {
    /**
     * 检查用户的某个节点是否授权
     * @param int $uid 用户id
     * @param string $group $group 权限分组，可以以点分开模型名称和分组名称，如user.group
     * @param int $node 需要检查的节点id
     * @author zg
     * @return bool
     */
    function check_shop_auth_node($uid = 0, $group = '', $node = 0)
    {
        return model('shop/access')->checkAuthNode($uid, $group, $node);
    }
}

if (!function_exists('get_shop_nickname')) {
    /**
     * 根据用户ID获取用户昵称
     * @param  integer $uid 用户ID
     * @return string  用户昵称
     */
    function get_shop_nickname($uid = 0)
    {
        static $list;
        // 获取当前登录用户名
        if (!($uid && is_numeric($uid))) {
            return session('shop_auth.username');
        }

        // 获取缓存数据
        if (empty($list)) {
            $list = cache('sys_shop_user_nickname_list');
        }

        // 查找用户信息
        $key = "u{$uid}";
        if (isset($list[$key])) {
            // 已缓存，直接使用
            $name = $list[$key];
        } else {
            // 调用接口获取用户信息
            $info = model('shop/user')->field('nickname')->find($uid);
            if ($info !== false && $info['nickname']) {
                $nickname = $info['nickname'];
                $name = $list[$key] = $nickname;
                /* 缓存用户 */
                $count = count($list);
                $max   = config('user_max_cache');
                while ($count-- > $max) {
                    array_shift($list);
                }
                cache('sys_shop_user_nickname_list', $list);
            } else {
                $name = '';
            }
        }
        return $name;
    }
}

if (!function_exists('action_shop_log')) {
    /**
     * 记录行为日志，并执行该行为的规则
     * @param null $action 行为标识
     * @param null $model 触发行为的模型名
     * @param string $record_id 触发行为的记录id
     * @param null $user_id 执行行为的用户id
     * @param string $details 详情
     * @author huajie <banhuajie@163.com>
     * @alter action_shop_log
     * @return bool|string
     */
    function action_shop_log($action = null, $model = null, $record_id = '', $user_id = null, $details = '')
    {
        // 判断是否开启系统日志功能
        if (config('system_log')) {
            // 参数检查
            if(empty($action) || empty($model)){
                return '参数不能为空';
            }
            if(empty($user_id)){
                $user_id = is_signin();
            }
            if (strpos($action, '.')) {
                list($module, $action) = explode('.', $action);
            } else {
                $module = request()->module();
            }

            // 查询行为,判断是否执行
            $action_info = model('shop/action')->where('module', $module)->getByName($action);
            if($action_info['status'] != 1){
                return '该行为被禁用或删除';
            }

            // 插入行为日志
            $data = [
                'action_id'   => $action_info['id'],
                'user_id'     => $user_id,
                'action_ip'   => get_client_ip(1),
                'model'       => $model,
                'record_id'   => $record_id,
                'create_time' => request()->time()
            ];

            // 解析日志规则,生成日志备注
            if(!empty($action_info['log'])){
                if(preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)){
                    $log = [
                        'user'    => $user_id,
                        'record'  => $record_id,
                        'model'   => $model,
                        'time'    => request()->time(),
                        'data'    => ['user' => $user_id, 'model' => $model, 'record' => $record_id, 'time' => request()->time()],
                        'details' => $details
                    ];

                    $replace = [];
                    foreach ($match[1] as $value){
                        $param = explode('|', $value);
                        if(isset($param[1])){
                            $replace[] = call_user_func($param[1], $log[$param[0]]);
                        }else{
                            $replace[] = $log[$param[0]];
                        }
                    }

                    $data['remark'] = str_replace($match[0], $replace, $action_info['log']);
                }else{
                    $data['remark'] = $action_info['log'];
                }
            }else{
                // 未定义日志规则，记录操作url
                $data['remark'] = '操作url：'.$_SERVER['REQUEST_URI'];
            }

            // 保存日志
            model('shop/log')->insert($data);

            if(!empty($action_info['rule'])){
                // 解析行为
                $rules = shop_parse_action($action, $user_id);
                // 执行行为
                $res = shop_execute_action($rules, $action_info['id'], $user_id);
                if (!$res) {
                    return '执行行为失败';
                }
            }
        }

        return true;
    }
}

if (!function_exists('shop_parse_action')) {
    /**
     * 解析行为规则
     * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
     * 规则字段解释：table->要操作的数据表，不需要加表前缀；
     *            field->要操作的字段；
     *            condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
     *            rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
     *            cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
     *            max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
     * 单个行为后可加 ； 连接其他规则
     * @param string $action 行为id或者name
     * @param int $self 替换规则里的变量为执行用户的id
     * @author huajie <banhuajie@163.com>
     * @alter action_shop_log
     * @return boolean|array: false解析出错 ， 成功返回规则数组
     */
    function shop_parse_action($action = null, $self){
        if(empty($action)){
            return false;
        }

        // 参数支持id或者name
        if(is_numeric($action)){
            $map = ['id' => $action];
        }else{
            $map = ['name' => $action];
        }

        // 查询行为信息
        $info = model('shop/action')->where($map)->find();
        if(!$info || $info['status'] != 1){
            return false;
        }

        // 解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
        $rule   = $info['rule'];
        $rule   = str_replace('{$self}', $self, $rule);
        $rules  = explode(';', $rule);
        $return = [];
        foreach ($rules as $key => &$rule){
            $rule = explode('|', $rule);
            foreach ($rule as $k => $fields){
                $field = empty($fields) ? array() : explode(':', $fields);
                if(!empty($field)){
                    $return[$key][$field[0]] = $field[1];
                }
            }
            // cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
            if (!isset($return[$key]['cycle']) || !isset($return[$key]['max'])) {
                unset($return[$key]['cycle'],$return[$key]['max']);
            }
        }

        return $return;
    }
}

if (!function_exists('shop_execute_action')) {
    /**
     * 执行行为
     * @param array|bool $rules 解析后的规则数组
     * @param int $action_id 行为id
     * @param array $user_id 执行的用户id
     * @author huajie <banhuajie@163.com>
     * @alter action_shop_log
     * @return boolean false 失败 ， true 成功
     */
    function shop_execute_action($rules = false, $action_id = null, $user_id = null){
        if(!$rules || empty($action_id) || empty($user_id)){
            return false;
        }

        $return = true;
        foreach ($rules as $rule){
            // 检查执行周期
            $map = ['action_id' => $action_id, 'user_id' => $user_id];
            $map['create_time'] = ['gt', request()->time() - intval($rule['cycle']) * 3600];
            $exec_count = model('shop/log')->where($map)->count();
            if($exec_count > $rule['max']){
                continue;
            }

            // 执行数据库操作
            $field = $rule['field'];
            $res   = Db::name($rule['table'])->where($rule['condition'])->setField($field, array('exp', $rule['rule']));

            if(!$res){
                $return = false;
            }
        }
        return $return;
    }
}

if (!function_exists('get_shop_location')) {
    /**
     * 获取当前位置
     * @param string $id 节点id，如果没有指定，则取当前节点id
     * @param bool $del_last_url 是否删除最后一个节点的url地址
     * @param bool $check 检查节点是否存在，不存在则抛出错误
     * @author zg
     * @return mixed
     */
    function get_shop_location($id = '', $del_last_url = false, $check = true)
    {
        $location = model('shop/menu')->getLocation($id, $del_last_url, $check);
        return $location;
    }
}