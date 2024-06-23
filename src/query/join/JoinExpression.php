<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\join;

use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;

abstract class JoinExpression extends Basic implements ReplicableInterface, SQLInterface{

	use ReplicableTrait;
	
	public abstract function getTableReferenceString();

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"escape"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"escape"
		]);
	}
	
	public function setEscapeFlag($value = true){
		return $this->setFlag("escape", $value);
	}

	public function getEscapeFlag(){
		return $this->getFlag("escape");
	}

	public final function toSQL(): string{
		$string = $this->getEscapeFlag() ? "{ OJ " : "";
		$string .= $this->getTableReferenceString();
		if($this->getEscapeFlag()){
			$string .= " }";
		}
		return $string;
	}
}
