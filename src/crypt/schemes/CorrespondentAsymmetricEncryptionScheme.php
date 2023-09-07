<?php

namespace JulianSeymour\PHPWebApplicationFramework\crypt\schemes;

use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

class CorrespondentAsymmetricEncryptionScheme extends AsymmetricEncryptionScheme{

	protected function extractPublicKey(Datum $datum):string{
		return $datum->getDataStructure()->getUserData()->getCorrespondentObject()->getPublicKey();
	}
}
