<?php
namespace JulianSeymour\PHPWebApplicationFramework;

/**
 *
 * @param string $data
 * @param string $nonce
 * @return NULL|string
 */
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use DateTime;
use DateTimeZone;
use Exception;
use ReflectionClass;

function argon_hash(string $data, string $nonce){
	$f = __FUNCTION__;
	if (strlen($nonce) !== SODIUM_CRYPTO_PWHASH_SALTBYTES) {
		Debug::error("{$f} nonce is wrong length (" . strlen($nonce) . ", should be " . SODIUM_CRYPTO_PWHASH_SALTBYTES . ")");
		return null;
	} elseif (empty($data)) {
		Debug::error("{$f} data is empty");
	} elseif ($data == null) {
		Debug::error("{$f} data is null");
	}
	return sodium_crypto_pwhash(32, $data, $nonce, 4, 33554432, SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13);
}

/**
 * returns true if array_key_exists($key, $array) => true for any array
 *
 * @param string $key
 * @param array[] ...$arrays
 * @return boolean
 */
function arrays_key_exists(string $needle, ...$haystacks): bool{
	foreach ($haystacks as $haystack) {
		if (array_key_exists($needle, $haystack)) {
			return true;
		}
	}
	return false;
}

function array_keys_exist(array $haystack, ...$needles): bool{
	$f = __FUNCTION__;
	if (! isset($haystack) || ! is_array($haystack)) {
		Debug::error("{$f} array is not an array");
	}
	foreach ($needles as $needle) {
		if (! array_key_exists($needle, $haystack)) {
			return false;
		}
	}
	return true;
}

/**
 * returns a copy of the array minus key $name
 *
 * @param array $array
 * @param string $name
 * @return array|NULL
 */
function array_remove_key(array $array, string $name): ?array
{
	$f = __FUNCTION__;
	if (empty($array)) {
		Debug::printArray($array);
		Debug::error("{$f} error removing index \"{$name}\": array is empty");
	} elseif (is_array($name)) {
		Debug::error("{$f} index is an array");
	}
	$keys = array_keys($array);
	$key = array_search($name, $keys);
	if ($key === false) {
		return $array;
		Debug::warning("{$f} index \"{$name}\" is undefined");
		Debug::printArray($keys);
		Debug::printStackTrace();
	}
	return array_diff_key($array, [
		$name => 'value'
	]);
}

function array_remove_keys(array $array, ...$keys): ?array
{
	// $f = __FUNCTION__;
	if (count($keys) === 1 && is_array($keys[0])) {
		return array_remove_keys($array, ...array_values($keys));
	}
	foreach ($keys as $name) {
		$array = array_remove_key($array, $name);
	}
	return $array;
}

function associate(array $arr): array
{
	$f = __METHOD__; //"associate()";
	if (is_associative($arr)) {
		Debug::error("{$f} array is already associative");
		return $arr;
	}
	$ret = [];
	foreach ($arr as $i) {
		$ret[$i] = $i;
	}
	return $ret;
}

/**
 * encode a string into a left side zero-padded base2 string
 *
 * @param string $s
 * @return string
 */
function base2_encode_padded(string $s)
{
	$r = '';
	foreach (str_split($s) as $c) {
		$r .= str_pad(decbin(ord($c)), 8, "0", STR_PAD_LEFT);
	}
	return $r;
}

/**
 * encode a string into base32 (i.e.
 * 5-bits per character) format used by TOTP authenticator systems
 *
 * @param string $encode_me
 * @return NULL|string
 */
function base32_encode(string $encode_me)
{
	$f = __FUNCTION__;
	try {
		$print = false;
		$debug = $print || false;
		$bit_count = strlen($encode_me) * 8;
		if ($bit_count % 5 !== 0) {
			Debug::error("{$f} string length is not divisible by 5");
			return null;
		}
		$backup = $encode_me;
		$backup_printable = bin2hex($backup);
		if ($print) {
			Debug::print("{$f} about to encode \"{$backup_printable}\"");
		}
		$encoded = "";
		$chars = [
			"A",
			"B",
			"C",
			"D",
			"E",
			"F",
			"G",
			"H",
			"I",
			"J",
			"K",
			"L",
			"M",
			"N",
			"O",
			"P",
			"Q",
			"R",
			"S",
			"T",
			"U",
			"V",
			"W",
			"X",
			"Y",
			"Z",
			"2",
			"3",
			"4",
			"5",
			"6",
			"7"
		];
		for ($i = 0; $i < $bit_count; $i += 5) {
			$byte = ord($encode_me[strlen($encode_me) - 1]);
			$byte_type = gettype($byte);
			if ($print) {
				Debug::print("{$f} byte \"{$byte}\" type is \"{$byte_type}\"");
			}
			$byte &= 0b00011111;
			if (! array_key_exists($byte, $chars)) {
				Debug::error("{$f} invalid offset \"{$byte}\"");
				return null;
			}
			$encoded = $chars[$byte] . $encoded;
			$encode_me = shift_right_string($encode_me, 5);
		}
		if ($print) {
			Debug::print("{$f} encoded value is \"{$encoded}\"");
		}
		if ($debug) {
			$decoded = base32_decode($encoded);
			$decoded_printable = bin2hex($decoded);
			if ($decoded_printable !== $backup_printable) {
				Debug::error("{$f} decoded value ({$decoded_printable}) does not match backup ({$backup_printable})");
				return null;
			}
			Debug::print("{$f} decoding works perfectly");
		}
		return $encoded;
	} catch (Exception $x) {
		x($f, $x);
	}
}

