<?php

namespace JulianSeymour\PHPWebApplicationFramework;

use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\app\ApplicationConfiguration;
use JulianSeymour\PHPWebApplicationFramework\app\ApplicationRuntime;
use JulianSeymour\PHPWebApplicationFramework\app\ModuleBundler;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\cache\MultiCache;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\SwitchCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\core\Debugger;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionData;
use JulianSeymour\PHPWebApplicationFramework\db\DatabaseManager;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\LazyLoadHelper;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\Event;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseCyclicalReferencesEvent;
use JulianSeymour\PHPWebApplicationFramework\notification\push\PushNotifier;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Closure;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\command\control\ControlStatementCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\LoopCommand;
use JulianSeymour\PHPWebApplicationFramework\command\control\TryCatchCommand;

function app():?ApplicationRuntime{
	global $__applicationInstance;
	return $__applicationInstance;
}

function cache():MultiCache{
	return app()->getCache();
}

function claim($resource, ?string $claimant_id = null){
	$f = __FUNCTION__;
	$print = false && $resource instanceof Basic && $resource->getDebugFlag();
	if(SUPPLEMENTAL_GARBAGE_COLLECTION_ENABLED){
		if($resource instanceof HitPointsInterface){
			if($print){
				$ds = $resource->getDebugString();
			}
			if(!$resource->getAllocatedFlag()){
				$ds = $resource->getDebugString();
				$dealloc = $resource->getDeallocationLine();
				if($resource instanceof Element){
					Debug::warning("{$f} cannot claim a {$ds} that was already deallocated {$dealloc}");
					$resource->debugPrintRootElement();
				}
				Debug::error("{$f} cannot claim a {$ds} that was already deallocated {$dealloc}");
			}
			$hp = $resource->hpUp();
			if($print){
				if($claimant_id === null){
					Debug::error("{$f} you forgot to pass claimant ID when claiming this {$ds}");
				}
				Debug::print("{$f} claimed a {$ds}. Said resource now has {$hp} HP.");
			}
			if(DEBUG_MODE_ENABLED && DEBUG_REFERENCE_MAPPING_ENABLED && $claimant_id !== null){
				if($print){
					Debug::print("{$f} about to add claimant \"{$claimant_id}\" to a {$ds}");
				}
				debug()->addClaimant($resource, $claimant_id);
			}elseif($print){
				Debug::print("{$f} claimant ID is null");
			}
		}elseif(is_array($resource)){
			foreach($resource as $subv){
				claim($subv, $claimant_id);
			}
		}
	}elseif($print){
		Debug::print("{$f} supplemental garbage collection is disabled");
	}
	return $resource;
}

function comma_separate_sql($arr): ?string{
	if(empty($arr)){
		return null;
	}
	$s = "";
	$count = 0;
	foreach($arr as $i){
		if($count > 0){
			$s .= ",";
		}
		$s .= $i->toSQL();
		$count ++;
	}
	return $s;
}

function config(): ApplicationConfiguration{
	return app()->getConfiguration();
}

function converse(int $relationship_type):int{
	$f = __FUNCTION__;
	switch($relationship_type){
		case RELATIONSHIP_TYPE_ONE_TO_MANY:
			return RELATIONSHIP_TYPE_MANY_TO_ONE;
		case RELATIONSHIP_TYPE_MANY_TO_ONE:
			return RELATIONSHIP_TYPE_ONE_TO_MANY;
		case RELATIONSHIP_TYPE_ONE_TO_ONE:
		case RELATIONSHIP_TYPE_MANY_TO_MANY:
			return $relationship_type;
		default:
			Debug::error("{$f} invalid relationship type {$relationship_type}");
	}
}

function db(): DatabaseManager{
	return app()->getDatabaseManager();
}

