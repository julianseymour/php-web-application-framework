<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\access;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use Exception;

abstract class UserFingerprint extends UserOwned
{

	/*
	 * public static function fingerprint($server){
	 * $tmp = new IncidentReport();
	 * if(isset($server['REMOTE_ADDR'])){
	 * $tmp->remoteAddr = $server['REMOTE_ADDR'];
	 * }
	 * if(isset($server['HTTP_X_FORWARDED_FOR'])){
	 * $tmp->httpXForwardedFor = $server['HTTP_X_FORWARDED_FOR'];
	 * }
	 * if(isset($server['HTTP_ACCEPT'])){
	 * $tmp->httpAccept = $server['HTTP_ACCEPT'];
	 * }
	 * if(isset($server['HTTP_ACCEPT_ENCODING'])){
	 * $tmp->httpAcceptEncoding = $server['HTTP_ACCEPT_ENCODING'];
	 * }
	 * if(isset($server['SERVER_PROTOCOL'])){
	 * $tmp->serverProtocol = $server['SERVER_PROTOCOL'];
	 * }
	 * if(isset($server['REQUEST_METHOD'])){
	 * $tmp->requestMethod = $server['REQUEST_METHOD'];
	 * }
	 * if(isset($server['REQUEST_TIME_FLOAT'])){
	 * $tmp->requestTimeFloat = $server['REQUEST_TIME_FLOAT'];
	 * }
	 * else if(isset($server['REQUEST_TIME']))
	 * {
	 * $tmp->requestTime = $server['REQUEST_TIME'];
	 * }
	 * if(isset($server['QUERY_STRING'])){
	 * $tmp->queryString = $server['QUERY_STRING'];
	 * }
	 * if(isset($server['HTTP_ACCEPT_LANGUAGE'])){
	 * $tmp->httpAcceptLanguage = $server['HTTP_ACCEPT_LANGUAGE'];
	 * }
	 * if(isset($server['HTTP_CONNECTION'])){
	 * $tmp->httpConnection = $server['HTTP_CONNECTION'];
	 * }
	 * if(isset($server['HTTP_HOST'])){
	 * $tmp->httpHost = $server['HTTP_HOST'];
	 * }
	 * if(isset($server['HTTP_USER_AGENT'])){
	 * $tmp->httpUserAgent = $server['HTTP_USER_AGENT'];
	 * //$browser = get_browser($tmp->httpUserAgent);
	 * //print_r($browser);
	 * }
	 * if(isset($server['HTTPS'])){
	 * $tmp->https = $server['HTTPS'];
	 * }
	 * if(isset($server['REMOTE_HOST'])){
	 * $tmp->remoteHost = $server['REMOTE_HOST'];
	 * }
	 * if(isset($server['REMOTE_PORT'])){
	 * $tmp->remotePort = $server['REMOTE_PORT'];
	 * }
	 * if(isset($server['REMOTE_USER'])){
	 * $tmp->remoteUser = $server['REMOTE_USER'];
	 * }
	 * else if(isset($server['REDIRECT_REMOTE_USER'])){
	 * $tmp->redirectRemoteUser = $server['REDIRECT_REMOTE_USER'];
	 * }
	 * if(isset($server['REQUEST_URI'])){
	 * $tmp->requestUri = $server['REQUEST_URI'];
	 * }
	 * print_r($tmp);
	 * }
	 */
	public function hasUserAgent()
	{
		return $this->hasColumnValue("userAgent");
	}

	public function getUserAgent()
	{
		return $this->getColumnValue('userAgent');
	}

	public function setUserAgent($ua)
	{
		return $this->setColumnValue('userAgent', $ua);
	}

	public function hasReasonLogged()
	{
		return $this->hasColumnValue("reasonLogged");
	}

	protected function afterGenerateInitialValuesHook(): int
	{
		$f = __METHOD__; //UserFingerprint::getShortClass()."(".static::getShortClass().")->afterGenerateInitialValuesHook()";
		if($this->hasColumn("userAgent")){
			if(!isset($_SERVER['HTTP_USER_AGENT'])){
				Debug::warning("{$f} HTTP user agent is undefined");
			}else{
				$this->setUserAgent($_SERVER['HTTP_USER_AGENT']);
			}
		}
		if($this->hasColumn("reasonLogged") && ! $this->hasReasonLogged()){
			$this->setReasonLogged(BECAUSE_NOREASON);
		}
		return parent::afterGenerateInitialValuesHook();
	}

