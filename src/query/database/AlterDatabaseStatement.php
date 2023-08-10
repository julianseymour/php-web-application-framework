<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\database;

use function JulianSeymour\PHPWebApplicationFramework\hasMinimumMySQLVersion;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CharacterSetTrait;
use JulianSeymour\PHPWebApplicationFramework\query\CollatedTrait;
use JulianSeymour\PHPWebApplicationFramework\query\EncryptionOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

class AlterDatabaseStatement extends QueryStatement
{

	use CharacterSetTrait;
	use CollatedTrait;
	use DatabaseNameTrait;
	use EncryptionOptionTrait;

	protected $readOnlyValue;

	public function __construct($databaseName = null)
	{
		parent::__construct();
		if ($databaseName !== null) {
			$this->setDatabaseName($databaseName);
		}
	}

	public function setReadOnly($value)
	{
		$f = __METHOD__; //AlterDatabaseStatement::getShortClass()."(".static::getShortClass().")->setReadOnly()";
		if ($value == null && $value !== 0) {
			unset($this->readOnlyValue);
			return null;
		} elseif (is_int($value)) {
			if ($value !== 1 && $value !== 0) {
				Debug::error("{$f} invalid read only value \"{$value}\"");
			}
		} elseif (is_string($value)) {
			$value = strtolower($value);
			switch ($value) {
				case "0":
				case "1":
				case CONST_DEFAULT:
					break;
				default:
					Debug::error("{$f} invalid read only value \"{$value}\"");
			}
		} else {
			Debug::error("{$f} neither of the above");
		}
		$this->setRequiredMySQLVersion("8.0.22");
		return $this->readOnlyValue = $value;
	}

	public function hasReadOnly()
	{
		return isset($this->readOnlyValue);
	}

	public function getReadOnly()
	{
		if (! $this->hasReadOnly()) {
			return CONST_DEFAULT;
		}
		return $this->readOnlyValue;
	}

	public function readOnly($value)
	{
		$this->setReadOnly($value);
		return $this;
	}

	public function getQueryStatementString()
	{
		// ALTER {DATABASE | SCHEMA} [db_name] alter_option ...
		$string = "alter database ";
		if ($this->hasDatabaseName()) {
			$string .= $this->getDatabaseName();
		}
		// [DEFAULT] CHARACTER SET [=] charset_name
		if ($this->hasCharacterSet()) {
			$string .= " character set " . $this->getCharacterSet();
		}
		// | [DEFAULT] COLLATE [=] collation_name
		if ($this->hasCollationName()) {
			$string .= " collate " . $this->getCollationName();
		}
		// | [DEFAULT] ENCRYPTION [=] {'Y' | 'N'}
		if ($this->hasEncryption()) {
			$string .= " encryption " . $this->getEncryption();
		}
		// | READ ONLY [=] {DEFAULT | 0 | 1}
		if ($this->hasReadOnly() && hasMinimumMySQLVersion("8.0.22")) {
			$string .= " read only " . $this->getReadOnly();
		}
		return $string;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->characterSet);
		unset($this->collationName);
		unset($this->databaseName);
		unset($this->encryptionOption);
		unset($this->readOnlyValue);
	}
}
