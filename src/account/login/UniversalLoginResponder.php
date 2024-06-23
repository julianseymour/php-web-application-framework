<?php

namespace JulianSeymour\PHPWebApplicationFramework\account\login;

use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\user;

class UniversalLoginResponder extends AbstractLoginResponder{

	protected function getStartingResponseCommandArray(): array{
		$ret = parent::getStartingResponseCommandArray();
		$lec = config()->getLoginReplacementElementClass();
		$e = new $lec(ALLOCATION_MODE_LAZY, user());
		$update = $e->update();
		array_push($ret, $update);
		return $ret;
	}
}
