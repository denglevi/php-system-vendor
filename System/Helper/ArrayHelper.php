<?php
namespace System\Helper;
class ArrayHelper {

    /**
     * @param $arr1
     * @param $arr2
     * @param null $arr3
     * @return mixed
     * @desc 递归的合并数组
     */
	public static function mergeRecursivly($arr1, $arr2, $arr3 = null) {
		$args = func_get_args();
		while(!is_array($result = array_shift($args)) || 0 === count($result)) { /* do nothing */ }
		while(0 !== count($args)) {
			$arr = array_shift($args);
			if(!is_array($arr) || 0 === count($arr)) continue;
			foreach($arr as $key => $value) {
				if(isset($result[$key]) && is_array($value) && is_array($result[$key])) {
					$result[$key] = self::mergeRecursivly($result[$key], $value);
				}
				else {
					$result[$key] = $value;
				}
			}
		}
		return $result;
	}
}