/**
 * decode a string encoded by the above function
 *
 * @param string $base32
 * @return NULL|string
 */
function base32_decode(string $base32)
{
	$f = __FUNCTION__;
	try {
		$print = false;
		$char2int = [
			"A" => 0,
			"B" => 1,
			"C" => 2,
			"D" => 3,
			"E" => 4,
			"F" => 5,
			"G" => 6,
			"H" => 7,
			"I" => 8,
			"J" => 9,
			"K" => 10,
			"L" => 11,
			"M" => 12,
			"N" => 13,
			"O" => 14,
			"P" => 15,
			"Q" => 16,
			"R" => 17,
			"S" => 18,
			"T" => 19,
			"U" => 20,
			"V" => 21,
			"W" => 22,
			"X" => 23,
			"Y" => 24,
			"Z" => 25,
			"2" => 26,
			"3" => 27,
			"4" => 28,
			"5" => 29,
			"6" => 30,
			"7" => 31
		];
		if (! preg_match('/^[' . implode('', array_keys($char2int)) . ']+$/', $base32)) {
			Debug::error("{$f} string \"{$base32}\" contains invalid characters");
			return null;
		}
		$current_byte = 0;
		$bit_count = 0;
		$decoded = "";
		for ($i = 0; $i < strlen($base32); $i ++) {
			$current_byte = ($current_byte << 5) + $char2int[$base32[$i]];
			$bit_count += 5;
			if ($bit_count > 7) {
				$bit_count -= 8;
				$mask = 0xff << $bit_count;
				$decoded .= chr(($mask & $current_byte) >> $bit_count);
			}
		}
		$printable = bin2hex($decoded);
		if ($print) {
			Debug::print("{$f} returning \"{$printable}\"");
		}
		return $decoded;
	} catch (Exception $x) {
		x($f, $x);
	}
}

/**
 * URL-safe version of base64_encode
 *
 * @param string $data
 * @return string
 */
