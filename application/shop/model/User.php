<?php


namespace app\shop\model;

use think\Model;
use think\helper\Hash;
use app\shop\model\Role as RoleModel;
use think\Db;

/**
 * 后台用户模型
 * @package app\admin\model
 */
class User extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__SHOP_USER__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    // 对密码进行加密
    public function setPasswordAttr($value)
    {
        return Hash::make((string)$value);
    }

    // 获取注册ip
    public function setSignupIpAttr()
    {
        return get_client_ip(1);
    }

    /**
     * 用户登录
     * @param string $username 用户名
     * @param string $password 密码
     * @param bool $rememberme 记住登录
     * @author 蔡伟明 <314013107@qq.com>
     * @return bool|mixed
     */
    public function login($username = '', $password = '', $rememberme = false)
    {
        $username = trim($username);
        $password = trim($password);

        // 匹配登录方式
        if (preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $username)) {
            // 邮箱登录
            $map['email'] = $username;
        } elseif (preg_match("/^1\d{10}$/", $username)) {
            // 手机号登录
            $map['mobile'] = $username;
        } else {
            // 用户名登录
            $map['username'] = $username;
        }

        $map['status'] = 1;

        // 查找用户
        $user = $this::get($map);
        if (!$user) {
            $this->error = '用户不存在或被禁用！';
        } else {
            // 检查是否分配用户组
            if ($user['role'] == 0) {
                $this->error = '禁止访问，原因：未分配角色！';
                return false;
            }
            // 检查是可登录后台
            if (!RoleModel::where('id', $user['role'])->value('access')) {
                $this->error = '禁止访问，原因：用户所在角色禁止访问后台！';
                return false;
            }
            if (!Hash::check((string)$password, $user['password'])) {
                $this->error = '密码错误！';
            } else {
                $uid = $user['id'];

                // 更新登录信息
                $user['last_login_time'] = request()->time();
                $user['last_login_ip']   = get_client_ip(1);
                if ($user->save()) {
                    // 自动登录
                    return $this->autoLogin($this::get($uid), $rememberme);
                } else {
                    // 更新登录信息失败
                    $this->error = '登录信息更新失败，请重新登录！';
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * 自动登录
     * @param object $user 用户对象
     * @param bool $rememberme 是否记住登录，默认7天
     * @author 蔡伟明 <314013107@qq.com>
     * @return bool|int
     */
    public function autoLogin($user, $rememberme = false)
    {
        // 记录登录SESSION和COOKIES
        $auth = array(
            'uid'             => $user->id,
            'group'           => $user->group,
            'role'            => $user->role,
            'role_name'       => Db::name('shop_role')->where('id', $user->role)->value('name'),
            'avatar'          => $user->avatar,
            'username'        => $user->username,
            'nickname'        => $user->nickname,
            'last_login_time' => $user->last_login_time,
            'last_login_ip'   => get_client_ip(1),
            'shopid'          => $user->shopid
        );
        session('shop_auth', $auth);
        session('shop_auth_sign', shop_data_auth_sign($auth));

        // 保存用户节点权限
        if ($user->role != 1) {
            $menu_auth = Db::name('shop_role')->where('id', session('shop_auth.role'))->value('menu_auth');
            $menu_auth = json_decode($menu_auth, true);
            if (!$menu_auth) {
                session('shop_auth', null);
                session('shop_auth_sign', null);
                $this->error = '未分配任何节点权限！';
                return false;
            }
        }

        // 记住登录
        if ($rememberme) {
            $signin_token = $user->username.$user->id.$user->last_login_time;
            cookie('suid', $user->id, 24 * 3600 * 7);
            cookie('shop_signin_token', shop_data_auth_sign($signin_token), 24 * 3600 * 7);
        }

        return $user->id;
    }
}
