<?php

namespace JulianSeymour\PHPWebApplicationFramework\session\hijack;

use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\auth\mfa\PreMultifactorAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\IpAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenData;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;

/**
 * Used to end a session if IP address/UA has changed.
 * Ineffective against MitM
 *
 * @author j
 *        
 */
class AntiHijackSessionData extends DataStructure{
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$boundIpAddress = new IpAddressDatum("boundIpAddress");
		$boundIpAddress->setNullable(true);
		$boundUserAgent = new TextDatum("boundUserAgent");
		$boundUserAgent->setNullable(true);
		$ipAddressChanged = new BooleanDatum("IpAddressChanged");
		$ipAddressChanged->setDefaultValue(false);
		$userAgentChanged = new BooleanDatum("userAgentChanged");
		$userAgentChanged->setDefaultValue(false);
		array_push($columns, $boundIpAddress, $boundUserAgent, $ipAddressChanged, $userAgentChanged);
	}

	public static function getDefaultPersistenceModeStatic(): int{
		return PERSISTENCE_MODE_SESSION;
	}

	public function isRegistrable(): bool{
		return false;
	}

	public static function isRegistrableStatic(): bool{
		return false;
	}

	public function setBoundUserAgent(string $ua):string{
		return $this->setColumnValue("boundUserAgent", $ua);
	}

	public function hasBoundUserAgent():bool{
		return $this->hasColumnValue("boundUserAgent");
	}

	public function getBoundUserAgent():string{
		return $this->getColumnValue("boundUserAgent");
	}

	public function setBoundIpAddress(string $value):string{
		return $this->setColumnValue("boundIpAddress", $value);
	}

	public function hasBoundIpAddress():bool{
		return $this->hasColumnValue("boundIpAddress");
	}

	public function getBoundIpAddress():string{
		return $this->getColumnValue("boundIpAddress");
	}

	public static function getPrettyClassName():string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getDataType(): string{
		return DATATYPE_UNKNOWN;
	}

	public static function getPrettyClassNames():string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getPhylumName(): string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function setUserData(UserData $user):UserData{
		if ($user->getBindIpAddress()) {
			$this->setBoundIpAddress($_SERVER['REMOTE_ADDR']);
		}
		if ($user->getBindUserAgent()) {
			$this->setBoundUserAgent($_SERVER['HTTP_USER_AGENT']);
		}
		return $user;
	}

	public function setUserAgentChanged(bool $value):bool{
		return $this->setColumnValue("userAgentChanged", $value);
	}

	public function getUserAgentChanged():bool{
		return $this->getColumnValue("userAgentChanged");
	}

	public function setIpAddressChanged(bool $value):bool{
		return $this->setColumnValue("IpAddressChanged", $value);
	}

	public function getIpAddressChanged():bool{
		return $this->getColumnValue("IpAddressChanged");
	}

	public function unsetBoundIpAddress(){
		return $this->getColumn("boundIpAddress")->unsetValue();
	}

	public function unsetBoundUserAgent(){
		return $this->getColumn("boundUserAgent")->unsetValue();
	}

	public static function protect():int{
		$f = __METHOD__;
		$print = false;
		$that = new static();
		$failed = false;
		if ($that->hasBoundIpAddress()) {
			if ($that->getBoundIpAddress() !== $_SERVER['REMOTE_ADDR']) {
				$failed = true;
				$that->setIpAddressChanged(true);
			} elseif ($print) {
				Debug::print("{$f} IP address \"{$_SERVER['REMOTE_ADDR']}\" matches perfectly");
			}
		} elseif ($print) {
			Debug::print("{$f} IP address is not bound");
		}
		if ($that->hasBoundUserAgent()) {
			if ($that->getBoundUserAgent() !== $_SERVER['HTTP_USER_AGENT']) {
				$failed = true;
				$that->setUserAgentChanged(true);
			} elseif ($print) {
				Debug::print("{$f} user agent \"{$_SERVER['HTTP_USER_AGENT']}\" matches perfectly");
			}
		} elseif ($print) {
			Debug::print("{$f} user agent is not bound");
		}
		if ($failed) {
			if ($that->hasBoundIpAddress()) {
				$that->unsetBoundIpAddress();
			}
			if ($that->hasBoundUserAgent()) {
				$that->unsetBoundUserAgent();
			}
			foreach ([
				PreMultifactorAuthenticationData::class,
				FullAuthenticationData::class,
				AntiXsrfTokenData::class
			] as $class) {
				$class::unsetColumnValuesStatic();
			}
			unset($_POST);
			unset($_GET);
			// XXX unset php input file contents
			return FAILURE;
		}
		return SUCCESS;
	}
}
