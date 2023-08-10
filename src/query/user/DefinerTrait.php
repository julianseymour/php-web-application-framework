<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\SQLSecurityTrait;

trait DefinerTrait
{

	use SQLSecurityTrait;

	protected $userDefiner;

	public function setDefiner(?DatabaseUserDefinition $user): ?DatabaseUserDefinition
	{
		$f = __METHOD__; //"DefinerTrait(".static::getShortClass().")->setDefiner()";
		if ($user == null) {
			unset($this->userDefiner);
			return null;
		} elseif (! $user instanceof DatabaseUserDefinition) {
			Debug::error("{$f} user must be an instanceof DatabaseUserDefinition");
		}
		return $this->userDefiner = $user;
	}

	public function hasDefiner(): bool
	{
		return isset($this->userDefiner) && $this->userDefiner instanceof DatabaseUserDefinition;
	}

	public function getDefiner(): DatabaseUserDefinition
	{
		$f = __METHOD__; //"DefinerTrait(".static::getShortClass().")->getDefiner()";
		if (! $this->hasDefiner()) {
			Debug::error("{$f} definer is undefined");
		}
		return $this->userDefiner;
	}

	public function definer($user): QueryStatement
	{
		$this->setDefiner($user);
		return $this;
	}
}
