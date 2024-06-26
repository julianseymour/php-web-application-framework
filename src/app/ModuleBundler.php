<?php

namespace JulianSeymour\PHPWebApplicationFramework\app;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\is_associative;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class ModuleBundler extends Basic{

	/**
	 * array of modules on which this application depends
	 *
	 * @var Module[]
	 */
	protected $modules;

	public function getValidMimeTypes(): ?array{
		return $this->bundle("getValidMimeTypes");
	}

	public function getValidDirectives(): ?array{
		return $this->bundle("getValidDirectives");
	}

	public function getWidgetClasses(): ?array{
		$f = __METHOD__;
		$print = false;
		$ret = $this->bundle("getWidgetClasses");
		if($print){
			Debug::print("{$f} returning the following:");
			Debug::printArray($ret);
		}
		return $ret;
	}

	public function getMiscellaneousClasses(): array{
		return $this->bundle("getMiscellaneousClasses");
	}

	public function getInvokeableJavaScriptFunctions(): ?array{
		return $this->bundle("getInvokeableJavaScriptFunctions");
	}

	public function getTemplateElementClasses(): ?array{
		return $this->bundle("getTemplateElementClasses");
	}

	public function getFormDataSubmissionClasses(): ?array{
		return $this->bundle("getFormDataSubmissionClasses");
	}

	public function getClientConstants(): ?array{
		$f = __METHOD__;
		$define_us = [];
		foreach($this->getModules() as $mod){
			$paths = $mod->getClientConstants();
			if(!is_associative($paths)){
				foreach($paths as $string){
					$define_us[$string] = $string;
				}
			}else{
				$define_us = array_merge($define_us, $paths);
			}
		}
		return $define_us;
	}

	public function getClientDataStructureClasses(): array{
		return $this->bundle("getClientDataStructureClasses");
	}

	public function getServiceWorkerDependencyFilePaths(): ?array{
		return $this->bundle("getServiceWorkerDependencyFilePaths");
	}

	public function getDebugJavaScriptFilePaths(): ?array{
		return $this->bundle("getDebugJavaScriptFilePaths");
	}

