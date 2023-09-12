<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait UriTrait
{

	protected $uri;

	public function setUri($uri)
	{
		$f = __METHOD__; //"UriTrait(".static::getShortClass().")->setUri()";
		$print = false;
		if($uri instanceof ValueReturningCommandInterface) {
			if($print) {
				Debug::print("{$f} URI is a value-returning command");
			}
		}elseif(!is_string($uri)) {
			Debug::error("{$f} URI is not a string");
		}elseif(empty($uri)) {
			Debug::error("{$f} URI is empty string");
		}elseif($print) {
			Debug::print("{$f} assigning URI \"{$uri}\"");
		}
		return $this->uri = $uri;
	}

	public function hasURI()
	{
		return isset($this->uri) && ! empty($this->uri);
	}

	public function getUri()
	{
		$f = __METHOD__; //"UriTrait(".static::getShortClass().")->getUri()";
		if(!$this->hasURI()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} URI is undefined; declared {$decl}");
		}
		return $this->uri;
	}
}
