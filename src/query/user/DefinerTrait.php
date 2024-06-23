<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\SQLSecurityTrait;
use function JulianSeymour\PHPWebApplicationFramework\release;

trait DefinerTrait{

	use SQLSecurityTrait;

	protected $userDefiner;

	public function setDefiner(?DatabaseUserDefinition $user): ?DatabaseUserDefinition{
		$f = __METHOD__;
		if(!$user instanceof DatabaseUserDefinition){
			Debug::error("{$f} user must be an instanceof DatabaseUserDefinition");
		}elseif($this->hasDefiner()){
			$this->release($this->userDefiner);
		}
		return $this->userDefiner = $this->claim($user);
	}

	public function hasDefiner(): bool{
		return isset($this->userDefiner) && $this->userDefiner instanceof DatabaseUserDefinition;
	}

	public function getDefiner(): DatabaseUserDefinition{
		$f = __METHOD__;
		if(!$this->hasDefiner()){
			Debug::error("{$f} definer is undefined");
		}
		return $this->userDefiner;
	}

	public function definer($user): QueryStatement{
		$this->setDefiner($user);
		return $this;
	}
}
