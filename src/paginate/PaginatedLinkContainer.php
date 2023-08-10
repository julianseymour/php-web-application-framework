<?php
namespace JulianSeymour\PHPWebApplicationFramework\paginate;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\CallbackTrait;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use Exception;

class PaginatedLinkContainer extends DivElement
{

	use CallbackTrait;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("page_links");
		$this->setIdAttribute("page_links");
	}

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //PaginatedLinkContainer::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		try {
			$mode = $this->getAllocationMode();
			$paginator = $this->getContext();
			$use_case = app()->getUseCase();
			$base_uri = $use_case->getPageContentGenerator()->getActionAttribute();
			$pages = $paginator->getLinkedPageNumbers();
			// Debug::print("{$f} generating links for the following page numbers:");
			// Debug::printArray($pages);
			foreach ($pages as $i) {
				$page_link = new PageLink($mode);
				$page_link->setPageNumber($i);
				$page_link->setBaseUri($base_uri);
				if ($i === $paginator->getDisplayPage()) {
					$page_link->addClassAttribute("current_page");
				}
				if ($this->hasCallback()) {
					$page_link->setAttribute("callback", $this->getCallback());
				}
				$page_link->bindContext($paginator);
				$this->appendChild($page_link);
			}
			return $this->getChildNodes();
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