function base64url_encode(string $data): string
{
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * decodes the output of base64url_encode
 *
 * @param string $data
 * @return string
 */
function base64url_decode(string $data)
{
	return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

/**
 * modified from https://www.php.net/manual/en/function.bbcode-create.php
 *
 * @param NULL|object $bbcode_container
 * @param string $string
 * @return mixed
 */
function bbcode_parse_extended($bbcode_container = null, $to_parse)
{
	$f = __METHOD__; //"bbcode_parse_extended()";
	$print = false;
	$tags = 'b|center|color|i|left|right|size|url|img|quote|video';
	$matches = [];
	while (preg_match_all('`\[(' . $tags . ')=?(.*?)\](.+?)\[/\1\]`', $to_parse, $matches)) {
		foreach ($matches[0] as $key => $match) {
			list ($tag, $param, $innertext) = array(
				$matches[1][$key],
				$matches[2][$key],
				$matches[3][$key]
			);
			switch ($tag) {
				case 'b':
					$replacement = "<strong>{$innertext}</strong>";
					break;
				case 'i':
					$replacement = "<em>{$innertext}</em>";
					break;
				case 'size':
					$replacement = "<span style=\"font-size:{$param}px;\">{$innertext}</span>";
					break;
				case 'color':
					$replacement = "<span style=\"color:{$param};\">{$innertext}</span>";
					break;
				case 'center':
					$replacement = "<span style=\"text-align:center;\">{$innertext}</span>";
					break;
				case 'left':
					$replacement = "<span style=\"float:left;\">{$innertext}</span>";
					break;
				case 'right':
					$replacement = "<span style=\"float:right;\">{$innertext}</span>";
					break;
				case 'quote':
					$replacement = "<blockquote>$innertext</blockquote>" . $param ? "<cite>$param</cite>" : '';
					break;
				case 'url':
					$replacement = '<a href="' . ($param ? $param : $innertext) . "\">{$innertext}</a>";
					break;
				case 'img':
					list ($width, $height) = preg_split('`[Xx]`', $param);
					$replacement = "<img src=\"{$innertext}\" " . (is_numeric($width) ? "width=\"{$width}\" " : '') . (is_numeric($height) ? "height=\"{$height}\" " : '') . '/>';
					break;
				case 'video':
					$videourl = parse_url($innertext);
					if ($print) {
						Debug::print("{$f} inner text is \"{$innertext}\". About to print parsed query string:");
						Debug::printArray($videourl);
					}
					// parse_str($videourl['query'], $videoquery);
					if (strpos($videourl['host'], 'youtube.com') !== FALSE) {
						// $replacement = '<embed src="http://www.youtube.com/v/' . $videoquery['v'] . '" type="application/x-shockwave-flash" width="425" height="344"></embed>';
						$replacement = "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed{$videourl['path']}\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" allowfullscreen></iframe>";
					} /*
					   * elseif(strpos($videourl['host'], 'google.com') !== FALSE){
					   * $replacement = '<embed src="http://video.google.com/googleplayer.swf?docid=' . $videoquery['docid'] . '" width="400" height="326" type="application/x-shockwave-flash"></embed>';
					   * }
					   */
					else {
						Debug::warning("{$f} invalid domain");
						return "<div>Invalid domain</div>";
					}
					break;
				default:
					$replacement = bbcode_parse($bbcode_container, $to_parse);
					break;
					Debug::error("{$f} invalid tag \"{$tag}\"");
			}
			if ($print) {
				Debug::print("{$f} replacement text is \"{$replacement}\"");
			}
			$to_parse = str_replace($match, $replacement, $to_parse);
		}
	}
	return $to_parse;
}

/**
 * returns true if IP address $ip matches CIDR IP address range $range, false if it doesn't,
 * and null if you passed in garbage
 */
function cidr_match(string $ip, string $range): ?bool
{
	$f = __FUNCTION__;
	try {
		$print = false;
		if ($ip === null) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} elseif (! is_string($ip)) {
			Debug::error("{$f} IP address is not a string");
			return null;
		}
		if ($print) {
			Debug::print("{$f} entered with IP address \"{$ip}\", CIDR range \"{$range}\"");
		}
		$version = ip_version($ip);
		switch ($version) {
			case 4:
				if ($print) {
					Debug::print("{$f} IP address version 4");
				}
				if (! preg_match(REGEX_IPv4_ADDRESS_OR_CIDR, $range)) {
					if ($print) {
						Debug::print("{$f} range is not an IPv4 address/range");
					}
					return false;
				}
				$splat = explode('/', $range);
				$subnet = ip2long($splat[0]);
				$mask_length = count($splat) === 1 ? 32 : intval($splat[1]);
				$mask = - 1 << (32 - $mask_length);
				return (ip2long($ip) & $mask) === $subnet & $mask;
			case 6:
				if ($print) {
					Debug::print("{$f} IP address version 6");
				}
				if (! preg_match(REGEX_IPv6_ADDRESS_OR_CIDR, $range)) {
					if ($print) {
						Debug::print("{$f} range is not an IPv6 address/range");
					}
					return false;
				}
				$ip_2 = base2_encode_padded(inet_pton($ip));
				$splat = explode('/', $range);
				$mask_length = count($splat) === 1 ? 128 : intval($splat[1]);
				$subnet_2 = base2_encode_padded(inet_pton($splat[0]));
				return substr($ip_2, 0, $mask_length) === substr($subnet_2, 0, $mask_length);
			default:
				Debug::error("{$f} invalid IP version \"{$version}\"");
				return null;
		}
	} catch (Exception $x) {
		x($f, $x);
	}
}

/**
 * compare two floating point values for approximate equivalence
 *
 * @param float $a
 * @param float $b
 * @return boolean
 */
function close_enough(float $a, float $b)
{
	return abs($a - $b) < PHP_FLOAT_EPSILON;
}

/**
 * Send a GET request using cURL
 *
 * @param string $url
 *        	to request
 * @param array $get
 *        	values to send
 * @param array $options
 *        	for cURL
 * @return string|bool
 */
function curl_get(string $url, array $get = null, array $options = array())
{
	$f = __METHOD__; //"curl_get()";
	try {
		$q = strpos($url, '?') === false ? '?' : '';
		$defaults = [
			CURLOPT_URL => $url . $q . http_build_query($get),
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 4
		];
		$ch = curl_init();
		curl_setopt_array($ch, ($options + $defaults));
		$result = curl_exec($ch);
		if (! $result) {
			trigger_error(curl_error($ch));
			return $result;
		}
		curl_close($ch);
		return $result;
	} catch (Exception $x) {
		x($f, $x);
	}
}

/**
 * Send a POST request using cURL
 *
 * @param string $url
 *        	to request
 * @param array $post
 *        	values to send
 * @param array $options
 *        	for cURL
 * @return string|bool
 */
function curl_post(string $url, array $post = null, array $options = array())
{
	$f = __METHOD__; //"curl_post()";
	try {
		$defaults = [
			CURLOPT_POST => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_URL => $url,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_TIMEOUT => 4,
			CURLOPT_POSTFIELDS => http_build_query($post)
		];

		$ch = curl_init();
		curl_setopt_array($ch, ($options + $defaults));
		if (! $result = curl_exec($ch)) {
			trigger_error(curl_error($ch));
			return $result;
		}
		curl_close($ch);
		return $result;
	} catch (Exception $x) {
		x($f, $x);
	}
}

/**
 * escape a string for a particular quote style
 *
 * @param string $string
 * @param string $quote_style
 *        	: `, ' or "
 * @return string|mixed
 */
function escape_quotes(string $string, string $quote_style)
{
	$f = __FUNCTION__;
	$print = false;
	if (is_int($string) || is_float($string) || is_double($string)) {
		return $string;
	} elseif (! isset($quote_style)) {
		Debug::error("{$f} received null quote style");
	} elseif (is_object($string) && $string instanceof StringifiableInterface) {
		$string = $string->__toString();
	} elseif (! is_string($string)) {
		$gottype = gettype($string);
		Debug::error("{$f} received a {$gottype}");
	}
	$replaced = str_replace($quote_style, "\\{$quote_style}", $string);
	if ($print && $string !== $replaced) {
		Debug::print("{$f} transformed string \"{$string}\" into \"{$replaced}\"");
	}
	return $replaced;
}

/**
 * escape a string for single quotes and return the quoted string
 *
 * @param string $string
 * @return string
 */
function single_quote(string $string): string
{
	return "'" . escape_quotes($string, QUOTE_STYLE_SINGLE) . "'";
}

function double_quote(string $string): string
{
	return "\"" . escape_quotes($string, QUOTE_STYLE_DOUBLE) . "\"";
}

function back_quote(string $string): string
{
	return "`" . escape_quotes($string, QUOTE_STYLE_BACKTICK) . "`";
}

function get12MonthsAgoTimestamp(?int $from = null)
{
	$f = __FUNCTION__;
	$print = false;
	if ($from === null) {
		$from = time();
	}
	$now = new DateTime();
	$now->setTimestamp($from);
	$then = new DateTime();
	$month = intval($now->format('n'));
	$day = intval($now->format('d'));
	if ($month == 2 && $day > 28) {
		$month = 3;
		$day -= 28;
	}
	if ($print) {
		Debug::print("{$f} month is \"{$month}\"; day is \"{$day}\"");
	}
	$then->setDate(intval($now->format('Y')) - 1, $month, $day);
	$then->setTime(intval($now->format('g')), $now->format('n'), intval($now->format('s')));
	if ($print) {
		$now_string = $now->format("D, Y M d H:i:s");
		Debug::print("{$f} 12 months before {$now_string} is " . $then->format("D, Y M d H:i:s"));
	}
	return $then->getTimestamp();
}

function get_class_filename(string $class_name): ?string
{
	$f = __METHOD__; //"get_class_filename()";
	if (! class_exists($class_name)) {
		Debug::error("{$f} class \"{$class_name}\" does not exist");
	}
	$reflector = new \ReflectionClass($class_name);
	$fn = $reflector->getFileName();
	if (! $fn) {
		Debug::error("{$f} no filename for class \"{$class_name}\"");
		return null;
	}
	return $fn;
}

/**
 * walks back through a debug backtrace until it gets to a function that is not part of $func_names
 *
 * @param array $func_names
 *        	: names of functions to be skipped over
 * @return string
 */
function get_file_line(?array $func_names = null, ?int $count = null): string
{
	$f = __FUNCTION__;
	try {
		Debug::checkMemoryUsage($f);
		$print = false;
		if ($func_names === null) {
			$func_names = [];
		}
		array_push($func_names, "get_file_line");
		if ($count === null) {
			$count = count($func_names);
		}
		$caller = backtrace_omit($count, $func_names);
		if (! array_keys_exist($caller, 'file', 'line')) {
			Debug::printArray($caller);
			Debug::error("{$f} nuts");
		}
		$ret = "{$caller['file']}:{$caller['line']}";
		if ($print) {
			Debug::print("{$f} returning \"{$ret}\"");
		}
		return $ret;
	} catch (Exception $x) {
		x($f, $x);
	}
}

function backtrace_omit(int $limit = 4, ?array $func_names = null, bool $return_next = false): array
{
	$f = __METHOD__; //"backtrace_omit()";
	$print = false;
	if ($func_names === null) {
		$func_names = [];
	}
	array_push($func_names, "backtrace_omit");
	$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit + 1);
	$caller = $bt[0];
	for ($i = 0; $i < count($bt); $i ++) {
		if (array_key_exists($i + 1, $bt)) {
			$next = $bt[$i + 1];
			if ($print) {
				Debug::print("{$f} called in function \"{$next['function']}\"");
			}
			$splat = explode('\\', $next['function']);
			$function_short = $splat[count($splat)-1];
			if (! in_array($function_short, $func_names, true)) {
				if ($print) {
					Debug::print("{$f} function \"{$next['function']}\" is not in the following array, breaking");
					Debug::printArray($func_names);
				}
				if ($return_next) {
					return $next;
				}
				break;
			}
			$caller = $next;
		} elseif ($print) {
			$j = $i + 1;
			Debug::print("{$f} backtrace does not have an index {$j}");
			Debug::printArray(array_keys($bt));
		}
	}
	$bt = null;
	if ($print) {
		Debug::print("{$f} returning the following:");
		Debug::printArray($caller);
	}
	return $caller;
}

