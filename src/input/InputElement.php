<?php

namespace JulianSeymour\PHPWebApplicationFramework\input;

use function JulianSeymour\PHPWebApplicationFramework\ends_with;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\require_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\control\IfCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\event\SetOnChangeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\event\SetOnInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\event\SetOnPropertyChangeCommand;
use JulianSeymour\PHPWebApplicationFramework\command\func\CallFunctionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SetInputValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\GenericData;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\ValuedElement;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\FormAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\TypeAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSubindexEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeSubindexEvent;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\choice\SelectInput;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\security\honeypot\Hunnypot;
use JulianSeymour\PHPWebApplicationFramework\validate\AjaxValidatorInterface;
use JulianSeymour\PHPWebApplicationFramework\validate\InstantValidatorInterface;
use JulianSeymour\PHPWebApplicationFramework\validate\MultipleValidatorsTrait;
use JulianSeymour\PHPWebApplicationFramework\validate\OnSubmitValidatorInterface;
use Closure;
use Exception;

abstract class InputElement extends ValuedElement implements InputInterface{

	use ColumnNameTrait;
	use FormAttributeTrait;
	use MultipleValidatorsTrait;
	use NameAttributeTrait;
	use TypeAttributeTrait;

	protected $honeypot;

	protected $labelElement;

	protected $negotiator;

