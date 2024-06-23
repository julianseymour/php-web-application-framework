<?php

namespace JulianSeymour\PHPWebApplicationFramework\email;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\image\ImageElement;
use Exception;

class EmailHeaderElement extends DivElement{

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$mode = $this->getAllocationMode();
			$logo1 = new ImageElement($mode);
			$logo1->setCacheKey(static::getShortClass() . "_attached"); // this is getting cached so the chunk_split function doesn't have to be called repeatedly for every single email that gets sent
			$logo1->setStyleProperties([
				"height" => "50px"
			]);
			$uri = WEBSITE_LOGO_URI_HORIZONTAL_DARK;
			$this->setStyleProperties([
				"height" => "50px",
				"background-color" => "#000", //XXX TODO this should not be hard coded
				"padding" => "8px"
			]);
			if(is_object($uri)){
				Debug::error("{$f} URI is an object of class " . get_class($uri) . ", declared " . $uri->getDeclarationLine());
			}elseif(is_string($uri)){
				if(file_exists($uri)){
					$logo1->setSourceAttribute($uri);
					$this->appendChild($logo1);
				}else{
					Debug::warning("{$f} file does not exist");
					$this->setAllowEmptyInnerHTML(true);
				}
			}
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
