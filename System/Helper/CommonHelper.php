<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/30 0030
 * Time: 15:55
 */

namespace System\Helper;


class CommonHelper
{
    /**
     * @param $string
     * @param string $delimiter
     * @param string $key_delimiter
     * @return array
     */
    public static function parseCondition($string,$delimiter=';',$key_delimiter=':'){
        $arr = explode($delimiter, $string);

        $newArr = array();
        if ( count($arr) ){
            foreach ($arr as $key=>$val){
                if (stripos($val, ":") !==false){
                    list($k,$v) = explode($key_delimiter, $val);
                    $newArr[$k] = trim($v);
                }
            }
        }

        return $newArr;
    }

    // 获取用户id
    public static function getUid(){
        if($_COOKIE['passport']) {
            $arr = explode("\t", $_COOKIE['passport']);
            $uid = intval($arr[0]);
        }
        return empty($uid)?0:$uid;
    }

    /**
     * 根据有效位数截取值并返回结果
     * @author magangjun
     * @param float|int $num 要转换的数值
     * @param int $type 类型(1-nav:净值(保留4位有效数字),2-percent：(添加百分比))
     * @param bool $isMine 是否是自己关联表查询
     * @param int $decimals 有效位数
     * @param string $unitValue 初始单位净值
     * @return string
     */
    public static function formatDecimal($num, $type, $isMine = false, $decimals = 4, $unitValue = '') {
        $result = "--";
        if (empty($num) || $num == '--') {
            return $result;
        }
        $unitValue = intval($unitValue);
        //数值转换成字符串
        $num = "$num";
        $pos = strpos($num, '.');
        if ($pos === false) {
            $num .= ".";
            for($i = 0; $i < $decimals; $i++) {
                $num .= "0";
            }
            return $num;
        }
        $length = $decimals + 1;
        $left = substr($num, 0, $pos);
        $right = substr($num, $pos, $length);
        if (strlen($right) < $length) {
            $len = $length - strlen($right);
            for($i = 0; $i < $len; $i++) {
                $right .= "0";
            }
        }
        if ($type == 1) {
            $result = $left.$right;
            if ($unitValue > 1) {
                if ($unitValue == 100) {
                    $result = '<span style="color:#f45;">*</span>'.$result;
                } elseif ($unitValue == 1000) {
                    $result = '<span style="color:#f45;">※</span>'.$result;
                }
            }
        } elseif($type == 2) {
            if ($isMine) {
                $result = number_format(floatval($left.$right) * 100, 2, '.', '') . "%";
            } else {
                $result = $left.substr($num, $pos, 3)."%";
            }
        }
        return $result;
    }

    //dec小数点位数, init type=nav:初始净值, type=percent:百分比基数
    public static function numFormat($num, $dec = 4, $init = 1, $type = 'nav', $colored = true, $exp='') {
        $str = '--';
        $pre = $suf = '';
        if($num !== NULL) {
            if('nav' == $type) {
                $init = (int)$init;
                if($init > 1){
                    $num /= $init;
                    if ($colored){
                        $pre = '<span class="f_red">*';
                        $suf = '</span>';
                    }
                    else{
                        $pre = '<span>*';
                        $suf = '</span>';
                    }
                }
            }else if('percent' == $type){
                $num *= $init;
                if($num > 0){
                    $pre = $colored? '<span [title] class="f_red">' : '<span>';
                    $suf = '%</span>';
                }else if($num < 0){
                    $pre = $colored? '<span [title] class="f_fgreen">' : '<span>';
                    $suf = '%</span>';

                }else {
                    $suf = '%';
                }
            }else if('percent_only_number' == $type){
                $num *= $init;
                $suf = '%';

            }else{
                $suf = $exp;
            }
            $str = number_format($num, $dec, '.', '');
            if(strlen(intval($str)) > 6 && 'percent' == $type) {
                $pre = str_replace('[title]', "title=$str%", $pre);
                $str = substr($str, 0, 6) . '..';
            }else {
                $pre = str_replace('[title]', '', $pre);
            }
            return $pre.$str.$suf;
        }
        return '--';
    }

    /**
     * 删除所有标签，包括已经转义过的
     *
     * @param string $content
     * @return string
     */
    public static function removeAllTags($content){
        $partner[0] = '/&lt;\w{1,}&gt;/i';
        $partner[1] = '/&lt;\/\w{1,}&gt;/i';
        $partner[2] = '/&lt;\w{1,}\s*\/&gt;/i';
        $content = preg_replace($partner, '', $content);
        $content = strip_tags($content);

        return $content;
    }
}