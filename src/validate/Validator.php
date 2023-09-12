<?php

namespace JulianSeymour\PHPWebApplicationFramework\validate;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessageTrait;
use JulianSeymour\PHPWebApplicationFramework\input\InputInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use Exception;
use mysqli;

abstract class Validator extends Basic implements JavaScriptCounterpartInterface, StaticPropertyTypeInterface{

	use ArrayPropertyTrait;
	use ErrorMessageTrait;
	use JavaScriptCounterpartTrait;
	use StaticPropertyTypeTrait;

	private $covalidateWhen;

	private $input;

	private $specialFailureStatus;

	public abstract function evaluate(&$validate_me): int;

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return [
			"covalidators" => Validator::class
		];
	}

	public function hasSpecialFailureStatus(){
		return isset($this->specialFailureStatus);
	}

	public function setSpecialFailureStatus($status){
		return $this->specialFailureStatus = $status;
	}

	public function getSpecialFailureStatus(){
		if($this->hasSpecialFailureStatus()) {
			return $this->specialFailureStatus;
		}
		return FAILURE;
	}

	public function hasCovalidators(){
		return $this->hasArrayProperty("covalidators");
	}

	public function pushCovalidators(...$covalidators){
		return $this->pushArrayProperty("covalidators", ...$covalidators);
	}

	public function getCovalidators(){
		return $this->getProperty("covalidators");
	}

	private function covalidate(&$validate_me){
		$f = __METHOD__; //Validator::getShortClass()."(".static::getShortClass().")->covalidate()";
		$print = false;
		if(!$this->hasCovalidators()) {
			Debug::error("{$f} no covalidators");
			return $this->getSpecialFailureStatus();
		}
		foreach($this->getCovalidators() as $validator) {
			if($print) {
				$vc = $validator->getClass();
				Debug::print("{$f} covalidate class is \"{$vc}\"");
			}
			$valid = $validator->validate($validate_me);
			if($valid !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($valid);
				Debug::warning("{$f} covalidator returned error status \"{$err}\"");
				return $this->setObjectStatus($valid);
			}
		}
		if($print) {
			Debug::print("{$f} all covalidators passed");
		}
		return SUCCESS;
	}

	private function covalidateBefore(){
		return $this->hasCovalidators() && $this->getCovalidateWhen() === COVALIDATE_BEFORE;
	}

	private function covalidateAfter()
	{
		return $this->hasCovalidators() && $this->getCovalidateWhen() === COVALIDATE_AFTER;
	}

	public function getCovalidateWhen(){
		if(! isset($this->covalidateWhen)) {
			return COVALIDATE_BEFORE;
		}
		return $this->covalidateWhen;
	}

	public function setCovalidateWhen(?string $when){
		if($when === null) {
			unset($this->covalidateWhen);
			return null;
		}
		return $this->covalidateWhen = $when;
	}

	public function extractParameters(&$params){
		return $params;
	}

	/**
	 * override this to deal with last-minute preparations that must occur prior to validation
	 *
	 * @param mysqli $mysqli
	 * @param array $validate_me
	 * @return int
	 */
	protected function prevalidate(&$validate_me){
		return SUCCESS;
	}

	/**
	 *
	 * @param mixed $validate_me
	 * @return int
	 */
	public final function validate(array &$validate_me): int{
		$f = __METHOD__;
		try{
			$print = false;
			$this->prevalidate($validate_me);
			if($this->covalidateBefore()) {
				$status = $this->covalidate($validate_me);
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} preemptive covalidation returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print) {
					Debug::print("{$f} preemptive covalidation successful");
				}
			}elseif($print) {
				Debug::print("{$f} skipping preemptive covalidation");
			}
			$valid = $this->evaluate($validate_me);
			if($valid !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($valid);
				Debug::warning("{$f} evaluate returned error status \"{$err}\"");
				return $this->setObjectStatus($valid);
			}elseif($print) {
				$did = $this->getDebugId();
				$decl = $this->getDeclarationLine();
				Debug::print("{$f} validation successful; debug ID is {$did}, instantiated {$decl}");
			}
			if($this->covalidateAfter()) {
				$status = $this->covalidate($validate_me);
				if($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} later covalidation returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print) {
					Debug::print("{$f} later covalidation successful");
				}
			}elseif($print) {
				Debug::print("{$f} skipping late covalidation");
			}
			return $this->setObjectStatus(SUCCESS);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setInput(InputInterface $input)
	{
		return $this->input = $input;
	}

	public function hasInput()
	{
		return isset($this->input);
	}

	public function getInput()
	{
		$f = __METHOD__; //Validator::getShortClass()."(".static::getShortClass().")->getInput()";
		if(!$this->hasInput()) {
			Debug::error("{$f} input is undefined");
		}
		return $this->input;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->covalidateWhen);
		unset($this->input);
		unset($this->specialFailureStatus);
	}
}