	public abstract static function getTypeAttributeStatic(): string;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setTypeAttribute($this->getTypeAttribute());
	}

	public function configure(AjaxForm $form): int{
		$f = __METHOD__;
		$print = false;
		if($this->hasContext() && ! $this->hasLabelString()) {
			$datum = $this->getContext();
			$cn = $datum->getName();
			if($datum->hasHumanReadableName()) {
				if($print) {
					Debug::print("{$f} datum has a human-readable name");
				}
				$hrvn = $datum->getHumanReadableName();
				if($print) {
					Debug::print("{$f} human readable name for column \"{$cn}\" is \"{$hrvn}\"");
				}
				/*if($form->hasSuperiorForm()) {
					$sf = $form->getSuperiorForm();
					$si = $form->getSuperiorFormIndex();
					$sc = $sf->getContext();
					$sd = $sc->getColumn($si);
					if($sd->hasHumanReadableName()) {
						$sv = $sd->getHumanReadableName();
						$hrvn = new ConcatenateCommand($sv, " (", $hrvn, ")");
					}
				}*/
				$this->setLabelString($hrvn);
			}elseif($print) {
				Debug::print("{$f} human readable name is undefined for column \"{$cn}\"");
			}
		}elseif($print) {
			Debug::print("{$f} context is undefined");
		}
		if(!$this->getTemplateFlag() && !$this->hasIdAttribute() && $form->hasIdAttribute() && $this->hasNameAttribute()){
			while($form->hasSuperiorForm()){
				$form = $form->getSuperiorForm();
			}
			if(!$form->hasIdAttribute()){
				return SUCCESS;
			}
			$this->setIdAttribute($form->getIdAttribute()."-".$this->getNameAttribute());
		}
		return SUCCESS;
	}

	public function dispose(): void{
		$f = __METHOD__;
		$print = false;
		if($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		parent::dispose();
		unset($this->form);
		unset($this->columnName);
		unset($this->honeypot);
		unset($this->labelElement);
		unset($this->negotiator);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"allowReservedName",
			"debugValidators",
			"decoy",
			"ignoreDatumSensitivity",
			"useFormAttribute"
		]);
	}

	public static function isEmptyElement(): bool{
		return true;
	}

	public function formNoValidate(){
		return $this->setAttribute("formnovalidate", null);
	}

	public function getDecoyFlag(): bool{
		return $this->getFlag("decoy");
	}

	public function setDecoyFlag(bool $value = true): bool{
		return $this->setFlag("decoy", $value);
	}

	public static function getElementTagStatic(): string{
		return "input";
	}

	/**
	 * parse a posted value into something that can be processed by a datum
	 * useful for DateTimeInput to convert a string to unix timestamp for example
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	protected function parseValue($value){
		return $value;
	}

	public function hasNegotiator(): bool{
		return isset($this->negotiator) && ($this->negotiator instanceof Closure || $this->negotiator instanceof StaticValueNegotiationInterface);
	}

	public function setNegotiator($negotiator){
		$f = __METHOD__;
		if($negotiator == null) {
			unset($this->negotiator);
			return null;
		}
		return $this->negotiator = $negotiator;
	}

	public function getNegotiator(){
		$f = __METHOD__;
		if(!$this->hasNegotiator()) {
			$cn = $this->getColumnName();
			Debug::error("{$f} input \"{$cn}\" lacks a negoatiator");
		}
		return $this->negotiator;
	}

	/**
	 * Convert the value into something non-human readable that can be processed by a Datum.
	 * Needed because certain inputs (e.g. DateTimeLocal) have unusual formats
	 *
	 * @return NULL
	 */
	public function negotiateValue(Datum $column){
		$f = __METHOD__;
		$cn = $this->getColumnName();
		$print = $this->getDebugFlag();
		if($this->hasNegotiator()) {
			if($print) {
				Debug::print("{$f} this input has an assigned negotiator");
			}
			$negotiator = $this->getNegotiator();
			if($negotiator instanceof Closure) {
				return $negotiator($this, $column);
			}elseif(is_string($negotiator) && is_a($negotiator, StaticValueNegotiationInterface::class, true)) {
				if($print) {
					Debug::print("{$f} negitiator is a static value negotiation interface");
				}
				return $negotiator::negotiateValueStatic($this, $column);
			}
			$nc = is_object($negotiator) ? $negotiator->getClass() : gettype($negotiator);
			Debug::error("{$f} whoops, negotiator is a {$nc}\"");
		}elseif($this instanceof StaticValueNegotiationInterface) {
			if($print) {
				Debug::print("{$f} this object is the negiotiator, and it is a static value negotiator interface");
			}
			return $this->negotiateValueStatic($this, $column);
		}
		$value = $this->getValueAttribute();
		if($print) {
			$gottype = gettype($value);
			Debug::print("{$f} input \"{$cn}\" is not a negitoatior, nor does it have one. Returning {$gottype} \"{$value}\"");
		}
		return $value;
	}

	public function processArray(array $arr): int{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($arr === null) {
			Debug::error("{$f} null parameter");
		}
		$name = $this->getNameAttribute();
		if($print) {
			Debug::print("{$f} name attribute is \"{$name}\"");
		}
		if(array_key_exists($name, $arr)) {
			$value = $arr[$name];
			if($value === null || $value === "") {
				if($print) {
					Debug::print("{$f} value is empty");
				}
				if($this->hasValueAttribute()) {
					$this->removeValueAttribute();
				}
				return SUCCESS;
			}
			$value = $this->parseValue($value);
			if($print) {
				Debug::print("{$f} returning \"{$value}\"");
			}
			$this->setValueAttribute($value);
		}elseif($print) {
			Debug::print("{$f} nothing to process for \"{$name}\"");
			Debug::printArray($arr);
		}
		return SUCCESS;
	}

	public function hasLabelElement(): bool{
		return isset($this->labelElement);
	}

	public function getHoneypot(){
		return $this->honeypot;
	}

	public function hasHoneypot(): bool{
		return isset($this->honeypot);
	}

	public function setHoneypot($pot){
		return $this->honeypot = $pot;
	}

	/**
	 * define how many honeypots this input should generate, and generate them now
	 *
	 * @param int $decoy_count
	 * @return int
	 */
	public function setHoneypotCount(int $decoy_count): int{
		$f = __METHOD__;
		try{
			require_class("Hunnypot");
			$pot = new Hunnypot($this);
			$pot->setDecoyCount($decoy_count);
			// $this->predecessor/Decoys = [];
			// $this->successor/Decoys = [];
			$all_count = $decoy_count + 1;
			$position = random_int(0, $decoy_count) + 1; // \Sodium\randombytes_uniform($decoy_count) + 1; //random % (decoy_count+1) = position of real input
			$decoy_num = 0;
			$nonce = $pot->getNonce();
			$after = false;
			for ($i = 0; $i < $all_count; $i ++) {
				if($position === $i) {
					// Debug::print("{$f} this input's position in the list of decoys is \"{$position}\"");
					$after = true;
					continue;
				}
				$decoy_num ++;
				$decoy_class = $this->getClass();
				$decoy = new $decoy_class();
				$decoy->setDecoyFlag(true);
				$attributes = $this->getAttributes();
				if(!empty($attributes)) {
					foreach(array_keys($attributes) as $attribute_key) {
						switch ($attribute_key) {
							case "name":
								// Debug::print("{$f} about to generate decoy name attribute");
								$name = Hunnypot::generateDecoyNameAttribute($nonce, $decoy_num);
								// Debug::print("{$f} generated name attribute \"{$name}\"");
								$decoy->setNameAttribute($name);
								break;
							case "id":
								// Debug::print("{$f} skipping ID copy");
								$decoy->setIdAttribute(null);
								break;
							case "required":
								continue 2;
							default:
								// Debug::print("{$f} about to copy attribute \"{$attribute_key}\"");
								$decoy->setAttribute($attribute_key, $this->getAttribute($attribute_key));
								break;
						}
					}
				}
				if($after) {
					$this->pushSuccessorDecoys($decoy);
				}else{
					$this->pushPredecessorDecoys($decoy);
				}
			}
			// Debug::print("{$f} returning normally");
			return $decoy_count;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 *
	 * @return LabelElement
	 */
	public function getLabelElement(): LabelElement{
		return $this->labelElement;
	}

	public function setLabelElement(?LabelElement $label): ?LabelElement{
		return $this->labelElement = $label;
	}

	public function getIgnoreDatumSensitivityFlag(): bool{
		return $this->getFlag("ignoreDatumSensitivity");
	}

	public function setIgnoreDatumSensitivity(bool $ignore = true): bool{
		return $this->setFlag("ignoreDatumSensitivity", $ignore);
	}

	public function hasValueAttribute(): bool{
		$f = __METHOD__;
		$print = false;
		if(!$this->getIgnoreDatumSensitivityFlag() && $this->getSensitiveFlag()) {
			if($print) {
				$cn = $this->hasColumnName() ? $this->getColumnName() : CONST_UNDEFINED;
				Debug::print("{$f} input with index \"{$cn}\" is sensitive -- returning false");
			}
			return false;
		}
		return parent::hasValueAttribute();
	}

	public function setOnKeyUpAttribute($handler){
		return $this->setAttribute("onkeyup", $handler);
	}

	public function getOnKeyUpAttribute(){
		return $this->getAttribute("onkeyup");
	}

	public function hasOnKeyUpAttribute(): bool{
		return $this->hasAttribute("onkeyup");
	}

	public function hasOnChangeAttribute(): bool{
		return $this->hasAttribute("onchange");
	}

	public function hasOnBlurAttribute(): bool{
		return $this->hasAttribute("onblur");
	}

	public function getOnBlurAttribute(){
		$f = __METHOD__;
		if(!$this->hasOnBlurAttribute()) {
			Debug::error("{$f} onblur attribute is undefined");
		}
		return $this->getAttribute("onblur");
	}

	public function setOnBlurAttribute($onblur){
		return $this->setAttribute("onblur", $onblur);
	}

	public function setOnChangeAttribute($handler){
		return $this->setAttribute("onchange", $handler);
	}

	public function getOnChangeAttribute(){
		return $this->getAttribute("onchange");
	}

	public function getOnPropertyChangeAttribute(){
		$f = __METHOD__;
		if(!$this->hasOnPropertyChangeAttribute()) {
			Debug::error("{$f} on property change attribute is undefined");
		}
		return $this->getAttribute("onpropertychange");
	}

	public function hasOnPropertyChangeAttribute(): bool{
		return $this->hasAttribute("onpropertychange");
	}

	public function setOnPropertyChangeAttribute($onpropertychange){
		return $this->setAttribute("onpropertychange", $onpropertychange);
	}

	/**
	 * kind of a hack but sensitivity should not get altered after declareColumns
	 *
	 * @return boolean
	 */
	public function getSensitiveFlag(): bool{
		$f = __METHOD__;
		if(!$this->hasContext()) {
			// Debug::print("{$f} this object lacks a datum");
			return false;
		}
		$datum = $this->getContext();
		return $datum->getSensitiveFlag();
	}

	public function hasOnFocusAttribute(): bool{
		return $this->hasAttribute("onfocus");
	}

	public function getOnFocusAttribute(){
		return $this->getAttribute("onfocus");
	}

	public function setOnFocusAttribute($onfocus){
		return $this->setAttribute("onfocus", $onfocus);
	}

	public function hasStepAttribute(){
		return false;
	}

	public function getColsAttribute(){
		return $this->getAttribute("cols");
	}

	public static function getValidOnEventCommands(): ?array{
		$keyvalues = parent::getValidOnEventCommands();
		$keyvalues['onchange'] = SetOnChangeCommand::class;
		$keyvalues["oninput"] = SetOnInputCommand::class;
		$keyvalues['onpropertychange'] = SetOnPropertyChangeCommand::class;
		return $keyvalues;
	}

	public function setAllowReservedNameFlag(bool $value = true): bool{
		return $this->setFlag("allowReservedName", $value);
	}

	/**
	 * assign name and value attributes, and index and reference to the datum;
	 * if the datum is standalone (not part of a structure) this function will fail
	 *
	 * @param Datum $datum
	 */
	public function bindContext($context){
		$f = __METHOD__;
		try{
			$vn = $context->getName();
			$print = $this->getDebugFlag();
			if($print) {
				$decl = $this->getDeclarationLine();
				$dsc = $context->hasDataStructure() ? get_short_class($context->getDataStructure()) : "unknown";
				$did = $this->getDebugId();
				Debug::printStackTraceNoExit("{$f} entered. Declared {$decl}. Column name is \"{$vn}\". Debug ID is {$did}. Context's data structure is a {$dsc}");
			}
			$this->setColumnName($vn);
			$this->setNameAttribute($vn);
			if(!$this instanceof SelectInput && $context->hasDataStructure()){
				if($print){
					Debug::print("{$f} this is not a select input, and context has a data structure");
				}
				$data = $context->getDataStructure();
				$get = new GetColumnValueCommand($data, $vn);
				if($this instanceof HiddenInput){
					$format = READABILITY_READABLE;
				}else{
					$format = READABILITY_WRITABLE;
				}
				if($print) {
					Debug::print("{$f} assigning readability \"{$format}\"");
				}
				$get->setFormat($format);
				$has = new HasColumnValueCommand($data, $vn);
				if($this->getTemplateFlag()){
					if($print){
						Debug::print("{$f} template flag is set");
					}
					$predicate = CommandBuilder::and(
						new GetDeclaredVariableCommand("context"),
						$has
					);
				}else{
					if($print){
						Debug::print("{$f} template flag is not set");
					}
					$predicate = $has;
				}
				$set = new SetInputValueCommand($this, $get);
				if($print) {
					$set->debug();
					$has->debug();
					$did = $set->getDebugId();
					Debug::print("{$f} instantiated a SetInputValueCommand with debug ID \"{$did}\"");
				}
				$if = new IfCommand($predicate);
				$if->then($set);
				$this->resolveTemplateCommand($if);
			}
			if($print){
				$name = $this->getNameAttribute();
				Debug::print("{$f} name attribute is \"{$name}\"; returning parent function");
			}
			return parent::bindContext($context);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setInputValueCommand($value): SetInputValueCommand{
		$f = __METHOD__;
		return new SetInputValueCommand($this, $value);
	}

	public function hasColsAttribute(): bool{
		return $this->hasAttribute("cols");
	}

	public function setColsAttribute($cols){
		return $this->setAttribute("cols", $cols);
	}

	public function getRowsAttribute(){
		$f = __METHOD__;
		if(!$this->hasRowsAttribute()) {
			Debug::error("{$f} rows attribute is undefined");
		}
		return $this->getAttribute("rows");
	}

	public function hasRowsAttribute(): bool{
		return $this->hasAttribute("rows");
	}

	public function setRowsAttribute($rows){
		return $this->setAttribute("rows", $rows);
	}

	public function getLabelString(): string{
		$f = __METHOD__;
		if(!$this->hasLabelString()){
			Debug::error("{$f} label string is undefined");
		}
		return $this->labelString;
	}

	public function hasLabelString(): bool{
		return isset($this->labelString);
	}

	public function setSuccessorDecoys(?array $values): ?array{
		return $this->setArrayProperty("successorDecoys", $values);
	}

	public function pushSuccessorDecoys(...$values): int{
		return $this->pushArrayProperty("successorDecoys", ...$values);
	}

	public function hasSuccessorDecoys(): bool{
		return $this->hasArrayProperty("successorDecoys");
	}

	public function getSuccessorDecoys(): ?array{
		return $this->getProperty("successorDecoys");
	}

	public function getSuccessorDecoyCount(): int{
		return $this->getArrayPropertyCount("successorDecoys");
	}

	protected function generateSuccessors(): ?array{
		$f = __METHOD__;
		try{
			$arr = parent::generateSuccessors();
			if($arr === null) {
				$arr = [];
			}
			if($this->hasSuccessorDecoys()) {
				$arr = array_merge($arr, $this->getSuccessorDecoys());
			}
			if($this->hasHoneypot()) {
				array_push($arr, $this->honeypot);
			}
			// Debug::print("{$f} returning normally");
			return $arr;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	protected function beforeRenderHook(): int{
		$f = __METHOD__;
		try{
			$print = false;
			$ret = parent::beforeRenderHook();
			if($this->getContentsGeneratedFlag()) {
				Debug::warning("{$f} contents already generated");
				return $ret;
			}elseif(!$this->hasValidators()) {
				if($print) {
					Debug::print("{$f} no validators to concern ourselves with");
				}
				return $ret;
			}
			$this->generateValidatorAttributes();
			return $ret;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * generates the attributes needed for automatica client-side validation to work
	 */
	protected function generateValidatorAttributes(): void{
		$f = __METHOD__;
		try{
			if($this->hasColumnName()) {
				$cn = $this->getColumnName();
			}elseif($this->hasNameAttribute()) {
				$cn = $this->getNameAttribute();
			}else{
				$cn = "undefined";
			}
			$print = $this->getDebugFlag();
			if($print){
				Debug::print("{$f} entered");
			}
			$instant = [];
			$submit = [];
			$validators = $this->getValidators();
			if($print) {
				$count = count($validators);
				Debug::print("{$f} about to iterate through {$count} validators");
				$validator_classes = [];
				foreach($validators as $validator){
					array_push($validator_classes, get_short_class($validator));
				}
				Debug::printArray($validator_classes);
			}
			foreach($validators as $validator) {
				$vc = get_short_class($validator);
				if($print){
					Debug::print("{$f} validator class \"{$vc}\"");
				}
				// AjaxValidators send data to server automatically
				if($validator instanceof AjaxValidatorInterface) {
					if($this->hasAttribute("__ajaxValidator")) {
						$did = $this->getDebugId();
						$decl = $this->getDeclarationLine();
						$sc = get_short_class($this);
						Debug::error("{$f} this input already has an ajax validator of class ".$this->getAttribute("__ajaxValidator")."; you are allowed at most one. Class is {$sc}. Debug ID is {$did}, declared {$decl}");
					}elseif($print) {
						Debug::print("{$f} applying AjaxValidator \"{$vc}\"");
					}
					$this->setAttribute("__ajaxValidator", $vc);
				}elseif($validator instanceof InstantValidatorInterface) { // non-AJAX InstantValidators
					if(false !== array_search($vc, $instant)) {
						Debug::error("{$f} duplicate instant validator \"{$vc}\" for input \"{$cn}\"");
					}elseif($print) {
						Debug::print("{$f} adding instant validator \"{$vc}\"");
					}
					array_push($instant, $vc);
				}elseif($validator instanceof OnSubmitValidatorInterface) {
					if($validator instanceof AjaxValidatorInterface) {
						Debug::error("{$f} {$vc} cannot implement both Ajax and OnSubmit validator interfaces");
					}elseif(false !== array_search($vc, $submit)) {
						Debug::error("{$f} dubplicate OnSubmit validator \"{$vc}\" for input \"{$cn}\"");
					}elseif($print) {
						Debug::print("{$f} adding OnSubmit validator \"{$vc}\"");
					}
					array_push($submit, $vc);
				}
			}
			// if there are any instant validators, set the instantValidators attribute
			// to a comma-separated list of validator names, and set this input's oninput attribute
			// to instantValidateStatic
			if(!empty($instant) || $this->hasAttribute("__ajaxValidator")) {
				if($this->hasOnInputAttribute()) {
					Debug::error("{$f} input \"{$cn}\" already has an oninput attribute");
				}elseif(!empty($instant)) {
					if($print) {
						$count = count($instant);
						Debug::print("{$f} {$count} instant validators");
					}
					$this->setAttribute("__instantValidators", implode(',', $instant));
				}elseif($print) {
					Debug::print("{$f} no instant validators; setting oninput attribute exclusively for AjaxValidator");
				}
				$this->setOnInputAttribute("Validator.instantValidateStatic(event, this);");
			}elseif($print) {
				Debug::print("{$f} no instant validators");
			}
			// if there are any validators that fire only on submit, set the submitValidators attribute
			// to a comma-separated list of validator names
			if(!empty($submit)) {
				if($print) {
					$count = count($submit);
					Debug::print("{$f} {$count} OnSubmit validators");
				}
				$this->setAttribute("__submitValidators", implode(',', $submit));
			}elseif($print) {
				Debug::print("{$f} no OnSubmit validators");
			}
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setPredecessorDecoys(?array $values): ?array{
		return $this->setArrayProperty("predecessorDecoys", $values);
	}

	public function hasPredecessorDecoys(): bool{
		return $this->hasArrayProperty("predecessorDecoys");
	}

	public function getPredecessorDecoys(): ?array{
		return $this->getProperty("predecessorDecoys");
	}

	public function getPredecessorDecoyCount(): int{
		return $this->getArrayPropertyCount("predecessorDecoys");
	}

	public function pushPredecessorDecoys(...$values): int{
		return $this->pushArrayProperty("predecessorDecoys", ...$values);
	}

	protected function generatePredecessors(): ?array{
		$f = __METHOD__;
		try{
			$arr = parent::generatePredecessors();
			if($arr === null) {
				$arr = [];
			}
			if($this->hasPredecessorDecoys()) {
				$arr = array_merge($arr, $this->getPredecessorDecoys());
			}
			// Debug::print("{$f} returning normally");
			return $arr;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function hasAutofocusAttribute(): bool{
		return $this->hasAttribute("autofocus");
	}

	public function setAutofocusAttribute($value){
		return $this->setAttribute("autofocus", $value);
	}

	public function getAutofocusAttribute(){
		$f = __METHOD__;
		if(!$this->hasAutofocusAttribute()) {
			Debug::error("{$f} autofocus attribute is undefined");
		}
		return $this->getAttribute("autofocus");
	}

	public function getTypeAttribute(): string{
		$f = __METHOD__;
		try{
			$attributes = [
				INPUT_TYPE_BUTTON => 'button',
				INPUT_TYPE_CHECKBOX => 'checkbox',
				INPUT_TYPE_COLOR => 'color',
				INPUT_TYPE_DATE => 'date',
				INPUT_TYPE_DATETIME_LOCAL => 'datatime-local',
				INPUT_TYPE_EMAIL => 'email',
				INPUT_TYPE_FILE => 'file',
				INPUT_TYPE_HIDDEN => 'hidden',
				INPUT_TYPE_IMAGE => 'image',
				INPUT_TYPE_MONTH => 'month',
				INPUT_TYPE_NUMBER => 'number',
				INPUT_TYPE_PASSWORD => 'password',
				INPUT_TYPE_RADIO => 'radio',
				INPUT_TYPE_RANGE => 'range',
				INPUT_TYPE_RESET => 'reset',
				INPUT_TYPE_SEARCH => 'search',
				INPUT_TYPE_SUBMIT => 'submit',
				INPUT_TYPE_TEL => 'tel',
				INPUT_TYPE_TEXT => 'text',
				INPUT_TYPE_TIME => 'time',
				INPUT_TYPE_URL => 'url',
				INPUT_TYPE_WEEK => 'week',
				// everything below this line is invalid
				INPUT_TYPE_UNDEFINED => 'none',
				INPUT_TYPE_SELECT => 'select',
				INPUT_TYPE_TEXTAREA => 'textarea'
			];
			$type = $this->getTypeAttributeStatic();
			if(! isset($type)) {
				Debug::error("{$f} input type is undefined");
			}
			$error = "error";
			$attr = array_key_exists($type, $attributes) ? $attributes[$type] : $error;
			if($attr == $error) {
				Debug::error("{$f} invalid type \"{$type}\"");
			}
			// Debug::print("{$f} returning \"{$attr}\"");
			return $attr;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * for submit inputs, this is the string printed on the button label
	 * for everything else, they can appear inside the appendix, or may not be used
	 *
	 * @param string $str
	 * @return string
	 */
	public function setLabelString($str){
		$f = __METHOD__;
		return $this->labelString = $str;
	}

	public function hasOnInputAttribute(){
		return $this->hasAttribute("oninput");
	}

	public function getOnInputAttribute(){
		$f = __METHOD__;
		if(!$this->hasOnInputAttribute()) {
			Debug::error("{$f} oninput attribute is undefined");
		}
		return $this->getAttribute("oninput");
	}

	/**
	 * return a javascript command for appending this input's value (extracted when binding the form)
	 * directly to a FormData, for submitting a form in scenarios where the HTML Document class is
	 * unavailable (e.g.
	 * in the service worker)
	 */
	public function getFormDataAppensionCommand($formdata_name = null){
		$f = __METHOD__;
		try{
			if(empty($formdata_name)) {
				$formdata_name = "fd";
			}
			$name = $this->getNameAttribute();
			$ds = new GenericData();
			$ds->setIdentifierName($this->getColumnName());
			$value = new GetColumnValueCommand($ds, $this->getColumnName());
			return new CallFunctionCommand("{$formdata_name}.append", $name, $value);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function setOnInputAttribute($oninput){
		return $this->setAttribute("oninput", $oninput);
	}

	public function hasOnInvalidAttribute():bool{
		return $this->hasAttribute("oninvalid");
	}

	public function getOnInvalidAttribute(){
		return $this->getAttribute("oninvalid");
	}

	public function setOnInvalidAttribute($value){
		return $this->setAttribute("oninvalid", $value);
	}

	/**
	 * Rename this input to make it part of an array $super_column_name
	 * e.g.
	 * old_name => super_column_name[old_name]
	 *
	 * {@inheritdoc}
	 * @see InputInterface::subindexNameAttribute()
	 */
	public function subindexNameAttribute($super_column_name){
		$f = __METHOD__;
		try{
			$print = $this->getDebugFlag();
			$this->dispatchEvent(new BeforeSubindexEvent($super_column_name));
			$oldname = $this->getNameAttribute();
			if($print) {
				Debug::print("{$f} old name is \"{$oldname}\"; about to subindex under \"{$super_column_name}\"");
			}
			$array = false;
			if(ends_with($oldname, "[]")) {
				if($print) {
					Debug::print("{$f} old name attribute was already an array");
				}
				$tempname = substr($oldname, 0, strlen($oldname) - 2);
				$array = true;
			}else{
				if($print) {
					Debug::print("{$f} old name attribute is just a regular string");
				}
				$tempname = $oldname;
			}
			$regex = '/([A-Za-z]+[A-Za-z0-9-_;.]*(\[[A-Za-z0-9-_;.]+\])+)/';
			// $regex = '/([A-Za-z]+[A-Za-z0-9-_;.]*(\[[A-Za-z][A-Za-z0-9-_;.]*\])+)/';
			if(preg_match($regex, $tempname)) {
				if($print) {
					Debug::print("{$f} temporary variable name \"{$tempname}\" is in the format name[index]+; about to split at []");
				}
				$splat = preg_split('/[\[\]]/', $tempname);
				$newname = $super_column_name;
				foreach($splat as $fragment) {
					if(empty($fragment)) {
						if($print) {
							Debug::print("{$f} fragment is empty string, continuing");
						}
						continue;
					}
					if(ends_with($fragment, "]")) {
						Debug::error("{$f} preg_split didn't work as planned");
					}elseif($print) {
						Debug::print("{$f} appending fragment \"{$fragment}\"");
					}
					$newname .= "[{$fragment}]";
				}
			}else{
				if($print) {
					Debug::print("{$f} temporary variable name \"{$tempname}\" is not in the format name[index]+");
				}
				$newname = "{$super_column_name}[{$tempname}]";
			}
			if($array) {
				if($print) {
					Debug::print("{$f} appending []");
				}
				$newname .= "[]";
			}elseif($print) {
				Debug::print("{$f} forgoing appension of []");
			}
			if($print) {
				Debug::print("{$f} old name was \"{$oldname}\"; new name is \"{$newname}\"");
			}
			$ret = $this->setNameAttribute($newname);
			$this->dispatchEvent(new AfterSubindexEvent($super_column_name, $oldname));
			return $ret;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
