<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\element\CompoundElement;
use Exception;

class ProcessedDataListElement extends CompoundElement
{

	public static function allowUseCaseAsContext():bool{
		return true;
	}
	
	public function generateComponents(){
		$f = __METHOD__;
		try {
			$context = $this->getContext();
			$print = false;
			$classes = $context->getProcessedDataListClasses();
			$ret = [];
			$duplicate_families = [];
			$mode = ALLOCATION_MODE_LAZY;
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			foreach ($classes as $class) {
				$type = $class::getDataType();
				if ($print) {
					Debug::print("{$f} about to call getConditionalElementClass({$type}) for class \"{$class}\"");
				}
				$ciec = $context->getConditionalElementClass($type);
				if (! is_string($ciec)) {
					$gottype = is_object($ciec) ? $ciec->getClass() : gettype($ciec);
					Debug::error("{$f} conditional element class returned a {$gottype}");
				} elseif (empty($ciec)) {
					Debug::error("{$f} conditional element class is undefined for class \"{$class}\"");
				} elseif (! class_exists($ciec)) {
					Debug::error("{$f} class \"{$ciec}\" does not exist");
				} elseif ($print) {
					Debug::print("{$f} conditional element class is \"{$ciec}\"");
				}
				if ($context->showNewItemForm()) {
					$ds = new $class();
					if ($ds instanceof UserOwned) {
						$owner = $context->acquireDataOperandOwner($mysqli, $ds);
						$ds->setUserData($owner);
					}
					$element = new $ciec($mode);
					$element->bindContext($ds);
					array_push($ret, $element);
				}
				$tree_name = $class::getPhylumName();
				if (isset($duplicate_families[$tree_name])) {
					continue;
				}
				if (user()->hasForeignDataStructureList($tree_name)) {
					$nodes = user()->getForeignDataStructureList($tree_name);
					if (! empty($nodes)) {
						foreach ($nodes as $node) {
							if ($node->getClass() !== $class) {
								continue;
							} elseif ($node->getTemporaryFlag()) {
								continue;
							}
							$e = new $ciec($mode);
							$e->bindContext($node);
							array_push($ret, $e);
						}
					}
				}
				$duplicate_families[$tree_name] = true;
			}
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}