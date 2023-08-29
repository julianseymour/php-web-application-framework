<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

// use JulianSeymour\PHPWebApplicationFramework\common\FlagBearingTrait;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\unset_cookie;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\file\UploadedFile;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\use_case\ProgramFlowControlUnit;
use Exception;

/**
 * Represents and incoming request
 *
 * @author j
 *        
 */
class Request extends Basic{

	use ArrayPropertyTrait;

	// use FlagBearingTrait;
	protected $action;

	protected $PHPInputFileContents;

	protected $requestURISegments;

	protected $directive;

	// insert, update, delete_struct, delete, regenerate, unset, validate
	public function __construct(){
		$f = __METHOD__;
		$print = false;
		// $this->requirePropertyType("repackedIncomingFiles", UploadedFile::class);
		if (! static::isXHREvent() && ! static::isFetchEvent() && isset($_COOKIE) && is_array($_COOKIE) && array_key_exists('nonAjaxJsEnabled', $_COOKIE)) {
			if ($print) {
				Debug::print("{$f} This is a non-ajax request where JS is enabled");
			}
			$this->setFlag("nonAjaxJsEnabled", true);
			unset_cookie("nonAjaxJsEnabled");
		} elseif ($print) {
			if (static::isXHREvent()) {
				Debug::print("{$f} this is an XHR");
			} elseif (static::isFetchEvent()) {
				Debug::print("{$f} this is a fetch");
			} else {
				Debug::print("{$f} Non-AJAX JS enabled cookie is not set");
				Debug::printArray($_COOKIE);
			}
		}
	}

	public static function declareFlags(): ?array{
		return [
			PROGRESSIVE_HYPERLINK_KEY,
			"nonAjaxJsEnabled"
		];
	}

	public function hasAction():bool{
		return is_string($this->action) && ! empty($this->action);
	}

	public function matchDirective(string $directive): bool{
		return $this->hasInputParameter("directive") && $this->getInputParameter("directive") === $directive;
	}

	public function matchForm(string $form_id): bool{
		if (class_exists($form_id) && is_a($form_id, AjaxForm::class, true)) {
			$form_id = $form_id::getFormDispatchIdStatic();
		}
		return $this->hasInputParameter("dispatch") && $this->getInputParameter("dispatch") === $form_id;
	}

	public function getRequestURISegmentCount():int{
		$segments = $this->getRequestURISegments();
		if ($segments == null) {
			return 0;
		}
		return count($segments);
	}

	public function getAction(){
		$f = __METHOD__;
		$print = false;
		if ($this->hasAction()) {
			return $this->action;
		}
		$segments = $this->getRequestURISegments();
		$count = count($segments);
		switch ($count) {
			case 0:
				if ($print) {
					Debug::print("{$f} there are no URL segments");
				}
				$action = ""; // "home";
				break;
			case 1:
				if ($print) {
					Debug::print("{$f} there is only one segment");
				}
				if (empty($segments[0]) || false !== strpos($segments[0], '=')) {
					if (false !== strpos($segments[0], '?')) {
						if ($print) {
							Debug::print("{$f} only segment contains a ?");
						}
						$segments = explode('?', $segments[0]);
						$action = $segments[0];
						$count = count($segments);
					} else {
						if ($print) {
							Debug::print("{$f} request is empty or GET parameters only");
						}
						$action = ""; // home";
					}
					break;
				} elseif ($print) {
					Debug::print("{$f} request is an action attribute only");
				}
			default:
				if ($print) {
					if ($count > 1) {
						Debug::print("{$f} there are multiple segments");
					}
				}
				$action = $segments[0];
				break;
		}
		return $this->action = $action;
	}

