<?php

namespace JulianSeymour\PHPWebApplicationFramework\style;

use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use Exception;

class CssKeyframesRule extends CssRule{

	use NamedTrait;

	public function __construct($name = null, ...$rules){
		$f = __METHOD__;
		parent::__construct();
		if(!empty($name)){
			$this->setName($name);
		}
		if($rules !== null && count($rules) > 0){
			foreach($rules as $rule){
				$this->appendChild($rule);
			}
		}
	}

	public function echo(bool $destroy = false): void{
		$f = __METHOD__;
		try{
			echo "@keyframes ";
			echo $this->getName();
			echo "{\n";
			foreach($this->getChildNodes() as $rule){
				echo "\t";
				$rule->echo($destroy);
				echo "\n";
			}
			echo "}\n";
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->name, $deallocate);
	}
}
