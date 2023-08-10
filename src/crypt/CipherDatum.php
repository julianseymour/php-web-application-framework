<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt;

use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class CipherDatum extends EncryptionComponentDatum
{

	protected $originalDatumClass;

	protected function hasDecryptedValue()
	{
		return $this->getOriginalDatum()->hasValue();
	}

	/**
	 *
	 * @return UserOwned
	 * {@inheritdoc}
	 * @see EncryptionComponentDatum::getDataStructure()
	 */
	public function getDataStructure()
	{
		return parent::getDataStructure();
	}

	public function acquirePublicKey($mysqli)
	{
		return $this->getDataStructure()->acquirePublicKey($mysqli);
	}

	public function getOriginalDatumClass()
	{
		return $this->originalDatumClass;
	}

	public function setOriginalDatumClass($class)
	{
		return $this->originalDatumClass = $class;
	}

	public function parseOriginal($v)
	{
		$f = __METHOD__; //CipherDatum::getShortClass()."(".static::getShortClass().")->parseOriginal()";
		$print = false;
		if ($print) {
			$odc = $this->getOriginalDatumClass();
			Debug::print("{$f} returning {$odc}::parseString()");
		}
		return $odc::parseString($v);
	}
}
