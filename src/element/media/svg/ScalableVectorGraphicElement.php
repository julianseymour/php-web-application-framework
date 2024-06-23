<?php

namespace JulianSeymour\PHPWebApplicationFramework\element\media\svg;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

class ScalableVectorGraphicElement extends Element{

	// preserveAspectRatio, viewBox, x, y, requiredExtensions, systemLanguage
	// there's a ton of attributes for SVG: see https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/Presentation
	public static function getElementTagStatic(): string{
		return "svg";
	}
}