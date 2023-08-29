<?php

namespace JulianSeymour\PHPWebApplicationFramework\json;

trait EchoJsonTrait{

	public abstract function echoInnerJson(bool $destroy = false): void;

	public function echoJson(bool $destroy = false): void{
		echo "{";
		$this->echoInnerJson($destroy);
		echo "}";
	}

	public function skipJson(): bool{
		return false;
	}
	
	public function toJSON():string{
		ob_start();
		$this->echoJson(false);
		return ob_get_clean();
	}
}

