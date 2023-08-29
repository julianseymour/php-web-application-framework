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
class SessionTimestampData extends DataStructure{

	public static function getDatabaseNameStatic():string{
		return "error";
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$lastActive = new TimestampDatum("lastActiveTimestamp");
		$regen = new TimestampDatum("regenerationTimestamp");
		static::pushTemporaryColumnsStatic($columns, $lastActive, $regen);
	}

	public function setRegnerationTimestamp($value){
		return $this->setColumnValue("regenerationTimestamp", $value);
	}

	public function hasRegenerationTimestamp():bool{
		return $this->hasColumnValue("regenerationTimestamp");
	}

	public function getRegenerationTimestamp(){
		return $this->getColumnValue("regenerationTimestamp");
	}

	public function updateRegenerationTimestamp($ts = null){
		if ($ts == null) {
			$ts = time();
		}
		return $this->setRegnerationTimestamp($ts);
	}

	public function isDueForRegeneration($ts = null):bool{
		if ($ts == null) {
			$ts = time();
		}
		return $ts - $this->getRegenerationTimestamp() >= SESSION_REGENERATION_INTERVAL;
	}

	public function regenerateId($ts = null){
		$f = __METHOD__;
		if ($ts == null) {
			$ts = time();
		}
		if (! session_regenerate_id(true)) {
			Debug::error("{$f} error regenerating session ID");
		}
		$this->setRegenerationTimestamp($ts);
	}

	public function setLastActiveTimestamp($ts){
		return $this->setColumnValue("lastActiveTimestamp", $ts);
	}

	public function hasLastActiveTimestamp():bool{
		return $this->hasColumnValue("lastActiveTimestamp");
	}

	public function getLastActiveTimestamp(){
		$f = __METHOD__;
		if (! $this->hasLastActiveTimestamp()) {
			Debug::error("{$f} last activity timestamp is undefined");
		}
		return $this->getColumnValue("lastActiveTimestamp");
	}

	public function isExpired($ts = null){
		if ($ts == null) {
			$ts = time();
		}
		return $ts - $this->getLastActiveTimestamp() >= intval(ini_get("session.gc_maxlifetime"));
	}

	public static function getPrettyClassName():string{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getTableNameStatic(): string{
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
		return "ERROR";
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		foreach ($columns as $c) {
			$c->setNullable(true);
		}
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

	public function updateLastActiveTimestamp($ts = null){
		if ($ts == null) {
			$ts = time();
		}
		return $this->setLastActiveTimestamp($ts);
	}

	public function refresh($ts = null){
		$f = __METHOD__;
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

	public static function sessionStart($ts = null){
		if ($ts == null) {
			$ts = time();
		}
		session_start();
		$session = new SessionTimestampData();
		return $session->refresh($ts);
	}
}
