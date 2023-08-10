<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use Exception;

class SharedMessageEncryptionScheme extends MessageEncryptionScheme implements SharedEncryptionSchemeInterface
{

	public function generateComponents(?DataStructure $ds = null): array
	{
		$components = parent::generateComponents();
		$datum = $this->getColumn();
		$vn = $datum->getColumnName();
		$aes_key = $components["{$vn}_aesKey"];
		$admin_copy = $aes_key->mirrorAtIndex("{$vn}_aesKeyCopy");
		$admin_copy->setPersistenceMode(PERSISTENCE_MODE_ENCRYPTED);
		$admin_copy->setEncryptionScheme(CorrespondentAsymmetricEncryptionScheme::class);
		$admin_copy->setNullable(false);
		$components3 = (new CorrespondentAsymmetricEncryptionScheme($admin_copy))->generateComponents();
		$components[$admin_copy->getColumnName()] = $admin_copy;
		return array_merge($components, $components3);
	}

	public function extractTranscryptionKey($datum)
	{
		$f = __METHOD__; //SharedMessageEncryptionScheme::getShortClass()."(".static::getShortClass().")::extractTranscryptionKey()";
		try {
			$ds = $datum->getDataStructure();
			$user = user();
			if ($ds->getUserKey() !== $user->getIdentifierValue()) {
				// Debug::print("{$f} data structure's user key differs from that of the current user");
				$vn = $datum->getColumnName() . "_aesKeyCopy";
				// $dsc = $ds->getClass();
				// Debug::print("{$f} about to call {$dsc}->getColumnValue({$vn})");

				$column = $ds->getColumn($vn);
				if (! $column->hasDataStructure()) {
					Debug::error("{$f} column \"{$vn}\" is not being assigned its data structure");
				}

				return $ds->getColumnValue($vn);
			} /*
			   * elseif($user instanceof Administrator){
			   * Debug::error("{$f} administrator should not be here");
			   * }
			   */
			// Debug::print("{$f} returning parent function");
			return parent::extractTranscryptionKey($datum);
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
