<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class RiggedLoadoutGenerator extends LoadoutGenerator{

	public function getRootNodeTreeSelectStatements(?PlayableUser $ds=null, ?UseCase $use_case=null):?array{
		return $this->getTreeSelectStatements(true, $ds, $use_case);
	}
	
	public function getNonRootNodeTreeSelectStatements(DataStructure $ds, ?UseCase $use_case=null):?array{
		return $this->getTreeSelectStatements(false, $ds, $use_case);
	}
	
	protected function getTreeSelectStatements(bool $root, ?DataStructure $object = null, ?UseCase $use_case = null): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			$dependencies = [];
			$menu_class = $use_case->getMenuElementClass();
			if($menu_class !== null) {
				if(method_exists($menu_class, "getLoadoutGeneratorClassStatic")) {
					$generator_class = $menu_class::getLoadoutGeneratorClassStatic();
					if($generator_class) {
						$generator = new $generator_class();
						if($root){
							$menu_dependencies = $generator->getRootNodeTreeSelectStatements($object, $use_case);
						}else{
							$menu_dependencies = $generator->getNonRootNodeTreeSelectStatements($object, $use_case);
						}
						if(isset($menu_dependencies) && is_array($menu_dependencies) && ! empty($menu_dependencies)) {
							foreach($menu_dependencies as $phylum => $classes) {
								if(array_key_exists($phylum, $dependencies)) {
									if($print) {
										Debug::print("{$f} phylum \"{$phylum}\" has some presence in the use case dependencies");
									}
									foreach($classes as $class => $query) {
										if(array_key_exists($class, $dependencies[$phylum])) {
											if($print) {
												Debug::print("{$f} class \"{$class}\" from phylum \"{$phylum}\" is already part of the use case loadout");
											}
											continue;
										}elseif($print) {
											Debug::print("{$f} adding class \"{$class}\" from phylum \"{$phylum}\" to the loadout");
										}
										$dependencies[$phylum][$class] = $query;
									}
								}else{
									if($print) {
										Debug::print("{$f} phylum \"{$phylum}\" has no presence in the use case dependencies");
										Debug::printArray($dependencies);
									}
									$dependencies[$phylum] = $classes;
								}
							}
							if($print) {
								Debug::print("{$f} got the following loadout dependencies from the menu:");
								Debug::printArray($dependencies);
							}
						}elseif($print) {
							Debug::print("{$f} menu dependencies is not an array");
						}
					}elseif($print) {
						Debug::print("{$f} menu generator class is null");
					}
				}
			}elseif($print) {
				Debug::print("{$f} menu class is null");
			}
			$widgets = mods()->getWidgetClasses();
			if($root && ! empty($widgets)) {
				foreach($widgets as $widget) {
					if(! class_exists($widget)) {
						Debug::error("{$f} class \"{$widget}\" does not exist");
					}
					$widget_generator_class = $widget::getLoadoutGeneratorClassStatic();
					if(!$widget_generator_class) {
						if($print) {
							Debug::print("{$f} widget loadout generator class is undefined");
						}
						continue;
					}
					$widget_generator = new $widget_generator_class();
					$widget_dependencies = $widget_generator->getRootNodeTreeSelectStatements($object, $use_case);
					if(!empty($widget_dependencies)) {
						$dependencies = array_merge($dependencies, $widget_dependencies);
					}elseif($print) {
						Debug::print("{$f} there are no widget dependencies for {$widget}");
					}
				}
			}elseif($print) {
				Debug::print("{$f} there are no widget classes");
			}
			if($print) {
				Debug::print("{$f} got the following dependencies after merging widget class depdendencies:");
				Debug::printArray($dependencies);
			}
			if($print) {
				if(!empty($dependencies)) {
					foreach(array_keys($dependencies) as $phylum) {
						if(!is_array($dependencies[$phylum])) {
							Debug::error("{$f} not an array");
						}
						foreach(array_keys($dependencies[$phylum]) as $class) {
							$query = $dependencies[$phylum][$class];
							if($print) {
								Debug::print("{$f} mapped query \"{$query}\" to class \"{$class}\" for phylum \"{$phylum}\"");
							}
						}
					}
				}elseif($print) {
					Debug::print("{$f} dependendencies array is empty");
				}
			}
			return $dependencies;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}

