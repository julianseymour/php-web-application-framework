<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveTrait;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SoftDisableInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatumInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\FormElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\ScriptElement;
use JulianSeymour\PHPWebApplicationFramework\element\StyleElement;
use JulianSeymour\PHPWebApplicationFramework\event\SubmitEvent;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\input\InputInterface;
use JulianSeymour\PHPWebApplicationFramework\input\MultipleInputsTrait;
use JulianSeymour\PHPWebApplicationFramework\input\URLInput;
use JulianSeymour\PHPWebApplicationFramework\input\choice\ChoiceGeneratorInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenCommand;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\SecondaryHmacCommand;
use JulianSeymour\PHPWebApplicationFramework\style\CssRule;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpanderElement;
use JulianSeymour\PHPWebApplicationFramework\validate\FormDataIndexValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use JulianSeymour\PHPWebApplicationFramework\validate\ValidatorTrait;
use Exception;

abstract class AjaxForm extends FormElement implements JavaScriptCounterpartInterface{

	use ColumnNameTrait;
	use JavaScriptCounterpartTrait;
	use MultipleInputsTrait;
	use PermissiveTrait;
	use StyleSheetPathTrait;
	use ValidatorTrait;

	/**
	 * special identifier for the server to recognize which form is being submitted
	 * XXX might as well just use the class name
	 *
	 * @var string
	 */
	protected $formDispatchId;

	/**
	 * generally not used -- see AjaxForm->getFormInputManifest
	 * this is like FormDataIndices except it also includes incidental inputs that are not involved in automatic form processing
	 *
	 * @var array
	 */
	protected $formInputManifest;

	/**
	 * Command class used to generate submit buttons
	 *
	 * @var string
	 */
	protected $generateFormButtonsCommandClass;

	/**
	 * IDs of inputs to disable with javascript once the form is rendered
	 *
	 * @var array
	 */
	protected $softDisabledInputIds;

	/**
	 * name of javascript function to call after the user's browser receives a successful response
	 *
	 * @var string
	 */
	protected $successCallback;

	/**
	 * name of javascript function to call if something goes wrong
	 *
	 * @var string
	 */
	protected $errorCallback;

	/**
	 * validates this form
	 *
	 * @var Validator
	 */
	protected $validator;

	// XXX forgot what most of this junk is used for
	protected $directives;

	protected $honeypotStyleElement;

	protected $honeypots;

	protected $importedCollapseLabel;

	protected $superiorForm;

	protected $superiorFormIndex;

	public abstract function getDirectives(): ?array;

	public abstract function generateButtons(string $directive): ?array;

	public abstract function getFormDataIndices(): ?array;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$f = __METHOD__;
		try {
			$print = false;
			$form_id = $this->getFormDispatchIdStatic();
			if ($form_id !== null) {
				$this->setFormDispatchId($form_id);
			}
			parent::__construct($mode, $context);
			if (method_exists($this, "getActionAttributeStatic")) {
				$action = static::getActionAttributeStatic($context);
				if (! empty($action)) {
					$this->setActionAttribute($action);
				} elseif ($print) {
					Debug::print("{$f} getActionAttributeStatic returned null");
				}
			} elseif ($print) {
				Debug::print("{$f} no method getActionAttributeStatic exists");
			}
			if (! $this->skipFormInitialization()) {
				$this->addClassAttribute("ajax_form");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getJavaScriptClassPath(): ?string{
		$fn = get_class_filename(AjaxForm::class);
		return substr($fn, 0, strlen($fn) - 3) . "js";
	}

	public static function getActionAttributeStatic(): ?string{
		return null;
	}

	public static function getSuccessCallbackStatic(): ?string{
		return 'controller';
	}

	public static function getFormDispatchIdStatic(): ?string{
		return static::class;
	}

	protected static function getGenerateFormButtonsCommandClassStatic(): ?string{
		return GenerateStaticFormButtonsCommand::class;
	}

	public static function getErrorCallbackStatic(): ?string{
		return "error_cb";
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_GET;
	}

	public function submitHook(): int{
		$this->dispatchEvent(new SubmitEvent());
		return SUCCESS;
	}

	public function getMethodAttribute(): ?string{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasMethodAttribute()) {
			if ($print) {
				$debug_id = $this->getDebugId();
				Debug::print("{$f} method attribute is undefined for form with debug ID \"{$debug_id}\" -- returning static fallback");
			}
			return $this->setMethodAttribute(static::getMethodAttributeStatic());
		} elseif ($print) {
			Debug::print("{$f} returning parent function");
		}
		return parent::getMethodAttribute();
	}

	public function getActionAttribute(): ?string{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasActionAttribute()) {
			$context = $this->hasContext() ? $this->getContext() : null;
			$static = $this->getActionAttributeStatic($context);
			if ($static === null) {
				if ($print) {
					Debug::print("{$f} static action attribute is undefined");
					$this->debugPrintRootElement();
				}
				return null;
			}
			return $this->setActionAttribute($static);
		}
		return parent::getActionAttribute();
	}

	public function hasGenerateFormButtonsCommandClass(): bool{
		return isset($this->generateFormButtonsCommandClass) && class_exists($this->generateFormButtonsCommandClass);
	}

	public function hasErrorCallback(): bool{
		return isset($this->errorCallback);
	}

	public function hasSuccessCallback(): bool{
		return isset($this->successCallback);
	}

	public function getSuccessCallback(): ?string{
		if (! $this->hasSuccessCallback()) {
			return static::getSuccessCallbackStatic();
		}
		return $this->successCallback;
	}

	public function getFormInputManifest(): ?array{
		if (! $this->hasFormInputManifest()) {
			$context = $this->hasContext() ? $this->getContext() : null;
			return $this->getFormDataIndices($context);
		}
		return $this->formInputManifest;
	}

	protected function getGenerateFormButtonsCommandClass(): ?string{
		if(!$this->hasGenerateFormButtonsCommandClass()) {
			return static::getGenerateFormButtonsCommandClassStatic();
		}
		return $this->generateFormButtonsCommandClass;
	}

	public function setGenerateFormButtonsCommandClass(?string $c): ?string{
		return $this->generateFormButtonsCommandClass = $c;
	}

	public function setSuccessCallback(?string $cb): ?string{
		return $this->successCallback = $cb;
	}