	public function getVirtualColumnValue(string $column_name)
	{
		// $f = __METHOD__;
		switch($column_name){
			case "dismissable":
				return $this->isDismissable();
			case "reasonLoggedString":
				return $this->getReasonLoggedString();
			default:
				// Debug::warning("{$f} undefined virtual column index \"{$column_name}\"; returning parent function");
				return parent::getVirtualColumnValue($column_name);
		}
	}

	public function hasVirtualColumnValue(string $column_name): bool
	{
		switch($column_name){
			case "dismissable":
				return $this->isDismissable();
			case "reasonLoggedString":
				return $this->hasReasonLogged();
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}

	public function getArrayMembershipConfiguration($config_id): ?array
	{
		$f = __METHOD__;
		$config = parent::getArrayMembershipConfiguration($config_id);
		foreach(array_keys($config) as $column_name){
			if(!$this->hasColumn($column_name)){
				Debug::error("{$f} datum \"{$column_name}\" does not exist");
			}
		}
		// Debug::print("{$f} parent function returned the following array:");
		// Debug::printArray($config);
		switch($config_id){
			case "default":
				if($this->hasColumn("reasonLoggedString")){
					$config['reasonLoggedString'] = true;
				}
			default:
				return $config;
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		// $f = __METHOD__;
		parent::declareColumns($columns, $ds);
		// $ip = new IpAddressDatum("ipAddress", "ip");
		$user_agent = new TextDatum("userAgent"); // we could use the name field for this, but we'll need that for filtering by login username as well
		$user_agent->setNullable(true);
		$closure = function (TextDatum $d){
			if(isset($_SERVER['HTTP_USER_AGENT'])){
				$d->setValue($_SERVER['HTTP_USER_AGENT']);
			}else{
				$d->setValue("");
			}
		};
		$user_agent->setGenerationClosure($closure);

		$reason = new StringEnumeratedDatum("reasonLogged", 8);
		// $reason->setDefaultValue(BECAUSE_NOREASON);
		$reason->setValidEnumerationMap([
			BECAUSE_NOREASON,
			BECAUSE_USER,
			BECAUSE_REGISTER,
			BECAUSE_LOGIN,
			BECAUSE_FORGOTNAME,
			BECAUSE_FORGOTPASS,
			BECAUSE_RESET,
			BECAUSE_WAIVER,
			BECAUSE_WHITELIST,
			BECAUSE_BLACKLIST,
			BECAUSE_REAUTH,
			BECAUSE_TIMEOUT,
			BECAUSE_REFILL,
			BECAUSE_CHECKOUT,
			BECAUSE_REFUND
		]);
		$reasonLoggedString = new VirtualDatum("reasonLoggedString");
		array_push($columns, $user_agent, $reason, $reasonLoggedString);
	}

	public function getReasonLogged(){
		return $this->getColumnValue('reasonLogged');
	}

	public function getReasonLoggedString(){
		return static::getReasonLoggedStringStatic($this->getReasonLogged());
	}

	public static function getReasonLoggedStringStatic($reason){
		$f = __METHOD__;
		try{
			switch($reason){
				case BECAUSE_USER:
					return _("User submitted");
				case BECAUSE_REGISTER:
					return _("Registration");
				case BECAUSE_LOGIN:
					return _("Login");
				case BECAUSE_FORGOTNAME:
					return _("Forgot username");
				case BECAUSE_FORGOTPASS:
					return _("Forgot password");
				case BECAUSE_RESET:
					return _("Password reset");
				case BECAUSE_WAIVER:
					return _("Lockout waiver");
				case BECAUSE_WHITELIST:
					return _("IP address authorization");
				case BECAUSE_BLACKLIST:
					return _("IP address banishment");
				case BECAUSE_REAUTH:
					return _("Reauthentication");
				case BECAUSE_TIMEOUT:
					return _("Session timeout mid-purchase");
				case BECAUSE_CHANGE_EMAIL:
					return _("Email address change");
				case BECAUSE_NOREASON:
				default:
					return _("No reason");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setReasonLogged($value){
		return $this->setColumnValue("reasonLogged", $value);
	}

	public function getExpiredTimestamp(){
		return $this->getInsertTimestamp() - LOCKOUT_DURATION;
	}

	public function generateExpiredTimestamp(){
		if($this->hasInsertTimestamp()){
			return $this->getExpiredTimestamp();
		}
		return $this->generateInsertTimestamp() - LOCKOUT_DURATION;
	}
}
