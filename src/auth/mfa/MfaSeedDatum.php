<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\mfa;

use function JulianSeymour\PHPWebApplicationFramework\base32_decode;
use function JulianSeymour\PHPWebApplicationFramework\base32_encode;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Base32Datum;
use Exception;

class MfaSeedDatum extends Base32Datum{

	public function regenerate(): int{
		$f = __METHOD__;
		try {
			$length = 16;
			$this->setValue(base32_encode(random_bytes(($length * 5) / 8)));
			$this->setUpdateFlag(true);
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function provisionOTPKey(string $data, string $name):string{
		$f = __METHOD__;
		try {
			// $name = rawurlencode($name);
			$ts = static::getKeyGenerationTimestamp();
			$domain = DOMAIN_PASCALCASE;
			$arr = [
				'secret' => $data,
				'issuer' => $domain,
				'digits' => 6,
				'counter' => $ts
			];
			$query = http_build_query($arr);
			$str = "otpauth://totp/{$domain}:{$name}?{$query}";
			return $str;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getKeyGenerationTimestamp($interval = null){
		$f = __METHOD__;
		if ($interval == null) {
			if (! defined("MFA_KEYGEN_INTERVAL")) {
				Debug::error("{$f} interval and default key turnover inverval are undefined");
			}
			$interval = MFA_KEYGEN_INTERVAL;
		}
		return floor(microtime(true) / $interval);
	}

	public static function generateOTP(string $secret_key, int $timestamp, ?int $length = null):string{
		$f = __METHOD__;
		if (strlen($secret_key) < 8) {
			Debug::error("{$f} secret key minimum length is 8 chars");
		} elseif ($length == null) {
			if (! defined("MFA_OTP_LENGTH")) {
				Debug::error("{$f} length and default OTP length are both undefined");
			}
			$length = MFA_OTP_LENGTH;
		}
		$ts_packed = pack('N*', 0) . pack('N*', $timestamp);
		$hash = hash_hmac('sha1', $ts_packed, $secret_key, true);
		$offset = ord($hash[19]) & 0xf;
		$otp = (((ord($hash[$offset + 0]) & 0x7f) << 24) | ((ord($hash[$offset + 1]) & 0xff) << 16) | ((ord($hash[$offset + 2]) & 0xff) << 8) | (ord($hash[$offset + 3]) & 0xff)) % pow(10, $length);
		return str_pad($otp, $length, '0', STR_PAD_LEFT);
	}

	public static function verifyOTPStatic(string $seed_32, string $otp, int $window = 4, ?int $timestamp = null):bool{
		$f = __METHOD__;
		$print = false;
		if ($timestamp === null) {
			if ($print) {
				Debug::print("{$f} timestamp is null");
			}
			$timestamp = static::getKeyGenerationTimestamp();
		} elseif ($print) {
			Debug::print("{$f} timestamp is \"{$timestamp}\"");
		}
		$decoded = base32_decode($seed_32);
		for ($ts = $timestamp - $window; $ts <= $timestamp + $window; $ts ++) {
			$generated = static::generateOTP($decoded, $ts);
			if ($generated == $otp) {
				if ($print) {
					Debug::print("{$f} yes, OTP \"{$otp}\"");
				}
				return true;
			} elseif ($print) {
				Debug::print("{$f} no, OTP \"{$otp}\" does not match generated OTP \"{$generated}\"");
			}
		}
		return false;
	}

	public function verifyOTP(string $otp, int $window = 4, ?int $timestamp = null):bool{
		return static::verifyOTPStatic($this->getValue(), $otp, $window, $timestamp);
	}
}
