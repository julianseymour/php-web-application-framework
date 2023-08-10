<?php
namespace JulianSeymour\PHPWebApplicationFramework\session\timeout;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;

/**
 * An object-oriented version of the function found in UseCase->sendHeaders
 *
 * @author j
 */
class SessionTimestampData extends DataStructure
{

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		$lastActive = new TimestampDatum("lastActiveTimestamp");
		$regen = new TimestampDatum("regenerationTimestamp");
		static::pushTemporaryColumnsStatic($columns, $lastActive, $regen);
	}

	public function setRegnerationTimestamp($value)
	{
		return $this->setColumnValue("regenerationTimestamp", $value);
	}

	public function hasRegenerationTimestamp()
	{
		return $this->hasColumnValue("regenerationTimestamp");
	}

	public function getRegenerationTimestamp()
	{
		return $this->getColumnValue("regenerationTimestamp");
	}

	public function updateRegenerationTimestamp($ts = null)
	{
		if ($ts == null) {
			$ts = time();
		}
		return $this->setRegnerationTimestamp($ts);
	}

	public function isDueForRegeneration($ts = null)
	{
		if ($ts == null) {
			$ts = time();
		}
		return $ts - $this->getRegenerationTimestamp() >= SESSION_REGENERATION_INTERVAL;
	}

	public function regenerateId($ts = null)
	{
		$f = __METHOD__; //SessionTimestampData::getShortClass()."(".static::getShortClass().")->regnerateId()";
		if ($ts == null) {
			$ts = time();
		}
		if (! session_regenerate_id(true)) {
			Debug::error("{$f} error regenerating session ID");
		}
		$this->setRegenerationTimestamp($ts);
	}

	public function setLastActiveTimestamp($ts)
	{
		return $this->setColumnValue("lastActiveTimestamp", $ts);
	}

	public function hasLastActiveTimestamp()
	{
		return $this->hasColumnValue("lastActiveTimestamp");
	}

	public function getLastActiveTimestamp()
	{
		$f = __METHOD__; //SessionTimestampData::getShortClass()."(".static::getShortClass().")->getLastActiveTimestamp()";
		if (! $this->hasLastActiveTimestamp()) {
			Debug::error("{$f} last activity timestamp is undefined");
		}
		return $this->getColumnValue("lastActiveTimestamp");
	}

	public function isExpired($ts = null)
	{
		if ($ts == null) {
			$ts = time();
		}
		return $ts - $this->getLastActiveTimestamp() >= intval(ini_get("session.gc_maxlifetime"));
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		$f = __METHOD__; //SessionTimestampData::getShortClass()."(".static::getShortClass().")::getPrettyClassName()";
		ErrorMessage::unimplemented($f);
	}

	public static function getTableNameStatic(): string
	{
		$f = __METHOD__; //SessionTimestampData::getShortClass()."(".static::getShortClass().")::getTableNameStatic()";
		ErrorMessage::unimplemented($f);
	}

	public static function getDataType(): string
	{
		return DATATYPE_UNKNOWN;
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		$f = __METHOD__; //SessionTimestampData::getShortClass()."(".static::getShortClass().")::getPrettyClassNames()";
		ErrorMessage::unimplemented($f);
	}

	public static function getPhylumName(): string
	{
		return "ERROR";
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void
	{
		foreach ($columns as $c) {
			$c->setNullable(true);
		}
	}

	public static function getDefaultPersistenceModeStatic(): int
	{
		return PERSISTENCE_MODE_SESSION;
	}

	public function isRegistrable(): bool
	{
		return false;
	}

	public static function isRegistrableStatic(): bool
	{
		return false;
	}

	public function updateLastActiveTimestamp($ts = null)
	{
		if ($ts == null) {
			$ts = time();
		}
		return $this->setLastActiveTimestamp($ts);
	}

	public function refresh($ts = null)
	{
		$f = __METHOD__; //SessionTimestampData::getShortClass()."(".static::getShortClass().")->refresh()";
		try {
			$print = false;
			if ($ts == null) {
				$ts = time();
			}
			if ($this->hasLastActiveTimestamp() && $this->isExpired($ts)) {
				if ($print) {
					Debug::print("{$f} session has expired");
				}
				session_unset();
				session_destroy();
			} elseif ($print) {
				Debug::print("{$f} session is still fresh");
			}
			if (! $this->hasRegenerationTimestamp()) {
				if ($print) {
					Debug::print("{$f} regeneration timestamp is undefined, setting it now");
				}
				$this->updateRegenerationTimestamp($ts);
			} elseif ($this->isDueForRegeneration($ts)) {
				if ($print) {
					Debug::print("{$f} session is due for regeneration");
				}
				$this->regenerateId($ts);
			} elseif ($print) {
				Debug::print("{$f} regeneration timestamp is defined and sufficiently fresh");
			}
			return $this->updateLastActiveTimestamp($ts);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function sessionStart($ts = null)
	{
		// $f = __METHOD__; //SessionTimestampData::getShortClass()."(".static::getShortClass().")::sessionStart()";
		// $print = false;
		if ($ts == null) {
			$ts = time();
		}
		session_start();
		/*
		 * if(
		 * isset($_SESSION['lastActiveTimestamp'])
		 * && $ts - $_SESSION['lastActiveTimestamp'] >= intval(ini_get("session.gc_maxlifetime"))
		 * ){
		 * if($print){
		 * Debug::print("{$f} session has expired");
		 * }
		 * session_unset();
		 * session_destroy();
		 * }elseif($print){
		 * Debug::print("{$f} session is still fresh");
		 * }
		 * if(!isset($_SESSION['regenerationTimestamp'])){
		 * if($print){
		 * Debug::print("{$f} regeneration timestamp is undefined, setting it now");
		 * }
		 * $_SESSION['regenerationTimestamp'] = $ts;
		 * }elseif($ts - $_SESSION['regenerationTimestamp'] >= SESSION_REGENERATION_INTERVAL){
		 * if($print){
		 * Debug::print("{$f} session is due for regeneration");
		 * }
		 * if(!session_regenerate_id(true)){
		 * Debug::error("{$f} error regenerating session ID");
		 * }
		 * $_SESSION['regenerationTimestamp'] = $ts;
		 * }elseif($print){
		 * Debug::print("{$f} regeneration timestamp is defined and sufficiently fresh");
		 * }
		 * return $_SESSION['lastActiveTimestamp'] = $ts;
		 */
		$session = new SessionTimestampData();
		return $session->refresh($ts);
	}
}