function getDateTimeStringFromTimestamp(int $ts, $zone = null, ?string $format = null)
{
	if ($format === null) {
		$format = "Y-m-d H:i:s";
	}
	return timestamp_to_str($ts, $zone, $format);
}

function getDateStringFromTimestamp(int $ts, $zone = null, ?string $format = null)
{
	if ($format === null) {
		$format = "Y-m-d";
	}
	return timestamp_to_str($ts, $zone, $format);
}

function getExecutionTime(?bool $get_as_float = null)
{
	return microtime($get_as_float) - $_SERVER['REQUEST_TIME_FLOAT'];
}

function get_short_class($object_or_string): string
{
	$f = __METHOD__; //"get_short_class()";
	if (is_object($object_or_string)) {
		$reflect = new ReflectionClass($object_or_string);
		return $reflect->getShortName();
	} elseif (is_string($object_or_string)) {
		if (str_contains($object_or_string, '\\')) {
			$splat = explode('\\', $object_or_string);
			$object_or_string = $splat[count($splat) - 1];
		}
		return $object_or_string;
	}
	Debug::error("{$f} received something that is not an object or string");
}

function getTimeStringFromTimestamp(int $ts, $zone = null, ?string $format = null)
{
	if ($format === null) {
		$format = "H:i:s";
	}
	return timestamp_to_str($ts, $zone, $format);
}

