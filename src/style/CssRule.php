<?php

namespace JulianSeymour\PHPWebApplicationFramework\style;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use Exception;

class CssRule extends Element{
	
	public static function rule(): CssRule{
		return new CssRule();
	}

	public function pushSelector(...$selectors):int{
		return $this->pushArrayProperty("selectors", ...$selectors);
	}

	public function echoJson(bool $destroy = false): void{
		echo json_encode($this->__toString());
	}

	public function setSelectors($selectors){
		return $this->setArrayProperty("selectors", $selectors);
	}

	public function withSelectors(...$selectors): CssRule{
		$this->setSelectors($selectors);
		return $this;
	}

	public function getSelectors(){
		return $this->getProperty("selectors");
	}

	public function hasSelectors():bool{
		return $this->hasArrayProperty("selectors");
	}

	public function echo(bool $destroy = false): void{
		$f = __METHOD__;
		try{
			$print = false;
			$i = 0;
			if(!$this->hasSelectors()){
				$dl = $this->getDeclarationLine();
				if($this->hasStyleProperties()){
					$properties = $this->getStyleProperties();
					if(!is_array($properties)){
						$decl = $this->getDeclarationLine();
						Debug::error("{$f} properties is not an array. Declared {$decl}");
					}elseif($print){
						Debug::print("{$f} printing properties");
						Debug::printArray($properties);
					}
				}else{
					Debug::error("{$f} selectors and properties are both undefined; declared at {$dl}");
				}
				if($print){
					Debug::warning("{$f} selectors are undefined; declared at {$dl}");
				}
				return;
			}
			foreach($this->getSelectors() as $selector){
				if($i > 0){
					echo ",";
				}
				if(is_string($selector)){
					echo $selector;
				}elseif(is_object($selector)){
					$selector->echo($destroy);
				}else{
					Debug::error("{$f} selector is neither string nor object");
				}
				$i ++;
				if($destroy){
					$this->release($selector, $destroy);
				}
			}
			if($destroy){
				unset($this->properties['selectors']);
			}
			echo "{\n";
			foreach($this->getStyleProperties() as $property_name => $property){
				if($property instanceof CssProperty){
					$property->echo($destroy);
				}else{
					echo "\t{$property_name}:{$property};\n";
				}
			}
			if($destroy){
				// unset($this->properties);
				$this->release($this->style, $destroy); //$this->setStyleProperties(null);
			}
			echo "}\n";
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
