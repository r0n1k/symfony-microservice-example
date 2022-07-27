<?php
/**
 * CryptoLib.php
 *
 * @author      Юра Головка
 * @copyright   © Yura Golovka <yugolovko@mail.ru>
 */
namespace App\Services\SignatureChecker;


class CryptoLib
{

	function __construct() {
		self::init();
	}


	/**
	 * Подготовка к работе
	 */
	public static $init;
	public static $openssl = 'openssl';

	public static function init() {
		if (self::$init) return;

		ExecLib::bin_find(self::$openssl);

		self::$init = TRUE;
	}


	/**
	 *
	 */
	const SUCCESS		= 0;
	const FAILURE		= 1;
	const NO_FORMAT		= 2;
	const NO_CONTENT	= 3;
	const NO_SIGCERT	= 4;
	const NO_VERIFY		= 5;

	const CRL_ISSUER_HASH		= 'issuerHash';
	const CRL_ISSUER			= 'issuer';
	const CRL_LAST_UPDATE		= 'lastUpdate';
	const CRL_NEXT_UPDATE		= 'nextUpdate';

	const X509_EMAIL_ADDRESS	= 'emailAddress';
	const X509_SERIAL_NUMBER	= 'serialNumber';
	const X509_ISSUER_HASH		= 'issuerHash';
	const X509_ISSUER			= 'issuer';
	const X509_SUBJECT_HASH		= 'subjectHash';
	const X509_SUBJECT			= 'subject';
	const X509_NOT_BEFORE		= 'notBefore';
	const X509_NOT_AFTER		= 'notAfter';
	const X509_PURPOSES			= 'purposes';
//	const X509_EXTENSIONS		= 'extensions';

	const X509V3_AUTHORITY_KEY_IDENTIFIER	= 'authorityKeyIdentifier';
	const X509V3_SUBJECT_KEY_IDENTIFIER		= 'subjectKeyIdentifier';
	const X509V3_KEY_IDENTIFIER				= 'keyIdentifier';
	const X509V3_KEY_USAGE					= 'keyUsage';
	const X509V3_EXT_KEY_USAGE				= 'extKeyUsage';
	const X509V3_BASIC_CONSTRAINTS			= 'basicConstraints';
	const X509V3_CERTIFICATE_POLICIES		= 'certificatePolicies';
	const X509V3_CRL_DISTRIBUTION_POINTS	= 'cRLDistributionPoints';
	const X509V3_AUTHORITY_INFO_ACCESS		= 'authorityInfoAccess';
	const X509V3_SUBJECT_INFO_ACCESS		= 'subjectInfoAccess';
	const X509V3_SIGNING_TOOL_OF_SUBJECT	= 'subjectSignTool';
	const X509V3_SIGNING_TOOL_OF_ISSUER		= 'issuerSignTool';

	const NEAP = 'Not enough actual parameters';
	const ERSM = 'Error reading S/MIME message'; // apps/cms.c:cms_main
	const UTLC = 'unable to load certificate'; // apps/apps.c:load_cert

	// include/openssl/err.h, include/openssl/cms.h
	const ERR_CMS_CHECK_CONTENT_NO_CONTENT							= 0x2E06307F;
	const ERR_CMS_SIGNERINFO_VERIFY_VERIFICATION_FAILURE			= 0x2E09809E;
	const ERR_CMS_SIGNERINFO_VERIFY_CERT_CERTIFICATE_VERIFY_ERROR	= 0x2E099064;
	const ERR_CMS_VERIFY_CONTENT_VERIFY_ERROR						= 0x2E09D06D;
	const ERR_CMS_VERIFY_SIGNER_CERTIFICATE_NOT_FOUND				= 0x2E09D08A;

	const CMS_HEADER = '-----BEGIN CMS-----';
	const CMS_FOOTER = '-----END CMS-----';
	const CRT_HEADER = '-----BEGIN CERTIFICATE-----';
	const CRT_FOOTER = '-----END CERTIFICATE-----';

	private static function openssl($opts, $pipe = NULL) {
		self::init();

		$exec = ExecLib::cmd_exec(self::$openssl, $opts, $pipe);

		if ($exec['error']) {
			$exec['errors'] = [];

			foreach (preg_grep('/:error:[[:xdigit:]]{8}:/', $exec['output']) as $s) {
				preg_match('/:error:([[:xdigit:]]{8}):/', $s, $m);
				$exec['errors'][] = intval($m[1], 16);
			}
		}

		return $exec;
	}

	/**
	 * Версия
	 *
	 * @return	string
	 */
	public static function openssl_version() {
		$ret = '';

		$exec = self::openssl('version');

		if (! $exec['error']) {
			$ret .= 'Version: ' . implode(', ', $exec['output']) . PHP_EOL;

			$exec = self::openssl(['engine' => NULL, '-c' => 'gost']);

			if (! $exec['error'] &&
				preg_match('/(?<=\[).*(?=\])/', implode(PHP_EOL, $exec['output']), $m)) {
				$ret .= 'GOST capabilities: ' . $m[0] . PHP_EOL;
			}
		} else {
			$ret .= implode(PHP_EOL, $exec['output']) . PHP_EOL;
		}

		return $ret;
	}

	/**
	 * Хеш файла
	 *
	 * @param	string		$file
	 * @param	string		$alg		алгоритм хеширования
	 * @return	string|false
	 */
	public static function openssl_dgst_file($file, $alg = 'md_gost94') {
		$ret = FALSE;

		if (is_null($file)) {
			return $ret;
		}

		$exec = self::openssl(['dgst' => "-$alg", $file]);

		if (! $exec['error'] &&
			count($exec['output']) &&
			preg_match('/[[:xdigit:]]+$/', $exec['output'][0], $m)) {
			$ret = $m[0];
		}

		return $ret;
	}

	/**
	 * Хеш строки
	 *
	 * @param	string		$str
	 * @param	string		$alg		алгоритм хеширования
	 * @return	string|false
	 */
	public static function openssl_dgst_str($str, $alg = 'md_gost94') {
		$ret = FALSE;

		if (is_null($str)) {
			return $ret;
		}

		$exec = self::openssl(['dgst' => "-$alg"], [$str]);

		if (! $exec['error'] &&
			count($exec['output']) &&
			preg_match('/[[:xdigit:]]+$/', $exec['output'][0], $m)) {
			$ret = $m[0];
		}

		return $ret;
	}

