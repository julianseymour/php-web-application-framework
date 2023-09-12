<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use Exception;

class SharedMessageEncryptionScheme extends MessageEncryptionScheme implements SharedEncryptionSchemeInterface{

	public function generateComponents(?DataStructure $ds = null): array{
		$components = parent::generateComponents();
		$datum = $this->getColumn();
		$vn = $datum->getName();
		$aes_key = $components["{$vn}AesKey"];
		$admin_copy = $aes_key->mirrorAtIndex("{$vn}AesKeyCopy");
		$admin_copy->setPersistenceMode(PERSISTENCE_MODE_ENCRYPTED);
		$admin_copy->setEncryptionScheme(CorrespondentAsymmetricEncryptionScheme::class);
		$admin_copy->setNullable(false);
		$components3 = (new CorrespondentAsymmetricEncryptionScheme($admin_copy))->generateComponents();
		$components[$admin_copy->getName()] = $admin_copy;
		return array_merge($components, $components3);
	}

	public function extractTranscryptionKey(Datum $datum):?string{
		$f = __METHOD__;
		try{
			$ds = $datum->getDataStructure();
			$user = user();
			if($ds->getUserKey() !== $user->getIdentifierValue()) {
				$vn = $datum->getName()."AesKeyCopy";
				$column = $ds->getColumn($vn);
				if(!$column->hasDataStructure()) {
					Debug::error("{$f} column \"{$vn}\" is not being assigned its data structure");
				}
				return $ds->getColumnValue($vn);
			}
			return parent::extractTranscryptionKey($datum);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
