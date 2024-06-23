<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;

/**
 * workaround for template attributes with dynamic names
 *
 * @author j
 *        
 */
class Attribute extends Basic implements JavaScriptInterface{

	use NamedTrait;
	use ValuedTrait;

	public function toJavaScript(): string{
		$value = $this->getValue();
		if($value instanceof JavaScriptInterface){
			$value = $value->toJavaScript();
		}elseif(is_string($value) || $value instanceof StringifiableInterface){
			$value = single_quote($value);
		}
		return $value;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
		$this->release($this->value, $deallocate);
	}
}
