<?php
/*
 * @Author       : lovefc
 * @Date         : 2022-12-06 15:16:15
 * @LastEditTime : 2022-12-08 20:59:21
 */

namespace lovefc\LaravelRouteNotes;

class RouteNotes
{
	protected static $generateStr = '';

	protected static $useClass = [];

	protected static $groups = [];

	protected static $attrs = [
		'prefix',
		'name',
		'where',
		'domain',
		'middleware',
		'redirect',
		'group'
	];

	protected static $verbAttrs = [
		'post',
		'get',
		'any',
		'match',
		'options',
		'patch',
		'view',
		'put',
		'delete',
	];

	/**
	 * 获取文件夹内指定后缀的所有文件
	 */
	public static function getFiles(&$result, $dir, $filter = [])
	{
		$files = array_diff(scandir($dir), array('.', '..', '__MACOSX'));
		if (is_array($files)) {
			foreach ($files as $value) {
				if (is_dir($dir . '/' . $value)) {
					self::getFiles($result, $dir . '/' . $value, $filter);
				} else {
					$path_info = pathinfo($dir . '/' . $value);
					$extension = array_key_exists('extension', $path_info) ? $path_info['extension'] : '';
					if (empty($filter) || (!empty($filter) && in_array($extension, $filter))) {
						$result[] = $dir . '/' . $value;
					}
				}
			}
		}
	}

	/**
	 * 读取控制器文件
	 */
	protected static function readControllerFiles($path = ''): array
	{
		if (!is_dir($path)) return [];
		$files = [];
		self::getFiles($files, $path, ['php']);
		$arr = [];
		foreach ($files as $k => $v) {
			$content = file_get_contents($v);
			if (preg_match("/namespace\s+(.*);/i", $content, $matches)) {
				$namespace = $matches[1] ?? '';
			}
			$pathinfo = pathinfo($v);
			$arr[$k] = $namespace . '\\' . $pathinfo['filename'];
		}
		return $arr;
	}

	/**
	 * 创建路由
	 */
	public static function creRouteApi($path)
	{
		$arr = RouteNotes::readControllerFiles($path);
		foreach ($arr as $v) {
			$attr = RouteNotes::getAttr($v);
			self::creRoute($attr);
		}
		self::creGroupRoute();
		return $code = self::joinCode();
	}

	/**
	 * 拼接代码
	 */
	public static function joinCode()
	{
		$time = date("Y-m-d H:i:s");
		$annotate = "/**\r\n * This code is automatically generated by lv-route-notes.\r\n * createTime:{$time}\r\n */";
		$str = "<?php\r\n{$annotate}\r\nuse Illuminate\Support\Facades\Route;\r\n";
		$str .= implode("\r\n", self::$useClass);
		$str .= "\r\n" . self::$generateStr;
		return $str;
	}

	/**
	 * 获取属性
	 */
	protected static function getAttr($class)
	{
		$reflector = new ClassAttrs($class);
		$arr = $reflector->getClassAnnotate();
		$arr['methods'] = $reflector->getMethodAnnotate();
		return $arr;
	}

	/**
	 * 生成命名空间
	 */
	protected static function useClass($attrs)
	{
		if (isset($attrs['annotate'][0]) && $attrs['annotate'][0] == true) {
			$class = $attrs['class'];
			self::$useClass[] = "use " . $attrs['namespace'] . '\\' . $class . ";";
		}
	}

	/**
	 * 生成路由
	 */
	protected static function creRoute($attrs)
	{
		if (isset($attrs['annotate']) && $attrs['annotate'] == true) {
			$class = $attrs['class'];
			self::$useClass[] = "use " . $attrs['namespace'] . '\\' . $class . ";";
			$methods = $attrs['methods'] ?? [];
			unset($attrs['methods']);
			$public_arr = self::verbParameter($attrs, '', '');
			$arr = [];
			foreach ($methods as $method => $_attrs) {
				$_arr = self::verbParameter($_attrs, $method, $class);
				$arr[] = array_merge($public_arr, $_arr);
			}
			foreach ($arr as $k => $verb) {
				self::group($verb);
			}
			$str = '';
			foreach ($arr as $k => $verb) {
				if (!array_key_exists('group', $verb)) {
					$str2 = implode("->", $verb);
					$str .= "\r\nRoute::{$str2};\r\n";
				}
			}
			self::$generateStr .= $str;
		}
	}

	/**
	 * 分组处理
	 */
	protected static function group($verb)
	{
		foreach ($verb as $k => $v) {
			if ($k === 'group') {
				unset($verb['group']);
				self::$groups[$v][] = $verb;
			}
		}
	}

	/**
	 * 生成分组路由
	 */
	protected static function creGroupRoute()
	{
		if (count(self::$groups) > 0) {
			$group_str = "";
			foreach (self::$groups as $k => $arr) {
				$group = $k;
				$str = '';
				foreach ($arr as $verb) {
					$str2 = implode("->", $verb);
					$str .= "\r\n    Route::{$str2};\r\n";
				}
				$group = "\r\nRoute::" . rtrim($group, ")") . ",function(){\r\n";
				$group_str .= "{$group}{$str}\r\n});\r\n";
			}
		}
		self::$generateStr .= $group_str;
	}

	/**
	 * 生成参数
	 */
	protected static function verbParameter($attrs, $method, $class)
	{
		$str_arr = [];
		foreach ($attrs as $k => $v) {
			if (is_array($v)) {
				(count($v) == 1) && $v = $v[0];
			}
			if (in_array($k, self::$verbAttrs) && !empty($method) &&  !empty($class)) {
				$str_arr[$k] = "{$k}(\"{$v}\",[{$class}::class,\"{$method}\"])";
			}
			if (in_array($k, self::$attrs)) {
				if (!is_array($v)) {
					$str_arr[$k] = "{$k}(\"{$v}\")";
				} else {
					$str = '';
					foreach ($v as $k2 => $v2) {
						if (!is_numeric($k2)) {
							if (!is_array($v2)) {
								$str .=  "'{$k2}'=>'{$v2}',";
							} else {
								$str .= "'{$k2}'=>['" . implode("','", $v2) . "']";
							}
						} else {
							$str .= "'{$v2}',";
						}
					}
					$str = rtrim($str, ",");
					if (strpos($str, '=>')) {
						$str = "[{$str}]";
					}
					$str_arr[$k] = "{$k}({$str})";
				}
			}
		}
		return $str_arr;
	}
}