function deallocate(&$v):void{
	$f = __FUNCTION__;
	$print = false && $v instanceof Basic && $v->getDebugFlag();
	if(is_array($v)){
		foreach(array_keys($v) as $key){
			if($print){
				$subv = $v[$key];
				$type = is_object($subv) ? $subv->getDebugString() : gettype($subv);
				Debug::print("{$f} disposing of a {$type} at index \"{$key}\"");
			}
			deallocate($v[$key]);
			$v[$key] = null;
		}
		$v = null;
	}elseif(is_object($v)){
		$copy = $v;
		$v = null;
		if($copy instanceof Basic){
			if($copy->getAllocatedFlag()){
				if($copy->getDisableDeallocationFlag()){
					$ds = $copy->getDebugString();
					Debug::error("{$f} {$ds} has deallocation disabled");
					return;
				}elseif($print && !$copy instanceof ApplicationRuntime){
					$ds = $copy->getDebugString();
					Debug::print("{$f} deallocating {$ds} with the debug flag set.");
				}
				if(app() !== null && app()->getFlag('debug')){
					$copy->setDeallocationLine(get_file_line([
						"{closure}",
						"addColumnEventListeners",
						"deallocate",
						"dispatchEvent",
						"dispose",
						"mutual_reference",
						"release",
						"releaseAllForeignDataStructures",
						"releaseArrayPropertyKey",
						"releaseChildNode",
						"releaseColumn",
						"releaseContext",
						"releaseDataStructure",
						"releaseForeignDataStructure",
						"releaseInput"
					], 24));
				}
				if($copy->hasAnyEventListener(EVENT_DEALLOCATE)){
					$copy->dispatchEvent(new DeallocateEvent());
				}
				if($copy->hasAnyEventListener(EVENT_RELEASE_CYCLE)){
					$copy->dispatchEvent(new ReleaseCyclicalReferencesEvent(true, true));
				}
				if($print){
					Debug::print("{$f} about to call dispose on ".$copy->getDebugString());
				}
				$copy->dispose(true);
			}else{
				$ds = $copy->getDebugString();
				$dealloc = $copy->getDeallocationLine();
				Debug::error("{$f} {$ds} was already deallocated {$dealloc}");
			}
		}elseif($print){
			$sc = get_short_class($copy);
			Debug::print("{$f} object is a {$sc}");
		}
	}
}

function debug():Debugger{
	$f = __FUNCTION__;
	global $__applicationInstance;
	if(!isset($__applicationInstance)){
		Debug::error("{$f} application instance is undefined");
	}
	return app()->getDebugger();
}

function directive(): ?string{
	return request()->getDirective();
}

function disable_destruct(){
	$f = __FUNCTION__;
	$GLOBALS['__destructDisabled'] = true;
	//Debug::print("{$f} disabled destructors");
}

function enable_destruct(){
	$f = __FUNCTION__;
	$GLOBALS['__destructDisabled'] = false;
	//Debug::print("{$f} enabled destructors");
}

function f(?string $class2 = null): string{
	$f = __FUNCTION__;
	if(! app()->getFlag("debug")){
		return "";
	}
	$print = false;
	$bto = backtrace_omit(2, [
		"f"
	], true);
	if($print){
		Debug::printArray($bto);
	}
	$class1 = array_key_exists("class", $bto) ? $bto['class'] : "";
	// $splat = explode('/', $bto['file']);
	// $class2 = explode('.', $splat[count($splat)-1])[0];
	$ret = $class1;
	if(is_string($class2) && class_exists($class1) && class_exists($class2) && is_a($class2, $class1, true)){
		$ret .= "({$class2})";
	}
	if(!empty($class1)){
		$ret .= "{$bto['type']}";
	}
	$ret .= "{$bto['function']}()";
	if($print){
		Debug::print("{$f} returning \"{$ret}\"");
	}
	return $ret;
}

function generate_elements(string $element_class, string $data_structure_class, SelectStatement $select, int $batch_size=500, bool $deallocate=false){
	
}

function generateSpecialTemplateKey(string $pc): string{
	return sha1("special_template-{$pc}");
}

function getCurrentUserAccountType(){
	return user()->getAccountType();
}

function getCurrentUserKey(){
	return user()->getIdentifierValue();
}

function getInputParameter(string $name, UseCase $use_case = null){
	return request()->getInputParameter($name, $use_case);
}

function getInputParameters(): ?array{
	return request()->getInputParameters();
}

function getRequestURISegment(int $i){
	return request()->getRequestURISegment($i);
}

function hasInputParameter(string $name, ?UseCase $use_case = null): bool{
	return request()->hasInputParameter($name, $use_case);
}

function hasInputParameters(...$names){
	$f = __FUNCTION__;
	if(empty($names)){
		Debug::error("{$f} received empty parameter names");
	}
	$arr = [];
	foreach($names as $name){
		array_push($arr, $name);
	}
	return request()->hasInputParameters(...$arr);
}