/**
 * get the type specifier string used by mysqli->bind_param for the received input value
 *
 * @param string|int|double|array $val
 * @return string
 */
function getTypeSpecifier($val)
{
	$f = __FUNCTION__;
	if (is_array($val)) {
		$string = "";
		foreach ($val as $v) {
			$string .= getTypeSpecifier($v);
		}
		return $string;
	} elseif (is_object($val)) {
		$class = $val->getClass();
		Debug::error("{$f} value is an object of class \"{$class}\"");
	} elseif (is_int($val)) {
		return "i";
	} elseif (is_double($val)) {
		return "d";
	} elseif (is_string($val)) {
		return "s";
	}
	$type = gettype($val);
	Debug::error("{$f} none of the above -- value type is \"{$type}\"");
}

function getYear($timezone = null, int $offset = 0)
{
	if ($timezone === null) {
		$timezone = date_default_timezone_get();
	}
	$datetime = new DateTime();
	$datetime->setTimezone($timezone);
	$year = $datetime->format('Y');
	if ($offset != 0) {
		$year += $offset;
	}
	return $year;
}

function getYearStartTimestamp(?int $year = null, $timezone = null)
{
	if ($timezone === null) {
		$timezone = date_default_timezone_get();
	} elseif (is_string($timezone)) {
		$timezone = new DateTimeZone($timezone);
	}
	if ($year === null) {
		$year = getYear($timezone);
	}
	$datetime = new DateTime();
	$datetime->setTimezone($timezone);
	$datetime->setDate($year, 1, 1);
	$datetime->setTime(0, 0, 0);
	return $datetime->getTimestamp();
}

function getYearEndTimestamp(?int $year = null, $timezone = null)
{
	if ($timezone === null) {
		$timezone = date_default_timezone_get();
	} elseif (is_string($timezone)) {
		$timezone = new DateTimeZone($timezone);
	}
	if ($year === null) {
		$year = getYear($timezone);
	}
	$dec31 = new DateTime();
	$dec31->setTimezone($timezone);
	$dec31->setDate($year, 12, 31);
	$dec31->setTime(23, 59, 59);
	return $dec31->getTimestamp();
}

function hasMinimumMySQLVersion(string $vs)
{
	$f = __FUNCTION__;
	try {
		Debug::print("{$f} testing minimum MySQL version {$vs}");
		$info = mysql_get_server_info();
		Debug::print($info);
		ErrorMessage::unimplemented($f); // XXX finish this dumbass
		return false;
	} catch (Exception $x) {
		x($f, $x);
	}
}

/**
 * negate a hexadecmial string
 *
 * @param string $hex
 * @return string
 */
function hex_negate(string $hex): string
{
	$f = __FUNCTION__;
	if (! ctype_xdigit($hex)) {
		Debug::error("{$f} invalid hex value \"{$hex}\"");
	}
	$swap = [
		"0" => "f",
		"1" => "e",
		"2" => "d",
		"3" => "c",
		"4" => "b",
		"5" => "a",
		"6" => "9",
		"7" => "8",
		"8" => "7",
		"9" => "6",
		"a" => "5",
		"b" => "4",
		"c" => "3",
		"d" => "2",
		"e" => "1",
		"f" => "0"
	];
	$negated = "";
	for ($i = 0; $i < strlen($hex); $i ++) {
		$negated .= $swap[$hex[$i]];
	}
	return $negated;
}

function implode_back_quotes($glue, $pieces): string
{
	$imploded = "";
	foreach ($pieces as $s) {
		if (! empty($imploded)) {
			$imploded .= $glue;
		}
		$imploded .= back_quote($s);
	}
	return $imploded;
}