	public function getUseCaseDictionary(): ?array{
		$f = __METHOD__;
		$print = false;
		if($print){
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
		$ds_classes = $this->getTypeSortedDataStructureClasses();
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

	public final function getModuleCount(): int{
		return $this->hasModules() ? count($this->modules) : 0;
	}

	public final function getUseCaseFromAction($action): string{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} action is \"{$action}\"");
		}
		$use_case_classes = $this->getUseCaseDictionary();
		if(array_key_exists($action, $use_case_classes)){
			return $use_case_classes[$action];
		}
		return \JulianSeymour\PHPWebApplicationFramework\error\FileNotFoundUseCase::class;
	}

	public function getDataStructureClasses():?array{
		return $this->bundle("getDataStructureClasses");
	}
	
	public final function getTypeSortedDataStructureClasses(): ?array{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			$cache = false;
			$key = "getTypeSortedDataStructureClasses";
			if(cache()->enabled() && !app()->getFlag("install")){
				if(cache()->hasAPCu($key)){
					if($print){
						Debug::print("{$f} cache hit");
					}
					return cache()->getAPCu($key);
				}elseif($print){
					Debug::print("{$f} cache miss");
				}
				$cache = true;
			}elseif($print){
				Debug::print("{$f} cache is disabled");
			}
			$ret = [];
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
				foreach($dscdm as $value){
					if(!class_exists($value)){
						Debug::error("{$f} class \"{$value}\" does not exist");
					}elseif($print){
						//Debug::print("{$f} class \"{$value}\"");
					}
					$type = $value::getDataType();
					if(is_a($value, StaticSubtypeInterface::class, true)){
						if(!array_key_exists($type, $ret)){
							$ret[$type] = [];
						}elseif(!is_array($ret[$type])){
							if(is_string($type)){
								Debug::error("{$f} return value at index {$type} is the string {$ret[$type]}");
							}
							Debug::error("{$f} retuirn value at index {$type} is neither array nor string");
						}
						$subtype = $value::getSubtypeStatic();
						if($print){
							Debug::print("{$f} type \"{$type}\", subtype \"{$subtype}\"");
						}
						$ret[$type][$subtype] = $value;
					}else{
						$ret[$type] = $value;
					}
				}
			}
			if($cache){
				cache()->setAPCu($key, $ret, time() + 30 * 60);
				if(!cache()->hasAPCu($key)){
					Debug::warning("{$f} cache is busted");
				}
			}
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getClientCommandClasses(): ?array{
		return $this->bundle("getClientCommandClasses");
	}

	public function getValidatorClass(string $className): string{
		$f = __METHOD__;
		$validatorClasses = $this->getValidatorClasses();
		if(false === array_key_exists($className, $validatorClasses)){
			Debug::error("{$f} validator class is undefined");
			return \JulianSeymour\PHPWebApplicationFramework\validate\ImpossibleValidator::class();
		}
		return $validatorClasses[$className];
	}

	public function getValidatorClasses():?array{
		$f = __METHOD__;
		$print = false;
		$cache = false;
		$key = "getValidatorClasses";
		if(cache()->enabled()){
			if(cache()->hasAPCu($key)){
				return cache()->getAPCu($key);
			}
			$cache = true;
		}
		$ret = [];
		$mods = $this->getModules();
		if(!empty($mods)){
			foreach($mods as $mod){
				$validator_classes = $mod->getValidatorClasses();
				if(empty($validator_classes)){
					continue;
				}else foreach($validator_classes as $vc){
					$ret[get_short_class($vc)] = $vc;
				}
			}
			if($cache){
				cache()->setAPCu($key, $ret, time() + 30 * 60);
			}
		}elseif($print){
			Debug::print("{$f} no data modules");
		}
		return $ret;
	}

	public function getClientRenderedFormClasses(): ?array{
		return $this->bundle("getClientRenderedFormClasses");
	}

	public function hasModules():bool{
		return isset($this->modules) && is_array($this->modules) && !empty($this->modules);
	}

	public function getModules(){
		$f = __METHOD__;
		$print = false;
		if($this->hasModules()){
			if($print){
				$count = count($this->modules);
				Debug::print("{$f} returning {$count} modules");
				foreach($this->modules as $mod){
					Debug::print(get_class($mod));
				}
			}
			return $this->modules;
		}
		return [];
	}

	public function load(?array $mc): ?array{
		if(isset($mc) && is_array($mc) && !empty($mc)){
			$mods = [];
			foreach($mc as $class){
				$mod = new $class();
				array_push($mods, $mod);
			}
			return $this->setModules($mods);
		}
		return [];
	}

	public function setModules($mods){
		if($this->hasModules()){
			$this->release($this->modules);
		}
		$this->modules = $this->claim($mods);
	}
	
	public final function getUserClass(string $account_type): ?string{
		$f = __METHOD__;
		$type_classes = $this->getUserClasses();
		if(!array_key_exists($account_type, $type_classes)){
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
		if(cache()->enabled()){
			if(cache()->hasAPCu($key)){
				return cache()->getAPCu($key);
			}
			$cache = true;
		}
		$ret = [];
		$mods = $this->getModules();
		foreach($mods as $mod){
			$types = $mod->getTypedNotificationClasses();
			foreach($types as $class){
				$ret[$class::getNotificationTypeStatic()] = $class;
			}
		}
		if($cache){
			cache()->setAPCu($key, $ret, time() + 30 * 60);
		}
		return $ret;
	}

	public final function getDataStructureClass($type, $subtype = null): ?string{
		$f = __METHOD__;
		$classes = $this->getTypeSortedDataStructureClasses();
		if(!array_key_exists($type, $classes)){
			Debug::error("{$f} this application doesn't know about a datatype \"{$type}\"");
		}
		$class = $classes[$type];
		if(!is_array($class)){
			return $class;
		}elseif(!array_key_exists($subtype, $class)){
			Debug::error("{$f} this application doesn't know about a datatype \"{$type}\" with subtype \"{$subtype}\"");
		}
		return $class[$subtype];
	}

	public final function getTypedNotificationClass($type): ?string{
		$f = __METHOD__;
		$classes = $this->getTypedNotificationClasses();
		if(!array_key_exists($type, $classes)){
			Debug::error("{$f} invalid notification type \"{$type}\"");
		}
		return $classes[$type];
	}

	public function getConditionalWidgetClasses(?UseCase $use_case = null): array{
		$f = __METHOD__;
		$print = false;
		$ret = [];
		foreach($this->getWidgetClasses() as $wc){
			if($wc::meetsDisplayCriteria($use_case)){
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
		$print = false && $this->getDebugFlag();
		//Debug::limitExecutionDepth();
		//Debug::checkMemoryUsage("bundle", 96000000);
		if(!$this->isLegalBundleName($bundle_name)){
			Debug::error("{$f} \"{$bundle_name}\" is not legal");
		}elseif($print){
			Debug::print("{$f} bundling \"{$bundle_name}\"");
		}
		$cache = false;
		if(cache()->enabled()){
			if(cache()->hasAPCu($bundle_name)){
				if($print){
					Debug::print("{$f} cache hit for \"{$bundle_name}\"");
				}
				return cache()->getAPCu($bundle_name);
			}else{
				if($print){
					Debug::print("{$f} cache miss for \"{$bundle_name}\"");
				}
				$cache = true;
			}
		}elseif($print){
			Debug::print("{$f} cache is disabled");
		}
		$bundle = [];
		$mods = $this->getModules();
		if(empty($mods)){
			if($print){
				Debug::print("{$f} there are no modules");
			}
			return [];
		}
		foreach($mods as $mod){
			if(!method_exists($mod, $bundle_name)){
				if($print){
					Debug::print("{$f} module ".$mod->getShortClass()." does not have a bundling function {$bundle_name}");
				}
				continue;
			}elseif($print){
				$bundle2 = $mod->$bundle_name();
				Debug::print("{$f} bundle from module " . get_class($mod));
				Debug::printArray($bundle2);
				$bundle = array_merge_recursive($bundle, $bundle2);
			}else{
				$bundle = array_merge_recursive($bundle, $mod->$bundle_name());
			}
		}
		if($cache){
			if($print){
				Debug::print("{$f} updating cache for \"{$bundle_name}\" with the following:");
				//Debug::print($bundle);
			}
			cache()->setAPCu($bundle_name, $bundle, time() + 30 * 60);
		}
		if($print){
			Debug::print("{$f} returning the following array:");
			Debug::printArray($bundle);
		}
		return $bundle;
	}

	public function getUniversalFormClasses(): ?array{
		return $this->bundle("getUniversalFormClasses");
	}

	public function getGrantArray(): ?array{
		$grants = [];
		foreach($this->getModules() as $mod){
			$arr = $mod->getGrantArray();
			foreach($arr as $name => $tablegrants){
				if(!array_key_exists($name, $grants)){
					$grants[$name] = $tablegrants;
				} else
					foreach($tablegrants as $table => $add_us){
						if(!array_key_exists($table, $grants[$name])){
							$grants[$name][$table] = $add_us;
						} else
							foreach($add_us as $add_me){
								if(false === array_search($add_me, $grants[$name][$table])){
									array_push($grants[$name][$table], $add_me);
								}
							}
					}
			}
		}
		return $grants;
	}

	public final function getPollingUseCaseClasses(): ?array{
		return $this->bundle("getPollingUseCaseClasses");
	}
	
	public final function getInstallDirectories(): ?array{
		return $this->bundle("getInstallDirectories");
	}
	
	public final function isLegalBundleName(string $name): bool{
		$names = $this->getLegalBundleNames();
		if(!is_array($names) || empty($names)){
			return false;
		}
		return false !== array_search($name, $names, true);
	}
	
	public function getContentSecurityPolicyDirectives():?array{
		return $this->bundle("getContentSecurityPolicyDirectives");
	}
	
	public function getModuleSpecificColumns(?DataStructure $ds):?array{
		$f = __METHOD__;
		$print = false;
		$ret = [];
		$mods = $this->getModules();
		foreach($mods as $mod){
			$columns = $mod->getModuleSpecificColumns($ds);
			if(empty($columns)){
				if($print){
					Debug::print("{$f} there are no embedded columns for module ".$mod->getShortClass());
				}
				continue;
			}
			foreach($columns as $column){
				if($column->hasEncryptionScheme()){
					if($print){
						Debug::print("{$f} column \"".$column->getName()."\" has encryption scheme \"".$column->getEncryptionScheme()."\"");
					}
					continue;
				}
				$pm = $column->hasPersistenceMode() ? $column->getPersistenceMode() : $ds->getDefaultPersistenceMode();
				switch($pm){
					case PERSISTENCE_MODE_DATABASE:
					case PERSISTENCE_MODE_EMBEDDED:
						if($print){
							Debug::print("{$f} embedding column \"".$column->getName()."\" from module \"".$mod->getShortClass()."\"");
						}
						$column->embed($mod->getEmbedName());
					case PERSISTENCE_MODE_ALIAS:
					case PERSISTENCE_MODE_COOKIE:
					case PERSISTENCE_MODE_ENCRYPTED:
					case PERSISTENCE_MODE_INTERSECTION:
					case PERSISTENCE_MODE_SESSION:
					case PERSISTENCE_MODE_UNDEFINED:
					case PERSISTENCE_MODE_VOLATILE:
					default:
				}
			}
			array_push($ret, ...$columns);
		}
		return $ret;
	}
	
	public function getLegalBundleNames(): ?array{
		return [
			"getCascadingStyleSheetFilePaths",
			"getClientCommandClasses",
			"getClientDataStructureClasses",
			"getClientRenderedFormClasses",
			"getClientUseCaseDictionary",
			"getContentSecurityPolicyDirectives",
			"getDataStructureClasses",
			"getDebugJavaScriptFilePaths",
			"getDependentClassespingFunctions",
			"getFormDataSubmissionClasses",
			"getInstallDirectories",
			"getInvokeableJavaScriptFunctions",
			"getJavaScriptFilePaths",
			"getJavaScriptFunctionGeneratorClasses",
			"getLegalIntersectionObservers",
			"getMessageEventHandlerCases",
			"getPhpFileInclusionPaths",
			"getPollingUseCaseClasses",
			"getServiceWorkerDependencyFilePaths",
			"getSpecialTemplateClasses",
			"getStoredRoutines",
			"getTemplateElementClasses",
			"getTypedNotificationClasses",
			"getTypeSortedDataStructureClasses",
			"getUseCaseDictionary",
			"getValidDirectives",
			"getValidMimeTypes",
			"getValidatorClasses",
			"getWidgetClasses",
			"getWidgetClasses"
		];
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		$this->release($this->modules, $deallocate);
	}
}
