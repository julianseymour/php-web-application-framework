<?php
namespace JulianSeymour\PHPWebApplicationFramework\paginate;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\pwa\ProgressiveHyperlinkElement;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

class PageLink extends ProgressiveHyperlinkElement
{

	protected $pageNumber;

	protected $baseUri;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("page_link");
	}

	public function setPageNumber($i)
	{
		return $this->pageNumber = $i;
	}

	public function hasPageNumber()
	{
		return isset($this->pageNumber);
	}

	public function getPageNumber()
	{
		$f = __METHOD__; //PageLink::getShortClass()."(".static::getShortClass().")->getPageNumber()";
		if(!$this->hasPageNumber()) {
			Debug::error("{$f} page number is undefined");
		}
		return $this->pageNumber;
	}

	public function setBaseUri($uri)
	{
		return $this->baseUri = $uri;
	}

	public function hasBaseUri()
	{
		return isset($this->baseUri);
	}

	public function getBaseUri()
	{
		$f = __METHOD__; //PageLink::getShortClass()."(".static::getShortClass().")->getBaseUri()";
		if(!$this->hasBaseUri()) {
			Debug::error("{$f} base URI is undefined");
		}
		return $this->baseUri;
	}

	public function bindContext($context)
	{
		$f = __METHOD__; //PageLink::getShortClass()."(".static::getShortClass().")->bindContext()";
		try{
			if(!$context instanceof Paginator) {
				Debug::error("{$f} context is not a paginator");
			}
			$ret = parent::bindContext($context);
			$pg = $this->getPageNumber();
			$params = $context->getPageLinkHTTPQueryParameters($pg);
			$http_query = http_build_query($params);
			// Debug::print("{$f} HTTP query is \"{$http_query}\"");
			$uri = $this->getBaseUri();
			$href = $uri . "?" . $http_query;
			$this->setHrefAttribute($href);
			$this->setInnerHTML($pg);
			return $ret;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