function intersectionalize($hostClass, $foreignClass, $foreignKeyName): int{
	$f = __FUNCTION__;
	$print = false;
	$intersection = new IntersectionData($hostClass, $foreignClass, $foreignKeyName);
	$db = $intersection->getDatabaseName();
	$table = $intersection->getTableName();
	$mysqli = db()->getConnection(PublicWriteCredentials::class);
	if(!$intersection->tableExists($mysqli)){
		if($print){
			Debug::print("{$f} about to create intersection table \"{$db}.{$table}\"");
		}
		$status = $intersection->createTable($mysqli);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} creating intersection table \"{$db}.{$table}\" returned error status \"{$err}\"");
		}
		return $status;
	}elseif($print){
		Debug::printStackTraceNoExit("{$f} table \"{$db}.{$table}\" already exists");
	}
	return SUCCESS;
}

function lazy(): LazyLoadHelper{
	return app()->getLazyLoadHelper();
}

function mark(?string $where = '?'){
	$f = __FUNCTION__;
	$print = false;
	global $__clock;
	if(!$__clock){
		return;
	}
	global $__mark;
	$time = microtime(true);
	$interval = $time - $__mark;
	if($print){
		Debug::print("{$f} {$interval} seconds have passed {$where} since previous mark");
	}
	$GLOBALS['__mark'] = $time;
}

function mods(): ModuleBundler{
	return app()->getModuleBundler();
}

/**
 * This function sets up two objects that have already established mutual references to each other so that when one of them is released of all claimants except for mutual references (which are counted by HitPointsTrait->$cyclicalReferenceCount), an event is fired that releases those references from both sides.
 * Necessary because if neither object is the entry point to a deallocate function call, neither one will ever get disposed by the release function because they are protected by each other's references.
 * The last 4 parameters are optional, and allow each item to unilaterally break each other's mutual references without screwing up their accounting in a way that could cause errors
 * @param HitPointsInterface $thing1
 * @param HitPointsInterface $thing2
 * @param Closure $closure0 : a function that when executed releases both objects' references to each other. The input parameter it is expecting is a boolean value that is passed as the second parameter to release().
 * @param string $event_type_1 : event type that cause $thing1 to unilaterally break the mutual references. It is necessary to properly account for mutual references even when they can be broken by one side explicitly releasing the other, otherwise there may be a premature ReleaseCyclicalReferencesEvent called in release()
 * @param string $event_type_2 : event type that causes $thing2 to unilaterally break the mutual references
 * @param array $required_properties_1 : optional event properties that must be matched for $thing1's $event_type_1 handler (created in this function) to execute
 * @param array $required_properties_2 : optional event properties that must be matched for $thing2's $event_type_2 handler to execute
 */
