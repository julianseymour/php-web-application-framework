<?php
namespace JulianSeymour\PHPWebApplicationFramework\script;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\ends_with;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\php2string;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\str_contains;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\app\generator\ApplicationJavaScriptGenerator;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\debug\LogCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\file\BundleUseCase;
use JulianSeymour\PHPWebApplicationFramework\template\TemplateContextInterface;
use Exception;

class JavaScriptBundleUseCase extends BundleUseCase{

	public function echoResponse(): void{
		$f = __METHOD__;
		try {
			$print = false;
			$optimize = true; // set to false to allow unbundled files
			$request = request();
			if(!$request->hasInputParameter("filename", $this)){
				parent::echoResponse();
				return;
			}
			$filename = $request->getInputParameter("filename", $this);
			/*if (! ends_with($filename, ".js")) {
				Debug::error("{$f} user is not requesting a javascript file");
			} elseif (starts_with($filename, "strings") && str_contains($filename, "-")) {
				$filename_noext = explode(".", $filename)[0];
				if ($print) {
					Debug::print("{$f} filename without extension is \"{$filename_noext}\"");
				}
				$splat = explode('-', $filename_noext);
				$lang = $splat[1];
				if (! config()->isLanguageSupported($lang)) {
					Debug::error("{$f} language \"{$lang}\" is not supported");
				}
				$filename_nolang = $splat[0];
				$cache = false;
				if (cache()->enabled() && JAVASCRIPT_CACHE_ENABLED) {
					if (cache()->hasFile("{$filename_nolang}_{$lang}.js")) {
						if ($print) {
							Debug::print("{$f} cache hit for {$filename}");
						}
						echo cache()->getFile("{$filename_nolang}_{$lang}.js");
						return;
					} else {
						if ($print) {
							Debug::print("{$f} cache miss for {$filename}");
						}
						$cache = true;
					}
				} elseif ($print) {
					Debug::print("{$f} cache is disabled");
				}
				app()->setLanguageOverride($lang);
				$class = JavaScriptStringTable::generateJavascriptClass()->__toString();
				if ($cache) {
					cache()->setFile("{$filename_nolang}_{$lang}.js", $class, time() + 30 * 60);
				}
				echo $class;
				unset($class);
				return;
			} elseif ($print) {
				Debug::print("{$f} returning generated javascript for {$filename}");
			}*/

			$cache = false;
			if (cache()->enabled() && JAVASCRIPT_CACHE_ENABLED) {
				if (cache()->hasFile($filename)) {
					if ($print) {
						Debug::print("{$f} cache hit for {$filename}");
					}
					echo cache()->getFile($filename);
					return;
				} else {
					if ($print) {
						Debug::print("{$f} cache miss for {$filename}");
					}
					$cache = true;
				}
			} elseif ($print) {
				Debug::print("{$f} cache is disabled");
			}

			switch ($filename) {
				case "application.js":
					if ($optimize) {
						Debug::error("{$f} requesting unbundled file \"{$filename}\" is not allowed");
					} elseif ($cache) {
						ob_start();
					}
					echo ApplicationJavaScriptGenerator::generateJavaScriptClass(mods());
					echo ApplicationJavaScriptGenerator::generateClassReturningFunction(mods());
					break;
				case 'async.js':
				case "commands.js":
				case "debug.js":
				case 'locutus.js':
				case 'notifications.js':
				case 'validators.js':
					if ($optimize) {
						Debug::error("{$f} requesting unbundled file \"{$filename}\" is not allowed");
					}
				case 'sw_bundle.js':
					if ($print) {
						Debug::print("{$f} about to echo javascript bundle");
					}
					if ($cache) {
						ob_start();
					}
					echo "\n//constants:\n";
					$this->echoApplicationClassName();
					$this->echoJavaScriptConstants();
					echo "\n//bundled hard coded files:\n";
					$this->echoJavaScriptBundle();
					echo "\n//form data submission functions:\n";
					$this->echoFormDataSubmissionFunctions();
					echo "\n//application class:\n";
					echo ApplicationJavaScriptGenerator::generateJavaScriptClass(mods());
					echo "\n//applications class returning function:\n";
					echo ApplicationJavaScriptGenerator::generateClassReturningFunction(mods());
					break;
				case "bundle.js":
					if ($cache) {
						ob_start();
					}
					echo "\n//constants:\n";
					$this->echoApplicationClassName();
					$this->echoJavaScriptConstants();
					echo "\n" . new LogCommand("/constants");
					echo "\n//application class:\n";
					echo ApplicationJavaScriptGenerator::generateJavaScriptClass(mods());
					echo "\n" . new LogCommand("/application class");
					echo "\n//applications class returning function:\n";
					echo ApplicationJavaScriptGenerator::generateClassReturningFunction(mods());
					echo "\n" . new LogCommand("/function for returning application class");
					echo "\n//bundled hard coded files:\n";
					$this->echoJavaScriptBundle();
					echo "\n" . new LogCommand("/bundled handwritten files");
					echo "\n//form data submission functions:\n";
					$this->echoFormDataSubmissionFunctions();
					echo "\n" . new LogCommand("/formdata submission functions");
					echo "\n//template functions:\n";
					$this->echoTemplateFunctions();
					echo "\n" . new LogCommand("/template functions");
					echo "\n//invokeable functions:\n";
					$this->echoInvokeableFunctionsConstant();
					echo "\n" . new LogCommand("/invokeable functions");
					echo "\n//legal intersection observers\n";
					$this->echoLegalIntersectionObserversConstant();
					echo "\n" . new LogCommand("/legal intersection observers");
					echo "\n//widget labels IDs:\n";
					$this->echoWidgetLabelIds();
					echo "\n" . new LogCommand("/widget label IDs");
					break;
				case "const.js":
					if ($optimize) {
						Debug::error("{$f} requesting unbundled file \"{$filename}\" is not allowed");
					} elseif ($print) {
						Debug::print("{$f} about to echo javascript constants");
					}
					if ($cache) {
						ob_start();
					}
					$this->echoJavaScriptConstants();
					break;
				case "formdata.js":
					if ($optimize) {
						Debug::error("{$f} requesting unbundled file \"{$filename}\" is not allowed");
					} elseif ($print) {
						Debug::print("{$f} about to echo FormData submission functions");
					}
					if ($cache) {
						ob_start();
					}
					$this->echoFormDataSubmissionFunctions();
					break;
				case 'invokeable.js':
					if ($cache) {
						ob_start();
					}
					$this->echoInvokeableFunctionsConstant();
					break;
				case "templates.js":
					if ($print) {
						Debug::print("{$f} about to echo template functions");
					}
					if ($cache) {
						ob_start();
					}
					$this->echoTemplateFunctions();
					break;
				default:
					Debug::error("{$f} invalid request segment \"{$filename}\"");
			}
			if ($cache) {
				if ($print) {
					Debug::print("{$f} updating cache for file \"{$filename}\"");
				}
				$string = ob_get_clean();
				cache()->setFile($filename, $string, time() + 30 * 60);
				echo $string;
				unset($string);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function echoApplicationClassName(): void{
		echo CommandBuilder::const('APPLICATION_CONFIG_CLASS_NAME', get_short_class(config()))->toJavaScript() . ";\n";
	}

	public function getUriSegmentParameterMap(): ?array{
		return [
			"action",
			"filename"
		];
	}

	protected function echoInvokeableFunctionsConstant(){
		$f = __METHOD__;
		$const = DeclareVariableCommand::const("invokable");
		// $const->setScopeType("const");
		$const->setEscapeType(ESCAPE_TYPE_OBJECT);
		$temp = mods()->getInvokeableJavaScriptFunctions();
		$values = [];
		foreach ($temp as $key => $value) {
			$values[$key] = new GetDeclaredVariableCommand($value);
		}
		$const->setValue($values);
		echo $const->toJavaScript();
	}

	protected function echoLegalIntersectionObserversConstant(){
		$const = DeclareVariableCommand::const("legalIntersectionObservers");
		$const->setEscapeType(ESCAPE_TYPE_OBJECT);
		$temp = mods()->getLegalIntersectionObservers();
		$values = [];
		foreach ($temp as $key => $value) {
			$values[$key] = new GetDeclaredVariableCommand($value);
		}
		$const->setValue($values);
		echo $const->toJavaScript();
	}

	protected function echoBindElementFunctionsConstant(){
		$f = __METHOD__;
		$print = false;
		$const = DeclareVariableCommand::const("bindElementFunctions");
		$const->setEscapeType(ESCAPE_TYPE_OBJECT);
		$arr = [];
		foreach (mods()->getTemplateElementClasses() as $class) {
			if ($print) {
				Debug::print("{$f} template element class \"{$class}\"");
			}
			$short = get_short_class($class);
			$arr[$short] = new GetDeclaredVariableCommand("bind{$short}");
		}
		$const->setValue($arr);
		echo $const->toJavaScript();
	}

	public function echoJavaScriptBundle(){
		$f = __METHOD__; 
		$scripts = $this->getBundledFilenames();
		foreach ($scripts as $s) {
			echo "//{$s}:\n";
			echo php2string($s) . "\n";
		}
	}

	public function sendHeaders(Request $request): bool{
		$request = request();
		if(!$request->hasInputParameter("filename", $this)){
			$this->setObjectstatus(ERROR_FILE_NOT_FOUND);
			return parent::sendHeaders($request);
		}
		header("Content-Type: application/x-javascript; charset=utf-8", true);
		header("X-Content-Type-Options: nosniff");
		$newest = $this->getLastModifiedTimestamp();
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $newest) . " GMT");
		return false;
	}

	public function getActionAttribute(): ?string{
		return "/script";
	}

	protected function getBundledFilenames(?string $filename = null){
		$f = __METHOD__;
		try {
			$print = false;
			if ($filename === null && $this->hasBundledFilenames()) {
				if ($print) {
					Debug::print("{$f} bundled filenames are already defined");
				}
				return $this->bundledFilenames;
			} elseif ($print) {
				Debug::print("{$f} bundled filenames are not already defined -- generating array now");
			}
			if ($filename === null) {
				$request = request();
				if(!$request->hasInputParameter("filename", $this)){
					return null;
				}
				$filename = $request->getInputParameter("filename", $this);
			}
			if (! str_contains($filename, ".js")) {
				Debug::error("{$f} user is not requesting a javascript file");
			}
			// script filenames starting with "strings" and containing - are reserved for localized string tables e.g. strings-en.js
			/*if (starts_with($filename, "strings") && str_contains($filename, "-")) {
				return $this->setBundledFilenames([
					FRAMEWORK_INSTALL_DIRECTORY . '/language/StringTable.php'
				]);
			}*/
			switch ($filename) {
				case 'application.js':
					$dir = APPLICATION_CONFIG_DIRECTORY;
					$class = APPLICATION_CONFIG_CLASS_NAME;
					$filenames = [
						"{$dir}/{$class}.php"
					];
					break;
				case 'async.js':
					$filenames = [
						FRAMEWORK_INSTALL_DIRECTORY . '/common/async.js'
					];
					break;
				case "bundle.js":
					$filenames = $this->getJavaScriptFilenames();
					$filenames = array_merge($filenames, $this->getBundledFilenames('async.js'), $this->getBundledFilenames('commands.js'), $this->getBundledFilenames('debug.js'), $this->getBundledFilenames('locutus.js'), $this->getBundledFilenames("notifications.js"), $this->getBundledFilenames('validators.js'));
					if ($print) {
						Debug::print("{$f} returning the following filenames:");
						Debug::printArray($filenames);
					}
					break;
				case "commands.js":
					$filenames = [];
					foreach (mods()->getClientCommandClasses() as $ccc) {
						array_push($filenames, $ccc::getJavaScriptClassPath());
					}
					if ($print) {
						Debug::print("{$f} returning the following:");
						Debug::printArray($filenames);
					}
					break;
				case "const.js":
					$filenames = [
						__FILE__
					];
					break;
				case "debug.js":
					$filenames = $this->getDebugJavaScriptFilenames();
					break;
				case "formdata.js":
					$filenames = [];
					foreach (mods()->getFormDataSubmissionClasses() as $fdsc) {
						array_push($filenames, $fdsc::getJavaScriptClassPath());
					}
					break;
				case 'invokeable.js': // XXX figure out how this should be handled
					return [
						FRAMEWORK_INSTALL_DIRECTORY . '/app/ApplicationConfig.php'
					];
				case 'locutus.js':
					$filenames = [
						FRAMEWORK_INSTALL_DIRECTORY . '/common/locutus.js'
					];
					break;
				case 'notifications.js':
					$filenames = [];
					foreach (mods()->getTypedNotificationClasses() as $tnc) {
						if ($print) {
							Debug::print("{$f} {$tnc} has js class path " . $tnc::getJavaScriptClassPath());
						}
						array_push($filenames, $tnc::getJavaScriptClassPath());
					}
					break;
				case 'sw_bundle.js':
					$filenames = $this->getServiceWorkerDependencyFilenames();
					break;
				case "templates.js":
					$filenames = mods()->getTemplateElementFilenames();
					break;
				case 'validators.js':
					$filenames = [];
					foreach (mods()->getValidatorClasses() as $vc) {
						array_push($filenames, $vc::getJavaScriptClassPath());
					}
					break;
				default:
					Debug::error("{$f} invalid request segment \"{$filename}\"");
			}
			return $this->setBundledFilenames($filenames);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echoFormDataSubmissionFunctions(){
		$f = __METHOD__;
		try {
			if (! app()->hasUseCase()) {
				Debug::error("{$f} application runtime lacks a use case");
			}
			$mode = ALLOCATION_MODE_TEMPLATE;
			foreach (mods()->getFormDataSubmissionClasses() as $form_class) {
				$context_class = $form_class::getTemplateContextClass();
				$context = new $context_class();
				if ($context instanceof TemplateContextInterface) {
					$context->template();
				} else {
					Debug::error("{$f} class \"{$context_class}\" is not a TemplateContextInterface");
				}
				$form = new $form_class($mode);
				$form->bindContext($context);
				echo $form->generateFormDataSubmissionFunction()->toJavaScript();
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echoTemplateFunctions(){
		$f = __METHOD__;
		try {
			$print = false;
			$mode = ALLOCATION_MODE_TEMPLATE;
			foreach (mods()->getTemplateElementClasses() as $tec) {
				if (! class_exists($tec)) {
					Debug::error("{$f} element class \"{$tec}\" does not exist");
				} elseif ($print) {
					Debug::print("{$f} about to get template context class for element \"{$tec}\"");
				}
				$context_class = $tec::getTemplateContextClass();
				if (! class_exists($context_class)) {
					Debug::error("{$f} class \"{$context_class}\" does not exist");
				}
				$context = new $context_class();
				if ($context instanceof TemplateContextInterface) {
					$context->template();
				} elseif ($print) {
					Debug::print("{$f} class \"{$context_class}\" is not a TemplateContextInterface");
				}
				$element = new $tec($mode);
				$element->bindContext($context);
				if ($print) {
					Debug::print("{$f} about to call {$tec}->generateTemplateFunction()->toJavaScript()");
				}
				$function = $element->generateTemplateFunction();
				echo $function->toJavaScript();
			}
			$this->echoBindElementFunctionsConstant();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echoJavaScriptConstants(){
		$f = __METHOD__;
		try {
			foreach(mods()->getClientConstants() as $name => $value){
				$const = DeclareVariableCommand::const($name, $value);
				echo $const->toJavaScript().";\n";
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echoWidgetLabelIds(){
		$f = __METHOD__;
		try {
			$labels = [];
			foreach (mods()->getWidgetClasses() as $class) {
				$wid = $class::getWidgetLabelId();
				if ($wid == null) {
					continue;
				}
				array_push($labels, $wid);
			}
			$cmd = CommandBuilder::var("widgetLabelIds", $labels);
			echo $cmd->toJavaScript();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private function getServiceWorkerDependencyFilenames(){
		$f = __METHOD__;
		try {
			$scripts = mods()->getServiceWorkerDependencyFilenames();
			return $scripts;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private function getDebugJavaScriptFilenames(){
		$f = __METHOD__;
		try {
			$filenames = mods()->getDebugJavaScriptFilenames();
			return $filenames;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private function getJavaScriptFilenames(){
		$f = __METHOD__;
		try {
			$filenames = mods()->getJavaScriptFilenames();
			return $filenames;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}

