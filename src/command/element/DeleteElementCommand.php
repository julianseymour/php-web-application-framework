<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;


use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class DeleteElementCommand extends Command implements JavaScriptInterface
{

	use EffectTrait;

	public static function getCommandId(): string
	{
		return "delete";
	}

	public function __construct(...$elements)
	{
		$f = __METHOD__;
		$print = false;
		parent::__construct();
		$ids = [];
		foreach($elements as $element) {
			while ($element instanceof ValueReturningCommandInterface) {
				$element = $element->evaluate();
			}
			if(is_string($element)) {
				$id = $element;
			}elseif($element instanceof Element) {
				$element->generateContents();
				if($element->hasPredecessors() || $element->hasSuccessors()) {
					if($element->hasPredecessors()) {
						if(!$element->getPreservePredecessorsFlag()) {
							if(!$element->getDeletePredecessorsFlag()) {
								Debug::error("{$f} delete precesessor nodes flag must be set to locate all occurences");
							}
							$predecessors = $element->getPredecessors();
							foreach($predecessors as $p) {
								array_push($ids, $p->getIdAttribute());
							}
						}elseif($print) {
							Debug::print("{$f} this element is not flagged to preserve its predecessors on deletion");
						}
					}
					array_push($ids, $element->getIdAttribute());
					if($element->hasSuccessors()) {
						if(!$element->getPreserveSuccessorsFlag()) {
							if(!$element->getDeleteSuccessorsFlag()) {
								Debug::error("{$f} delete successor nodes flag must be set to locate all occurences");
							}
							$successors = $element->getSuccessors();
							foreach($successors as $s) {
								array_push($ids, $s->getIdAttribute());
							}
						}elseif($print) {
							Debug::print("{$f} this element is not flagged to preserve its successors on deletion");
						}
					}
					continue;
				}elseif($print) {
					Debug::print("{$f} element does not have predecessor/antecessor nodes");
				}
				$id = $element->getIdAttribute();
			}else{
				Debug::error("{$f} neither of the above");
			}
			array_push($ids, $id);
		}
		$this->setIds($ids);
	}

	public function setIds($ids)
	{
		return $this->setArrayProperty("ids", $ids); // ids = $ids;
	}

	public function hasIds()
	{
		return $this->hasArrayProperty("ids"); // isset($this->ids) && is_array($this->ids) && !empty($this->ids);
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('ids', $this->getIds());
		if($this->hasEffect()) {
			Json::echoKeyValuePair('effect', $this->getEffect());
		}
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		$f = __METHOD__;
		$print = false;
		if($print) {
			if(!$this->getAllocatedFlag()) {
				Debug::warning("{$f} debug ID is undefined, object has already been destroyed");
			}else{
				Debug::print("{$f} entered; debug ID is {$this->debugId}");
			}
		}
		parent::dispose();
		// unset($this->ids);
		unset($this->effect);
	}

	public function getIds()
	{
		$f = __METHOD__;
		if(!$this->hasIds()) {
			Debug::error("{$f} IDs are undefined");
		}
		return $this->getProperty("ids"); // ids;
	}

	public function getIdCount(): int
	{
		return $this->getArrayPropertyCount("ids");
	}

	public function getEffectJavaScriptFunctionName()
	{
		$f = __METHOD__;
		if(!$this->hasEffect()) {
			return "removeElementById";
		}
		$effect = $this->getEffect();
		switch ($effect) {
			case EFFECT_NONE:
				return "removeElementById";
			case EFFECT_FADE:
				return "fadeElementById";
			default:
				Debug::error("{$f} no js function name for effect \"{$effect}\"");
		}
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__;
		try{
			if($this->getIdCount() > 1) {
				Debug::error("{$f} not implemented: delete multiple elements toString");
			}
			$ids = $this->getIds();
			$id = $ids[0];
			if($id instanceof JavaScriptInterface) {
				$id = $id->toJavaScript();
			}elseif(is_string($id) || $id instanceof StringifiableInterface) {
				$id = single_quote($id);
			}
			return $this->getEffectJavaScriptFunctionName() . "({$id})";
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