	public function setErrorCallback(?string $cb): ?string{
		return $this->errorCallback = $cb;
	}

	public function setDirectives(?array $directives): ?array{
		return $this->directives = $directives;
	}

	public function setFormInputManifest(?array $manifest): ?array{
		return $this->formInputManifest = $manifest;
	}

	public function hasFormDispatchId(): bool{
		return isset($this->formDispatchId);
	}

	public function hasFormInputManifest(): bool{
		return isset($this->formInputManifest) && is_array($this->formInputManifest);
	}

	public function setFormDispatchId(?string $id): ?string{
		return $this->formDispatchId = $id;
	}

	public function getFormDispatchId(): ?string{
		$f = __METHOD__;
		if (! $this->hasFormDispatchId()) {
			$id = static::getFormDispatchIdStatic();
			if ($id === null) {
				Debug::error("{$f} form dispatch ID is undefined");
			}
			return $id;
		}
		return $this->formDispatchId;
	}

	public function getInputClass(string $column_name): ?string{ // XXX TODO ugly
		$f = __METHOD__;
		$fdi = $this->getFormDataIndices();
		if (array_key_exists($column_name, $fdi)) {
			return $fdi[$column_name];
		}
		Debug::error("{$f} invalid column name \"{$column_name}\"");
		return null;
	}

