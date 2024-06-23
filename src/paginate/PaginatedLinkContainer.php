<?php

namespace JulianSeymour\PHPWebApplicationFramework\paginate;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\CallbackTrait;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use Exception;

class PaginatedLinkContainer extends DivElement{

	use CallbackTrait;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("page_links");
		$this->setIdAttribute("page_links");
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try{
			$mode = $this->getAllocationMode();
			$paginator = $this->getContext();
			$use_case = app()->getUseCase();
			$base_uri = $use_case->getPageContentGenerator()->getActionAttribute();
			$pages = $paginator->getLinkedPageNumbers();
			foreach($pages as $i){
				$page_link = new PageLink($mode);
				$page_link->setPageNumber($i);
				$page_link->setBaseUri($base_uri);
				if($i === $paginator->getDisplayPage()){
					$page_link->addClassAttribute("current_page");
				}
				if($this->hasCallback()){
					$page_link->setAttribute("callback", $this->getCallback());
				}
				$page_link->bindContext($paginator);
				$this->appendChild($page_link);
			}
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->callback, $deallocate);
	}
}