function mutual_reference(HitPointsInterface $thing1, HitPointsInterface $thing2, Closure $closure0, Closure $closure1, ?string $event_type_1=null, ?string $event_type_2=null, ?array $required_properties_1=null, ?array $required_properties_2=null){
	//return;
	$f = __METHOD__;
	$print = false;
	disable_destruct();
	if($thing1->hasDebugId() && $thing2->hasDebugId() && $thing1->getDebugId() === $thing2->getDebugId()){
		Debug::error("{$f} same object");
	}elseif($print){
		Debug::print("{$f} setting up a mutual reference between ".$thing1->getDebugString()." and ".$thing2->getDebugString());
	}
	$random1 = sha1(random_bytes(32));
	$random2 = sha1(random_bytes(32));
	$closure3 = function(ReleaseCyclicalReferencesEvent $event, HitPointsInterface $target) 
	use ($thing2, $closure0, $closure1, $event_type_1, $event_type_2, $random1, $random2, $f, $print){
		$target->removeEventListener(EVENT_RELEASE_CYCLE, $random1, "Self");
		$thing2->removeEventListener(EVENT_RELEASE_CYCLE, $random1, $target->getDebugString());
		if($event_type_1 !== null){
			if($target->hasEventListener($event_type_1, $random2)){
				if($print){
					Debug::print("{$f} about to tell ".$target->getDebugString()." to remove its {$event_type_1} listener with index {$random2}");
				}
				$target->removeEventListener($event_type_1, $random2);
			}elseif($print){
				Debug::error("{$f} ".$target->getDebugString()." does not have a {$event_type_1} listener with index {$random2}");
			}
		}elseif($print){
			Debug::error("{$f} event type 1 is null");
		}
		if($event_type_2 !== null){
			if($thing2->hasEventListener($event_type_2, $random2)){
				if($print){
					Debug::print("{$f} about to tell ".$thing2->getDebugString()." to remove its {$event_type_2} listener with index {$random2}");
				}
				$thing2->removeEventListener($event_type_2, $random2);
			}elseif($print){
				Debug::error("{$f} ".$thing2->getDebugString()." does not have a {$event_type_2} listener with index {$random2}");
			}
		}elseif($print){
			Debug::error("{$f} event type 2 is null");
		}
		
		$target->decrementCyclicalReferenceCount();
		$thing2->decrementCyclicalReferenceCount();
		$deallocate = $event->hasProperty("recursive") ? $event->getProperty('recursive') : false;
		$closure0(
			$target, 
			$deallocate && (
				$thing2->getHitPoints() === 0 || 
				$thing2->hasOnlyCyclicalReferences()
			)
		);
		if($thing2->hasOnlyCyclicalReferences()){
			$closure1($thing2, $deallocate && ($target->getHitPoints() === 0 || $target->hasOnlyCyclicalReferences()));
		}
		if($print){
			Debug::print("{$f} returning from ReleaseCyclicalReferencesEvent listener for ".$target->getDebugString());
		}
	};
	$thing1->addEventListener(EVENT_RELEASE_CYCLE, $closure3, $random1);
	$thing1->incrementCyclicalReferenceCount();
	$closure4 = function(ReleaseCyclicalReferencesEvent $event, HitPointsInterface $target) 
	use ($thing1, $closure0, $closure1, $event_type_1, $event_type_2, $random1, $random2, $f, $print){
		$thing1->removeEventListener(EVENT_RELEASE_CYCLE, $random1, $target->getDebugString());
		$target->removeEventListener(EVENT_RELEASE_CYCLE, $random1, "Self");
		$thing1->decrementCyclicalReferenceCount();
		$target->decrementCyclicalReferenceCount();
		if($event_type_1 !== null){
			if($thing1->hasEventListener($event_type_1, $random2)){
				if($print){
					Debug::print("{$f} about to remove {$event_type_1} listener with index {$random2} from ".$thing1->getDebugString());
				}
				$thing1->removeEventListener($event_type_1, $random2);
			}elseif($print){
				Debug::error("{$f} ".$thing1->getDebugString()." does not have a {$event_type_1} listener with index {$random2}");
			}
		}elseif($print){
			Debug::error("{$f} event type 1 is null");
		}
		if($event_type_2 !== null){
			if($target->hasEventListener($event_type_2, $random2)){
				if($print){
					Debug::print("{$f} about to remove {$event_type_2} listener with index {$random2} from ".$target->getDebugString());
				}
				$target->removeEventListener($event_type_2, $random2);
			}elseif($print){
				Debug::error("{$f} ".$target->getDebugString()." does not have a {$event_type_2} listener with index {$random2}");
			}
		}elseif($print){
			Debug::error("{$f} event type 2 is null");
		}
		$deallocate = $event->hasProperty("recursive") ? $event->getProperty('recursive') : false;
		if($thing1->hasOnlyCyclicalReferences()){
			$closure0($thing1, $deallocate && ($target->getHitPoints() === 0 || $target->hasOnlyCyclicalReferences()));
		}
		$closure1($target, $deallocate && ($thing1->getHitPoints() === 0 || $thing1->hasOnlyCyclicalReferences()));
		if($print){
			Debug::print("{$f} returning from ReleaseCyclicalReferencesEvent listener for ".$target->getDebugString());
		}
	};
	$thing2->addEventListener(EVENT_RELEASE_CYCLE, $closure4, $random1);
	$thing2->incrementCyclicalReferenceCount();
	if($event_type_1 !== null){
		$closure5 = function(Event $event, HitPointsInterface $target)
		use($thing2, $random1, $random2, $event_type_1, $required_properties_1, $f, $print){
			if($print){
				Debug::print("{$f} inside the closure for unilaterally removing ReleaseCyclicalReferencesEvent listeners for this ".$target->getDebugString());
			}
			if($required_properties_1 !== null){
				foreach($required_properties_1 as $key => $value){
					if(!$event->hasProperty($key) || $event->getProperty($key) !== $value){
						if($print){
							Debug::print("{$f} failed event property {$key} value {$value} check");
						}
						return;
					}
				}
			}elseif($print){
				Debug::print("{$f} there are no required pproperty types");
			}
			if($target->hasEventListener($event_type_1, $random2)){
				$target->removeEventListener($event_type_1, $random2);
			}
			if($target->hasEventListener(EVENT_RELEASE_CYCLE, $random1)){
				$target->removeEventListener(EVENT_RELEASE_CYCLE, $random1);
				$target->decrementCyclicalReferenceCount();
			}
			if($thing2->hasEventListener(EVENT_RELEASE_CYCLE, $random1)){
				$thing2->removeEventListener(EVENT_RELEASE_CYCLE, $random1);
				$thing2->decrementCyclicalReferenceCount();
			}
			if($print){
				Debug::print("{$f} returning from ReleaseCyclicalReferencesEvent listener removal closure for ".$target->getDebugString());
			}
		};
		$thing1->addEventListener($event_type_1, $closure5, $random2);
	}elseif($print){
		Debug::error("{$f} event type 1 is null");
	}
	if($event_type_2 !== null){
		$closure6 = function(Event $event, HitPointsInterface $target) 
		use ($thing1, $random1, $random2, $event_type_2, $required_properties_2, $f, $print){
			if($print){
				Debug::print("{$f} inside the closure for unilaterally removing ReleaseCyclicalReferencesEvent listeners for this ".$target->getDebugString());
			}
			if($required_properties_2 !== null){
				foreach($required_properties_2 as $key => $value){
					if(!$event->hasProperty($key) || $event->getProperty($key) !== $value){
						return;
					}
				}
			}
			if($target->hasEventListener($event_type_2, $random2)){
				$target->removeEventListener($event_type_2, $random2);
			}
			if($target->hasEventListener(EVENT_RELEASE_CYCLE, $random1)){
				$target->removeEventListener(EVENT_RELEASE_CYCLE, $random1);
				$target->decrementCyclicalReferenceCount();
			}
			if($thing1->hasEventListener(EVENT_RELEASE_CYCLE, $random1)){
				$thing1->removeEventListener(EVENT_RELEASE_CYCLE, $random1);
				$thing1->decrementCyclicalReferenceCount();
			}
			if($print){
				Debug::print("{$f} returning from ReleaseCyclicalReferencesEvent listener removal closure for ".$target->getDebugString());
			}
		};
		$thing2->addEventListener($event_type_2, $closure6, $random2);
	}elseif($print){
		Debug::error("{$f} event type 2 is null");
	}
	enable_destruct();
}