function ip_mask(?string $ip_address): int
{
	$f = __FUNCTION__;
	if ($ip_address === null) {
		$ip_address = $_SERVER['REMOTE_ADDR'];
	}
	$version = ip_version($ip_address);
	switch ($version) {
		case 4:
			return 32;
		case 6:
			return 128;
		default:
			Debug::error("{$f} invalid IP version \"{$version}\"");
			return - 1;
	}
}

/**
 * get the IP version of an IP version 4 or 6 address
 *
 * @param string $ip
 * @return int : -1 if an error occurs
 */
function ip_version(string $ip): int
{
	$f = __FUNCTION__;
	// Debug::print(REGEX_IPv6_ADDRESS_OR_CIDR);
	if (! is_string($ip)) {
		Debug::warning("{$f} received something that is not a string");
		return - 1;
	} elseif (strlen($ip) === 0) {
		Debug::error("{$f} empty string");
	} elseif (preg_match(REGEX_IPv4_ADDRESS_OR_CIDR, $ip)) {
		return 4;
	} elseif (preg_match(REGEX_IPv6_ADDRESS_OR_CIDR, $ip)) {
		return 6;
	}
	Debug::error("{$f} invalid IP address \"{$ip}\"");
	return - 1;
}

/**
 * returns null if the input parameter is not a class,
 * true if the class is abstract,
 * false otherwise
 *
 * @param string $name
 * @return bool
 */
function is_abstract(string $name): ?bool
{
	$f = __FUNCTION__;
	if (! class_exists($name)) {
		Debug::warning("{$f} class \"{$name}\" does not exist");
		return null;
	}
	$class = new ReflectionClass($name);
	return $class->isAbstract();
}

/**
 * returns true if an array is associative
 *
 * @param array $arr
 * @return bool
 */
function is_associative(array $arr): bool
{
	return (array() === $arr) ? false : (array_keys($arr) !== range(0, count($arr) - 1));
}

function is_json(string $string): bool
{
	json_decode($string);
	return json_last_error() === JSON_ERROR_NONE;
}

/**
 * returns true if $str is a hexadecimal string, false otherwise
 *
 * @param string $str
 * @return bool
 */
function is_sha1(string $str): bool
{
	return boolval(preg_match('/^[0-9a-f]{40}$/i', $str));
}

/**
 * returns true if $data is a base64 encoded string, false otherwise
 *
 * @param mixed $data
 * @return bool
 */
function is_base64($data): bool
{
	return base64_encode(base64_decode($data, true)) === $data;
}

function luhn($imei)
{
	$f = __METHOD__; //"luhn({$imei})";
	$print = false;
	$sum = 0;
	$check = $imei % 10;
	$imei = intdiv($imei, 10);
	$double = true;
	while ($imei > 9) {
		$digit = $imei % 10;
		if ($print) {
			Debug::print("{$f} current digit is {$digit}");
		}
		if ($double) {
			$digit *= 2;
			if ($print) {
				Debug::print("{$f} digit is now {$digit}");
			}
			if ($digit > 9)
				$digit -= 9;
		}
		$double = $double ? false : true;
		$sum += $digit;
		$imei = intdiv($imei, 10);
		if ($print) {
			Debug::print("{$f} sum is now {$sum}; imei is now {$imei}");
		}
	}
	$sum += $imei;
	if (($sum * 9) % 10 === $check && ($sum + $check) % 10 === 0) {
		if ($print) {
			Debug::print("{$f} check digit {$check} OK");
		}
		return true;
	} elseif ($print) {
		Debug::warning("{$f} FAILED (sum {$sum}, check digit {$check})");
	}
	return false;
}

/**
 * returns the contents of a php file as a processed string
 *
 * @param string $filename
 * @return string|NULL
 */
function php2string(string $filename): ?string
{
	$f = __FUNCTION__;
	if (empty($filename)) {
		Debug::error("{$f} filename is null or empty string");
	} elseif (! file_exists($filename)) {
		Debug::error("{$f} file \"{$filename}\" does not exist");
	} elseif (! is_file($filename)) {
		Debug::error("{$f} filename is for something that isn't a file");
	} elseif (is_dir($filename)) {
		Debug::error("{$f} file is a directory");
	} elseif (! is_readable($filename)) {
		Debug::error("{$f} file \"{$filename}\" is not readable");
	}
	ob_start();
	include $filename;
	$string = ob_get_clean();
	if (empty($string)) {
		Debug::printStackTraceNoExit("{$f} null or empty string");
	}
	return $string;
}

/**
 * same as the above except $obj will be in scope as $that
 *
 * @param string $filename
 * @param object $obj
 * @return string|NULL
 */