	public function getRequestURI(){
		$f = __METHOD__;
		try {
			$uri = $this->getRequestURIWithoutParams();
			if (! empty($_GET)) {
				$uri .= "?" . http_build_query($_GET);
			}
			return $uri;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getRequestURIWithoutParams(): string{
		$pieces = $this->getRequestUriSegments();
		return "/" . implode('/', $pieces);
	}

	public static function getOriginHeader(){
		$f = __METHOD__;
		$print = false;
		if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
			if ($print) {
				Debug::print("{$f} origin header is set");
			}
			return $_SERVER['HTTP_ORIGIN'];
		} elseif (array_key_exists('HTTP_REFERER', $_SERVER)) {
			if ($print) {
				Debug::print("{$f} HTTP referrer header is set");
			}
			return $_SERVER['HTTP_REFERER'];
		} elseif ($print) {
			Debug::print("{$f} returning remote address");
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	public function setProgressiveHyperlinkFlag(bool $value = true):bool{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		return $this->setFlag(PROGRESSIVE_HYPERLINK_KEY, $value);
	}

	public function getProgressiveHyperlinkFlag():bool{
		return $this->getFlag(PROGRESSIVE_HYPERLINK_KEY);
	}

	/**
	 * rewrites $_GET superglobal and $this->requestURISegments to make them useful
	 */
	public function rewriteGetParameters(){
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::print("{$f} entered");
		}
		$segments = $this->getRawRequestURISegments();
		$count = count($segments);
		if(
			$count > 0 && 
			(
				false !== strpos($segments[$count - 1], '?') || 
				false !== strpos($segments[$count - 1], '=')
			)
		){
			if ($print) {
				Debug::print("{$f} there are GET parameters to rewrite");
			}
			$encoded_string = $segments[$count - 1];
			$segments = array_slice($segments, 0, count($segments) - 1, true);
			if (false !== strpos($encoded_string, '?')) {
				if ($print) {
					Debug::print("{$f} GET parameter 0 contains a ?");
				}
				$splat = explode('?', $encoded_string);
				if (! empty($splat[0])) {
					array_push($segments, $splat[0]);
				}
				if (! empty($splat[1])) {
					$encoded_string = $splat[1];
				} else {
					// Debug::error("{$f} impossible?");
					$encoded_string = null;
				}
				// $encoded_string = substr($encoded_string, 1);
			}
			$temp_get = [];
			parse_str($encoded_string, $temp_get);
			if ($print) {
				if(!empty($temp_get)){
					Debug::print("{$f} parsed the following GET parameters:");
					Debug::print($temp_get);
				}else{
					Debug::print("{$f} no parsed get parameters");
				}
			}
			$arr = [];
			foreach ($temp_get as $key => $value) {
				if ($key === PROGRESSIVE_HYPERLINK_KEY) {
					if ($print) {
						Debug::print("{$f} about to set progressive hyperlink flag");
					}
					$this->setProgressiveHyperlinkFlag(true);
					continue;
				}
				$arr[$key] = $value;
			}
			if(!empty($arr)){
				$_GET = $arr;
				if ($print) {
					Debug::print("{$f} GET params after rewrite:");
					Debug::printArray($_GET);
				}
			}
		} elseif ($print) {
			Debug::print("{$f} no GET parameters to rewrite");
		}
		$this->setRequestURISegments($segments);
	}

	public static final function getAsynchronousRequestMethod(){
		$f = __METHOD__;
		$print = false;
		if (! array_key_exists("HTTP_X_REQUESTED_WITH", $_SERVER)) {
			if ($print) {
				Debug::print("{$f} custom header was not sent");
			}
			return ASYNC_REQUEST_METHOD_NONE;
		}
		switch ($_SERVER['HTTP_X_REQUESTED_WITH']) {
			case 'XMLHttpRequest':
				if ($print) {
					Debug::print("{$f} XHR request method");
				}
				return ASYNC_REQUEST_METHOD_XHR;
			case 'fetch':
				if ($print) {
					Debug::print("{$f} this is a fetch request");
				}
				return ASYNC_REQUEST_METHOD_FETCH;
			default:
				if ($print) {
					Debug::print("{$f} no asynchronous request method");
				}
				return ASYNC_REQUEST_METHOD_NONE;
		}
	}

	public static function isXHREvent():bool{
		return static::getAsynchronousRequestMethod() === ASYNC_REQUEST_METHOD_XHR;
	}

	public static final function isFetchEvent():bool{
		return static::getAsynchronousRequestMethod() === ASYNC_REQUEST_METHOD_FETCH;
	}

	public function isCurlEvent():bool{
		$f = __METHOD__;
		try {
			$post = $this->getInputParameters();
			if ($post == null) {
				return false;
			}
			if (array_key_exists('curl', $post)) {
				return true;
			}
			return false;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function hasPHPInputFileContents():bool{
		return $this->isFetchEvent() && isset($this->PHPInputFileContents) && is_array($this->PHPInputFileContents) && ! empty($this->PHPInputFileContents);
	}

	protected function getPHPInputFileContents(){
		$f = __METHOD__;
		if (! $this->isFetchEvent()) {
			Debug::error("{$f} don't call this unless you're fetching something");
		} elseif ($this->hasPHPInputFileContents()) {
			return $this->PHPInputFileContents;
		}
		$arr = json_decode(file_get_contents('php://input'), true);
		if (empty($arr)) {
			Debug::printPost("{$f} don't call this unless there are arguments to return");
		}
		return $this->PHPInputFileContents = $arr;
	}

	public static function getHTTPRequestMethod(): string{
		if (isset($_POST) && is_array($_POST) && ! empty($_POST)) {
			return HTTP_REQUEST_METHOD_POST;
		}
		return HTTP_REQUEST_METHOD_GET;
	}

	public function getInputParameters(...$names){
		$f = __METHOD__;
		try {
			$print = false;
			$method = static::getAsynchronousRequestMethod();
			$arr = null;
			switch ($method) {
				case ASYNC_REQUEST_METHOD_FETCH:
					$arr = $this->getPHPInputFileContents();
					if ($print) {
						Debug::print("{$f} this is a fetch event with the following parameters:");
						Debug::printArray($arr);
					}
					break;
				case ASYNC_REQUEST_METHOD_XHR:
					if ($print) {
						Debug::print("{$f} this is an XHR event");
					}
				default:
					if (! empty($_POST)) {
						if ($print) {
							Debug::print("{$f} post is non-empty, let's assume it contains the requested input");
						}
						$arr = $_POST;
						break;
					} elseif (! empty($_GET)) {
						if ($print) {
							Debug::print("{$f} GET is non-empty, this is our last fallback");
							// Debug::printArray($_GET);
						}

						$arr = $_GET;
						break;
					} else {
						if ($print) {
							Debug::print("{$f} oh well");
						}
						return null;
					}
			}
			if (! isset($names) || ! is_array($names) || empty($names)) {
				return $arr;
			}
			$ret = [];
			foreach ($names as $name) {
				if (array_key_exists($name, $arr)) {
					$ret[$name] = $arr[$name];
				} else {
					$ret[$name] = null;
				}
			}
			unset($arr);
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function configureAsynchronousRequestMethodFlags(){
		$f = __METHOD__;
		try {
			$ajax = static::getAsynchronousRequestMethod();
			if ($ajax === ASYNC_REQUEST_METHOD_FETCH) {
				$_POST['sw'] = 1;
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasInputParameter(string $name, ProgramFlowControlUnit $use_case = null):bool{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if ($use_case instanceof ProgramFlowControlUnit) {
			if($print){
				Debug::print("{$f} received a use case input parameter");
			}
			if ($use_case->hasImplicitParameter($name)) {
				return true;
			} elseif ($use_case->URISegmentParameterExists($name)) {
				return true;
			}elseif($print){
				Debug::print("{$f} {$name} is not an implicit or segmented parameter for this use case");
			}
		}elseif($print){
			Debug::printStackTraceNoExit("{$f} did not receive a use case parameter when checking for parameter \"{$name}\"");
		}
		$post = $this->getInputParameters();
		if($print){
			Debug::print("{$f} about to check the following post/get parameters:");
			Debug::print(json_encode($post));
		}
		return is_array($post) && array_key_exists($name, $post) && $post[$name] !== null && $post[$name] !== "";
	}

	public function hasInputParameters(...$names):bool{
		$f = __METHOD__;
		if (empty($names)) {
			Debug::error("{$f} received no input parameter names");
		}
		foreach ($names as $name) {
			if (! $this->hasInputParameter($name)) {
				return false;
			}
		}
		return true;
	}

	public function getInputParameter(string $name, ProgramFlowControlUnit $use_case = null){
		$f = __METHOD__;
		$print = false;
		if ($print) {
			if ($use_case instanceof ProgramFlowControlUnit) {
				Debug::print("{$f} received a use case parameter");
			} else {
				Debug::print("{$f} did not receive a use case parameter");
			}
		}
		if ($use_case instanceof ProgramFlowControlUnit) {
			if($print){
				Debug::print("{$f} use case is a use case");
			}
			if ($use_case->hasImplicitParameter($name)) {
				if ($print) {
					Debug::print("{$f} use case has an implicit parameter \"{$name}\"");
				}
				return $use_case->getImplicitParameter($name);
			} elseif ($use_case->URISegmentParameterExists($name)) {
				if ($print) {
					Debug::print("{$f} use case explicitly maps input parameter");
				}
				return $this->getRequestURISegment(
					array_search($name, $use_case->getUriSegmentParameterMap())
				);
			}elseif($print){
				Debug::print("{$f} use case lacks an implicit input parameter {$name}, nor does a URI segment exist for it");
			}
		}elseif($print){
			$gottype = is_object($use_case) ? get_short_class($use_case) : gettype($use_case);
			Debug::print("{$f} use case parameter is a \"{$gottype}\"");
		}
		//
		if (! $this->hasInputParameter($name)) {
			Debug::warning("{$f} input parameter \"{$name}\" does not exist");
			if (! empty($_POST)) {
				Debug::printPost();
			} else {
				Debug::printStackTrace();
			}
		}
		return $this->getInputParameters()[$name];
	}

	public function setInputParameter($key, $value){
		$f = __METHOD__;
		$method = static::getAsynchronousRequestMethod();
		if ($method === ASYNC_REQUEST_METHOD_FETCH) {
			if (! $this->hasPHPFileInputContents()) {
				Debug::error("{$f} PHP file input contents are undefined");
			}
			return $this->PHPInputFileContents[$key] = $value;
		} elseif (! empty($_POST)) {
			// Debug::print("{$f} post is non-empty, let's assume it contains the requested input");
			return $_POST[$key] = $value;
		} elseif (! empty($_GET)) {
			// Debug::print("{$f} GET is non-empty, this is our last fallback");
			// Debug::printArray($_GET);
			return $_GET[$key] = $value;
		}
		Debug::error("{$f} oh well");
	}

	private static function emptyFiles($unfixed_arr):bool{
		return empty($unfixed_arr['name']) && empty($unfixed_arr['type']) && empty($unfixed_arr['tmp_name']) && $unfixed_arr['error'] === 4 && $unfixed_arr['size'] === 0;
	}

	public static function repackIncomingFiles(?array $unfixed_arr): ?array{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			$fixed_arr = [];
			if (! array_key_exists('name', $unfixed_arr)) {
				if ($print) {
					Debug::print("{$f} unfixed array does not have a name index; calling recursively");
				}
				foreach (array_keys($unfixed_arr) as $fk) {
					$temp_arr2 = static::repackIncomingFiles($unfixed_arr[$fk]);
					if (empty($temp_arr2)) {
						if ($print) {
							Debug::print("{$f} recursive call for index \"{$fk}\" is empty");
						}
						continue;
					}
					$fixed_arr[$fk] = $temp_arr2;
				}
				if ($print) {
					Debug::print("{$f} returning on line 467 with the following array:");
					Debug::printArray($fixed_arr);
				}
				return $fixed_arr;
			} elseif (! is_array($unfixed_arr['name'])) {
				if (static::emptyFiles($unfixed_arr)) {
					if ($print) {
						Debug::print("{$f} nothing was uploaded you twit");
					}
					return null;
				} elseif ($print) {
					Debug::print("{$f} name is not an array, assuming it's a string literal");
				}
				return [
					0 => new UploadedFile($unfixed_arr)
				];
			} elseif ($print) {
				Debug::print("{$f} neither of the above (line 482)");
			}
			foreach (array_keys($unfixed_arr['name']) as $i) {
				$temp_arr = [
					'name' => $unfixed_arr['name'][$i],
					'type' => $unfixed_arr['type'][$i],
					'tmp_name' => $unfixed_arr['tmp_name'][$i],
					'error' => $unfixed_arr['error'][$i],
					'size' => $unfixed_arr['size'][$i]
				];
				if (static::emptyFiles($temp_arr)) {
					if ($print) {
						Debug::print("{$f} array is multidimensional, and nothing was uploaded");
					}
					continue;
				}
				if (is_int($i)) {
					if ($print) {
						Debug::print("{$f} key \"{$i}\" is an integer");
					}
					$fixed_arr[$i] = new UploadedFile($temp_arr);
				} else {
					if ($print) {
						Debug::print("{$f} key is not an integer -- array goes deeper");
					}
					$temp_arr2 = static::repackIncomingFiles($temp_arr);
					if ($print) {
						Debug::print("{$f} returned from recursive function call");
					}
					if (! empty($temp_arr2)) {
						$fixed_arr[$i] = $temp_arr2;
					}
				}
			}
			if (empty($fixed_arr)) {
				if ($print) {
					Debug::print("{$f} fixed array is empty -- returning null");
				}
				return null;
			} elseif ($print) {
				Debug::print("{$f} returning the following array:");
				Debug::printArray($fixed_arr);
			}
			return $fixed_arr;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setRepackedIncomingFiles($files){
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::print("{$f} setting the following repacked incoming files");
			Debug::print($files);
		}
		return $this->setArrayProperty("repackedIncomingFiles", $files);
	}

	public function hasRepackedIncomingFiles():bool{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			if ($this->hasArrayProperty("repackedIncomingFiles")) {
				Debug::print("{$f} yes, there are repacked incoming files");
			} else {
				Debug::print("{$f} no, there are no repacked incoming files");
			}
		}
		return $this->hasArrayProperty("repackedIncomingFiles");
	}

	/**
	 *
	 * @return UploadedFile[]
	 */
	public function getRepackedIncomingFiles(){
		return $this->getProperty("repackedIncomingFiles");
	}

	public static function isAjaxRequest():bool{
		return static::getAsynchronousRequestMethod() !== ASYNC_REQUEST_METHOD_NONE;
	}

	public function getDirective():string{
		$f = __METHOD__;
		try {
			$print = false;
			if (! $this->hasDirective()) {
				if ($this->hasInputParameter("directive")) {
					$directive = $this->getInputParameter("directive");
					if (is_array($directive)) {
						if (count($directive) !== 1) {
							Debug::error("{$f} count(directive) !== 1");
						}
						$directive = array_keys($directive)[0];
					} elseif ($print) {
						Debug::print("{$f} directive is not an array");
					}
					$directives = mods()->getValidDirectives();
					if (! in_array($directive, $directives, true)) {
						Debug::warning("{$f} invalid directive \"{$directive}\"");
						Debug::printArray($this->getInputParameters(null));
						return $this->setDirective(DIRECTIVE_ERROR);
					} elseif ($print) {
						Debug::print("{$f} returning \"{$directive}\"");
					}
					return $this->setDirective($directive);
				} elseif ($print) {
					Debug::print("{$f} no server command");
					$post = getInputParameters();
					if (is_array($post)) {
						Debug::printArray($post);
					}
				}
				return $this->setDirective(DIRECTIVE_NONE);
			}
			return $this->directive;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasDirective():bool{
		return isset($this->directive);
	}

	protected function setDirective($command){
		return $this->directive = $command;
	}

	public function getRequestURISegments(){
		$f = __METHOD__;
		if (! $this->hasRequestURISegments()) {
			Debug::error("{$f} request URI segments undefined");
		}
		return $this->requestURISegments;
	}

	public static function getRawRequestURISegments(){
		$f = __METHOD__;
		$print = false;
		$uri = $_SERVER['REQUEST_URI'];
		if ($print) {
			Debug::print("{$f} raw request URI is \"{$uri}\"");
		}
		return explode('/', trim($uri, '/'));
	}

	public function hasRequestURISegments():bool{
		return isset($this->requestURISegments) && is_array($this->requestURISegments);
	}

	public function setRequestURISegments($segments){
		return $this->requestURISegments = $segments;
	}

	public function getRequestURISegment($i){
		$f = __METHOD__;
		if (! $this->hasRequestURISegments()) {
			Debug::error("{$f} request URI segments are undefined");
		} elseif (! array_key_exists($i, $this->requestURISegments)) {
			Debug::error("{$f} request URI does not have a segment {$i}");
		}
		$segments = $this->getRequestURISegments();
		return $segments[$i];
	}
}
