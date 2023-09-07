<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

ErrorMessage::deprecated(__FILE__);

class GenericImageData extends ImageData{

	public static function getSubtypeStatic():string{
		return IMAGE_TYPE_GENERIC;
	}

	public function getWebFileDirectory():string{
		return "/images";
	}
}
