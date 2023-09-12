<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter\charset;

use JulianSeymour\PHPWebApplicationFramework\query\CharacterSetTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\AlterOption;

class CharacterSetOption extends AlterOption
{

	use CharacterSetTrait;

	public function __construct($charset, $collationName = null)
	{
		parent::__construct();
		$this->setCharacterSet($charset);
		if($collationName !== null) {
			$this->setCollationName($collationName);
		}
	}

	public function toSQL(): string
	{
		$string = "character set " . $this->getCharacterSet();
		if($this->hasCollationName()) {
			$cn = $this->getCollationName();
			$string .= " collate {$cn}";
		}
		return $string;
	}

	public function dispose(): void
	{
		unset($this->characterSet);
		unset($this->collationName);
	}
}