	public final function getSubordinateForm(string $column_name, $ds): AjaxForm{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			if ($ds->hasIdentifierValue()) {
				Debug::print("{$f} struct identifier is " . $ds->getIdentifierValue());
			} else {
				Debug::print("{$f} struct lacks an identifier");
			}
		}
		$form_class = $this->getInputClass($column_name);
		if ($print) {
			Debug::print("{$f} column \"{$column_name}\" maps to a form of class \"{$form_class}\"");
		}
		$mode = $this->getTemplateFlag() ? ALLOCATION_MODE_FORM_TEMPLATE : ALLOCATION_MODE_FORM;
		$form = new $form_class($mode);
		if ($this->getAllocationMode() === ALLOCATION_MODE_TEMPLATE) {
			$form->setTemplateFlag(true);
		}
		if (! $form->hasActionAttribute() && $this->hasActionAttribute()) {
			$form->setActionAttribute($this->getActionAttribute());
		}
		$form->setSuperiorForm($column_name, $this);
		$form->bindContext($ds);
		return $form;
	}

	public function hasSuperiorForm(): bool{
		return isset($this->superiorForm) && $this->superiorForm instanceof FormElement;
	}

	public function setSuperiorForm($column_name, $form){
		$this->superiorFormIndex = $column_name;
		return $this->superiorForm = $form;
	}

	public function getSuperiorForm(): AjaxForm{
		$f = __METHOD__;
		if (! $this->hasSuperiorForm()) {
			Debug::error("{$f} superior form is undefined");
		} elseif (! $this->hasSuperiorFormIndex()) {
			Debug::error("{$f} superior form index is undefined");
		}
		return $this->superiorForm;
	}

	public function hasSuperiorFormIndex(): bool{
		return !empty($this->superiorFormIndex);
	}

	public function getSuperiorFormIndex(): ?string{
		$f = __METHOD__;
		if (! $this->hasSuperiorForm()) {
			Debug::error("{$f} superior form is undefined");
		} elseif (! $this->hasSuperiorFormIndex()) {
			Debug::error("{$f} superior form index is undefined");
		}
		return $this->superiorFormIndex;
	}

	/**
	 * set the number of decoys per each input
	 *
	 * @return array
	 */
	public static function getHoneypotCountArray(): ?array{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::print("{$f} replace me in a derived class if you want your form to have honeypots");
		}
		return [];
	}

	public function hasSoftDisabledInputIds(): bool{
		return is_array($this->softDisabledInputIds) && ! empty($this->softDisabledInputIds);
	}

	public function getSoftDisabledInputIds():?array{
		return $this->softDisabledInputIds;
	}

	public function getImportedCollapseLabel():?LabelElement{
		return $this->importedCollapseLabel;
	}

	public function hasImportedCollapseLabel():bool{
		return isset($this->importedCollapseLabel);
	}

	/**
	 * takes the collapse label from the expander element and adds it to the submit button labels
	 *
	 * @param ExpanderElement $expander
	 */
	public function setImportedCollapseLabel(?LabelElement $label){
		return $this->importedCollapseLabel = $label;
	}

	public function hasEncodingTypeAttribute(): bool{
		return $this->hasAttribute("enctype");
	}

	public function hasDirectives(): bool{
		return ! empty($this->directives);
	}

	/**
	 * automates many boring aspects of input generation by drawing information from the datum
	 *
	 * @param InputInterface $input
	 */
	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($input == null) {
				Debug::error("{$f} input is undefined");
			} elseif ($print) {
				Debug::print("{$f} calling input->configure(this)");
			}
			return $input->configure($this);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getHoneypotStyleElement(): StyleElement{
		if (isset($this->honeypotStyleElement)) {
			return $this->honeypotStyleElement;
		}
		$style = new StyleElement();
		$form_id = $this->getIdAttribute();
		$style->setIdAttribute(sha1($form_id . random_bytes(32)));
		$rule = new CssRule();
		$rule->setStyleProperty("display", "none !important");
		$style->appendChild($rule);
		return $this->honeypotStyleElement = $style;
	}

	public function dispatchCommands(): int{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($this->skipFormInitialization()){
			if ($print) {
				Debug::print("{$f} skipping form initialization command because this form does not get initialized");
			}
			return parent::dispatchCommands();
		}elseif($this->hasSoftDisabledInputIds()){
			$input_ids = $this->getSoftDisabledInputIds();
			foreach ($input_ids as $input_id) {
				$subcommand = new SoftDisableInputCommand($input_id);
				$this->reportSubcommand($subcommand);
			}
		}
		$subcommand = new InitializeFormCommand($this);
		if($print){
			Debug::print("{$f} about to report InitializeFormCommand");
		}
		$this->reportSubcommand($subcommand);
		return parent::dispatchCommands();
	}

	public function getErrorCallback(): ?string{
		if (! $this->hasErrorCallback()) {
			return static::getErrorCallbackStatic();
		}
		return $this->errorCallback;
	}

	public static function isUltraLazyRenderingCompatible(): bool{
		return false;
	}

	private final function subindexNestedInputHelper(&$input, string $super_index):void{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if(is_array($input)){
			if($print){
				Debug::print("{$f} input is an array, calling recursively");
			}
			foreach($input as $subinput){
				if($subinput instanceof AjaxForm){
					if($print){
						$decl = $subinput->getDeclarationLine();
						Debug::print("{$f} nested input is an AjaxForm, instantiated {$decl}");
					}
					foreach($subinput->getInputs() as $subsubinput){
						$this->subindexNestedInputHelper($subsubinput, $super_index);
					}
					continue;
				}
				$this->subindexNestedInputHelper($subinput, $super_index);
			}
			return;
		}elseif ($print) {
			$input_class = $input->getClass();
			Debug::print("{$f} about to call {$input_class}->subindexNameAttribute({$super_index})");
		}
		if($input instanceof AjaxForm){
			Debug::error("{$f} input is an AjaxForm");
		}
		$this->subindexNestedInput($input, $super_index);
		// return $reindex;
	}
	
	/**
	 * This function is called by subindexNestedInputHelper.
	 * Override to change subindexing behavior for this form.
	 *
	 * @param InputInterface|AjaxForm|array $input
	 * @param string $super_index
	 * @return string
	 */
	
	protected function subindexNestedInput(InputInterface &$input, string $super_index):void{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		$reindex = $input->subindexNameAttribute($super_index);
		if ($print) {
			Debug::print("{$f} set input name attribute to \"{$reindex}\"");
		}
	}
	
	public function getChoiceGenerationParameters($input): ?array{
		if ($this->hasSuperiorForm()) {
			return $this->getSuperiorForm()->getChoiceGenerationParameters($input);
		}
		return [];
	}

	/**
	 * generate subordinate form(s) for index $column_name bound to foreign data structure(s) $structs, subindex the
	 * inputs contained therein, and optionally stick them into a subcontainer
	 *
	 * @param Datum $datum
	 * @param DataStructure[] $structs
	 */
	public final function subindexNestedInputs(Datum $datum, $structs): array{
		$f = __METHOD__;
		try {
			$context = $this->getContext();
			$print = $this->getDebugFlag();
			$ret = [];
			$mode = $this->getAllocationMode();
			$column_name = $datum->getName();
			$multiple = false;
			if ($datum instanceof KeyListDatum || ($datum instanceof VirtualDatum && $datum->getReturnType() === TYPE_ARRAY)) {
				$multiple = true;
			}
			$struct_num = 0;
			$total_count = count($structs);
			foreach ($structs as $struct_id => $subordinate_struct) {
				if ($multiple) {
					if ($print) {
						Debug::print("{$f} struct ID is \"{$struct_id}\"");
					}
					$super_index = (new ConcatenateCommand($column_name, '[', $struct_id, ']'))->evaluate();
				} else {
					if ($print) {
						Debug::print("{$f} datum class is a ForeignKeyDatum");
					}
					$super_index = $column_name;
				}
				$subordinate_form = $this->getSubordinateForm($column_name, $subordinate_struct);
				if ($subordinate_form instanceof RepeatingFormInterface) {
					if (! $subordinate_form->hasIterator()) {
						if ($subordinate_struct->hasIterator()) {
							$subordinate_form->setIterator($subordinate_struct->getIterator());
						} else {
							$subordinate_form->setIterator($struct_num);
						}
					}
					if ($struct_num === $total_count - 1) {
						$subordinate_form->setLastChildFlag(true);
					} elseif ($print) {
						Debug::print("{$f} struct #{$struct_num} is not the last child of {$total_count}");
					}
				}
				$sfc = $subordinate_form->getClass();
				if ($print) {
					Debug::print("{$f} about to get input map for subordinate form of class \"{$sfc}\"");
				}
				if($subordinate_form->hasInputs()){
					if($print){
						Debug::print("{$f} inputs were already generated");
					}
					$subordinate_map = $subordinate_form->getInputs();
				}else{
					if($print){
						Debug::print("{$f} generating inputs now");
					}
					$subordinate_map = $subordinate_form->generateInputs($subordinate_struct);
				}
				if ($subordinate_struct->hasIdentifierValue()) {
					if ($print) {
						$fdsc = $subordinate_struct->getClass();
						Debug::print("{$f} foreign data structure of class \"{$fdsc}\" has an identifier value");
					}
					$key_input = new HiddenInput($mode);
					$name = $subordinate_struct->getIdentifierName();
					$key_input->setColumnName($name);
					$key_input->setNameAttribute($name);
					$key_input->setValueAttribute($subordinate_struct->getIdentifierValue());
					$this->reconfigureInput($key_input);
					$subordinate_map[$name] = $key_input;
				}
				if ($print) {
					Debug::print("{$f} about to print inputs generated from {$sfc}");
					foreach ($subordinate_map as $input_name => $input) {
						$gottype = is_object($input) ? $input->getClass() : gettype($input);
						Debug::print("{$f} {$input_name}: {$gottype}");
					}
				}
				foreach ($subordinate_map as $name => $input) {
					if ($print) {
						Debug::print("{$f} about to subindex input with name \"{$name}\"");
					}
					if (is_array($input)) {
						if ($print) {
							Debug::print("{$f} generated an array for index \"{$name}\"");
						}
						$this->subindexNestedInputHelper($input, $super_index);
					} elseif ($input instanceof InputInterface) {
						$input_class = $input->getClass();
						if (! $input->hasNameAttribute()) {
							$did = $input->getDebugId();
							$decl = $input->getDeclarationLine();
							Debug::error("{$f} {$input_class} input \"{$column_name}\" with debug ID \"{$did}\" lacks a name attribute; constructed {$decl}");
						}
						if ($print) {
							Debug::print("{$f} about to call subindexNestedInputHelper(input, {$super_index})");
						}
						$this->subindexNestedInputHelper($input, $super_index);
						$input->setForm($this);
					} elseif ($print) {
						Debug::error("{$f} subordinate container lacks child nodes, nothing to reindex");
					} elseif ($print) {
						Debug::print("{$f} subordinate form is its own input container (676)");
					}
					if (is_array($input)) {
						if ($print) {
							Debug::printArray(array_keys($subordinate_map));
							Debug::print("{$f} input \"{$name}\" generated an array");
						}
					} elseif (! $input instanceof InputInterface) {
						$decl = $input->getDeclarationLine();
						Debug::error("{$f} input is not an InputInterface; it was declared {$decl}");
					} elseif ($print) {
						Debug::print("{$f} pushing input for column \"{$column_name}\"");
					}
				}
				$subordinate_form->setInputs($subordinate_map);
				$ret[$struct_num ++] = $subordinate_form; // subordinate_map;
			}
			if ($print) {
				Debug::print("{$f} returning the following array:");
				foreach ($ret as $num => $element) {
					Debug::print("{$f} {$num} : " . $element->getClass());
				}
			}
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getValidateInputNames(): ?array{
		return array_keys($this->getFormDataIndices());
	}

	private final function attachInputValidatorsHelper($input){
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		$mode = $this->getAllocationMode();
		if ($input === null) {
			Debug::error("{$f} input is null");
		} elseif (is_array($input)) {
			if ($print) {
				Debug::print("{$f} input is an array");
			}
			foreach ($input as $sub_input) {
				$this->attachInputValidatorsHelper($sub_input);
			}
		}elseif($input instanceof AjaxForm){
			if($print){
				Debug::print("{$f} input is an AjaxForm");
			}
			foreach($input->getInputs() as $sub_input){
				$this->attachInputValidatorsHelper($sub_input);
			}
		} elseif (! $input->hasAllocationMode()) {
			Debug::error("{$f} input lacks a rendering mode on line 885");
		} elseif ($input instanceof InputInterface) {
			if (! $this->getDisableRenderingFlag() && $input->hasColumnName()) {
				$this->reconfigureInput($input);
			} elseif ($print) {
				Debug::print("{$f} rendering is disabled, or the input lacks a column name");
			}
			if ($print) {
				$input_class = $input->getClass();
				$column_name = $input->getColumnName();
				Debug::print("{$f} about to call attachInputValidators for {$input_class} at index \"{$column_name}\"");
			}
			if ($mode !== ALLOCATION_MODE_FORM && $mode !== ALLOCATION_MODE_FORM_TEMPLATE) {
				$this->attachInputValidators($input); //XXX causes duplicate validators
			}
		}elseif($input instanceof Element){
			if($print){
				Debug::print("{$f} input is an element");
			}
		}else{
			Debug::error("{$f} none of the above. Input is a ".$input->getShortClass());
		}
		return $input;
	}

	/**
	 * override this to apply validators to individual inputs
	 *
	 * @param InputInterface $input
	 * @return InputInterface
	 */
	protected function attachInputValidators(InputInterface $input):InputInterface{
		return $input;
	}
	
	/**
	 * override this with a function that calls setNegotiator on the input as needed
	 *
	 * @param InputInterface $input
	 * @return InputInterface
	 */
	public function attachNegotiator(InputInterface $input): InputInterface{
		return $input;
	}

	/**
	 * override this with something that returns true if this form should generate a new foreign data structure
	 * when the superior form does not have a structure for its foreign key
	 *
	 * @return boolean
	 */
	public static function getNewFormOption(): bool{
		return false;
	}

	public function generateChoices($input): ?array{
		$f = "AjaxForm(".static::getShortClass().")->generateChoices()";
		if($input->hasContext()){
			$context = $input->getContext();
			if($context instanceof ChoiceGeneratorInterface){
				return $context->generateChoices($input);
			}
		}
		$cn = $input->hasColumnName() ? $input->getColumnName() : "[undefined]";
		$dsc = $input->hasContext() && $input->getContext()->hasDataStructure() ? $input->getContext()->getDataStructure()->getShortClass() : "[undefined]";
		$decl = $this->getDeclarationLine();
		Debug::error("{$f} Input column name is {$cn}. DataStructure class is {$dsc}. Instantiated {$decl}");
		/*$print = false;
		if ($this->hasSuperiorForm()) {
			if($print){
				Debug::print("{$f} asking superior form");
			}
			return $this->getSuperiorForm()->generateChoices($input);
		}elseif($print){
			Debug::print("{$f} asking the use case");
		}
		$context = $input->getContext();
		return use_case()->generateChoices($context);*/
	}

	/**
	 * automatically generate form contents
	 * XXX 122 lines long
	 *
	 * @param object $context
	 * @return InputInterface[]
	 */
	public function generateInputs($context): array{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			$manifest = $this->getFormInputManifest();
			if ($print) {
				Debug::print("{$f} about to utilize the following manifest:");
				Debug::printArray($manifest);
			}
			$mode = $this->getAllocationMode();
			$inputs = [];
			if (! empty($manifest)) {
				foreach ($manifest as $column_name => $input_class) {
					if($context == null){
						$decl = $this->getDeclarationLine();
						Debug::error("{$f} context is null. Instantiated {$decl}");
					}
					$context_class = $context->getClass();
					if (! method_exists($context, "getColumn")) {
						Debug::error("{$f} context of class \"{$context_class}\" does not have a getColumn function");
					}elseif(!$context->hasColumn($column_name)){
						Debug::error("{$f} ".get_short_class($context)." does not have a column \"{$column_name}\" required by form ".get_short_class($this));
					}elseif($print){
						Debug::print("{$f} about to call {$context_class}->getColumn({$column_name})");
					}
					$datum = $context->getColumn($column_name);
					if (! isset($manifest[$column_name])) {
						Debug::error("{$f} form input map is undefined at index \"{$column_name}\"");
					} elseif (! is_string($manifest[$column_name])) {
						Debug::error("{$f} index \"{$column_name}\" is not mapped to a string");
					} elseif (! class_exists($manifest[$column_name])) {
						Debug::error("{$f} class \"{$manifest[$column_name]}\" does not exist. Form class is ".$this->getShortClass());
					} elseif ($print) {
						Debug::print("{$f} about to create a new input of class \"{$input_class}\" with child generation mode \"{$mode}\" for index \"{$column_name}\"");
					}
					//
					if (is_a($input_class, AjaxForm::class, true)) {
						$input = $this->generateNestedInputs($datum);
						if ($input === null) {
							if ($print) {
								Debug::warning("{$f} generated null input from class \"{$input_class}\"");
							}
							continue;
						}
						$inputs[$column_name] = $input;
					} else {
						$input = new $input_class($mode);
						if ($input === null) {
							Debug::error("{$f} somehow constructed a null from class \"{$input_class}\"");
						} elseif ($input instanceof InputInterface) {
							$input->setForm($this);
						}
						if ($this->getDisableRenderingFlag()) {
							if ($print) {
								Debug::print("{$f} disabling rendering for index \"{$column_name}\"");
							}
							$input->disableRendering();
						}
						if ($input->hasNameAttribute() && is_string($input->getNameAttribute())) {
							Debug::error("{$f} yes this happens");
							$input->bindContext($datum);
							$inputs[$input->getNameAttribute()] = $input;
						} elseif ($input->hasColumnName()) {
							$input->bindContext($datum);
							$cn = $input->getColumnName();
							if (! is_string($cn)) {
								$gottype = is_object($cn) ? $cn->getClass() : gettype($cn);
								Debug::error("{$f} {$input_class} column name is a {$gottype}");
							}
							$inputs[] = $input;
						} else {
							$input->bindContext($datum);
							$inputs[$column_name] = $input;
						}
					}
				}
			}
			// reconfigure inputs and set validators
			foreach ($inputs as $input) {
				$this->attachInputValidatorsHelper($input);
			}
			if ($print) {
				Debug::print("{$f} returning the following inputs:");
				foreach ($inputs as $name => $input) {
					if (is_array($input)) {
						Debug::print("{$f} input \"{$name}\" is an array:");
						Debug::printArray(array_keys($inputs));
						// Debug::error("{$f} input \"{$name}\" is an array");
					} else {
						$gottype = is_object($input) ? $input->getClass() : gettype($input);
						Debug::print("{$f} {$name}: {$gottype}");
					}
				}
			}
			return $inputs;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * helper function for generateInputs().
	 * Generates inputs from subordinate forms and adds them to the array.
	 * Also handles input subcontainers
	 *
	 * @param array $inputs
	 * @param Datum $datum
	 * @param string $subcontainer_name
	 * @param array $subcontainers
	 */
	private function generateNestedInputs(Datum $datum): ?array{
		$f = __METHOD__;
		try {
			$context = $this->getContext();
			$column_name = $datum->getName();
			$print = $this->getDebugFlag();
			if($context->hasForeignDataStructure($column_name)){
				if ($print) {
					Debug::print("{$f} context already has a subordinate data structure at index \"{$column_name}\"");
				}
				if ($datum instanceof VirtualDatum) {
					if ($print) {
						Debug::print("{$f} datum \"{$column_name}\" is virtual");
					}
					$structs = $datum->getValue();
					if (! is_array($structs)) {
						$structs = [
							$structs
						];
					}
				} elseif ($datum instanceof KeyListDatum) {
					if ($print) {
						Debug::print("{$f} subordinate forms iterating over KeyListDatum");
					}
					$structs = [];
					$unchecked_structs = $context->hasForeignDataStructureList($column_name) ? $context->getForeignDataStructureList($column_name) : [];
					$count = count($unchecked_structs);
					if ($print) {
						Debug::print("{$f} {$count} unchecked structs");
					}
					foreach ($unchecked_structs as $temp_struct) {
						if ($temp_struct->hasIdentifierValue()) {
							$identifier = $temp_struct->getIdentifierValue();
							if ($print) {
								Debug::print("{$f} assigning structure with identifier \"{$identifier}\"");
							}
							$structs[$identifier] = $temp_struct;
						} else {
							if ($print) {
								Debug::print("{$f} structure does not have an identifier, pushing to the end of array");
							}
							array_push($structs, $temp_struct);
						}
					}
					$count = count($structs);
					if ($print) {
						Debug::print("{$f} array contains {$count} objects");
					}
				} elseif ($datum instanceof ForeignKeyDatum) {
					$structs = [
						$context->getForeignDataStructure($column_name)
					];
				} else {
					Debug::error("{$f} neither of the above");
				}
			} else {
				if ($print) {
					Debug::print("{$f} context does not have a foreign data structure at index \"{$column_name}\"");
				}
				$form_class = $this->getInputClass($column_name);
				if ($form_class::getNewFormOption()) {
					if ($print) {
						Debug::print("{$f} about to get foreign data structure class for column \"{$column_name}\"");
					}
					if (! $datum instanceof ForeignKeyDatumInterface) {
						$context_class = $context->getShortClass();
						Debug::error("{$f} column \"{$column_name}\" is not a foreign key datum for context of class \"{$context_class}\"");
					}
					$subordinate_class = $datum->getForeignDataStructureClass($context);
					$subordinate_struct = new $subordinate_class();
					if ($print) {
						Debug::print("{$f} subordinate data structure class is \"{$subordinate_class}\"");
					}
					if($context->hasColumn($column_name)){
						$column = $context->getColumn($column_name);
						if($column instanceof ForeignKeyDatum){
							$context->setForeignDataStructure($column_name, $subordinate_struct);
							$context->ejectForeignDataStructure($column_name);
						}elseif($column instanceof KeyListDatum){
							$context->setForeignDataStructureListMember($column_name, $subordinate_struct);
							$key = $subordinate_struct->ejectIdentifierValue();
							$context->ejectForeignDataStructureListMember($column_name, $key);
						}elseif($print){
							Debug::print("{$f} column \"{$column_name}\" is neither a ForeignKey nor KeyListDatum, skipping setting it as a relationship");
						}
					}elseif($print){
						Debug::print("{$f} context does not have a column \"{$column_name}\"");
					}
					$structs = [
						$subordinate_struct
					];
				} else {
					if ($print) {
						Debug::print("{$f} form class \"{$form_class}\" does not allow new forms when foreign data structures for that column do not exist");
					}
					$structs = null;
				}
			}
			// iterate through data structures (only 1 for ForeignKeyDatum indices)
			if (isset($structs) && is_array($structs)) {
				return $this->subindexNestedInputs($datum, $structs);
			}
			return null;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function bindContext($context){
		$f = __METHOD__;
		try {
			$print = false;
			if($context instanceof Datum){
				Debug::error("{$f} cannot bind forms to datums");
			}elseif(!$this->hasSuperiorForm() && !$this->hasIdAttribute()){
				$fdis = $this->getFormDispatchIdStatic();
				if($fdis !== null){
					$id = "{$fdis}_form";
					if(
						!$this instanceof UniqueFormInterface && 
						$context instanceof DataStructure && 
						$context->hasColumn($context->getIdentifierName())
					){
						$resolved_key = $this->getResolvedKey($context);
						$this->setIdAttribute(new ConcatenateCommand("{$id}-", $resolved_key));
					}else{
						$this->setIdAttribute($id);
					}
				}
			}
			$context = parent::bindContext($context);
			if (! isset($context)) {
				Debug::error("{$f} data is undefined");
			} elseif ($context instanceof ValueReturningCommandInterface) {
				Debug::error("{$f} context should have already been evaluated");
			} elseif ($context instanceof DataStructure) {
				$short = $context->getDataType();
				$this->addClassAttribute("{$short}_form");
			}
			$mode = $this->getAllocationMode();
			if ($mode === ALLOCATION_MODE_FORM || $mode === ALLOCATION_MODE_FORM_TEMPLATE) { // this must be done here for form processing to work because otherwise the inputs would not be generated when the child nodes are not needed
				if ($print) {
					Debug::print("{$f} form rendering mode");
				}
				$inputs = array_merge($this->generateInputs($context), $this->getAdHocInputs());
				foreach ($inputs as $input) {
					if($print){
						if ($input instanceof InputInterface){
							if($input->hasColumnName()) {
								Debug::print("{$f} about to attach input validators to input " . $input->getColumnName());
							}else{
								Debug::print("{$f} input " . $input->getNameAttribute() . " has no column name");
							}
						}
					}
					$this->attachInputValidatorsHelper($input);
				}
				$this->setInputs($inputs);
			}
			return $context;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isUninitialized():bool{
		return ! $this->hasContext() || $this->getContext()->isUninitialized();
	}

	public function setValidator(?Validator $validator):?Validator{
		if($validator === null){
			unset($this->validator);
			return null;
		}
		return $this->validator = $validator;
	}
	
	public function getValidator():?Validator{
		if($this->hasValidator()){
			return $this->validator;
		}
		$class = $this->getValidatorClass();
		return $this->setValidator(new $class($this));
	}

	public static function getValidatorClassStatic(?AjaxForm $form = null): ?string{
		return FormDataIndexValidator::class;
	}

	public function getValidatorClass(): ?string{
		return static::getValidatorClassStatic($this);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"lastChild",
			"nested"
		]);
	}

	public function setNestedFlag(bool $value = true): bool{
		return $this->setFlag("nested", true);
	}

	public function getNestedFlag(): bool{
		return $this->getFlag("nested");
	}

	public function nested(bool $value = true): AjaxForm{
		$this->setNestedFlag($value);
		return $this;
	}

	public function validate(array &$arr): int{
		$f = __METHOD__;
		$validator = $this->getValidator();
		if (! isset($validator)) {
			Debug::warning("{$f} validator returned null");
			return parent::validate($arr);
		}
		$valid = $validator->validate($arr);
		$this->setValidator($validator);
		return $valid;
	}

	public static function skipAntiXsrfTokenInputs(): bool{
		return false;
	}

	public function autoGenerateHiddenInputs(): bool{
		return true;
	}

	public static function getAntiXsrfTokenInput(int $mode): HiddenInput{
		$f = __METHOD__;
		try {
			$xsrf_token = new HiddenInput($mode);
			$xsrf_token->setNameAttribute("xsrf_token");
			$xsrf_token->setValueAttribute(new AntiXsrfTokenCommand());
			return $xsrf_token;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getSecondaryHmacInput(int $mode, $action): HiddenInput{
		$f = __METHOD__;
		try {
			$print = false;
			$secondary_hmac = new HiddenInput($mode); // this->getAllocationMode());
			$secondary_hmac->setNameAttribute("secondary_hmac");
			if (empty($action)) {
				Debug::error("{$f} action attribute is undefined");
			} elseif ($print) {
				if (is_object($action)) {
					$class = $action->getClass();
					Debug::print("{$f} action attribute is a {$class}");
				} else {
					Debug::print("{$f} action attribute is \"{$action}\"");
				}
			}
			$secondary_hmac->setValueAttribute(new SecondaryHmacCommand($action));
			if ($this->hasIdAttribute()) {
				$secondary_hmac->setIdAttribute(new ConcatenateCommand("secondary_hmac-", $this->getIdAttribute()));
			}
			return $secondary_hmac;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * returns InputElements that are generated as needed by the form, but not stored, including ant-XSRF token & HMAC
	 *
	 * @return Element[]
	 */
	public function getAdHocInputs(): ?array{
		$f = __METHOD__;
		try {
			$print = false;
			$inputs = [];
			$mode = $this->getAllocationMode();
			$method = strtoupper($this->getMethodAttribute());
			if ($method === HTTP_REQUEST_METHOD_POST) {
				if ($this->hasContext()) {
					$context = $this->getContext();
					if ($context instanceof DataStructure) {
						// dataType input
						if ($context->hasColumn("dataType")) {
							$input = new HiddenInput($mode);
							$input->setNameAttribute("dataType");
							$input->setValueAttribute(
								new GetColumnValueCommand($context, "dataType")
							);
							$inputs[$input->getNameAttribute()] = $input;
						}
						// key, parentKey
						if(!$this->autoGenerateHiddenInputs()){
							if($print){
								Debug::print("{$f} skipping auto-generated hidden inputs");
							}
						}elseif(!$this->getTemplateFlag() && $context->isUninitialized()){
							if($print){
								$decl = $context->getDeclarationLine();
								$did = $context->getDebugId();
								Debug::print("{$f} template flag is not set, context is uninitialized. Debug ID is {$did}, declared {$decl}");
							}
						}else{
							if($print){
								Debug::print("{$f} auto generating hidden inputs for common indices");
							}
							$common_indices = [
								'uniqueKey',
								'parentKey'
							];
							$fdi = $this->getFormDataIndices($context);
							foreach ($common_indices as $ci) {
								if (! empty($fdi) && array_key_exists($ci, $fdi)) {

									continue;
								}elseif(
									$context->hasColumn($ci) && 
									($this->getTemplateFlag() || $context->hasColumnValue($ci))
								){
									$key_input = new HiddenInput($mode);
									$key_input->bindContext($context->getColumn($ci));
									$this->reconfigureInput($key_input);
									$inputs[$key_input->getNameAttribute()] = $key_input;
								} elseif ($print) {
									Debug::print("{$f} context lacks a column \"{$ci}\"");
								}
							}
						}
					}
				} elseif ($print) {
					Debug::print("{$f} context is undefined");
				}
				// form dispatch ID
				if ($this->hasFormDispatchId() && !$this->hasSuperiorForm()) {
					$dispatch_id = $this->getFormDispatchId();
					$mode = $this->getAllocationMode();
					$dispatch = new HiddenInput($mode);
					$dispatch->setIdOverride("dispatch");
					$dispatch->setNameAttribute("dispatch");
					$dispatch->setValueAttribute($dispatch_id);
					$dispatch->setIgnoreDatumSensitivity(true);
					$inputs[$dispatch->getNameAttribute()] = $dispatch;
				}
				// anti-XSRF tokens
				if(!$this->hasSuperiorForm() && !$this->skipAntiXsrfTokenInputs()){
					if ($print) {
						Debug::print("{$f} about to create anti-XSRF token inputs");
					}
					$inputs['xsrf_token'] = $this->getAntiXsrfTokenInput($mode);
					$action = $this->getActionAttribute();
					if (! empty($action)) {
						if ($print) {
							Debug::print("{$f} action attribute is \"{$action}\"");
						}
						$inputs['secondaty hmac'] = $this->getSecondaryHmacInput($mode, $action);
					} elseif ($print) {
						Debug::print("{$f} action attribute is null");
					}
				} elseif ($print) {
					Debug::print("{$f} skipping anti-XSRF token inputs");
				}
			} elseif ($print) {
				Debug::print("{$f} form submits using the HTTP {$method} method");
			}
			// simple URL honeypot
			$honey = new URLInput($mode);
			$honey->setAllowReservedNameFlag(true);
			$honey->setNameAttribute("url");
			$honey->addClassAttribute("hidden");
			$honey->setPlaceholderAttribute(_("URL"));
			$inputs[$honey->getNameAttribute()] = $honey;
			return $inputs;
		} catch (Exception $x) {
			x($f, $x);
		}
		return [];
	}

	public function pushHoneypot($pot){
		if (! is_array($this->honeypots)) {
			$this->honeypots = [];
		}
		array_push($this->honeypots, $pot);
		$pot->setPotNumber(count($this->honeypots));
		return $pot;
	}

	public function generateFormHeader(): void{
		return;
	}

	public function generateFormFooter(): void{
		return;
	}

	/**
	 * default behavior so derived classes and traits can use this function
	 *
	 * @param array $inputs
	 * @return array|NULL
	 */
	public function getInternalFormElementsHelper(array $inputs): ?array{
		$arr = [];
		foreach ($inputs as $input) {
			if (is_array($input)) {
				array_push($arr, ...$this->getInternalFormElements($input));
			} elseif ($input instanceof AjaxForm) {
				array_push($arr, ...$input->getInternalFormElements($input->getInputs()));
			} else {
				array_push($arr, $input);
			}
		}
		return $arr;
	}

	public function getInternalFormElements(array $inputs): ?array{
		return $this->getInternalFormElementsHelper($inputs);
	}

	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try {
			$print = false;
			$context = $this->hasContext() ? $this->getContext() : null;
			$inputs = $this->generateInputs($context);
			$ad_hoc = $this->getAdHocInputs();
			if (! empty($ad_hoc)) {
				$count = count($ad_hoc);
				if ($print) {
					Debug::print("{$f} this form has {$count} ad hoc inputs");
				}
				foreach ($ad_hoc as $input) {
					if (! $input->hasAllocationMode()) {
						if($print){
							$decl = $input->getDeclarationLine();
							Debug::error("{$f} input instantiated {$decl} lacks allocation mode on line 1163");
						}
						$input->setAllocationMode(ALLOCATION_MODE_UNDEFINED);
					}
					//if ($input instanceof InputInterface && $input->hasColumnName()) {
					$this->attachInputValidatorsHelper($input);
					//}
				}
			} elseif ($print) {
				Debug::print("{$f} this form has no ad hoc inputs");
			}
			$inputs = $this->setInputs(array_merge($inputs, $ad_hoc));
			$honeypots = static::getHoneypotCountArray();
			if (! empty($honeypots)) {
				foreach ($inputs as $input) {
					if ($input instanceof InputInterface) {
						if (! $input->hasColumnName()) {
							continue;
						}
						$column_name = $input->getColumnName();
						if (array_key_exists($column_name, $honeypots)) {
							$input->setHoneypotCount($honeypots[$column_name]);
						}
					}
				}
			} elseif ($print) {
				Debug::print("{$f} no honeypot inputs");
			}
			if (isset($this->honeypotStyleElement)) {
				$this->appendChild($this->honeypotStyleElement);
			} elseif ($print) {
				Debug::print("{$f} no honeypot style element");
			}
			// header
			$this->generateFormHeader();
			// inputs
			$this->appendChild(...array_values($this->getInternalFormElements($inputs)));
			// loading container
			$this->generateLoadingContainer();
			// footer
			$this->generateFormFooter();
			// initialization script
			if (! Request::isXHREvent() && ! $this->skipFormInitialization()) {
				if ($this->hasSoftDisabledInputIds()) {
					$script = new ScriptElement();
					$disabled_inputs = $this->getSoftDisabledInputIds();
					foreach ($disabled_inputs as $disabled_input) {
						$line = new SoftDisableInputCommand($disabled_input);
						$script->appendChild($line);
					}
					$this->appendChild($script);
				}
				// $this->appendChild($this->getInitializeFormScript());
			} elseif ($print) {
				Debug::print("{$f} skipping form initialization script");
			}
			return $this->getChildNodes();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getLoadingContainerParent(): Element{
		return $this;
	}

	/**
	 * generate the auto-reloading part of the form
	 */
	public function generateLoadingContainer(): Element{
		$f = __METHOD__;
		try {
			$print = false;
			$parent = $this->getLoadingContainerParent();
			if (! isset($parent)) {
				Debug::error("{$f} loading container parent node is undefined");
			}
			$load_container = new DivElement();
			$load_container->addClassAttribute("load_container");
			$load_container->setTemplateFlag($this->getTemplateFlag());
			if (! $this->hasIdAttribute()) {
				if ($this->hasAttribute("temp_id")) {
					$tida = $this->getAttribute("temp_id");
					if ($print) {
						Debug::print("{$f} temp ID attribute \"{$tida}\"");
					}
				} else {
					Debug::error("{$f} you must assign a media command or string literal template ID attribute to the form in order for the loading container to template its own ID attribute");
				}
			} else {
				$tida = $this->getIdAttribute();
				if ($print) {
					if ($tida instanceof Command) {
						Debug::print("{$f} ID attribute is a command that cannot be evaluated right now");
					} else {
						Debug::print("{$f} regular ID attribute \"{$tida}\"");
					}
				}
			}
			$load_container->setIdAttribute(new ConcatenateCommand('load_', $tida));
			$load_container->setAllowEmptyInnerHTML(true);
			$parent->appendChild($load_container);
			$gfbc = $this->getGenerateFormButtonsCommandClass();
			if ($print) {
				Debug::print("{$f} generate form buttons command class is \"{$gfbc}\"");
			}
			$parent->resolveTemplateCommand(new $gfbc($this));
			if ($this->hasImportedCollapseLabel()) {
				$label = $this->getImportedCollapseLabel();
				$parent->appendChild($label);
			}
			return $load_container;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * interrupt automatic form initialization for an inline script
	 *
	 * @return boolean
	 */
	public function skipFormInitialization(): bool{
		return $this->getTemplateFlag();
	}

	public function generateGenericButton(string $directive, $value = null): ButtonInput{
		$f = __METHOD__;
		try {
			$mode = $this->getAllocationMode();
			$button = new ButtonInput($mode);
			if ($value !== null) {
				$button->setValueAttribute($value);
				$button->setNameAttribute(new ConcatenateCommand("directive", "[", $directive, "]"));
			} else {
				$button->setNameAttribute("directive");
				$button->setValueAttribute($directive);
			}
			if (! $this->getTemplateFlag()) {
				$form_id = $this->getIdAttribute();
				$id = "{$directive}-{$form_id}";
				if ($value !== null) {
					$id .= "-" . NameDatum::normalize($value);
				}
				$button->setIdAttribute($id);
			}
			$button->setOnClickAttribute($button->getDefaultOnClickAttribute());
			$button->setTypeAttribute("submit");
			$button->setForm($this);
			$context = $this->hasContext() ? $this->getContext() : null;
			if (method_exists($context, "getPrettyClassName")) {
				$pretty = $context->getPrettyClassName();
			} else {
				$pretty = _("Undefined");
			}
			switch ($directive) {
				case DIRECTIVE_IMPORT_CSV:
					$innerHTML = _("Import CSV files");
					break;
				case DIRECTIVE_INSERT:
					$innerHTML = substitute(_("Insert %1%"), $pretty);
					break;
				case DIRECTIVE_REGENERATE:
				case DIRECTIVE_UNSET:
				case DIRECTIVE_UPDATE:
					$innerHTML = substitute(_("Update %1%"), $pretty);
					break;
				case DIRECTIVE_DELETE:
				case DIRECTIVE_DELETE_FOREIGN:
				case DIRECTIVE_MASS_DELETE:
					$innerHTML = substitute(_("Delete %1%"), $pretty);
					break;
				case DIRECTIVE_DOWNLOAD:
					$innerHTML = substitute(_("Download %1%"), $pretty);
					break;
				case DIRECTIVE_EMAIL_CONFIRMATION_CODE:
					$innerHTML = _("Send confirmation code");
					break;
				case DIRECTIVE_PROCESS:
					$innerHTML = substitute(_("Process %1%"), $pretty);
					break;
				case DIRECTIVE_READ:
				case DIRECTIVE_READ_MULTIPLE:
					$innerHTML = substitute(_("Read %1%"), $pretty);
					break;
				case DIRECTIVE_REFRESH_SESSION:
					$innerHTML = _("Refresh session");
					break;
				case DIRECTIVE_SEARCH:
					$innerHTML = _("Search");
					break;
				case DIRECTIVE_SELECT:
					$innerHTML = substitute(_("Select %1%"), $pretty);
					break;
				case DIRECTIVE_MFA:
				case DIRECTIVE_SUBMIT:
					$innerHTML = _("Submit");
					break;
				case DIRECTIVE_UPLOAD:
					$innerHTML = _("Upload");
					break;
				case DIRECTIVE_VALIDATE:
					$innerHTML = _("Validate");
					break;
				default:
					Debug::error("{$f} invalid directive \"{$directive}\"");
			}
			$button->setInnerHTML($innerHTML);
			return $button;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->directives);
		unset($this->errorCallback);
		unset($this->formDispatchId);
		unset($this->formInputManifest);
		unset($this->inputs);
		unset($this->generateFormButtonsCommandClass);
		unset($this->honeypots);
		unset($this->honeypotStyleElement);
		unset($this->importedCollapseLabel);
		unset($this->softDisabledInputIds);
		unset($this->successCallback);
		unset($this->superiorForm);
		unset($this->superiorFormIndex);
		unset($this->validator);
	}

	public function echoAttributeString(bool $destroy = false): void{
		$f = __METHOD__;
		if (! $this->hasMethodAttribute()) {
			$debug_id = $this->getDebugId();
			Debug::error("{$f} method attribute is undefined for form with debug ID \"{$debug_id}\"");
		}
		parent::echoAttributeString($destroy);
	}

	/**
	 * generate a JavaScriptFunction that converts to a string JS function to submit a FormData
	 * simulating this form for a context parameter that is passed client side when no form exists
	 *
	 * @return JavaScriptFunction
	 */
	public final function generateFormDataSubmissionFunction(): JavaScriptFunction{
		$generator = new FormDataSubmissionFunctionGenerator();
		return $generator->generate($this);
	}

	protected function beforeRenderHook(): int{
		$f = __METHOD__;
		$print = false;
		$ret = parent::beforeRenderHook();
		$method = $this->getMethodAttribute();
		if (empty($method)) {
			Debug::error("{$f} method attribute is undefined");
		}
		$action = $this->getActionAttribute();
		if (! empty($action) && $print) {
			$debug_id = $this->getDebugId();
			Debug::print("{$f} method is \"{$method}\"; action is \"{$action}\"; debug ID is \"{$debug_id}\"");
		}
		$this->setAttributes([
			"callback_success" => $this->getSuccessCallback(),
			"callback_error" => $this->getErrorCallback()
		]);
		if ($this->hasSoftDisabledInputIds()) {
			$input_ids = implode(',', $this->getSoftDisabledInputIds());
			$this->setAttribute("soft_disable", $input_ids);
		}
		return $ret;
	}
}
