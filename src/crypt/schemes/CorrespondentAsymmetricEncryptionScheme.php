<?php
namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

class CorrespondentAsymmetricEncryptionScheme extends AsymmetricEncryptionScheme
{

	protected function extractPublicKey($datum)
	{
		return $datum->getDataStructure()
			->getUserData()
			->getCorrespondentObject()
			->getPublicKey();
	}
}