function php2string4object(string $filename, object $obj): ?string
{
	$f = __FUNCTION__;
	try {
		ob_start();
		if (empty($filename)) {
			Debug::error("{$f} filename is null or empty string");
		} elseif (! file_exists($filename)) {
			Debug::error("{$f} file does not exist");
		} elseif (! is_file($filename)) {
			Debug::error("{$f} filename is for something that isn't a file");
		} elseif (is_dir($filename)) {
			Debug::error("{$f} file is a directory");
		} elseif (! is_readable($filename)) {
			Debug::error("{$f} file is not readable");
		}
		$realpath = realpath($filename);
		if ($realpath === false) {
			Debug::error("{$f} realpath returned false, which means the file does not exist");
		} elseif (empty($realpath)) {
			Debug::error("{$f} realpath is an empty string");
		}
		$that = $obj;
		include $filename;
		$string = ob_get_clean();
		if (empty($string)) {
			Debug::warning("{$f} null or empty string");
		}
		return $string;
	} catch (Exception $x) {
		x($f, $x);
	}
}

/**
 * reformats a PHP regular expression for javascript
 *
 * @param string $regex
 * @return string
 */
function regex_js(string $regex): string
{
	$f = __FUNCTION__;
	$print = false;
	if (starts_with($regex, '/')) {
		$i = 1;
		while ($regex[strlen($regex) - $i] !== '/') {
			$i ++;
		}
		if ($print) {
			Debug::print("{$f} {$i} flags to truncate");
		}
		$regex = substr($regex, 1, strlen($regex) - ($i + 1));
		if ($print) {
			Debug::print("{$f} stripped regex is \"{$regex}\"");
		}
	} elseif ($print) {
		Debug::print("{$f} regular expression does not start with /");
	}
	$regex = str_replace('\/', '/', $regex);
	if ($print) {
		Debug::print("{$f} returning \"{$regex}\"");
	}
	return $regex;
}

/**
 * terminate execution if a class does not exist
 *
 * @param string $class_name
 */
function require_class(string $class_name)
{
	$f = __METHOD__; //"require_Class()";
	if (! class_exists($class_name)) {
		error_log("\033[31mError: {$f} class \"{$class_name}\" does not exist\033[0m");
		$trace = (new Exception())->getTraceAsString();
		$exploded = explode("\n", $trace);
		foreach ($exploded as $line) {
			error_log($line);
		}
		exit();
	}
}

/**
 * get an RGB color value that contrasts nicely with $color
 *
 * @param string $color
 * @return string
 */
function rgb_contrast(string $color): string
{
	$f = __FUNCTION__;
	if (! ctype_xdigit($color) || strlen($color) !== 6) {
		Debug::error("{$f} invalid RGB value \"{$color}\"");
	}
	$contrast = "";
	for ($i = 0; $i < 6; $i += 2) {
		if (hexdec(substr($color, $i, 2)) < 128) {
			$contrast .= "ff";
		} else {
			$contrast .= "00";
		}
	}
	return $contrast;
}

/**
 * set cookie at index $name to value $value and update the $_COOKIE superglobal array
 *
 * @param string $name
 * @param string $value
 * @return string
 */
function set_secure_cookie(string $name, $value, ?int $expires = null)
{
	$f = __FUNCTION__;
	try {
		$print = false;
		if ($expires === null) {
			$expires = null; // time()+60*60*24*30;
		}
		$path = '/';
		$domain = "." . WEBSITE_DOMAIN; // important: leading . allows for subdomains
		$secure = true; // set to true once the backup server has HTTPS
		$httponly = true;
		$samesite = 'Strict';
		if ($print) {
			$options = [
				'expires' => $expires,
				'path' => $path,
				'domain' => $domain,
				'secure' => $secure,
				'httponly' => $httponly,
				'samesite' => $samesite
			];
			Debug::print("{$f} setting cookie at index \"{$name}\" to \"{$value}\"");
			Debug::printArray($options);
		}
		$set = setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
		if (! $set) {
			Debug::error("{$f} setcookie failed");
		} elseif ($print) {
			Debug::print("{$f} setcookie succeeded");
		}
		return $_COOKIE[$name] = $value;
	} catch (Exception $x) {
		x($f, $x);
	}
}

function unset_cookie(string $name): bool
{
	$f = __METHOD__; //"unset_cookie()";
	$print = false;
	if (isset($_COOKIE[$name])) {
		if ($print) {
			Debug::print("{$f} unsetting cookie \"{$name}\"");
		}
		setcookie($name, '', 1, '/', WEBSITE_DOMAIN);
		unset($_COOKIE[$name]);
		return true;
	} elseif ($print) {
		Debug::print("{$f} cookie \"{$name}\" wasn't set in the first place");
	}
	return false;
}

/**
 * shift a string to the right by $shift bits with rollover
 *
 * @param string $string
 * @param int $shift
 * @return string
 */
