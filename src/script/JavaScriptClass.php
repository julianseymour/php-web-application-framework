<?php

namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use Exception;

class JavaScriptClass extends Element implements JavaScriptInterface{

	use NamedTrait;

	protected $superClass;

	public function __construct($className = null, $superClass = null){
		parent::__construct();
		if(isset($className)){
			$this->setName($className);
		}
		if(isset($superClass)){
			$this->setSuperClass($superClass);
		}
	}

	public function hasSuperClass():bool{
		return isset($this->superClass);
	}

	public function setSuperClass($superClass){
		if($this->hasSuperClass()){
			$this->release($this->superClass);
		}
		return $this->superClass = $this->claim($superClass);
	}

	public function getSuperClass(){
		$f = __METHOD__;
		if(!$this->hasSuperClass()){
			Debug::error("{$f} superClass is undefined");
		}
		return $this->superClass;
	}

	public function getClassDeclarationString():string{
		$className = $this->getName();
		$string = "";
		$string = "if(typeof {$className} !== 'undefined'){
	var {$className} = null;
}
";
		$string .= "var {$className} = class ";
		if($this->hasSuperClass()){
			$super = $this->getSuperClass();
			$string .= " extends {$super}";
		}
		return $string;
	}

	public function toJavaScript(): string{
		$f = __METHOD__;
		try{
			$print = false;
			$cache = false;
			if($this->isCacheable() && JAVASCRIPT_CACHE_ENABLED){
				if(cache()->hasFile($this->getCacheKey() . ".js")){
					if($print){
						Debug::print("{$f} cache hit");
					}
					return cache()->getFile($this->getCacheKey() . ".js");
				}else{
					if($print){
						Debug::print("{$f} cache miss");
					}
					$cache = true;
				}
			}elseif($print){
				Debug::print("{$f} this class is not cacheable");
			}
			$string = $this->getClassDeclarationString()."{\n";
			foreach($this->getChildNodes() as $node){
				$string .= "\t".$node->toJavaScript()."\n";
				if($node instanceof JavaScriptFunction){
					$string .= "\n";
				}
			}
			$string .= "}\n";
			if($cache){
				cache()->setFile($this->getCacheKey().".js", $string, time() + 30 * 60);
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function echo(bool $destroy = false): void{
		$js = $this->toJavaScript();
		echo $js;
	}

	public function __toString(): string{
		return $this->toJavaScript();
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
		$this->release($this->superClass, $deallocate);
	}
}
