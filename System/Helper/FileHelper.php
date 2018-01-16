<?php

class FileHelper {

    /**
     * @param $dir
     */
	public static function ensureWritableDir($dir) {
		if(!file_exists($dir)) {
			@mkdir($dir, 0777, true);
			@chmod($dir, 0777);
		}
		else if(!is_writable($dir)) {
			@chmod($dir, 0777);
			if(!@is_writable($dir)) {
				throw new MyException("目录不可写", 0, null, $dir);
			}
		}
	}

    /**
     * @param $path
     * @return bool|mixed|string
     * @desc 获取短路径（去除掉 ROOT 部分）
     */
	public static function shortPath($path) {
		static $rootLen = 0;
		if($rootLen === 0) $rootLen = strlen(ROOT) + 1;
		return 0 === strncasecmp($path = str_replace('\\', '/', $path), ROOT, $rootLen) ? substr($path, $rootLen) : $path;
	}
}

