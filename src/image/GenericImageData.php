<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

class GenericImageData extends ImageData{

	public static function getImageTypeStatic(){
		return IMAGE_TYPE_GENERIC;
	}

	public function getWebFileDirectory():string{
		return "/images";
	}
}
