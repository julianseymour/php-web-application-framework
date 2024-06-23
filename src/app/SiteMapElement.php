<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use JulianSeymour\PHPWebApplicationFramework\core\Document;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\use_case\Router;

class SiteMapElement extends Element{
	
	public function __construct(int $mode=ALLOCATION_MODE_UNDEFINED, $context=null){
		parent::__construct($mode, $context);
		$this->setAttribute("xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");
	}
	
	public static function getElementTagStatic():string{
		return "urlset";
	}
	
	protected function getSelfGeneratedPredecessors():?array{
		return [
			"<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
		];
	}
	
	public function generateChildNodes():?array{
		$count = 0;
		foreach(mods()->getUseCaseDictionary() as $uri => $ucc){
			if(is_a($ucc, Router::class, true)){
				continue;
			}elseif($ucc::isSiteMappable()){
				$url = Document::createElement("url");
				$loc = Document::createElement("loc");
				$loc->setInnerHTML(WEBSITE_URL."/".$uri);
				$url->appendChild($loc);
				$this->appendChild($url);
				$count++;
			}
		}
		if($count === 0){
			$this->setAllowEmptyInnerHTML(true);
		}
		return $this->hasChildNodes() ? $this->getChildNodes() : [];
	}
}

