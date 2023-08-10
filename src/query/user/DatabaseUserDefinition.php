<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\role\DatabaseRoleData;
use Exception;

class DatabaseUserDefinition extends DatabaseRoleData
{

	protected $oldPassword;

	protected $password;

	protected $queryStatement;

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"discard old password",
			"random password",
			"retain current password"
		]);
	}

	public function setQueryStatement($q)
	{
		$f = __METHOD__; //DatabaseUserDefinition::getShortClass()."(".static::getShortClass().")->setQueryStatement()";
		if (! $q instanceof UserStatement) {
			Debug::error("{$f} statement must be an instanceof UserStatement");
		}
		return $this->queryStatement = $q;
	}

	public function hasQueryStatement()
	{
		return isset($this->queryStatement) && $this->queryStatement instanceof UserStatement;
	}

	public function getQueryStatement()
	{
		$f = __METHOD__; //DatabaseUserDefinition::getShortClass()."(".static::getShortClass().")->getQueryStatement()";
		if (! $this->hasQueryStatement()) {
			Debug::error("{$f} query statement is undefined");
		}
		return $this->queryStatement;
	}

	public function hasPassword()
	{
		return ! empty($this->password);
	}

	public function getPassword()
	{
		$f = __METHOD__; //DatabaseUserDefinition::getShortClass()."(".static::getShortClass().")->getPassword()";
		if (! $this->hasPassword()) {
			Debug::error("{$f} password is undefined");
		}
		return $this->password;
	}

	public function setPassword($password)
	{ // XXX validate password
		$f = __METHOD__; //DatabaseUserDefinition::getShortClass()."(".static::getShortClass().")->setPassword()";
		if ($password == null) {
			unset($this->password);
			return null;
		} elseif (! is_string($password)) {
			Debug::error("{$f} password must be a string");
		}
		return $this->password = $password;
	}

	public function by($password): DatabaseUserDefinition
	{
		$this->setPassword($password);
		return $this;
	}

	public function setOldPassword($pw)
	{
		$f = __METHOD__; //DatabaseUserDefinition::getShortClass()."(".static::getShortClass().")->setOldPassword()";
		if ($pw == null) {
			unset($this->oldPassword);
			return null;
		} elseif (! is_string($pw)) {
			Debug::error("{$f} current password must be a string");
		}
		return $this->oldPassword = $pw;
	}

	public function hasOldPassword()
	{
		return isset($this->oldPassword);
	}

	public function getOldPassword()
	{
		$f = __METHOD__; //DatabaseUserDefinition::getShortClass()."(".static::getShortClass().")->getOldPassword()";
		if (! $this->hasOldPassword()) {
			Debug::error("{$f} current password is undefined");
		}
		return $this->oldPassword;
	}

	public function replace($current): DatabaseUserDefinition
	{
		$this->setOldPassword($current);
		return $this;
	}

	public function setRandomPasswordFlag($value = true)
	{
		$f = __METHOD__; //DatabaseUserDefinition::getShortClass()."(".static::getShortClass().")->setRandomPasswordFlag()";
		if ($this->getStatementType() === STATEMENT_TYPE_ALTER_CURRENT_USER) {
			Debug::error("{$f} random password is unsupported by alter current user statement");
			return false;
		}
		$this->setRequiredMySQLVersion("8.0.18");
		return $this->setFlag("random password", $value);
	}

	public function getRandomPasswordFlag()
	{
		return $this->getFlag("random password");
	}

	public function byRandomPassword(): DatabaseUserDefinition
	{
		$this->setRandomPasswordFlag(true);
		return $this;
	}

	public function setDiscardOldPasswordFlag($value = true)
	{
		return $this->setFlag("discard old password", $value);
	}

	public function getDiscardOldPasswordFlag()
	{
		return $this->getFlag("discard old password");
	}

	public function discardOldPassword(): DatabaseUserDefinition
	{
		$this->setDiscardOldPasswordFlag(true);
		return $this;
	}

	public function setRetainCurrentPasswordFlag($value = true)
	{
		return $this->setFlag("retain current password", $value);
	}

	public function getRetainCurrentPasswordFlag()
	{
		return $this->getFlag("retain current password");
	}

	public function retainCurrentPassword(): DatabaseUserDefinition
	{
		$this->setRetainCurrentPasswordFlag(true);
		return $this;
	}

	public function getAuthOptionString()
	{
		$f = __METHOD__; //DatabaseUserDefinition::getShortClass()."(".static::getShortClass().")->getAuthOptionString()";
		$string = "";
		if ($this->getDiscardOldPasswordFlag()) {
			$string .= " discard old password";
			return $string;
		} elseif ($this->hasAuthPlugin() || $this->hasPassword() || $this->getRandomPasswordFlag()) {
			$string .= " identified";
			$qs = $this->hasQueryStatement() ? $this->getQueryStatement() : null;
			$current_user = $qs instanceof AlterUserStatement && $qs->getCurrentUserFlag();
			if (! $current_user && $this->hasAuthPlugin()) {
				$string .= " with " . $this->getAuthPlugin();
			}
			if ($this->hasPassword() || $this->getRandomPasswordFlag()) {
				if ($this->getRandomPasswordFlag()) {
					if ($current_user) {
						Debug::error("{$f} cannot have random password for alter current user");
					}
					$string .= " by random password";
				} elseif ($this->hasPassword()) {
					$string .= " by " . single_quote($this->getPassword());
				} else {
					Debug::error("{$f} neither of the above");
				}
				if (! $qs instanceof CreateUserStatement) {
					if ($this->hasOldPassword()) {
						$string .= " replace " . single_quote($this->getOldPassword());
					}
					if ($this->getRetainCurrentPasswordFlag()) {
						$string .= " retain current password";
					}
				}
			}
		}
		return $string;
	}

	public function toSQL(): string
	{
		$f = __METHOD__; //DatabaseUserDefinition::getShortClass()."(".static::getShortClass().")->toSQL()";
		try {
			return parent::toSQL() . $this->getAuthOptionString();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->oldPassword);
		unset($this->password);
		unset($this->queryStatement);
	}
}