	/**
	 * CMS. Определение формата (перебором вариантов)
	 *
	 * @param	array		$data		in
	 * @return	array					errors, status, result: DER|PEM|SMIME
	 */
	public static function openssl_cms_input_format($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'cms' => NULL,
			'-cmsout' => NULL,
			'-noout' => NULL,
			'-in' => $data['in']
		];

		$forms = ['DER', 'PEM', 'SMIME'];

		foreach ($forms as $form) {
			$args['-inform'] = $form;
			$exec = self::openssl($args);

			if (! $exec['error']) break;
		}

		if ($exec['error']) {
			if ($exec['output'][0] === self::ERSM) {
				$ret['status'] = self::NO_FORMAT;
			} else {
				$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
			}
		} else {
			$ret['result'] = $form;
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * CMS. Разбор структуры
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status, result: []
	 */
	public static function openssl_cms_parse($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$tmp = tempnam(sys_get_temp_dir(), '');

		$args = [
			'cms' => NULL,
			'-cmsout' => NULL,
			'-print' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-out' => $tmp
		];

		$exec = self::openssl($args);

		if ($exec['error']) {
			@unlink($tmp);

			$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;

			return $ret;
		}

		$dump = '';

		// cut eContent
		if ($h = @fopen($tmp, 'r')) {
			$skip = FALSE;

			while (($s = @fgets($h)) !== FALSE) {
				if ($skip) {
					if (preg_match('/^[[:space:]]*[[:xdigit:]]{4,} - /', $s)) continue;

					$skip = FALSE;
				} elseif (preg_match('/^[[:space:]]*eContent:/', $s)) {
					$skip = TRUE;
				}

				$dump .= rtrim($s) . PHP_EOL;
			}

			fclose($h);
		}

		@unlink($tmp);

		$cms = [];

		preg_match('/^[[:space:]]*d.signedData:$.*/ms', $dump, $signedData) || $signedData = ['']; // ???
		// version
		// digestAlgorithms
		// encapContentInfo
		// [certificates]
		// [crls]
		// signerInfos

		/*if (preg_match('/^[[:space:]]*version:[[:space:]]*(.*)$/m', $signedData[0], $a)) {
			$cms['version'] = $a[1];
		}*/

		/*if (preg_match('/^[[:space:]]*digestAlgorithms:$(.*?)^[[:space:]]*encapContentInfo:$/ms', $signedData[0], $a)) {
			$cms['digestAlgorithms'] = $a[1];
		}*/

		/*if (preg_match('/^[[:space:]]*encapContentInfo:$(.*?)^[[:space:]]*(certificates|crls|signerInfos):$/ms', $signedData[0], $a)) {
			$cms['encapContentInfo'] = $a[1];
		}*/

		if (preg_match('/^[[:space:]]*certificates:$(.*?)^[[:space:]]*(crls|signerInfos):$/ms', $signedData[0], $a)) {
			$cms['certificates'] = [];

			foreach ((array) preg_split('/^[[:space:]]*d.certificate:$/m', $a[1], NULL, PREG_SPLIT_NO_EMPTY) as $b) {
				// tbsCertificate
				// signatureAlgorithm
				// signatureValue
				$certificate = [];

				if (preg_match('/^[[:space:]]*cert_info:$(.*?)^[[:space:]]*sig_alg:$/ms', $b, $c)) { // ???
					// version
					// serialNumber
					// signature
					// issuer
					// validity
					// subject
					// subjectPublicKeyInfo
					// [issuerUniqueID]
					// [subjectUniqueID]
					// [extensions]

					if (preg_match('/^[[:space:]]*serialNumber:[[:space:]]*(.*)$/m', $c[1], $d)) {
						$certificate[self::X509_SERIAL_NUMBER] = $d[1];
					}

					if (preg_match('/^[[:space:]]*issuer:[[:space:]]*(.*)$/m', $c[1], $d)) {
						$certificate[self::X509_ISSUER] = stripcslashes($d[1]);
					}

					if (preg_match('/^[[:space:]]*extensions:$(.*)/ms', $c[1], $d)) {
						foreach ((array) preg_split('/^$/m', $d[1], NULL, PREG_SPLIT_NO_EMPTY) as $e) {
							// extnID
							// critical
							// extnValue

							if (preg_match('/^[[:space:]]*object:[[:space:]]*.*\(2\.5\.29\.14\)$/m', $e)) {
								$certificate[self::X509V3_SUBJECT_KEY_IDENTIFIER] = self::openssl_cms_parse_dump($e, TRUE);
								continue;
							}
						}
					}
				}

				if ($certificate) {
					$cms['certificates'][] = $certificate;
				}
			}
		}

		/*if (preg_match('/^[[:space:]]*crls:$(.*?)^[[:space:]]*signerInfos:$/ms', $signedData[0], $a)) {
			$cms['crls'] = $a[1];
		}*/

		if (preg_match('/^[[:space:]]*signerInfos:$(.*)/ms', $signedData[0], $a)) {
			$cms['signerInfos'] = [];

			foreach ((array) preg_split('/^$(?=^[[:space:]]*version:)/m', $a[1], NULL, PREG_SPLIT_NO_EMPTY) as $b) {
				// version
				// sid
				// digestAlgorithm
				// [signedAttrs]
				// signatureAlgorithm
				// signature
				// [unsignedAttrs]
				$signerInfo = [];

				if (preg_match('/^[[:space:]]*d.issuerAndSerialNumber:$(.*?)^[[:space:]]*digestAlgorithm:$/ms', $b, $c)) {
					if (preg_match('/^[[:space:]]*issuer:[[:space:]]*(.*)$/m', $c[1], $d)) {
						$signerInfo[self::X509_ISSUER] = stripcslashes($d[1]);
					}

					if (preg_match('/^[[:space:]]*serialNumber:[[:space:]]*(.*)$/m', $c[1], $d)) {
						$signerInfo[self::X509_SERIAL_NUMBER] = $d[1];
					}
				}

				if (preg_match('/^[[:space:]]*d.subjectKeyIdentifier:$(.*?)^[[:space:]]*digestAlgorithm:$/ms', $b, $c)) {
					$signerInfo[self::X509V3_SUBJECT_KEY_IDENTIFIER] = self::openssl_cms_parse_dump($c[1]);
				}

				if (preg_match('/^[[:space:]]*signedAttrs:$(.*?)^[[:space:]]*signatureAlgorithm:$/ms', $b, $c)) {
					foreach ((array) preg_split('/^$/m', $c[1], NULL, PREG_SPLIT_NO_EMPTY) as $d) {
						// attrType
						// attrValues

						if (preg_match('/^[[:space:]]*object:[[:space:]]*.*\(1\.2\.840\.113549\.1\.9\.5\)$/m', $d)) {
							$signerInfo['signingTime'] = self::openssl_cms_parse_time($d);
							continue;
						}

						if (preg_match('/^[[:space:]]*object:[[:space:]]*.*\(1\.2\.643\.2\.45\.1\.1\.2\)$/m', $d)) {
							if (preg_match('/(?<=^file:).*/', (string) self::openssl_cms_parse_bmps($d), $e)) {
								$signerInfo['fileName'] = $e[0];
							}
							continue;
						}
					}
				}

				/*if (preg_match('/^[[:space:]]*unsignedAttrs:$(.*)/ms', $b, $c)) {
					foreach ((array) preg_split('/^$/m', $c[1], NULL, PREG_SPLIT_NO_EMPTY) as $d) {
						// attrType
						// attrValues

						if (preg_match('/^[[:space:]]*object:[[:space:]]*.*\(1\.2\.840\.113549\.1\.9\.6\)$/m', $d)) {
							continue;
						}
					}
				}*/

				if ($signerInfo) {
					$cms['signerInfos'][] = $signerInfo;
				}
			}
		}

		$ret['result'] = $cms;
		$ret['status'] = self::SUCCESS;

		return $ret;
	}

	/**
	 * CMS. Пустая проверка
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status
	 */
	public static function openssl_cms_verify_null($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'cms' => NULL,
			'-verify' => NULL,
			'-binary' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-out' => '/dev/null',
			'-noverify' => NULL,
			'-no_attr_verify' => NULL,
			'-no_content_verify' => NULL
		];

		if (isset($data['certfile'])) {
			$args['-certfile'] = $data['certfile'];
		}

		if (isset($data['content'])) {
			$args['-content'] = $data['content'];
		}

		$exec = self::openssl($args);

		if ($exec['error']) {
			if (in_array(self::ERR_CMS_VERIFY_SIGNER_CERTIFICATE_NOT_FOUND, $exec['errors'])) {
				$ret['status'] = self::NO_SIGCERT;
			} elseif (in_array(self::ERR_CMS_CHECK_CONTENT_NO_CONTENT, $exec['errors'])) {
				$ret['status'] = self::NO_CONTENT;
			} else {
				$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
			}
		} else {
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * CMS. Проверка аттрибутов
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status
	 */
	public static function openssl_cms_verify_attr($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'cms' => NULL,
			'-verify' => NULL,
			'-binary' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-out' => '/dev/null',
			'-noverify' => NULL,
			'-no_content_verify' => NULL
		];

		if (isset($data['certfile'])) {
			$args['-certfile'] = $data['certfile'];
		}

		if (isset($data['content'])) {
			$args['-content'] = $data['content'];
		}

		$exec = self::openssl($args);

		if ($exec['error']) {
			if (in_array(self::ERR_CMS_SIGNERINFO_VERIFY_VERIFICATION_FAILURE, $exec['errors'])) {
				$ret['status'] = self::NO_VERIFY;
			} else {
				$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
			}
		} else {
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * CMS. Проверка содержимого
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status
	 */
	public static function openssl_cms_verify_content($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'cms' => NULL,
			'-verify' => NULL,
			'-binary' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-out' => '/dev/null',
			'-noverify' => NULL,
			'-no_attr_verify' => NULL
		];

		if (isset($data['certfile'])) {
			$args['-certfile'] = $data['certfile'];
		}

		if (isset($data['content'])) {
			$args['-content'] = $data['content'];
		}

		$exec = self::openssl($args);

		if ($exec['error']) {
			if (in_array(self::ERR_CMS_VERIFY_CONTENT_VERIFY_ERROR, $exec['errors'])) {
				$ret['status'] = self::NO_VERIFY;
			} else {
				$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
			}
		} else {
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * CMS. Проверка подписантов
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status
	 */
	public static function openssl_cms_verify_signers($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'cms' => NULL,
			'-verify' => NULL,
			'-binary' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-out' => '/dev/null',
			'-no_attr_verify' => NULL,
			'-no_content_verify' => NULL
		];

		if (isset($data['certfile'])) {
			$args['-certfile'] = $data['certfile'];
		}

		if (isset($data['content'])) {
			$args['-content'] = $data['content'];
		}

		if (isset($data['CAfile'])) {
			$args['-CAfile'] = $data['CAfile'];
		}

		if (isset($data['CApath'])) {
			$args['-CApath'] = $data['CApath'];
			$args['-crl_check_all'] = NULL;
		}

		/*if (isset($data['attime'])) {
			$args['-attime'] = $data['attime'];
		} else {
			$args['-no_check_time'] = NULL;
		}*/

		$exec = self::openssl($args);

		if ($exec['error']) {
			if (in_array(self::ERR_CMS_SIGNERINFO_VERIFY_CERT_CERTIFICATE_VERIFY_ERROR, $exec['errors'])) {
				$ret['status'] = self::NO_VERIFY;
			} else {
				$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
			}
		} else {
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * CMS. Полная проверка
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status
	 */
	public static function openssl_cms_verify_all($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'cms' => NULL,
			'-verify' => NULL,
			'-binary' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-out' => '/dev/null'
		];

		if (isset($data['certfile'])) {
			$args['-certfile'] = $data['certfile'];
		}

		if (isset($data['content'])) {
			$args['-content'] = $data['content'];
		}

		if (isset($data['CAfile'])) {
			$args['-CAfile'] = $data['CAfile'];
		}

		if (isset($data['CApath'])) {
			$args['-CApath'] = $data['CApath'];
			$args['-crl_check_all'] = NULL;
		}

		/*if (isset($data['attime'])) {
			$args['-attime'] = $data['attime'];
		} else {
			$args['-no_check_time'] = NULL;
		}*/

		$exec = self::openssl($args);

		if ($exec['error']) {
			if (in_array(self::ERR_CMS_VERIFY_SIGNER_CERTIFICATE_NOT_FOUND, $exec['errors'])) {
				$ret['status'] = self::NO_SIGCERT;
			} elseif (in_array(self::ERR_CMS_CHECK_CONTENT_NO_CONTENT, $exec['errors'])) {
				$ret['status'] = self::NO_CONTENT;
			} elseif (in_array(self::ERR_CMS_SIGNERINFO_VERIFY_VERIFICATION_FAILURE, $exec['errors']) ||
				in_array(self::ERR_CMS_SIGNERINFO_VERIFY_CERT_CERTIFICATE_VERIFY_ERROR, $exec['errors']) ||
				in_array(self::ERR_CMS_VERIFY_CONTENT_VERIFY_ERROR, $exec['errors'])) {
				$ret['status'] = self::NO_VERIFY;
			} else {
				$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
			}
		} else {
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * CMS. Извлечение содержимого
	 *
	 * @param	array		$data		in, inform
	 * @param	string		$prefix		префикс файла
	 * @return	array					errors, status, result: $prefix*
	 */
	public static function openssl_cms_xtract_content($data, $prefix) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$dir = dirname($prefix);
		$prefix = basename($prefix);

		$content = tempnam($dir, $prefix);

		$args = [
			'cms' => NULL,
			'-verify' => NULL,
			'-binary' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-out' => $content,
			'-noverify' => NULL,
			'-no_attr_verify' => NULL,
			'-no_content_verify' => NULL
		];

		if (isset($data['certfile'])) {
			$args['-certfile'] = $data['certfile'];
		}

		if (isset($data['content'])) {
			$args['-content'] = $data['content'];
		}

		$exec = self::openssl($args);

		if ($exec['error']) {
			@unlink($content);

			$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
		} else {
			$ret['result'] = $content;
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * CMS. Извлечение сертификатов
	 *
	 * @param	array		$data		in, inform
	 * @param	string		$prefix		префикс файлов
	 * @return	array					errors, status, result: [certs: [$prefix*], signers: [$prefix*]]
	 */
	public static function openssl_cms_xtract_certs($data, $prefix) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$dir = dirname($prefix);
		$prefix = basename($prefix);

		$certs = tempnam($dir, $prefix);
		$signers = tempnam($dir, $prefix);

		$args = [
			'cms' => NULL,
			'-verify' => NULL,
			'-binary' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-out' => '/dev/null',
			'-certsout' => $certs,
			'-signer' => $signers,
			'-noverify' => NULL,
			'-no_attr_verify' => NULL,
			'-no_content_verify' => NULL
		];

		if (isset($data['certfile'])) {
			$args['-certfile'] = $data['certfile'];
		}

		if (isset($data['content'])) {
			$args['-content'] = $data['content'];
		}

		$exec = self::openssl($args);

		if ($exec['error']) {
			@unlink($signers);
			@unlink($certs);

			$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;

			return $ret;
		}

		$pattern = '/^' . self::CRT_HEADER . '$.*?^' . self::CRT_FOOTER . '$/ms';

		preg_match_all($pattern, @file_get_contents($certs), $certm) || $certm = [[]];
		@unlink($certs);

		preg_match_all($pattern, @file_get_contents($signers), $signerm) || $signerm = [[]];
		@unlink($signers);

		$files = ['certs' => [], 'signers' => []];

		foreach ($certm[0] as $cert) {
			foreach ($signerm[0] as $signer) {
				if ($signer === $cert) break;

				unset($signer);
			}

			$file = tempnam($dir, $prefix);
			@file_put_contents($file, $cert . PHP_EOL);
			$files[isset($signer) ? 'signers' : 'certs'][] = $file;
		}

		$ret['result'] = $files;
		$ret['status'] = self::SUCCESS;

		return $ret;
	}

	/**
	 * CMS. Подписанты с сертификатами
	 *
	 * @param	array		$data		in, inform, cms
	 * @return	array					errors, status, result: []
	 */
	public static function openssl_cms_intern_signers($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if (! isset($data['cms'])) {
			$ret['errors'] .= self::NEAP . ': cms' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$ret['result'] = [];
		$ret['status'] = self::SUCCESS;

		foreach ($data['cms']['signerInfos'] as $i => $sig) {
			foreach ($data['cms']['certificates'] as $j => $crt) {
				if (isset($sig[self::X509V3_SUBJECT_KEY_IDENTIFIER]) &&
					isset($crt[self::X509V3_SUBJECT_KEY_IDENTIFIER]) &&
					($sig[self::X509V3_SUBJECT_KEY_IDENTIFIER] === $crt[self::X509V3_SUBJECT_KEY_IDENTIFIER])) {
					$ret['result'][$i] = $j;
					break;
				}

				if (isset($sig[self::X509_ISSUER]) &&
					isset($crt[self::X509_ISSUER]) &&
					($sig[self::X509_ISSUER] === $crt[self::X509_ISSUER]) &&
					isset($sig[self::X509_SERIAL_NUMBER]) &&
					isset($crt[self::X509_SERIAL_NUMBER]) &&
					($sig[self::X509_SERIAL_NUMBER] === $crt[self::X509_SERIAL_NUMBER])) {
					$ret['result'][$i] = $j;
					break;
				}
			}

			if (! isset($ret['result'][$i])) {
				$ret['status'] = self::NO_SIGCERT;
			}
		}

		return $ret;
	}

	/**
	 * CMS. Обнаружение содержимого среди файлов из списка
	 *
	 * @param	array		$data		in, inform
	 * @param	array		$files		список файлов
	 * @return	array					errors, status, result: filename
	 */
	public static function openssl_cms_detect_content($data, $files) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$ret['status'] = self::NO_CONTENT;

		foreach ((array) $files as $file) {
			if (@is_dir($file)) continue;

			$data['content'] = $file;
			$a = self::openssl_cms_verify_content($data);

			if ($a['status'] === self::SUCCESS) {
				$ret['result'] = $file;
				$ret['status'] = self::SUCCESS;
				break;
			}
		}

		return $ret;
	}

	/**
	 * CMS. Обнаружение подписантов среди файлов из списка
	 *
	 * @param	array		$data		in, inform, cms
	 * @param	array		$files		список файлов
	 * @param	array		$exist		список уже обнаруженных подписантов
	 * @return	array					errors, status, result: [filenames]
	 */
	public static function openssl_cms_detect_signers($data, $files, $exist = []) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if (! isset($data['cms'])) {
			$ret['errors'] .= self::NEAP . ': cms' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$ret['result'] = [];
		$ret['status'] = self::NO_SIGCERT;

		foreach ((array) $files as $file) {
			if (@is_dir($file)) continue;

			if (($cms = self::openssl_cert_parse($file)) === FALSE) continue;

			$ret['status'] = self::SUCCESS;

			foreach ($data['cms']['signerInfos'] as $i => $sig) {
				if (isset($exist[$i]) ||
					isset($ret['result'][$i])) continue;

				foreach ($cms['certificates'] as $j => $crt) {
					if (isset($sig[self::X509V3_SUBJECT_KEY_IDENTIFIER]) &&
						isset($crt[self::X509V3_SUBJECT_KEY_IDENTIFIER]) &&
						($sig[self::X509V3_SUBJECT_KEY_IDENTIFIER] === $crt[self::X509V3_SUBJECT_KEY_IDENTIFIER])) {
						$ret['result'][$i] = $file;
						break;
					}

					if (isset($sig[self::X509_ISSUER]) &&
						isset($crt[self::X509_ISSUER]) &&
						($sig[self::X509_ISSUER] === $crt[self::X509_ISSUER]) &&
						isset($sig[self::X509_SERIAL_NUMBER]) &&
						isset($crt[self::X509_SERIAL_NUMBER]) &&
						($sig[self::X509_SERIAL_NUMBER] === $crt[self::X509_SERIAL_NUMBER])) {
						$ret['result'][$i] = $file;
						break;
					}
				}

				if (! isset($ret['result'][$i])) {
					$ret['status'] = self::NO_SIGCERT;
				}
			}

			if ($ret['status'] === self::SUCCESS) break;
		}

		return $ret;
	}

	/**
	 * CMS. Поиск содержимого среди файлов по шаблону
	 *
	 * @param	array		$data		in, inform
	 * @param	string		$pattern	шаблон для glob
	 * @return	array					errors, status, result: filename
	 */
	public static function openssl_cms_search_content($data, $pattern) {
		return self::openssl_cms_detect_content($data, @glob($pattern, GLOB_NOSORT));
	}

	/**
	 * CMS. Поиск подписантов среди файлов по шаблону
	 *
	 * @param	array		$data		in, inform, cms
	 * @param	string		$pattern	шаблон для glob
	 * @return	array					errors, status, result: [filenames]
	 */
	public static function openssl_cms_search_signers($data, $pattern) {
		$a = self::openssl_cms_intern_signers($data);

		switch ($a['status']) {
		case self::SUCCESS:
			return ['errors' => $a['errors'], 'status' => $a['status'], 'result' => []];
		case self::FAILURE:
			return ['errors' => $a['errors'], 'status' => $a['status']];
		case self::NO_SIGCERT:
			return self::openssl_cms_detect_signers($data, @glob($pattern, GLOB_NOSORT), $a['result']);
		}
	}

	/**
	 * CRL. Определение формата (перебором вариантов)
	 *
	 * @param	array		$data		in
	 * @return	array					errors, status, result: DER|PEM
	 */
	public static function openssl_crl_input_format($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'crl' => NULL,
			'-noout' => NULL,
			'-in' => $data['in']
		];

		$forms = ['DER', 'PEM'];

		foreach ($forms as $form) {
			$args['-inform'] = $form;
			$exec = self::openssl($args);

			if (! $exec['error']) break;
		}

		if ($exec['error']) {
			if ($exec['output'][0] === self::UTLC) {
				$ret['status'] = self::NO_FORMAT;
			} else {
				$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
			}
		} else {
			$ret['result'] = $form;
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * CRL. Разбор основных полей
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status, result: []
	 */
	public static function openssl_crl_parse_basic($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'crl' => NULL,
			'-noout' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-hash' => NULL,
			'-issuer' => NULL,
			'-lastupdate' => NULL,
			'-nextupdate' => NULL,
			'-nameopt' => 'lname,sep_multiline,utf8'
		];

		$exec = self::openssl($args);

		if ($exec['error']) {
			$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;

			return $ret;
		}

		$CRL = [];

		$dump = implode(PHP_EOL, $exec['output']);

		if (preg_match('/.*(?=[\n\r]+issuer=$)/m', $dump, $a)) {
			$CRL[self::CRL_ISSUER_HASH] = $a[0];
		}

		if (preg_match('/(?<=^issuer=$).*?(?=^[^[:space:]])/ms', $dump, $a) &&
			preg_match_all('/^[[:space:]]+(.*?)=(.*)$/m', $a[0], $b)) {
			$CRL[self::CRL_ISSUER] = array_combine($b[1], $b[2]);
		}

		if (preg_match('/(?<=^lastUpdate=).*$/m', $dump, $a)) {
			$CRL[self::CRL_LAST_UPDATE] = strtotime($a[0]);
		}

		if (preg_match('/(?<=^nextUpdate=).*$/m', $dump, $a)) {
			$CRL[self::CRL_NEXT_UPDATE] = strtotime($a[0]);
		}

		$ret['result'] = $CRL;
		$ret['status'] = self::SUCCESS;

		return $ret;
	}

	/**
	 * CRL. Запись в формате PEM
	 *
	 * @param	array		$data		in, inform, out
	 * @return	array					errors, status
	 */
	public static function openssl_crl_save($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if (! isset($data['out'])) {
			$ret['errors'] .= self::NEAP . ': out' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'crl' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-out' => $data['out'],
			'-outform' => 'PEM'
		];

		$exec = self::openssl($args);

		if ($exec['error']) {
			$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
		} else {
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * CRL. Запись в хранилище
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status, result
	 */
	public static function openssl_crl_store($data, $path) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$ret = self::openssl_crl_parse_basic($data);

		if ($ret['status'] !== self::SUCCESS) {
			return $ret;
		}

		$data['out'] = $path . $ret['result'][self::CRL_ISSUER_HASH] . '.r';
		$certs = @glob($path . $ret['result'][self::CRL_ISSUER_HASH] . '.*', GLOB_NOSORT);

		foreach ((array) $certs as $cert) {
			if (! preg_match('/(?<=\.)[[:digit:]]+$/', $cert, $a)) continue;

			$crt = self::openssl_x509_parse_basic(['in' => $cert, 'inform' => 'PEM']);

			if (($crt['status'] === self::SUCCESS) &&
				($crt['result'][self::X509_SUBJECT_HASH] === $ret['result'][self::CRL_ISSUER_HASH]) &&
				($crt['result'][self::X509_SUBJECT] == $ret['result'][self::CRL_ISSUER])) {
				$data['out'] .= $a[0];
				break;
			}
		}

		$crl = self::openssl_crl_save($data);

		if ($crl['status'] === self::SUCCESS) {
			$ret['crl'] = $data['out'];
		}

		return $ret;
	}

	/**
	 * CRL. Загрузка в хранилище
	 */
	public static function openssl_crl_load($uris, $path) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		$file = tempnam(sys_get_temp_dir(), '');

		foreach ((array) $uris as $uri) {
			if (($data = @file_get_contents($uri)) &&
				@file_put_contents($file, $data)) {
				$data = ['in' => $file];
				$ret = self::openssl_crl_input_format($data);

				if ($ret['status'] === self::SUCCESS) {
					$data['inform'] = $ret['result'];
					$ret = self::openssl_crl_store($data, $path);

					if ($ret['status'] === self::SUCCESS) break;
				}
			}
		}

		@unlink($file);

		return $ret;
	}

	/**
	 * CRL. Поиск в хранилище
	 */
	public static function openssl_crl_find($issuerHash, $issuer, $path) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		$crls = @glob($path . $issuerHash . '.r*', GLOB_NOSORT);

		foreach ((array) $crls as $crl) {
			$ret = self::openssl_crl_parse_basic(['in' => $crl, 'inform' => 'PEM']);

			if (($ret['status'] === self::SUCCESS) &&
				($ret['result'][self::CRL_ISSUER_HASH] === $issuerHash) &&
				($ret['result'][self::CRL_ISSUER] == $issuer)) {
				$ret['crl'] = $crl;
				break;
			}
		}

		return $ret;
	}

	/**
	 * x509. Определение формата (перебором вариантов)
	 *
	 * @param	array		$data		in
	 * @return	array					errors, status, result: DER|PEM
	 */
	public static function openssl_x509_input_format($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'x509' => NULL,
			'-noout' => NULL,
			'-in' => $data['in']
		];

		$forms = ['DER', 'PEM'];

		foreach ($forms as $form) {
			$args['-inform'] = $form;
			$exec = self::openssl($args);

			if (! $exec['error']) break;
		}

		if ($exec['error']) {
			if ($exec['output'][0] === self::UTLC) {
				$ret['status'] = self::NO_FORMAT;
			} else {
				$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
			}
		} else {
			$ret['result'] = $form;
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * x509. Разбор основных полей
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status, result: []
	 */
	public static function openssl_x509_parse_basic($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'x509' => NULL,
			'-noout' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-email' => NULL,
			'-serial' => NULL,
			'-issuer_hash' => NULL,
			'-issuer' => NULL,
			'-subject_hash' => NULL,
			'-subject' => NULL,
			'-dates' => NULL,
			'-purpose' => NULL,
			'-nameopt' => 'lname,sep_multiline,utf8'
		];

		$exec = self::openssl($args);

		if ($exec['error']) {
			$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;

			return $ret;
		}

		$x509 = [];

		$dump = implode(PHP_EOL, $exec['output']);

		if (preg_match('/.*(?=[\n\r]+serial=)/m', $dump, $a)) {
			$x509[self::X509_EMAIL_ADDRESS] = $a[0];
		}

		if (preg_match('/(?<=^serial=).*$/m', $dump, $a)) {
			$x509[self::X509_SERIAL_NUMBER] = strtolower($a[0]);
		}

		if (preg_match('/.*(?=[\n\r]+issuer=$)/m', $dump, $a)) {
			$x509[self::X509_ISSUER_HASH] = $a[0];
		}

		if (preg_match('/(?<=^issuer=$).*?(?=^[^[:space:]])/ms', $dump, $a) &&
			preg_match_all('/^[[:space:]]+(.*?)=(.*)$/m', $a[0], $b)) {
			$x509[self::X509_ISSUER] = array_combine($b[1], $b[2]);
		}

		if (preg_match('/.*(?=[\n\r]+subject=$)/m', $dump, $a)) {
			$x509[self::X509_SUBJECT_HASH] = $a[0];
		}

		if (preg_match('/(?<=^subject=$).*?(?=^[^[:space:]])/ms', $dump, $a) &&
			preg_match_all('/^[[:space:]]+(.*?)=(.*)$/m', $a[0], $b)) {
			$x509[self::X509_SUBJECT] = array_combine($b[1], $b[2]);
		}

		if (preg_match('/(?<=^notBefore=).*$/m', $dump, $a)) {
			$x509[self::X509_NOT_BEFORE] = strtotime($a[0]);
		}

		if (preg_match('/(?<=^notAfter=).*$/m', $dump, $a)) {
			$x509[self::X509_NOT_AFTER] = strtotime($a[0]);
		}

//		if (preg_match('/(?<=^Certificate purposes:$).*/ms', $dump, $a) &&
//			preg_match_all('/^.*(?= : Yes$)/mi', $a[0], $b)) {
//			$x509[self::X509_PURPOSES] = $b[0];
//		}

		$ret['result'] = $x509;
		$ret['status'] = self::SUCCESS;

		return $ret;
	}

	/**
	 * x509. Разбор расширений
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status, result: []
	 */
	public static function openssl_x509_parse_extv3($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'x509' => NULL,
			'-noout' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-text' => NULL,
			'-certopt' => 'no_header,no_version,no_serial,no_signame,no_validity,no_subject,no_issuer,no_pubkey,no_sigdump,no_aux,ext_parse'
		];

		$exec = self::openssl($args);

		if ($exec['error']) {
			$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;

			return $ret;
		}

		$x509v3 = [];

		$b = ['X509v3.*?', 'Authority Information Access', 'Subject Information Access', 'Signing Tool of Subject', 'Signing Tool of Issuer'];
		$a = preg_split('/^[[:space:]]*(' . implode('|', $b) . '):.*$/im', implode(PHP_EOL, $exec['output']), NULL, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

		for ($i = 0; $i < count($a); $i += 2) {
			$key = $a[$i];
			$val = $a[$i + 1];

			switch ($key) {
			case 'X509v3 extensions':
				continue 2;

			case 'X509v3 Key Usage':
				$key = self::X509V3_KEY_USAGE;
				$val = preg_split('/, /', trim($val));
				break;

			case 'X509v3 Extended Key Usage':
				$key = self::X509V3_EXT_KEY_USAGE;
				$val = preg_split('/, /', trim($val));
				break;

			case 'X509v3 Basic Constraints':
				$key = self::X509V3_BASIC_CONSTRAINTS;
				preg_match('/CA:(TRUE|FALSE)/i', $val, $b) &&
					$val = $b[1];
				break;

			case 'Signing Tool of Subject':
				$key = self::X509V3_SIGNING_TOOL_OF_SUBJECT;
				preg_match('/UTF8STRING[[:space:]]*:(.*)/i', $val, $b) &&
					$val = $b[1];
				break;

			case 'Signing Tool of Issuer':
				$key = self::X509V3_SIGNING_TOOL_OF_ISSUER;
				preg_match_all('/UTF8STRING[[:space:]]*:(.*)/i', $val, $b) &&
					$val = $b[1];
				break;

			case 'X509v3 Certificate Policies':
				$key = self::X509V3_CERTIFICATE_POLICIES;
				preg_match_all('/Policy: (.*)/i', $val, $b) &&
					$val = $b[1];
				break;

			case 'X509v3 CRL Distribution Points':
				$key = self::X509V3_CRL_DISTRIBUTION_POINTS;
				preg_match_all('/URI:(.*)/i', $val, $b) &&
					$val = $b[1];
				break;

			case 'Authority Information Access':
				$key = self::X509V3_AUTHORITY_INFO_ACCESS;
				preg_match_all('/CA Issuers - URI:(.*)/i', $val, $b) &&
					$val = $b[1];
				break;

			case 'X509v3 Subject Key Identifier':
				$key = self::X509V3_SUBJECT_KEY_IDENTIFIER;
				$val = strtolower(strtr(trim($val), [':' => '']));
				break;

			case 'X509v3 Authority Key Identifier':
				$key = self::X509V3_AUTHORITY_KEY_IDENTIFIER;
				$c = [];
				preg_match('/keyid:(.*)/i', $val, $b) &&
					$c[self::X509V3_KEY_IDENTIFIER] = strtolower(strtr($b[1], [':' => '']));
				preg_match('/serial:(.*)/i', $val, $b) &&
					$c[self::X509_SERIAL_NUMBER] = strtolower(strtr($b[1], [':' => '']));
				preg_match('/DirName:(.*)/i', $val, $b) &&
					$c[self::X509_ISSUER] = stripcslashes($b[1]);
				$val = $c;
				break;

//			default:
//				$val = trim($val);
			}

			$x509v3[$key] = $val;
		}

		$ret['result'] = $x509v3;
		$ret['status'] = self::SUCCESS;

		return $ret;
	}

	/**
	 * x509. Запись в формате PEM
	 *
	 * @param	array		$data		in, inform, out
	 * @return	array					errors, status
	 */
	public static function openssl_x509_save($data) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if (! isset($data['out'])) {
			$ret['errors'] .= self::NEAP . ': out' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$args = [
			'x509' => NULL,
			'-in' => $data['in'],
			'-inform' => $data['inform'],
			'-out' => $data['out'],
			'-outform' => 'PEM'
		];

		$exec = self::openssl($args);

		if ($exec['error']) {
			$ret['errors'] = implode(PHP_EOL, $exec['output']) . PHP_EOL;
		} else {
			$ret['status'] = self::SUCCESS;
		}

		return $ret;
	}

	/**
	 * x509. Запись в хранилище
	 *
	 * @param	array		$data		in, inform
	 * @return	array					errors, status, result
	 */
	public static function openssl_x509_store($data, $path) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		if (! isset($data['in'])) {
			$ret['errors'] .= self::NEAP . ': in' . PHP_EOL;
		}
		if (! isset($data['inform'])) {
			$ret['errors'] .= self::NEAP . ': inform' . PHP_EOL;
		}
		if ($ret['errors']) {
			return $ret;
		}

		$ret = self::openssl_x509_parse_basic($data);

		if ($ret['status'] !== self::SUCCESS) {
			return $ret;
		}

		$certs = @glob($path . $ret['result'][self::X509_SUBJECT_HASH] . '.*', GLOB_NOSORT);

		foreach ((array) $certs as $cert) {
			if (! preg_match('/(?<=\.)[[:digit:]]+$/', $cert)) continue;

			$crt = self::openssl_x509_parse_basic(['in' => $cert, 'inform' => 'PEM']);

			if (($crt['status'] === self::SUCCESS) &&
				($crt['result'] == $ret['result'])) {
				$ret['cert'] = $cert;
				break;
			}
		}

		if (! isset($ret['cert'])) {
			for ($i = 0; $i < 100; $i++) {
				$data['out'] = $path . $ret['result'][self::X509_SUBJECT_HASH] . '.' . $i;

				if (! @file_exists($data['out'])) {
					$crt = self::openssl_x509_save($data);

					if ($crt['status'] === self::SUCCESS) {
						$ret['cert'] = $data['out'];
					}

					break;
				}
			}
		}

		return $ret;
	}

	/**
	 * x509. Загрузка в хранилище
	 */
	public static function openssl_x509_load($uris, $path) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		$file = tempnam(sys_get_temp_dir(), '');

		foreach ((array) $uris as $uri) {
			if (($data = @file_get_contents($uri)) &&
				@file_put_contents($file, $data)) {
				$data = ['in' => $file];
				$ret = self::openssl_x509_input_format($data);

				if ($ret['status'] === self::SUCCESS) {
					$data['inform'] = $ret['result'];
					$ret = self::openssl_x509_store($data, $path);

					if ($ret['status'] === self::SUCCESS) break;
				}
			}
		}

		@unlink($file);

		return $ret;
	}

	/**
	 * x509. Поиск в хранилище
	 */
	public static function openssl_x509_find($subjectHash, $subject, $path) {
		$ret = ['errors' => '', 'status' => self::FAILURE];

		$certs = @glob($path . $subjectHash . '.*', GLOB_NOSORT);

		foreach ((array) $certs as $cert) {
			if (! preg_match('/(?<=\.)[[:digit:]]+$/', $cert)) continue;

			$ret = self::openssl_x509_parse_basic(['in' => $cert, 'inform' => 'PEM']);

			if (($ret['status'] === self::SUCCESS) &&
				($ret['result'][self::X509_SUBJECT_HASH] === $subjectHash) &&
				($ret['result'][self::X509_SUBJECT] == $subject)) {
				$ret['cert'] = $cert;
				break;
			}
		}

		return $ret;
	}

	/**
	 * x509. Проверка
	 */
	public static function openssl_x509_verify($file, $path) {
		$ret = self::SUCCESS;

		$args = [
			'verify' => NULL,
			'-CApath' => $path,
			'-crl_check_all' => NULL,
//			'-no_check_time' => NULL,
			$file
		];

		$exec = self::openssl($args);

		if ($exec['error'] && preg_match('/^error ([[:digit:]]+) at [[:digit:]]+ depth lookup:/m', implode(PHP_EOL, $exec['output']), $a)) {
			$ret = $a[1];
		}

		return $ret;
	}

	/**
	 * Парсер ASN.1 пока не готов
	 */
	private static function openssl_cms_parse_bmps($str) {
		return preg_match('/(?<=BMPSTRING:).*/i', $str, $m) ? json_decode('"' . strtolower($m[0]) . '"') : FALSE;
	}

	private static function openssl_cms_parse_time($str) {
		return preg_match('/(?<=UTCTIME:).*/i', $str, $m) ? @strtotime($m[0]) : FALSE;
	}

	private static function openssl_cms_parse_dump($str, $asn = FALSE) {
		$ret = '';

		if (preg_match_all('/^[[:space:]]*([[:xdigit:]]{4,}) - (<SPACES\/NULS>|.*?(?=[[:space:]]{2,}))/m', $str, $m, PREG_SET_ORDER)) {
			foreach ($m as $a) {
				if ($a[2] === '<SPACES/NULS>') {
					$ret = str_pad($ret, intval($a[1], 16) * 2, '0');
				} else {
					$ret .= strtr($a[2], ['-' => '', ' ' => '']);
				}
			}
		}

		if ($asn && strlen($ret) > 1) {
			switch (substr($ret, 0, 2)) {
//			case '03': // BIT STRING
			case '04': // OCTET STRING
//			case '0c': // UTF8String
//			case '13': // PrintableString
//			case '14': // TeletexString
//			case '16': // IA5String
//			case '1e': // BMPString
				$ret = substr($ret, 4, intval(substr($ret, 2, 2), 16) * 2);
			}
		}

		return $ret;
	}

	private static function openssl_cert_parse($file) {
		$ret = FALSE;

		do {
			$a = self::openssl_x509_input_format(['in' => $file]);

			if ($a['status'] !== self::SUCCESS) break;

			if ($a['result'] === 'DER') {
				$cert = tempnam(sys_get_temp_dir(), '');

				$args = [
					'x509' => NULL,
					'-in' => $file,
					'-inform' => 'DER',
					'-out' => $cert,
					'-outform' => 'PEM'
				];

				$exec = self::openssl($args);

				if ($exec['error']) break;
			} else {
				$cert = $file;
			}

			$test = tempnam(sys_get_temp_dir(), '');

			@file_put_contents($test, '-----BEGIN CERTIFICATE-----
MIIBTjCB/KADAgECAgRURVNUMAoGBiqFAwICAwUAMA8xDTALBgNVBAMMBFRFU1Qw
HhcNMTgwMTEwMTcwMDAwWhcNMTkxMjMxMTcwMDAwWjAPMQ0wCwYDVQQDDARURVNU
MGMwHAYGKoUDAgITMBIGByqFAwICIwAGByqFAwICHgEDQwAEQG+JTo8YPX2eauq/
8OOddKVGVDxHi69mmfJB1gKqtmBaqQCgtbsh9tbJyGv7meCqAc8ASux8eT1tjsv5
MPfLdX2jPDA6MB0GA1UdDgQWBBSPqcvRovJGO8oYsmEvGf5h7VQ+cTAMBgNVHRME
BTADAQH/MAsGA1UdDwQEAwIBhjAKBgYqhQMCAgMFAANBABdLxpPf4Rbx1CaTQA0c
3ezp0LVAu7uwFoQxO64i+PWiZUcz9NHFRGRqOymdW3pvfstaFqJ5UTiflQz9tyyL
m1c=
-----END CERTIFICATE-----
-----BEGIN PRIVATE KEY-----
MEUCAQAwHAYGKoUDAgITMBIGByqFAwICIwAGByqFAwICHgEEIgIgT8m97CaEFo9U
jawbVRxicoCq2c+94cq/O1LLMEDMKqw=
-----END PRIVATE KEY-----');

			$sign = tempnam(sys_get_temp_dir(), '');

			$args = [
				'cms' => NULL,
				'-sign' => NULL,
				'-in' => '/dev/null',
				'-out' => $sign,
				'-outform' => 'PEM',
				'-signer' => $test,
				'-certfile' => $cert,
				'-nocerts' => NULL,
				'-noattr' => NULL
			];

			$exec = self::openssl($args);

			if ($exec['error']) break;

			$a = self::openssl_cms_parse(['in' => $sign, 'inform' => 'PEM']);

			if ($a['status'] !== self::SUCCESS) break;

			$ret = $a['result'];
		} while (0);

		if (isset($sign)) {
			@unlink($sign);
		}

		if (isset($test)) {
			@unlink($test);
		}

		if (isset($cert) && ($cert !== $file)) {
			@unlink($cert);
		}

		return $ret;
	}
}
