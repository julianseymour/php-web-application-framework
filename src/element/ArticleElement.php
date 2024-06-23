<?php

namespace JulianSeymour\PHPWebApplicationFramework\element;

/**
 * the element has global attributes only
 *
 * @author j
 */
class ArticleElement extends Element{

	public static function getElementTagStatic(): string{
		return "article";
	}
}
