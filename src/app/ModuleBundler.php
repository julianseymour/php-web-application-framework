<?php
namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\config;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class ModuleBundler extends Basic
{

	/**
	 * array of modules on which this application depends
	 *
	 * @var Module[]
	 */
	protected $modules;

	public function getValidMimeTypes(): ?array
	{
		return $this->bundle("getValidMimeTypes");
	}

	public function getValidDirectives(): ?array
	{
		return $this->bundle("getValidDirectives");
	}

	public function getWidgetClasses(): ?array
	{
		$f = __METHOD__; //ModuleBundler::getShortClass() . "(" . static::getShortClass() . ")->getWidgetClasses()";
		$print = false;
		$ret = $this->bundle("getWidgetClasses");
		if ($print) {
			Debug::print("{$f} returning the following:");
			Debug::printArray($ret);
		}
		return $ret;
	}

	public function getMiscellaneousClasses(): array
	{
		return $this->bundle("getMiscellaneousClasses");
	}

	public function getInvokeableJavaScriptFunctions(): ?array
	{
		return $this->bundle("getInvokeableJavaScriptFunctions");
	}

	public function getTemplateElementClasses(): ?array
	{
		return $this->bundle("getTemplateElementClasses");
	}

	public function getFormDataSubmissionClasses(): ?array
	{
		return $this->bundle("getFormDataSubmissionClasses");
	}

	public function getClientConstants(): ?array{
		$f = __METHOD__;
		$define_us = [];
		foreach ($this->getModules() as $mod) {
			$paths = $mod->getClientConstants();
			if (! is_associative($paths)) {
				foreach ($paths as $string) {
					$define_us[$string] = $string;
				}
			} else {
				$define_us = array_merge($define_us, $paths);
			}
		}
		return $define_us;
	}

	public function getClientDataStructureClasses(): array
	{
		return $this->bundle("getClientDataStructureClasses");
	}

	public function getServiceWorkerDependencyFilePaths(): ?array
	{
		return $this->bundle("getServiceWorkerDependencyFilePaths");
	}

	public function getDebugJavaScriptFilePaths(): ?array
	{
		return $this->bundle("getDebugJavaScriptFilePaths");
	}

	public function getUseCaseDictionary(): ?array{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			$use_cases = $this->bundle("getUseCaseDictionary");
			Debug::print("{$f} this application has the following URIs:");
			Debug::printArray($use_cases);
			return $use_cases;
		}
		return $this->bundle("getUseCaseDictionary");
	}

	public function getJavaScriptFilePaths(): ?array{
		return $this->bundle("getJavaScriptFilePaths");
	}

	public function getUserClasses():?array{
		$f = __METHOD__;
		$ds_classes = $this->getDataStructureClasses();
		if(!is_array($ds_classes)){
			Debug::error("{$f} getDataStructureClasses returned something other than an array");
		}
		return $ds_classes[DATATYPE_USER];
	}

	public function getCascadingStyleSheetFilePaths(): ?array{
		return $this->bundle("getCascadingStyleSheetFilePaths");
	}

	public function getPhpFileInclusionPaths(): ?array{
		return $this->bundle("getPhpFileInclusionPaths");
	}

	public function getStoredRoutines(): ?array{
		return $this->bundle("getStoredRoutines");
	}

	public function getSpecialTemplateClasses(): array{
		return $this->bundle("getSpecialTemplateClasses");
	}

	public function getForeignDataStructureSharingClasses(string $shared_class):?array{
		$f = __METHOD__;
		Debug::error("{$f} redeclare this please");
	}

	public final function getJavaScriptFilenames(): ?array{
		return $this->getJavaScriptFilePaths();
	}

	public final function getDebugJavaScriptFilenames(): ?array{
		return $this->getDebugJavaScriptFilePaths();
	}

	public final function getServiceWorkerDependencyFilenames(): ?array
	{
		return $this->getServiceWorkerDependencyFilePaths();
	}

	public final function getModuleCount(): int
	{
		return $this->hasModules() ? count($this->modules) : 0;
	}

	public final function getUseCaseFromAction($action): string
	{
		$f = __METHOD__; //ModuleBundler::getShortClass() . "(" . static::getShortClass() . ")->getUseCaseFromAction()";
		$print = false;
		if ($print) {
			Debug::print("{$f} action is \"{$action}\"");
		}
		$use_case_classes = $this->getUseCaseDictionary();
		if (array_key_exists($action, $use_case_classes)) {
			return $use_case_classes[$action];
		}
		return \JulianSeymour\PHPWebApplicationFramework\error\FileNotFoundUseCase::class;
	}

	public final function getDataStructureClasses(): ?array
	{
		$f = __METHOD__; //ModuleBundler::getShortClass() . "(" . static::getShortClass() . ")->getDataStructureClasses()";
		try{
			$print = false;
			$cache = false;
			$key = "getDataStructureClasses";
			if (cache()->enabled()) {
				if (cache()->hasAPCu($key)) {
					return cache()->getAPCu($key);
				}
				$cache = true;
			}
			$dsc = [];
			$mods = $this->getModules();
			if(empty($mods)){
				if($print){
					Debug::print("{$f} no modules");
				}
				return [];
			}
			foreach($mods as $mod){
				$dscdm = $mod->getDataStructureClasses();
				if(empty($dscdm)){
					if($print){
						Debug::print("{$f} no data structure classes for module ".get_short_class($mod));
					}
					continue;
				}
				foreach ($dscdm as $value) {
					if ($print) {
						Debug::print($value);
					}
					if (is_array($value)) {
						$sub_arr = [];
						foreach ($value as $subclass) {
							$sub_arr[$subclass::getSubtypeStatic()] = $subclass;
						}
						$dsc[$subclass::getDataType()] = $sub_arr;
					} else {
						if($value::getDataType() === DATATYPE_USER){
							Debug::error("{$f} gothca, module name is ".get_short_class($mod));
						}
						$dsc[$value::getDataType()] = $value;
					}
				}
			}
			if ($cache) {
				cache()->setAPCu($key, $dsc, time() + 30 * 60);
			}
			return $dsc;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getClientCommandClasses(): ?array
	{
		return $this->bundle("getClientCommandClasses");
	}

	public function getValidatorClass(string $className): string
	{
		$f = __METHOD__; //ModuleBundler::getShortClass() . "(" . static::getShortClass() . ")->" . __METHOD__ . "()";
		$validatorClasses = $this->getValidatorClasses();
		if (false === array_key_exists($className, $validatorClasses)) {
			Debug::error("{$f} validator class is undefined");
			return \JulianSeymour\PHPWebApplicationFramework\validate\ImpossibleValidator::class();
		}
		return $validatorClasses[$className];
	}

	public function getValidatorClasses():?array{
		$f = __METHOD__; //ModuleBundler::getShortClass() . "(" . static::getShortClass() . ")->getValidatorClasses()";
		$print = false;
		$cache = false;
		$key = "getValidatorClasses";
		if (cache()->enabled()) {
			if (cache()->hasAPCu($key)) {
				return cache()->getAPCu($key);
			}
			$cache = true;
		}
		$ret = [];
		$mods = $this->getModules();
		if (! empty($mods)) {
			foreach ($mods as $mod) {
				$validator_classes = $mod->getValidatorClasses();
				if(empty($validator_classes)){
					continue;
				}else foreach($validator_classes as $vc){
					$ret[get_short_class($vc)] = $vc;
				}
			}
			if ($cache) {
				cache()->setAPCu($key, $ret, time() + 30 * 60);
			}
		} elseif ($print) {
			Debug::print("{$f} no data modules");
		}
		return $ret;
	}

	public function getClientRenderedFormClasses(): ?array
	{
		return $this->bundle("getClientRenderedFormClasses");
	}

	public function hasModules()
	{
		return isset($this->modules) && is_array($this->modules) && ! empty($this->modules);
	}

	public function getModules(){
		$f = __METHOD__;
		$print = false;
		if ($this->hasModules()) {
			if ($print) {
				$count = count($this->modules);
				Debug::print("{$f} returning {$count} modules");
				foreach ($this->modules as $mod) {
					Debug::print(get_class($mod));
				}
			}
			return $this->modules;
		}
		return [];
	}

	public function load(?array $mc): ?array{
		if (isset($mc) && is_array($mc) && ! empty($mc)) {
			$mods = [];
			foreach ($mc as $class) {
				$mod = new $class();
				array_push($mods, $mod);
			}
			return $this->modules = $mods;
		}
		return [];
	}

	public final function getUserClass(string $account_type): ?string{
		$f = __METHOD__;
		$type_classes = $this->getUserClasses();
		if(! array_key_exists($account_type, $type_classes)){
			Debug::warning("{$f} invalid user account type \"{$account_type}\"");
			Debug::printArray($type_classes);
			Debug::printStackTrace();
		}
		return $type_classes[$account_type];
	}

	public final function getTypedNotificationClasses(): ?array{
		$f = __METHOD__;
		$cache = false;
		$key = "getTypedNotificationClasses";
		if (cache()->enabled()) {
			if (cache()->hasAPCu($key)) {
				return cache()->getAPCu($key);
			}
			$cache = true;
		}
		$ret = [];
		$mods = $this->getModules();
		foreach ($mods as $mod) {
			$types = $mod->getTypedNotificationClasses();
			foreach($types as $class){
				$ret[$class::getNotificationTypeStatic()] = $class;
			}
		}
		if ($cache) {
			cache()->setAPCu($key, $ret, time() + 30 * 60);
		}
		return $ret;
	}

	public final function getDataStructureClass($type, $subtype = null): ?string{
		$f = __METHOD__;
		$classes = $this->getDataStructureClasses();
		if (! array_key_exists($type, $classes)) {
			Debug::error("{$f} this application doesn't know about a datatype \"{$type}\"");
		}
		$class = $classes[$type];
		if (! is_array($class)) {
			return $class;
		} elseif (! array_key_exists($subtype, $class)) {
			Debug::error("{$f} this application doesn't know about a datatype \"{$type}\" with subtype \"{$subtype}\"");
		}
		return $class[$subtype];
	}

	public final function getTypedNotificationClass($type): ?string
	{
		$f = __METHOD__; //ModuleBundler::getShortClass() . "(" . static::getShortClass() . ")->getTypedNotificationClass()";
		$classes = $this->getTypedNotificationClasses();
		if (! array_key_exists($type, $classes)) {
			Debug::error("{$f} invalid notification type \"{$type}\"");
		}
		return $classes[$type];
	}

	public function getConditionalWidgetClasses(?UseCase $use_case = null): array{
		$f = __METHOD__;
		$print = false;
		$ret = [];
		foreach ($this->getWidgetClasses() as $wc) {
			if ($wc::meetsDisplayCriteria($use_case)) {
				array_push($ret, $wc);
			}
		}
		if($print){
			Debug::print("{$f} returning the following:");
			Debug::printArray($ret);
		}
		return $ret;
	}

	public function getClientUseCaseDictionary(): array{
		return $this->bundle("getClientUseCaseDictionary");
	}

	public function getJavaScriptFunctionGeneratorClasses(): array{
		return $this->bundle("getJavaScriptFunctionGeneratorClasses");
	}

	public function getMessageEventHandlerCases(): array{
		return $this->bundle("getMessageEventHandlerCases");
	}

	public function getLegalIntersectionObservers(): array{
		return $this->bundle("getLegalIntersectionObservers");
	}

	public function bundle(string $bundle_name): array{
		$f = __METHOD__;
		$print = false;
		//Debug::limitExecutionDepth();
		//Debug::checkMemoryUsage("bundle", 96000000);
		if (! config()->isLegalBundleName($bundle_name)) {
			Debug::error("{$f} \"{$bundle_name}\" is not legal");
		} elseif ($print) {
			Debug::print("{$f} bundling \"{$bundle_name}\"");
		}
		$cache = false;
		if (cache()->enabled()) {
			if (cache()->hasAPCu($bundle_name)) {
				if ($print) {
					Debug::print("{$f} cache hit for \"{$bundle_name}\"");
				}
				return cache()->getAPCu($bundle_name);
			} else {
				if ($print) {
					Debug::print("{$f} cache miss for \"{$bundle_name}\"");
				}
				$cache = true;
			}
		} elseif ($print) {
			Debug::print("{$f} cache is disabled");
		}
		$bundle = [];
		$mods = $this->getModules();
		if (empty($mods)) {
			if ($print) {
				Debug::print("{$f} there are no modules");
			}
			return [];
		}
		foreach ($mods as $mod) {
			if ($print) {
				$bundle2 = $mod->$bundle_name();
				Debug::print("{$f} bundle from module " . get_class($mod));
				Debug::printArray($bundle2);
				$bundle = array_merge_recursive($bundle, $bundle2);
			} else {
				$bundle = array_merge_recursive($bundle, $mod->$bundle_name());
			}
		}
		if ($cache) {
			if ($print) {
				Debug::print("{$f} updating cache for \"{$bundle_name}\" with the following:");
				//Debug::print($bundle);
			}
			cache()->setAPCu($bundle_name, $bundle, time() + 30 * 60);
		}
		if ($print) {
			Debug::print("{$f} returning the following array:");
			Debug::printArray($bundle);
		}
		return $bundle;
	}

	public function getUniversalFormClasses(): ?array
	{
		return $this->bundle("getUniversalFormClasses");
	}

	public function getGrantArray(): ?array
	{
		$grants = [];
		foreach ($this->getModules() as $mod) {
			$arr = $mod->getGrantArray();
			foreach ($arr as $name => $tablegrants) {
				if (! array_key_exists($name, $grants)) {
					$grants[$name] = $tablegrants;
				} else
					foreach ($tablegrants as $table => $add_us) {
						if (! array_key_exists($table, $grants[$name])) {
							$grants[$name][$table] = $add_us;
						} else
							foreach ($add_us as $add_me) {
								if (false === array_search($add_me, $grants[$name][$table])) {
									array_push($grants[$name][$table], $add_me);
								}
							}
					}
			}
		}
		return $grants;
	}

	public final function getPollingUseCaseClasses(): ?array
	{
		return $this->bundle("getPollingUseCaseClasses");
	}
}