function push(): PushNotifier{
	return app()->getPushNotifier();
}

function registry(){
	return app()->getRegistry();
}

function release(&$resource, bool $deallocate=false, ?string $claimant_id=null){
	$f = __FUNCTION__;
	$print = false && $resource instanceof Basic && $resource->getDebugFlag();
	if($print){
		$ds = $resource->getDebugString();
	}
	if(is_array($resource)){
		foreach(array_keys($resource) as $key){
			release($resource[$key], $deallocate, $claimant_id);
		}
	}elseif(SUPPLEMENTAL_GARBAGE_COLLECTION_ENABLED && $resource instanceof HitPointsInterface){
		if(!$resource->getAllocatedFlag()){
			$ds = $resource->getDebugString();
			Debug::error("{$f} allocated flag is not set for this {$ds}. Claimant is ".debug()->getObjectDescription($claimant_id));
			$resource = null;
			return;
		}elseif($print){
			if($claimant_id === null){
				Debug::error("{$f} you forgot to pass claimant ID when deallocating {$ds}");
			}
		}
		if(DEBUG_MODE_ENABLED && DEBUG_REFERENCE_MAPPING_ENABLED && $claimant_id !== null){
			debug()->removeClaimant($resource, $claimant_id);
		}
		if($resource->getHitPoints() >= 1){
			$hp = $resource->damageHP();
			if($print){
				Debug::print("{$f} decrementing resource counter for a {$ds}. Said resource now has {$hp} HP.");
				if(DEBUG_MODE_ENABLED && DEBUG_REFERENCE_MAPPING_ENABLED && $hp < debug()->get($resource->getDebugId())->getEdgeCount()){
					Debug::error("{$f} error, claimant count exceeds HP {$hp}");
				}
			}
		}else{
			Debug::error("{$f} no hit points to decrement");
		}
		$hp = $resource->getHitPoints();
		if($hp < 0){
			Debug::error("{$f} negative HP after releasing ".$resource->getDebugString());
		}elseif($deallocate && !$resource->getDisableDeallocationFlag()){
			if($resource->hasOnlyCyclicalReferences()){
				if($resource->hasAnyEventListener(EVENT_RELEASE_CYCLE)){
					if($print){
						Debug::print("{$f} dispatching a ReleaseCyclicalReferencesEvent on this {$ds}");
					}
					$event = new ReleaseCyclicalReferencesEvent($deallocate);
					if($claimant_id !== null){
						$event->setProperty("claimant", $claimant_id);
					}
					if(starts_with($resource->getDebugString(), "AnchorElement declared /var/www/vendor/julianseymour/php-web-application-framework/src/security/SecurityNotificationElement.php:26")){
						Debug::printStackTraceNoExit("{$f} ".$resource->getDebugString()." has only cyclical references. Event is ".$event->getDebugString());
					}
					$resource->dispatchEvent($event);
				}elseif($print){
					Debug::print("{$f} there is no ReleaseCyclicalReferencesEvent listener for this ".$resource->getDebugString());
				}
			}elseif($print){
				if($hp <= 0){
					Debug::print("{$f} non-positive HP");
				}
				$crc = $resource->getCyclicalReferenceCount();
				if(!$resource->hasCyclicalReferenceCount()){
					Debug::print("{$f} no mutual references");
				}elseif($hp !== $resource->getCyclicalReferenceCount()){
					Debug::print("{$f} HP ({$hp}) != cyclical reference count ({$crc}) for this ".$resource->getDebugString());
				}
			}
			if($resource->getAllocatedFlag()){
				$hp = $resource->getHitPoints();
				if($hp === 0){
					if(!$resource->getDisableDeallocationFlag()){
						if($print){
							Debug::print("{$f} depleted hit points of {$ds}, about to deallocate");
						}
						deallocate($resource);
					}elseif($print){
						Debug::print("{$f} deallocation is disabled for {$ds}");
					}
				}elseif($print){
					Debug::print("{$f} object still has {$hp} HP");
					if($hp === 10){
						debug()->printClaimants($resource);
					}
				}
			}elseif($print){
				Debug::print("{$f} {$ds} was deallocated by releasing its cyclical references");
			}
		}elseif($print){
			Debug::print("{$f} not deallocating at this time");
			if($resource->getDisableDeallocationFlag()){
				Debug::print("{$f} deallocation is disabled for ".$resource->getDebugString());
			}elseif($hp === 0){
				Debug::printStackTraceNoExit("{$f} deallocation is enabled and hit points are depleted, but we are not interested in deallocation for ".$resource->getDebugString().". This function call was prompted by {$claimant_id}");
			}else{
				Debug::print("{$f} object still has {$hp} HP");
				if($hp === 10){
					debug()->printClaimants($resource);
				}
			}
		}
	}elseif($print){
		Debug::print("{$f} supplemental garbage collection is not enabled, or input parameter is not a HitPointsInterface");
	}
	$resource = null;
}

