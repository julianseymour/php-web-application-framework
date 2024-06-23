<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use Exception;

class AspectRatioInput extends TextInput{
	
	public function negotiateValue(Datum $column)
	{
		$f = __METHOD__;
		try{
			if($this->hasNegotiator()){
				return parent::negotiateValue($column);
			}
			$print = false;
			$raw = $this->getValueAttribute();
			if(is_string($raw) && str_contains($raw, ":")){
				$splat = explode(":", $raw);
				$x = doubleval($splat[0]);
				$y = doubleval($splat[1]);
				$v = $x / $y;
			}else{
				if($print){
					$gottype = gettype($raw);
					Debug::print("{$f} {$raw} is a {$gottype}");
				}
				if($raw < 0){
					$v = $raw * - 1;
				}elseif($raw === 0){
					$v = 1.3333;
				}else{
					$v = $raw;
				}
			}
			if($print){
				Debug::print("{$f} returning \"{$v}\"");
			}
			return $v;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}