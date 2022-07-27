<?php
/**
 * ExecLib.php
 *
 * @author      Юра Готовко
 * @copyright   © Yura Gotovko <yugotovko@mail.ru>
 */
namespace App\Services\SignatureChecker;


class ExecLib
{
	function __construct() {
		self::init();
	}


	/**
	 * Подготовка к работе
	 */
	public static $init;

	public static function init() {
		if (self::$init) return;

		self::$init = TRUE;
	}


	/**
	 *
	 */
	const PATH_BIN = '/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin';

	private static function find_bin($name) {
		$ret = FALSE;

		$path = array_reverse(array_unique(array_merge(
			explode(PATH_SEPARATOR, getenv('PATH')),
			explode(':', self::PATH_BIN)
		)));

		foreach ($path as $dir) {
			$file = $dir . DIRECTORY_SEPARATOR . $name;

			if (is_executable($file)) {
				$ret = $file;
				break;
			}
		}

		return $ret;
	}

	public static function bin_find(&$name) {
		if (strpos($name, DIRECTORY_SEPARATOR) === FALSE) {
			$file = self::find_bin($name);

			if ($file !== FALSE) {
				$name = $file;
			}
		}

		return $name;
	}

	public static function cmd_line($cmd, $opts = NULL) {
      $xhost = getenv('XHOST') ?: 'localhost';
      $ret = "XHOST={$xhost} {$cmd}";

		foreach ((array) $opts as $opt => $args) {
			if (is_null($args) && is_string($opt) && strlen($opt)) {
				$ret .= ' ' . $opt;
			}

			foreach ((array) $args as $arg) {
				if (! is_null($arg)) {
					if (is_string($opt) && strlen($opt)) {
						$ret .= ' ' . $opt;
					}

					$ret .= ' ' . escapeshellarg($arg);
				}
			}
		}

		return $ret;
	}

	public static function cmd_exec($name, $opts = NULL, $pipe = NULL) {
		$ret = [];

		$cmd = self::cmd_line(self::bin_find($name), $opts);

		foreach ((array) $pipe as $key => $val) {
			$cmd = self::cmd_line($key ?: 'echo', $val) . ' | ' . $cmd;
		}

		@exec($cmd . ' 2>&1', $ret['output'], $ret['error']);

		return $ret;
	}

	public static function cmd_background($name, $opts = NULL) {
		$ret = NULL;

		@exec(self::cmd_line(self::bin_find($name), $opts) . ' > /dev/null 2>&1 &', $ret, $ret);

		return $ret;
	}

	public static function cmd_passthru($name, $opts = NULL) {
		$ret = NULL;

		@passthru(self::cmd_line(self::bin_find($name), $opts), $ret);

		return $ret;
	}
}
