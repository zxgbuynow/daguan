<?php


// 门户模块公共函数库
use think\Db;
use Overtrue\Pinyin\Pinyin;


if (!function_exists('get_column_name')) {
    /**
     * 获取栏目名称
     * @param int $cid 栏目id
     * @author zg
     * @return string
     */
    function get_column_name($cid = 0)
    {
        $column_list = model('cms/column')->getList();
        return isset($column_list[$cid]) ? $column_list[$cid]['name'] : '';
    }
}

if (!function_exists('get_model_name')) {
    /**
     * 获取内容模型名称
     * @param string $id 内容模型id
     * @author zg
     * @return string
     */
    function get_model_name($id = '')
    {
        $model_list = model('cms/model')->getList();
        return isset($model_list[$id]) ? $model_list[$id]['name'] : '';
    }
}

if (!function_exists('get_model_title')) {
    /**
     * 获取内容模型标题
     * @param string $id 内容模型标题
     * @author zg
     * @return string
     */
    function get_model_title($id = '')
    {
        $model_list = model('cms/model')->getList();
        return isset($model_list[$id]) ? $model_list[$id]['title'] : '';
    }
}

if (!function_exists('get_model_type')) {
    /**
     * 获取内容模型类别：0-系统，1-普通，2-独立
     * @param int $id 模型id
     * @author zg
     * @return string
     */
    function get_model_type($id = 0)
    {
        $model_list = model('cms/model')->getList();
        return isset($model_list[$id]) ? $model_list[$id]['type'] : '';
    }
}

if (!function_exists('get_model_table')) {
    /**
     * 获取内容模型附加表名
     * @param int $id 模型id
     * @author zg
     * @return string
     */
    function get_model_table($id = 0)
    {
        $model_list = model('cms/model')->getList();
        return isset($model_list[$id]) ? $model_list[$id]['table'] : '';
    }
}

if (!function_exists('is_default_field')) {
    /**
     * 检查是否为系统默认字段
     * @param string $field 字段名称
     * @author zg
     * @return bool
     */
    function is_default_field($field = '')
    {
        $system_fields = cache('cms_system_fields');
        if (!$system_fields) {
            $system_fields = Db::name('cms_field')->where('model', 0)->column('name');
            cache('cms_system_fields', $system_fields);
        }
        return in_array($field, $system_fields, true);
    }
}

if (!function_exists('table_exist')) {
    /**
     * 检查附加表是否存在
     * @param string $table_name 附加表名
     * @author zg
     * @return string
     */
    function table_exist($table_name = '')
    {
        return true == Db::query("SHOW TABLES LIKE '{$table_name}'");
    }
}

if (!function_exists('time_tran')) {
    /**
     * 转换时间
     * @param int $timer 时间戳
     * @author zg
     * @return string
     */
    function time_tran($timer)
    {
        $diff = $_SERVER['REQUEST_TIME'] - $timer;
        $day  = floor($diff / 86400);
        $free = $diff % 86400;
        if ($day > 0) {
            return $day . " 天前";
        } else {
            if ($free > 0) {
                $hour = floor($free / 3600);
                $free = $free % 3600;
                if ($hour > 0) {
                    return $hour . " 小时前";
                } else {
                    if ($free > 0) {
                        $min = floor($free / 60);
                        $free = $free % 60;
                        if ($min > 0) {
                            return $min . " 分钟前";
                        } else {
                            if ($free > 0) {
                                return $free . " 秒前";
                            } else {
                                return '刚刚';
                            }
                        }
                    } else {
                        return '刚刚';
                    }
                }
            } else {
                return '刚刚';
            }
        }
    }

}
/**
 * [convertfpy 首字母]
 * @param  [type] $string [description]
 * @return [type]         [description]
 */
function convertfpy($string)
{
    $pinyin = new Pinyin();
    if (!$string) {
        return '';
    }
    return $pinyin->abbr($string);

}
/**
 * [convertfpy 字母拼接]
 * @param  [type] $string [description]
 * @return [type]         [description]
 */
function convertpy($string)
{
    $pinyin = new Pinyin();
    if (!$string) {
        return '';
    }
    return $pinyin->permalink($string,'');

}
/**
 * @desc 将字符串转换成拼音字符串
 * @param $string 汉字字符串
 * @param $upper 是否大写
 * @return string
 * 
 * 例如：getChineseChar('我是作者'); 全部字符串+小写
 * return "wo shi zuo zhe"
 * 
 * 例如：getChineseChar('我是作者',true); 首字母+小写
 * return "w s z z"
 * 
 * 例如：getChineseChar('我是作者',true,true); 首字母+大写
 * return "W S Z Z"
 * 
 * 例如：getChineseChar('我是作者',false,true); 首字母+大写
 * return "WO SHI ZUO ZHE"
 */
function getChineseChar($string,$isOne=false,$upper=false) {
    global $spellArray;
    $str_arr = utf8_str_split($string,1); //将字符串拆分成数组
    $result = array();
    foreach($str_arr as $char)
    {
        // if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u',$char))
        // {
        //     $chinese = $spellArray[$char];
        //     $chinese  = $chinese[0];
        // }else{
        //     $chinese = $char;
        // }
        $chinese = $char;
        $chinese = $isOne ? substr($chinese,0,1) : $chinese;
        $result[] = $upper ? strtoupper($chinese) : $chinese;
    }
    return implode('',$result);
}
/**
 * @desc 将字符串转换成数组
 * @param $str 要转换的数组
 * @param $split_len
 * @return array
 */
function utf8_str_split($str,$split_len=1) {
    if(!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1) {
        return FALSE;
    }

    $len = mb_strlen($str, 'UTF-8');

    if ($len <= $split_len) {
        return array($str);
    }
    preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);

    return $ar[0];
}