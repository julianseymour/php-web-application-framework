<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use Exception;

class PasswordFunction extends ExpressionCommand
{

	use ColumnNameTrait;

	protected $password;

	public function __construct($vn = null, $password = null)
	{
		parent::__construct();
		if (! empty($vn)) {
			$this->setColumnName($vn);
		}
		if (! empty($password)) {
			$this->setPassword($password);
		}
	}

	public function setPassword($password)
	{
		return $this->password = $password;
	}

	public function hasPassword()
	{
		return ! empty($this->password);
	}

	public function getParameterCount()
	{
		return 0;
	}

	public function getPassword()
	{
		$f = __METHOD__; //PasswordFunction::getShortClass()."(".static::getShortClass().")->getPassword()";
		try {
			if (! $this->hasPassword()) {
				Debug::error("{$f} password is undefined");
			}
			return $this->password;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getCommandId(): string
	{
		return "password";
	}

	public function toSQL(): string
	{
		return "PASSWORD(" . single_quote($this->getPassword()) . ")";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //PasswordFunction::getShortClass()."(".static::getShortClass().")->evaulate()";
		ErrorMessage::unimplemented($f);
	}
}
