<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\CharacterSetAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\SourceAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\TypeAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

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
		$ret = parent::beforeRenderHook();
		if(!$this->hasInnerHTML() && $this->hasChildNodes()) {
			$string = "";
			foreach($this->getChildNodes() as $child) {
				if($child instanceof JavaScriptInterface) {
					$string .= $child->toJavaScript();
				}elseif($child instanceof StringifiableInterface || is_string($child)) {
					$string .= $child;
				}else{
					Debug::error("{$f} invalid child type");
				}
				$string .= "\n";
			}
			$this->setInnerHTML($string);
		}
		return $ret;
	}
}
