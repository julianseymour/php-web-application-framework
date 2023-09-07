<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class FullTextStringDatum extends StringDatum{

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"fulltext"
		]);
	}

	public function setFulltextFlag(bool $value = true): bool{
		return $this->setFlag("fulltext", $value);
	}

	public function getFulltextFlag(): bool{
		return $this->getFlag("fulltext");
	}

	public function setFlag(string $flag_name, bool $value = true): bool{
		$f = __METHOD__; //FullTextStringDatum::getShortClass()."(".static::getShortClass().")->setFlag()";
		$print = false;
		if ($flag_name === COLUMN_FILTER_SEARCHABLE) {
			if($print){
				Debug::print("{$f} setting serachable AND fulltext flags");
			}
			$this->setFulltextFlag($value);
		}
		return parent::setFlag($flag_name, $value);
	}
}
