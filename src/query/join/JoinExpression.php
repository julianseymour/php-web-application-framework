<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\join;

use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

abstract class JoinExpression extends Basic implements SQLInterface
{

	public abstract function getTableReferenceString();

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"escape"
		]);
	}

	public function setEscapeFlag($value = true)
	{
		return $this->setFlag("escape", $value);
	}

	public function getEscapeFlag()
	{
		return $this->getFlag("escape");
	}

	public final function toSQL(): string
	{
		$string = $this->getEscapeFlag() ? "{ OJ " : "";
		$string .= $this->getTableReferenceString();
		if ($this->getEscapeFlag()) {
			$string .= " }";
		}
		return $string;
	}
}
