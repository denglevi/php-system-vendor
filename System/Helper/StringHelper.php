<?php
namespace System\Helper;
class StringHelper {
	/**
	* 判断 subject 是否以 search 结尾。 参数指定是否忽略大小写。
	*
	* @param string The string to search in.
	* @param string
	* @param bool 是否忽略大小写
	*/
	public static function startWith($subject, $search, $ignoreCase = false) {
		if(0 === ($len2 = strlen($search))) return true;
		if(($len1 = strlen($subject)) < $len2) return false;
		if($ignoreCase) {
			return 0 === strncasecmp($subject, $search, $len2);
		}
		else {
			return 0 === strncmp($subject, $search, $len2);
		}
	}

	/**
	* 判断 subject 是否以 search 开始。 参数指定是否忽略大小写。
	*
	* @param string The string to search in.
	* @param string
	* @param bool 是否忽略大小写
	*/
	public static function endWith($subject, $search, $ignoreCase = false) {
		if(0 === ($len2 = strlen($search))) return true;
		if(($len1 = strlen($subject)) < $len2) return false;
		if($ignoreCase) {
			return 0 === strcasecmp(substr($subject, $len1 - $len2), $search);
		}
		else {
			return 0 === strcmp(substr($subject, $len1 - $len2), $search);
		}
	}

	/**
	* 创建随即字符串
	*
	* @param int 字符串长度, type=sha1|md5 时无效
	* @param string	al|au|alu|alnum|aunum|alunum|salt|numeric|nozero|sha1|md5
	*/
	public static function rand($length, $type = 'alnum') {
		switch ($type) {
			case 'al':
				$pool = 'abcdefghijklmnopqrstuvwxyz';
				break;

			case 'au':
				$pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;

			case 'alu':
				$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;

			case 'alnum':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyz';
				break;

			case 'aunum':
				$pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;

			case 'alunum':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;

			case 'salt':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*()-=_+[]{}|;:,.<>?';
				break;

			case 'numeric':
				$pool = '0123456789';
				break;

			case 'nozero':
				$pool = '123456789';
				break;

			case 'sha1':
				return sha1(uniqid(mt_rand(), TRUE));

			case 'md5':
				return md5(uniqid(mt_rand()));

			default:
				return md5(uniqid(mt_rand()));
		}
		return substr(str_shuffle(str_repeat($pool, ceil($length / strlen($pool)))), 0, $length);
	}

	/**
	* (多字节)字符串拆分
	*
	* @param mixed 要拆分的字符列表
	* @param string The input string.
	* @param mixed
	* @param bool 是否去掉结果中的空字符串
	*/
	public static function explode($delimiter, $string, $limit = NULL, $noEmpty = true) {
		if(empty($string)) return [];
		if($delimiter === null || $delimiter === false) return (array)$string;

		if(!is_array($delimiter)) {
			return self::explode(self::toChars($delimiter), $string, $limit, $noEmpty);
		}

		$delimiterFlip = array_flip($delimiter);
		$charList = self::toChars($string);
		$limit = $limit < 1 ? 2147483647 : $limit;
		$result = [];
		$lastPos = -1;
		foreach($charList as $pos => $char) {
			if(isset($delimiterFlip[$char])) {
				$subStr = trim(mb_substr($string, $lastPos + 1, $pos - $lastPos - 1));
				if(!$noEmpty || 0 !== strlen($subStr)) {
					$result []= $subStr;
				}
				$lastPos = $pos;
			}
		}
		if($lastPos != count($charList) - 1) {
			$subStr = trim(mb_substr($string, $lastPos + 1));
			if(!$noEmpty || 0 !== strlen($subStr)) {
				$result []= $subStr;
			}
		}
		return $result;
	}

	public static function toChars($string) {
		$result = [];
		$len = mb_strlen($string);
		for($i = 0; $i < $len; $i ++) $result []= mb_substr($string, $i, 1);
		return $result;
	}

	public static function isEmail($string) {
		return 1 === preg_match('/^[\w\-\.]+@[\w\-]+(?:\.\w+)+$/i', $string);
	}

	public static function isValidInt($string){
		return 1 === preg_match('/^[1-9]\d*$/', $string);
	}

	public static function isDateString($string){
		return 1 === preg_match('/^\\d{4}(\\-|\\/|.)\\d{1,2}\\1\\d{1,2}$/', $string);
	}
}

