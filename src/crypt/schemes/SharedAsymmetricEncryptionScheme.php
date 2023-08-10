<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use JulianSeymour\PHPWebApplicationFramework\crypt\CipherDatum;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class SharedAsymmetricEncryptionScheme extends AsymmetricEncryptionScheme implements SharedEncryptionSchemeInterface
{

	public function generateComponents(?DataStructure $ds = null): array
	{
		$components = parent::generateComponents();
		$datum = $this->getColumn();
		$vn = $datum->getColumnName();
		$admin_cipher = new CipherDatum("{$vn}_cipherCopy");
		// $admin_cipher->setEncryptionComponent(ENCRYPTION_COMPONENT_ASYMMETRIC_CIPHER_SHARED);
		$admin_cipher->setEncryptionScheme(static::class);
		$admin_cipher->setSensitiveFlag($datum->getSensitiveFlag());
		$admin_cipher->setOriginalDatumClass($datum->getClass());
		array_push($components, $admin_cipher);
		return $components;
	}
}
