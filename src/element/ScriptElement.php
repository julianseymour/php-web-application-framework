<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\CharacterSetAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\TypeAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use JulianSeymour\PHPWebApplicationFramework\app\Request;

class ScriptElement extends IntangibleElement{

	use CharacterSetAttributeTrait;
	use SourceAttributeTrait;
	use TypeAttributeTrait;

	public static function getElementTagStatic(): string{
		return "script";
	}

	public function setAsyncAttribute($async){
		return $this->setAttribute("async", null);
	}

	public function hasAsyncAttribute(){
		return $this->hasAttribute("async");
	}

	public function setDeferAttribute($defer){
		return $this->setAttribute("defer", null);
	}

	public function hasDeferAttribute(){
		return $this->hasAttribute("defer");
	}

	protected function beforeRenderHook(): int{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$ret = parent::beforeRenderHook();
		if(Request::isAjaxRequest()){
			if(!$this->hasInnerHTML() && $this->hasChildNodes()){
				if($print){
					Debug::print("{$f} this element lacks explicitly defined inner HTML, or has child nodes");
				}
				$string = "";
				foreach($this->getChildNodes() as $child){
					if($child instanceof JavaScriptInterface){
						$string .= $child->toJavaScript();
					}elseif($child instanceof StringifiableInterface || is_string($child)){
						$string .= $child;
					}else{
						Debug::error("{$f} invalid child type");
					}
					$string .= "\n";
				}
				$this->releaseChildNodes(true); //$this->childNodes, true, $this->getDebugString());
				$this->setInnerHTML($string);
			}elseif($print){
				Debug::print("{$f} this element either has inner HTML explicitly defined, or lacks child nodes");
			}
		}
		return $ret;
	}
}
