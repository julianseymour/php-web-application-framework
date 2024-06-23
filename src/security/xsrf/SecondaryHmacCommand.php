<?php

namespace JulianSeymour\PHPWebApplicationFramework\security\xsrf;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\UriTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class SecondaryHmacCommand extends Command implements JavaScriptInterface, ValueReturningCommandInterface{

	use UriTrait;

	public function __construct($uri=null){
		parent::__construct();
		if($uri !== null){
			$this->setUri($uri);
		}
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasURI()){
			$this->setURI(replicate($that->getUri()));
		}
		return $ret;
	}
	
	public static function getCommandId(): string{
		return "secondary_hmac";
	}

	public function setUri($uri){
		$f = __METHOD__;
		$print = false;
		if($uri == null){
			unset($this->uri);
			return null;
		}elseif(str_contains($uri, "?")){
			$uri = explode("?", $uri)[0];
			if($print){
				Debug::print("{$f} split string at ? to make new URI \"{$uri}\"");
			}
		}
		return $this->uri = $uri;
	}

	public function evaluate(?array $params=null){
		$f = __METHOD__;
		$session = new AntiXsrfTokenData();
		if(!$session->hasAntiXsrfToken()){
			Debug::error("{$f} session is uninitialized");
			$session->initializeSessionToken(1);
		}
		$ret = $session->getSecondaryHmac($this->getUri());
		deallocate($session);
		return $ret;
	}

	public function echoInnerJson(bool $destroy = false):void{
		$f = __METHOD__;
		Json::echoKeyValuePair('action', $this->getUri(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->uri, $deallocate);
	}

	public function toJavaScript():string{
		$f = __METHOD__;
		try{
			$action = $this->getUri();
			if($action instanceof JavaScriptInterface){
				$action = $action->toJavaScript();
			}elseif(is_string($action)){
				$action = single_quote($action);
			}
			return "getSecondaryHmac({$action})";
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