function shift_right_string(string $string, int $shift){
	$f = __FUNCTION__;
	try {
		if (! is_int($shift)) {
			Debug::error("{$f} second parameter should be the number of bits you want to shift right");
		} elseif ($shift < 0) {
			Debug::error("{$f} second parameter must be a positive number");
		} elseif ($shift == 0) {
			Debug::warning("{$f} dumbass");
			return $string;
		}
		$full_bytes = 0;
		if ($shift > 7) {
			$full_bytes = floor($shift / 8);
			$shift %= 8; // -= ($shift * $full_bytes);
		}
		if ($full_bytes > 0) {
			$string = substr($string, 0, strlen($string) - $full_bytes);
		}
		if ($shift == 0) {
			return $string;
		}
		$mask = 0xff >> (8 - $shift);
		$prev_byte = null;
		$output = "";
		for ($i = strlen($string) - 1; $i >= 0; $i --) {
			$current_byte = ord($string[$i]);
			if ($prev_byte !== null) {
				$rollover = ($current_byte & $mask) << (8 - $shift);
				$prev_byte = $prev_byte | $rollover;
				$output = chr($prev_byte) . $output;
			}
			$prev_byte = $current_byte >> $shift;
		}
		$output = chr($prev_byte) . $output;
		return $output;
	} catch (Exception $x) {
		x($f, $x);
	}
}

function starts_with(string $haystack, string $needle): bool{
	if ($haystack instanceof ValueReturningCommandInterface) {
		while ($haystack instanceof ValueReturningCommandInterface) {
			$haystack = $haystack->evaluate();
		}
	} elseif ($haystack instanceof StringifiableInterface) {
		$haystack = $haystack->__toString();
	}
	$length = strlen($needle);
	return substr($haystack, 0, $length) === $needle;
}

function ends_with(string $haystack, string $needle): bool{
	if ($haystack instanceof ValueReturningCommandInterface) {
		while ($haystack instanceof ValueReturningCommandInterface) {
			$haystack = $haystack->evaluate();
		}
	} elseif ($haystack instanceof StringifiableInterface) {
		$haystack = $haystack->__toString();
	}
	return substr($haystack, - strlen($needle)) === $needle;
}

function starts_ends_with(string $haystack, string $needle): bool{
	if ($haystack instanceof ValueReturningCommandInterface) {
		while ($haystack instanceof ValueReturningCommandInterface) {
			$haystack = $haystack->evaluate();
		}
	} elseif ($haystack instanceof StringifiableInterface) {
		$haystack = $haystack->__toString();
	}
	return starts_with($haystack, $needle) && ends_with($haystack, $needle);
}

function strip_nonalphanumeric(string $str){
	$f = __FUNCTION__;
	if (is_array($str)) {
		Debug::error("{$f} received array, expecting string");
	}
	$pruned = preg_replace('/[^0-9A-Za-z\+]/', '', $str);
	return $pruned;
}

function substitute(string $subject, ...$substitutions):string{
	$f = __FUNCTION__;
	$print = false;
	$i = 1;
	foreach ($substitutions as $sub) {
		if ($sub instanceof ValueReturningCommandInterface) {
			if ($print) {
				Debug::print("{$f} substitution {$i} is a value returning media command");
			}
			while ($sub instanceof ValueReturningCommandInterface) {
				$sub = $sub->evaluate();
			}
		}
		if ($print) {
			Debug::print("{$f} Iterator \"{$i}\". Before substitution: \"{$subject}\". Substitution \"{$sub}\"");
		}
		$subject = str_replace("%{$i}%", $sub, $subject);
		if ($print) {
			Debug::print("{$f} after substitution: \"{$subject}\"");
		}
		$i++;
	}
	return $subject;
}

/**
 * Effectively drop the request without sending a response
 */
function drop_request(): void{
	foreach ($GLOBALS as $key => $v) {
		unset($GLOBALS[$key]);
	}
	unset($GLOBALS);
	sleep(60 * 60 * 24 * 365);
	header("HTTP/1.0 404 Not Found");
	exit();
}

// XXX TODO reorder parameters so format comes first
function timestamp_to_str(int $ts, $zone, string $format): string{
	$f = __FUNCTION__;
	if ($zone === null) {
		$zone = date_default_timezone_get();
	} elseif ($zone instanceof ValueReturningCommandInterface) {
		Debug::error("{$f} please evaluate commands before sending them as parameters to this function");
	} //
	if (is_string($zone)) {
		$zone = new DateTimeZone($zone);
	}
	$date = new DateTime(null, $zone);
	$date->setTimestamp($ts);
	return $date->format($format);
}

function timezone_offset(DateTimeZone $timezone1, DateTimeZone $timezone2): int
{
	$local = new DateTime('now', $timezone1);
	$user = new DateTime('now', $timezone2);
	$local_offset = $local->getOffset() / 3600;
	$user_offset = $user->getOffset() / 3600;
	return $user_offset - $local_offset;
}

function validateTableName(string $table): bool
{
	$f = __FUNCTION__;
	if (! is_string($table)) {
		Debug::warning("{$f} table name is not a string");
		return false;
	} elseif (empty($table)) {
		Debug::warning("{$f} table name is empty");
	} elseif (preg_match('/[a-z]+[a-z_0-9]*\.\*/', $table)) {
		Debug::error("{$f} table is something like database.*");
		return true;
	}
	return true;
}
