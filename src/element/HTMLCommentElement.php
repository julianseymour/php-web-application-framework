<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\Json;

class HTMLCommentElement extends Element{

	public function echo(bool $destroy = false): void{
		echo "<!-- ";
		$this->echoInnerHTML();
		echo " -->";
	}

	public function echoInnerJson(bool $destroy = false): void{
		Json::echoKeyValuePair("tag", "!--", $destroy);
		Json::echoKeyValuePair("innerHTML", $this->getInnerHTML(), $destroy, false);
	}

	public function __construct($comment = null){
		parent::__construct(ALLOCATION_MODE_NEVER);
		// $this->setDebugId(sha1(random_bytes(32)));
		if(!empty($comment)){
			$this->setInnerHTML($comment);
		}
	}

	public function setAttribute($key, $value = null){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function getChildNodeCount():int{
		return 0;
	}

	public function hasChildNodes():bool{
		return false;
	}

	public function getAllocationMode(): int{
		return ALLOCATION_MODE_UNDEFINED;
	}

	public function hasAllocationMode(): bool{
		return true;
	}

	public static function getElementTagStatic(): string{
		return "!--";
	}
}
