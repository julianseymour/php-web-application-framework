<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\role\DatabaseRoleData;

class RenameUserDefinition extends DatabaseRoleData
{

	protected $newUsername;

	protected $newHost;

	public function setNewUsername($name)
	{
		$f = __METHOD__; //RenameUserDefinition::getShortClass()."(".static::getShortClass().")->setNewUsername()";
		if ($name == null) {
			unset($this->newUsername);
			return null;
		} elseif (! is_string($name)) {
			Debug::error("{$f} ");
		}
		return $this->newUsername = $name;
	}

	public function hasNewUsername()
	{
		return isset($this->newUsername);
	}

	public function getNewUsername()
	{
		if (! $this->hasNewUsername()) {
			return $this->getUsername();
		}
		return $this->newUsername;
	}

	public function setNewHost($host)
	{
		$f = __METHOD__; //RenameUserDefinition::getShortClass()."(".static::getShortClass().")->setNewHost()";
		if ($host == null) {
			unset($this->newHost);
			return null;
		} elseif (! is_string($host)) {
			Debug::error("{$f} new host must be a string");
		}
		return $this->newHost = $host;
	}

	public function hasNewHost()
	{
		return isset($this->newHost);
	}

	public function getNewHost()
	{
		if (! $this->hasNewHost()) {
			return $this->getHost();
		}
		return $this->newHost;
	}

	public function getArrayKey(int $count)
	{
		return $this->getUsernameHostString();
	}

	public function getNewUsernameHostString()
	{
		$user = escape_quotes($this->getNewUsername(), QUOTE_STYLE_SINGLE);
		$host = escape_quotes($this->getNewHost(), QUOTE_STYLE_SINGLE);
		return "'{$user}'@'{$host}'";
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->newHost);
		unset($this->newUsername);
	}
}