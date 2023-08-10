<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\element\MainElement;

class PageContentElement extends MainElement
{

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->setIdAttribute("page_content");
		$this->addClassAttribute("page_content", "background_color_3");
	}
}
