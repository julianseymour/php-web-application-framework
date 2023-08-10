<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\IncrementVariableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\command\NodeBearingCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;

abstract class InsertElementCommand extends MultipleElementCommand implements AllocationModeInterface, NodeBearingCommandInterface{

	use AllocationModeTrait;
	use ReferenceElementIdTrait;
	
	protected $onDuplicateId;

	public abstract static function getInsertWhere();

	public function __construct($insert_here, ...$inserted_elements){
		$f = __METHOD__;
		$print = false;
		parent::__construct(...$inserted_elements);
		foreach ($inserted_elements as $element) {
			if ($element instanceof Element) {
				if (! $element->getCatchReportedSubcommandsFlag()) {
					$element->setCatchReportedSubcommandsFlag(true);
				}
				if (! $element->hasSubcommandCollector()) {
					$element->setSubcommandCollector($this);
				}
			}
		}
		if (! isset($insert_here)) {
			Debug::error("{$f} insert here is undefined");
		} elseif ($print) {
			Debug::print("{$f} insertion target is defined");
		}
		if (is_string($insert_here)) {
			if ($print) {
				Debug::print("{$f} insertion target is the string \"{$insert_here}\"");
			}
			$this->setReferenceElementId($insert_here);
		} elseif (is_object($insert_here)) {
			if ($insert_here instanceof Element) {
				if ($print) {
					Debug::print("{$f} insertion target is an element");
				}
				if ($insert_here->hasIdOverride()) {
					if ($print) {
						Debug::print("{$f} insertion target has an ID override");
					}
					$id = $insert_here->getIdOverride();
					$this->setReferenceElementId($id);
				} elseif ($insert_here->hasIdAttribute()) {
					if ($print) {
						Debug::print("{$f} insertion target has an ID attribute");
					}
					$id = $insert_here->getIdAttribute();
					$this->setReferenceElementId($id);
				} elseif ($print) {
					$where = $insert_here->getDeclarationLine();
					Debug::print("{$f} insertion target does not have an ID attribute or override; declared {$where}");
				}
			} elseif ($insert_here instanceof ValueReturningCommandInterface) {
				$this->setElement($insert_here);
			} elseif ($print) {
				Debug::error("{$f} insertion target is an object, but not an element or command");
			}
		} else {
			Debug::error("{$f} invalid insertion target");
		}
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->onDuplicateId);
	}

	public function hasOnDuplicateIdCommand(){
		return isset($this->onDuplicateId) && $this->onDuplicateId instanceof Command;
	}

	public function setOnDuplicateIdCommand($command){
		return $this->onDuplicateId = $command;
	}

	public function getOnDuplicateIdCommand(){
		$f = __METHOD__;
		if (! $this->hasOnDuplicateIdCommand()) {
			Debug::error("{$f} on duplicate ID media command is undefined");
		}
		return $this->onDuplicateId;
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		Json::echoKeyValuePair('elements', $this->getElements(), $destroy);
		Json::echoKeyValuePair('insert_here', $this->getReferenceElementId(), $destroy);
		Json::echoKeyValuePair('where', $this->getInsertWhere(), $destroy);
		if ($this->hasOnDuplicateIdCommand()) {
			Json::echoKeyValuePair('onDuplicateId', $this->getOnDuplicateIdCommand(), $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public static final function getCommandId(): string{
		return "insert";
	}

	public function extractChildNodes(int $mode): ?array{
		return $this->getElements();
	}

	public static function extractAnyway(){
		return false;
	}

	public function resolve(){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public function evaluate(?array $params = null){
		return $this->resolve();
	}

	public function incrementVariableName(int &$counter){
		$f = __METHOD__;
		$print = false;
		foreach ($this->getElements() as $e) {
			if ($e instanceof IncrementVariableNameInterface) {
				$e->incrementVariableName($counter);
			} elseif ($print) {
				$gottype = is_object($e) ? $e->getClass() : gettype($e);
				Debug::print("{$f} element is a \"{$gottype}\"");
			}
		}
	}
}