function replicate($object){
	$f = __FUNCTION__;
	if(is_array($object)){
		$ret = [];
		foreach($object as $key => $v){
			$ret[$key] = replicate($v);
		}
		return $ret;
	}elseif(is_object($object)){
		if(!$object instanceof ReplicableInterface){
			$sc = get_short_class($object);
			Debug::error("{$f} {$sc} is not replicable");
		}
		return $object->getReplica();
	}
	return $object;
}

function request():?Request{
	return app()->getRequest();
}

function resolve_template_command(Element $that, ...$commands):void{
	$f = __FUNCTION__;
	try{
		$print = $that->getDebugFlag();
		$mode = $that->getAllocationMode();
		foreach($commands as $command){
			$print = $command->getDebugFlag();
			if(is_array($command)){
				Debug::error("{$f} command is an array");
			}elseif($print){
				Debug::print("{$f} resolving command ".$command->getDebugString()." for this ".$this->getDebugString());
			}
			if($command instanceof DeclareVariableCommand){ // variable declarations go at the top
				if($print){
					Debug::print("{$f} DeclareVariableCommand");
				}
				if($that->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE){
					if($print){
						Debug::print("{$f} command is a DeclareVariableCommand, and this is a templated object");
					}
					$that->pushLocalDeclaration($command);
					if($command->hasVariableName() && $command->hasValue() && $command->hasScope()){
						$value = $command->getValue();
						$name = $command->getVariableName();
						while($name instanceof ValueReturningCommandInterface){
							$name = $name->evaluate();
						}
						$scope = $command->getScope();
						while($scope instanceof ValueReturningCommandInterface){
							$scope = $scope->evaluate();
						}
						$scope->setLocalValue($name, $value);
					}
				}else{
					if($print){
						Debug::print("{$f} command is a DeclareVariableCommand, but this is not templated");
					}
					$command->resolve();
				}
			}elseif($command instanceof NodeBearingCommandInterface){ // extract child nodes from node-bearing commands
				$cc = $command->getClass();
				if($print){
					Debug::print("{$f} command is a \"{$cc}\"");
				}
				if(($that->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE) && ! $command->extractAnyway()){
					if($print){
						Debug::print("{$f} command is nodebearing, and it isn't flagged to force extraction");
					}
					$that->appendChild($command);
				}else{
					if($print){
						Debug::print("{$f} command is nodebearing, and either this object is not templated or the command is flagged to force extraction");
					}
					// $children = [];
					$mode = $that->getAllocationMode();
					$children = $command->extractChildNodes($mode);
					if(!empty($children)){
						if($print){
							$count = count($children);
							Debug::print("{$f} extracted {$count} child nodes");
						}
						foreach($children as $child){
							$ccc = is_object($child) ? $child->getClass() : gettype($child);
							if($child instanceof Command){
								if($child instanceof ValueReturningCommandInterface){
									if($print){
										Debug::print("{$f} child is a value returning media command of class \"{$ccc}\"");
									}
									while($child instanceof ValueReturningCommandInterface){
										if($child instanceof AllocationModeInterface){
											$child->setAllocationMode($mode);
										}
										$child = $child->evaluate();
									}
									// array_push($children, $child); //[$child_key] = $child;
									$ccc = is_object($child) ? $child->getClass() : gettype($child);
									if($print){
										Debug::print("{$f} after evaluation, child is now a \"{$ccc}\"");
									}
								}else{
									Debug::error("{$f} command \"{$cc}\" returned a child command of class \"{$ccc}\"");
								}
							}elseif($print){
								Debug::print("{$f} child is a {$ccc}");
							}
							if($print){
								Debug::print("{$f} appending a {$ccc}");
							}
							$that->appendChild($child);
						}
					}elseif($print){
						Debug::warning("{$f} extracted child nodes array is empty");
					}
				}
			}elseif($command instanceof ControlStatementCommand || $command instanceof LoopCommand || $command instanceof TryCatchCommand){ // treated just like declared variables, but we have to check for node-bearing commands first
				if($print){
					Debug::print("{$f} command is an if or switch statement");
				}
				if($that->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE){
					if($print){
						Debug::print("{$f} pushing a local declaration");
					}
					$that->pushLocalDeclaration($command);
				}else{
					if($print){
						Debug::print("{$f} resolving command");
					}
					$command->resolve();
				}
			}elseif($command instanceof ServerExecutableCommandInterface){ // everything else that can be resolved server side
				if($print){
					$cc = $command->getClass();
					Debug::print("{$f} {$cc} is server executable");
				}
				if($that->getTemplateFlag() || $mode === ALLOCATION_MODE_FORM_TEMPLATE){
					if(!$command->getResolvedFlag()){
						if($print){
							Debug::print("{$f} appending a template command of class \"{$cc}\"");
						}
						$that->appendChild($command);
						$command->setResolvedFlag(true);
					}elseif($print){
						Debug::print("{$f} template command of class \"{$cc}\" has already been resolved");
					}
				}else{
					$command->resolve();
				}
			}elseif(!is_object($command)){
				$gottype = gettype($command);
				Debug::error("{$f} received a {$gottype}");
			}else{
				$class = $command->getClass();
				Debug::error("{$f} this function doesn't yet support resolution of {$class} commands");
			}
		}
	}catch(Exception $x){
		x($f, $x);
	}
}

function setInputParameter(string $name, $value){
	return request()->setInputParameter($name, $value);
}

function use_case(): ?UseCase{
	return app()->getUseCase();
}

function user(): ?PlayableUser{
	return app()->getUserData();
}

function x(string $f, Exception $x){
	Debug::error("{$f} exception: \"" . $x->__toString() . "\"");
}
