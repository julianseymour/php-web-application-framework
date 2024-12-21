<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth;

use function JulianSeymour\PHPWebApplicationFramework\f;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BlobDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

abstract class AuthenticationCookie extends DataStructure{

	protected abstract static function getReauthenticationKeyColumnName();
	
	public static function getPrettyClassName():string{
		return _("Reauthentication cookie");
	}

	public static function getDataType(): string{
		return DATATYPE_UNKNOWN;
	}

	public static function getPrettyClassNames():string{
		return _("Reauthentication cookies");
	}

	public static function getPhylumName(): string{
		ErrorMessage::unimplemented(f(static::class));
	}

	public static function getDefaultPersistenceModeStatic(): int{
		return PERSISTENCE_MODE_COOKIE;
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$reauthenticationKey = new BlobDatum(static::getReauthenticationKeyColumnName());
		array_push($columns, $reauthenticationKey);
	}

	public function hasReauthenticationKey():bool{
		return $this->hasColumnValue(static::getReauthenticationKeyColumnName());
	}

	public function getReauthenticationKey(){
		return $this->getColumnValue(static::getReauthenticationKeyColumnName());
	}

	public function setReauthenticationKey($value){
		return $this->setColumnValue(static::getReauthenticationKeyColumnName(), $value);
	}

	public function generateReauthenticationKey():string{
		$f = __METHOD__;
		$key = base64_encode(random_bytes(32));
		if(strlen($key) == 0){
			Debug::error("{$f} generated a null/empty string");
		}
		return $this->setReauthenticationKey($key);
	}
}
