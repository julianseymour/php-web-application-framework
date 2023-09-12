<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\data;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class GetForeignDataStructureListCommand extends ForeignDataStructureCommand implements ValueReturningCommandInterface{

	public static function getCommandId(): string{
		return "getForeignDataStructureList";
	}

	public function evaluate(?array $params = null){
		return $this->getDataStructure()->getForeignDataStructureList($this->getColumnName());
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$idcs = $this->getIdCommandString();
			if($idcs instanceof JavaScriptInterface) {
				$idcs = $idcs->toJavaScript();
			}
			$cn = $this->getColumnName();
			if($cn instanceof JavaScriptInterface) {
				$cn = $cn->toJavaScript();
			}elseif(is_string($cn) || $cn instanceof StringifiableInterface) {
				$cn = single_quote($cn);
			}
			return "{$idcs}.getForeignDataStructureList({$cn})";
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
