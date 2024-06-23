<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\array_associate_random;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\mutual_reference;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveInterface;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveTrait;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\input\SoftDisableInputCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\FormElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\ScriptElement;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\SubmitEvent;
use JulianSeymour\PHPWebApplicationFramework\input\ButtonInput;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;
use JulianSeymour\PHPWebApplicationFramework\input\InputlikeInterface;
use JulianSeymour\PHPWebApplicationFramework\input\MultipleInputsTrait;
use JulianSeymour\PHPWebApplicationFramework\input\URLInput;
use JulianSeymour\PHPWebApplicationFramework\input\choice\ChoiceGeneratorInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenCommand;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\SecondaryHmacCommand;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpanderElement;
use JulianSeymour\PHPWebApplicationFramework\validate\FormDataIndexValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use JulianSeymour\PHPWebApplicationFramework\validate\ValidatorTrait;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\input\FancyCheckbox;
use JulianSeymour\PHPWebApplicationFramework\input\TextInput;

abstract class AjaxForm extends FormElement 
implements JavaScriptCounterpartInterface, PermissiveInterface{

	use ColumnNameTrait;
	use MultipleHoneypotsTrait;
	use JavaScriptCounterpartTrait;
	use MultipleInputsTrait;
	use NestedFormsTrait;
	use PermissiveTrait;
	use StyleSheetPathTrait;
	use SuccessAndErrorCallbacksTrait;
	use SuperiorFormTrait;
	use ValidatorTrait;

	/**
	 * special identifier for the server to recognize which form is being submitted
	 * XXX TODO might as well just use the class name
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

	// XXX forgot what most of this junk is used for
	protected $directives;

	protected $importedCollapseLabel;

	public abstract function getDirectives(): ?array;

	public abstract function generateButtons(string $directive): ?array;

	public abstract function getFormDataIndices(): ?array;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		$f = __METHOD__;
		try{
			$print = false;
			$form_id = $this->getFormDispatchIdStatic();
			if($form_id !== null){
				$this->setFormDispatchId($form_id);
			}
			parent::__construct($mode, $context);
			if(method_exists($this, "getActionAttributeStatic")){
				$action = static::getActionAttributeStatic($context);
				if(!empty($action)){
					$this->setActionAttribute($action);
				}elseif($print){
					Debug::print("{$f} getActionAttributeStatic returned null");
				}
			}elseif($print){
				Debug::print("{$f} no method getActionAttributeStatic exists");
			}
			if(!$this->skipFormInitialization()){
				$this->addClassAttribute("ajax_form");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"lastChild",
			"nested"
		]);
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
		return static::getShortClass();
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
		if($this->hasAnyEventListener(EVENT_SUBMIT_FORM)){
			$this->dispatchEvent(new SubmitEvent());
		}
		return SUCCESS;
	}

	public function getMethodAttribute(): ?string{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasMethodAttribute()){
			if($print){
				$debug_id = $this->getDebugId();
				Debug::print("{$f} method attribute is undefined for form with debug ID \"{$debug_id}\" -- returning static fallback");
			}
			return $this->setMethodAttribute(static::getMethodAttributeStatic());
		}elseif($print){
			Debug::print("{$f} returning parent function");
		}
		return parent::getMethodAttribute();
	}

	public function getActionAttribute(): ?string{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasActionAttribute()){
			$context = $this->hasContext() ? $this->getContext() : null;
			$static = $this->getActionAttributeStatic($context);
			if($static === null){
				if($print){
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

	public function getFormInputManifest(): ?array{
		if(!$this->hasFormInputManifest()){
			$context = $this->hasContext() ? $this->getContext() : null;
			return $this->getFormDataIndices($context);
		}
		return $this->formInputManifest;
	}

	public function getGenerateFormButtonsCommandClass(): ?string{
		if(!$this->hasGenerateFormButtonsCommandClass()){
			return static::getGenerateFormButtonsCommandClassStatic();
		}
		return $this->generateFormButtonsCommandClass;
	}

	public function setGenerateFormButtonsCommandClass(?string $c): ?string{
		if($this->hasGenerateFormButtonsCommandClass()){
			$this->release($this->generateFormButtonsCommandClass);
		}
		return $this->generateFormButtonsCommandClass = $this->claim($c);
	}

	public function setDirectives(?array $directives): ?array{
		if($this->hasDirectives()){
			$this->release($this->directives);
		}
		return $this->directives = $this->claim($directives);
	}

	public function setFormInputManifest(?array $manifest): ?array{
		if($this->hasFormInputManifest()){
			$this->release($this->formInputManifest);
		}
		return $this->formInputManifest = $this->claim($manifest);
	}

	public function hasFormDispatchId(): bool{
		return isset($this->formDispatchId);
	}

	public function hasFormInputManifest(): bool{
		return isset($this->formInputManifest) && is_array($this->formInputManifest) && !empty($this->formInputManifest);
	}

	public function setFormDispatchId(?string $id): ?string{
		if($this->hasFormDispatchId()){
			$this->release($this->formDispatchId);
		}
		return $this->formDispatchId = $this->claim($id);
	}

	public function getFormDispatchId(): ?string{
		$f = __METHOD__;
		if(!$this->hasFormDispatchId()){
			$id = static::getFormDispatchIdStatic();
			if($id === null){
				Debug::error("{$f} form dispatch ID is undefined");
			}
			return $id;
		}
		return $this->formDispatchId;
	}

	public function getInputClass(string $column_name): ?string{ // XXX TODO ugly
		$f = __METHOD__;
		$fdi = $this->getFormDataIndices();
		if(array_key_exists($column_name, $fdi)){
			return $fdi[$column_name];
		}
		Debug::error("{$f} invalid column name \"{$column_name}\"");
		return null;
	}

	public function hasSoftDisabledInputIds(): bool{
		return is_array($this->softDisabledInputIds) && !empty($this->softDisabledInputIds);
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
		if($this->hasImportedCollapseLabel()){
			$this->release($this->importedCollapseLabel);
		}
		return $this->importedCollapseLabel = $this->claim($label);
	}

	public function hasEncodingTypeAttribute(): bool{
		return $this->hasAttribute("enctype");
	}

	public function hasDirectives(): bool{
		return !empty($this->directives);
	}

	/**
	 * automates many boring aspects of input generation by drawing information from the datum
	 *
	 * @param InputlikeInterface $input
	 */
	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($input == null){
				Debug::error("{$f} input is undefined");
			/*}elseif($this->hasSuperiorForm()){
				if($print){
					Debug::print("{$f} skipping configure() function call because we have a superior form");
				}
				return SUCCESS;*/
			}elseif($print){
				Debug::print("{$f} calling input->configure(this)");
			}
			return $input->configure($this);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispatchCommands(): int{
		$f = __METHOD__;
		$print = false;
		if($this->skipFormInitialization()){
			if($print){
				Debug::print("{$f} skipping form initialization command because this form does not get initialized");
			}
			return parent::dispatchCommands();
		}elseif($this->hasSoftDisabledInputIds()){
			$input_ids = $this->getSoftDisabledInputIds();
			foreach($input_ids as $input_id){
				$subcommand = new SoftDisableInputCommand($input_id);
				$this->reportSubcommand($subcommand);
			}
		}
		$subcommand = new InitializeFormCommand();
		if($print){
			Debug::printStackTraceNoExit("{$f} instantiated ".$subcommand->getDebugString()." for this ".$this->getDebugString());
		}
		$subcommand->setElement($this);
		if(!$subcommand->hasElement()){
			Debug::error("{$f} immediately after assignment, InitializeFormCommand does not know about its element");
		}
		if($print){
			Debug::print("{$f} InitializeFormCommand ".$subcommand->getDebugString()." has element ".$subcommand->getElement()->getDebugString());
			Debug::print("{$f} about to report InitializeFormCommand");
		}
		$this->reportSubcommand($subcommand);
		return parent::dispatchCommands();
	}

	public function getSuccessCallback(): ?string{
		if(!$this->hasSuccessCallback()){
			return static::getSuccessCallbackStatic();
		}
		return $this->successCallback;
	}
	
	public function getErrorCallback():?string{
		if(!$this->hasErrorCallback()){
			return static::getErrorCallbackStatic();
		}
		return $this->errorCallback;
	}

	public static function isUltraLazyRenderingCompatible(): bool{
		return false;
	}
	
	public function getChoiceGenerationParameters($input): ?array{
		if($this->hasSuperiorForm()){
			return $this->getSuperiorForm()->getChoiceGenerationParameters($input);
		}
		return [];
	}

	public function getValidateInputNames(): ?array{
		return array_keys($this->getFormDataIndices());
	}

	private function attachInputValidatorsHelper($input){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$mode = $this->getAllocationMode();
		if($input === null){
			Debug::error("{$f} input is null");
		}elseif(is_array($input)){
			if($print){
				Debug::print("{$f} input is an array");
			}
			foreach($input as $sub_input){
				$this->attachInputValidatorsHelper($sub_input);
			}
			return $input;
		}elseif($input instanceof AjaxForm){
			if($print){
				Debug::print("{$f} input is an AjaxForm");
			}
			foreach($input->getInputs() as $sub_input){
				$this->attachInputValidatorsHelper($sub_input);
			}
		}elseif(!$input->getAllocatedFlag()){
			$ds = $input->getDebugString();
			Debug::error("{$f} {$ds} was already deallocated on ".$input->getDeallocationLine());
		}
		if($input instanceof InputlikeInterface){
			if(!$this->getDisableRenderingFlag() && $input->hasColumnName()){
				$this->reconfigureInput($input);
			}elseif($print){
				Debug::print("{$f} rendering is disabled, or the input lacks a column name");
			}
			if($print){
				$input_class = $input->getClass();
				$column_name = $input->hasColumnName() ? $input->getColumnName() : "[unnamed]";
				Debug::print("{$f} about to call attachInputValidators for {$input_class} at index \"{$column_name}\"");
			}
			if($mode !== ALLOCATION_MODE_FORM && $mode !== ALLOCATION_MODE_FORM_TEMPLATE){
				$this->attachInputValidators($input);
			}
		}elseif($input instanceof Element){
			if($print){
				Debug::print("{$f} input is an element");
			}
		}else{
			$sc = is_array($input) ? "array" : $input->getShortClass();
			Debug::error("{$f} none of the above. Input is a {$sc}");
		}
		return $input;
	}

	/**
	 * override this to apply validators to individual inputs
	 *
	 * @param InputlikeInterface $input
	 * @return InputlikeInterface
	 */
	protected function attachInputValidators(InputlikeInterface $input):InputlikeInterface{
		return $input;
	}
	
	/**
	 * override this with a function that calls setNegotiator on the input as needed
	 *
	 * @param InputlikeInterface $input
	 * @return InputlikeInterface
	 */
	public function attachNegotiator(InputlikeInterface $input): InputlikeInterface{
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
		if($this->hasSuperiorForm()){
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

	public function setInputs(?array $inputs): ?array{
		if($this->hasInputs()){
			$this->releaseInputs();
		}
		$random1 = sha1(random_bytes(32));
		$that = $this;
		foreach($inputs as $name => $input){
			if(!$input instanceof InputlikeInterface){
				continue;
			}
			$closure1 = function(DeallocateEvent $event, InputlikeInterface $input) use ($that, $name, $random1){
				$f = __FUNCTION__;
				$print = false;
				$input->removeEventListener(EVENT_DEALLOCATE, $random1);
				if($that->hasInput($name)){
					if($print){
						Debug::print("{$f} about to release input \"{$name}\"");
					}
					$that->releaseInput($name);
				}elseif($print){
					Debug::print("{$f} no, we don't have an input \"{$name}\" to release");
				}
			};
			$input->addEventListener(EVENT_DEALLOCATE, $closure1, $random1);
		}
		return $this->inputs = $this->claim($inputs);
	}
	
	/**
	 * automatically generate form contents
	 * XXX TODO 122 lines long
	 *
	 * @param object $context
	 * @return InputlikeInterface[]
	 */
	public function generateInputs($context): array{
		$f = __METHOD__;
		try{
			$print = false;
			$manifest = $this->getFormInputManifest();
			if($print){
				Debug::print("{$f} about to utilize the following manifest:");
				Debug::printArray($manifest);
			}
			$mode = $this->getAllocationMode();
			$inputs = [];
			if(!empty($manifest)){
				foreach($manifest as $column_name => $input_class){
					if($context == null){
						$decl = $this->getDeclarationLine();
						Debug::error("{$f} context is null. Instantiated {$decl}");
					}
					$context_class = $context->getClass();
					if(!method_exists($context, "getColumn")){
						Debug::error("{$f} context of class \"{$context_class}\" does not have a getColumn function");
					}elseif(!$context->hasColumn($column_name)){
						$context->debugPrintColumns("{$f} ".get_short_class($context)." does not have a column \"{$column_name}\" required by form ".get_short_class($this));
					}elseif($print){
						Debug::print("{$f} about to call {$context_class}->getColumn({$column_name})");
					}
					$datum = $context->getColumn($column_name);
					if(!isset($manifest[$column_name])){
						Debug::error("{$f} form input map is undefined at index \"{$column_name}\"");
					}elseif(!is_string($manifest[$column_name])){
						Debug::error("{$f} index \"{$column_name}\" is not mapped to a string");
					}elseif(!class_exists($manifest[$column_name])){
						Debug::error("{$f} class \"{$manifest[$column_name]}\" does not exist. Form is ".$this->getDebugString());
					}elseif($print){
						Debug::print("{$f} about to create a new input of class \"{$input_class}\" with allocation mode{$mode} for index \"{$column_name}\"");
					}
					//
					if(is_a($input_class, AjaxForm::class, true)){
						$input = $this->generateNestedInputs($datum);
						if($input === null){
							if($print){
								Debug::warning("{$f} generated null input from class \"{$input_class}\"");
							}
							continue;
						}
						$inputs[$column_name] = $input;
					}else{
						$input = new $input_class($mode);
						if($input instanceof InputlikeInterface){
							$input->setForm($this);
						}
						if($this->getDisableRenderingFlag()){
							if($print){
								Debug::print("{$f} disabling rendering for index \"{$column_name}\"");
							}
							$input->disableRendering();
						}
						$input->bindContext($datum);
						if($input->hasColumnName() && is_string($input->getColumnName())){
							$index = $input->getColumnName();
						}elseif($input->hasNameAttribute() && is_string($input->getNameAttribute())){
							$index = $input->getNameAttribute();
						}else{
							$index = count($inputs);
						}
						$inputs[$index] = $input;
						if($input->hasForm()){
							if(BACKWARDS_REFERENCES_ENABLED){
								$closure1 = function(AjaxForm $form, bool $deallocate=false) 
								use ($index){
									if($form->hasInput($index)){
										$form->releaseInput($index, $deallocate);
									}
								};
								$closure2 = function(InputlikeInterface $input, bool $deallocate=false){
									if($input->hasForm()){
										$input->releaseForm($deallocate);
									}
								};
								mutual_reference($this, $input, $closure1, $closure2, EVENT_RELEASE_INPUT, EVENT_RELEASE_FORM, [
									"name" => $index
								]);
							}
						}
					}
				}
			}
			// reconfigure inputs and set validators. This has to happen in a separate loop because validators oftentimes must reference other inputs
			if($mode !== ALLOCATION_MODE_FORM && $mode !== ALLOCATION_MODE_FORM_TEMPLATE){
				foreach($inputs as $input){
					$this->attachInputValidatorsHelper($input);
				}
			}
			if($print){
				Debug::print("{$f} returning the following inputs:");
				foreach($inputs as $name => $input){
					if(is_array($input)){
						Debug::print("{$f} input \"{$name}\" is an array:");
						Debug::printArray(array_keys($inputs));
						// Debug::error("{$f} input \"{$name}\" is an array");
					}else{
						$gottype = is_object($input) ? $input->getClass() : gettype($input);
						Debug::print("{$f} {$name}: {$gottype}");
					}
				}
			}
			return $inputs;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function bindContext($context){
		$f = __METHOD__;
		try{
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
						if($print){
							Debug::print("{$f} about to call getResolvedKey for this ".$this->getDebugString());
						}
						$resolved_key = $this->getResolvedKey($context);
						$concat = new ConcatenateCommand("{$id}-", $resolved_key);
						$this->setIdAttribute($concat);
						if(!$this->getTemplateFlag()){
							deallocate($concat);
						}
					}else{
						$this->setIdAttribute($id);
					}
				}
			}
			$context = parent::bindContext($context);
			if(!isset($context)){
				Debug::error("{$f} data is undefined");
			}elseif($context instanceof ValueReturningCommandInterface){
				Debug::error("{$f} context should have already been evaluated");
			}elseif($context instanceof DataStructure){
				$short = $context->getDataType();
				$this->addClassAttribute("{$short}_form");
			}
			$mode = $this->getAllocationMode();
			if($mode === ALLOCATION_MODE_FORM || $mode === ALLOCATION_MODE_FORM_TEMPLATE){ // this must be done here for form processing to work because otherwise the inputs would not be generated when the child nodes are not needed
				if($print){
					Debug::print("{$f} form rendering mode");
				}
				$inputs = array_merge($this->generateInputs($context), array_associate_random($this->getAdHocInputs()));
				foreach($inputs as $input){
					if($print){
						if($input instanceof InputlikeInterface){
							if($input->hasColumnName()){
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
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function isUninitialized():bool{
		return !$this->hasContext() || $this->getContext()->isUninitialized();
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

	public function validate(array &$arr): int{
		$f = __METHOD__;
		$validator = $this->getValidator();
		if(!isset($validator)){
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

	public function getAntiXsrfTokenInput(int $mode): HiddenInput{
		$f = __METHOD__;
		try{
			$xsrf_token = new HiddenInput($mode);
			$xsrf_token->setNameAttribute("xsrf_token");
			$xsrf_command = new AntiXsrfTokenCommand();
			$xsrf_token->setValueAttribute($xsrf_command);
			if(!$this->getTemplateFlag()){
				deallocate($xsrf_command);
			}
			return $xsrf_token;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getSecondaryHmacInput(int $mode, $action): HiddenInput{
		$f = __METHOD__;
		try{
			$print = false;
			$secondary_hmac = new HiddenInput($mode);
			$secondary_hmac->setNameAttribute("secondary_hmac");
			if(empty($action)){
				Debug::error("{$f} action attribute is undefined");
			}elseif($print){
				if(is_object($action)){
					$class = $action->getClass();
					Debug::print("{$f} action attribute is a {$class}");
				}else{
					Debug::print("{$f} action attribute is \"{$action}\"");
				}
			}
			$hmac_cmd = new SecondaryHmacCommand($action);
			$secondary_hmac->setValueAttribute($hmac_cmd);
			if(!$this->getTemplateFlag()){
				deallocate($hmac_cmd);
			}
			if($this->hasIdAttribute()){
				$id = $this->getIdAttribute();
				$concat = new ConcatenateCommand("secondary_hmac-", $id);
				$secondary_hmac->setIdAttribute($concat);
				if(!$this->getTemplateFlag()){
					deallocate($concat);
				}
			}
			return $secondary_hmac;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * returns InputElements that are generated as needed by the form, but not stored, including ant-XSRF token & HMAC
	 *
	 * @return Element[]
	 */
	public function getAdHocInputs():?array{
		$f = __METHOD__;
		try{
			$print = false;
			$inputs = [];
			$mode = $this->getAllocationMode();
			$method = strtoupper($this->getMethodAttribute());
			if($method === HTTP_REQUEST_METHOD_POST){
				if($this->hasContext()){
					$context = $this->getContext();
					if($context instanceof DataStructure){
						// dataType input
						if($context->hasColumn("dataType")){
							$input = new HiddenInput($mode);
							$input->setNameAttribute("dataType");
							$gcv = new GetColumnValueCommand($context, "dataType");
							$input->setValueAttribute($gcv);
							if(!$this->getTemplateFlag()){
								deallocate($gcv);
							}
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
							if((
								$context->hasIdentifierName() || 
								$context->getIdentifierNameStatic() !== null
							) && (
								$this->getTemplateFlag() || 
								$context->hasIdentifierValue()
							)){
								$fdi = $this->getFormDataIndices($context);
								$ci = $context->getIdentifierName();
								if(
									!empty($fdi) && 
									!array_key_exists($ci, $fdi)
								){
									$datum = $context->getColumn($ci);
									$key_input = new HiddenInput($mode);
									$key_input->bindContext($datum);
									$this->reconfigureInput($key_input);
									$inputs[$key_input->getNameAttribute()] = $key_input;
								}elseif($print){
									Debug::print("{$f} context lacks a column \"{$ci}\"");
								}
							}
						}
					}
				}elseif($print){
					Debug::print("{$f} context is undefined");
				}
				// form dispatch ID
				if($this->hasFormDispatchId() && !$this->hasSuperiorForm()){
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
					if($print){
						Debug::print("{$f} about to create anti-XSRF token inputs");
					}
					$inputs['xsrf_token'] = $this->getAntiXsrfTokenInput($mode);
					$action = $this->getActionAttribute();
					if(!empty($action)){
						if($print){
							Debug::print("{$f} action attribute is \"{$action}\"");
						}
						$inputs['secondaty hmac'] = $this->getSecondaryHmacInput($mode, $action);
					}elseif($print){
						Debug::print("{$f} action attribute is null");
					}
				}elseif($print){
					Debug::print("{$f} skipping anti-XSRF token inputs");
				}
			}elseif($print){
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
		}catch(Exception $x){
			x($f, $x);
		}
		return [];
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
		$f = __METHOD__;
		$arr = [];
		foreach($inputs as $input){
			if(is_array($input)){
				array_push($arr, ...$this->getInternalFormElementsHelper($input));
			}elseif($input instanceof AjaxForm){
				array_push($arr, ...$input->getInternalFormElements($input->getInputs()));
			}else{
				array_push($arr, $input);
			}
		}
		return $arr;
	}

	public function getInternalFormElements(array $inputs):?array{
		$f = __METHOD__;
		$print = false;
		$ret = $this->getInternalFormElementsHelper($inputs);
		if($print){
			Debug::print("{$f} generated the following internal form elements:");
			Debug::printArray($ret);
		}
		return $ret;
	}

	public function generateChildNodes():?array{
		$f = __METHOD__;
		try{
			$print = false;
			$context = $this->hasContext() ? $this->getContext() : null;
			$inputs = $this->generateInputs($context);
			$ad_hoc = $this->getAdHocInputs();
			if(!empty($ad_hoc)){
				$count = count($ad_hoc);
				if($print){
					Debug::print("{$f} this form has {$count} ad hoc inputs");
				}
				foreach($ad_hoc as $input){
					if(!$input->hasAllocationMode()){
						if($print){
							$decl = $input->getDeclarationLine();
							Debug::error("{$f} input instantiated {$decl} lacks allocation mode on line 1163");
						}
						$input->setAllocationMode(ALLOCATION_MODE_UNDEFINED);
					}
					$this->attachInputValidatorsHelper($input);
				}
			}elseif($print){
				Debug::print("{$f} this form has no ad hoc inputs");
			}
			$inputs = $this->setInputs(array_merge($inputs, array_associate_random($ad_hoc)));
			$honeypots = static::getHoneypotCountArray();
			if(!empty($honeypots)){
				foreach($inputs as $input){
					if($input instanceof InputlikeInterface){
						if(!$input->hasColumnName()){
							continue;
						}
						$column_name = $input->getColumnName();
						if(array_key_exists($column_name, $honeypots)){
							$input->setHoneypotCount($honeypots[$column_name]);
						}
					}
				}
			}elseif($print){
				Debug::print("{$f} no honeypot inputs");
			}
			if(isset($this->honeypotStyleElement)){
				$this->appendChild($this->honeypotStyleElement);
			}elseif($print){
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
			if(!Request::isXHREvent() && ! $this->skipFormInitialization()){
				if($this->hasSoftDisabledInputIds()){
					$script = new ScriptElement();
					$disabled_inputs = $this->getSoftDisabledInputIds();
					foreach($disabled_inputs as $disabled_input){
						$line = new SoftDisableInputCommand($disabled_input);
						$script->appendChild($line);
					}
					$this->appendChild($script);
				}
			}elseif($print){
				Debug::print("{$f} skipping form initialization script");
			}
			return $this->hasChildNodes() ? $this->getChildNodes() : [];
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getLoadingContainerParent():Element{
		return $this;
	}

	/**
	 * generate the auto-reloading part of the form
	 */
	public function generateLoadingContainer():Element{
		$f = __METHOD__;
		$print = false;
		$e = new FormLoadingElement($this->getAllocationMode(), $this);
		$parent = $this->getLoadingContainerParent();
		if(!isset($parent)){
			Debug::error("{$f} loading container parent node is undefined");
		}
		$parent->appendChild($e);
		$gfbc = $this->getGenerateFormButtonsCommandClass();
		if($print){
			Debug::print("{$f} generate form buttons command class is \"{$gfbc}\"");
		}
		$button_generator = new $gfbc();
		$button_generator->setElement($this);
		if($print){
			Debug::print("{$f} about to resolve button generator command of class {$gfbc} for this ".$this->getDebugString());
		}
		$parent->resolveTemplateCommand($button_generator);
		if(!$this->getTemplateFlag() || $button_generator->extractAnyway()){
			if($print){
				Debug::print("{$f} deallocating button generator command");
			}
			$this->disableDeallocation();
			deallocate($button_generator);
			$this->enableDeallocation();
		}
		if($this->hasImportedCollapseLabel()){
			$label = $this->getImportedCollapseLabel();
			$parent->appendChild($label);
		}
		return $e;
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
		return ButtonGenerator::generate($this, $directive, $value);
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasDirectives()){
			$this->setDirectives(replicate($that->getDirectives()));
		}
		if($that->hasErrorCallback()){
			$this->setErrorCallback(replicate($that->getErrorCallback()));
		}
		if($that->hasFormDispatchId()){
			$this->setFormDispatchId(replicate($that->getFormDispatchId()));
		}
		if($that->hasFormInputManifest()){
			$this->setFormInputManifest(replicate($that->getFormInputManifest()));
		}
		if($that->hasGenerateFormButtonsCommandClass()){
			$this->setGenerateFormButtonsCommandClass(replicate($that->getGenerateFormButtonsCommandClass()));
		}
		if($that->hasHoneypots()){
			$this->setHoneypots(replicate($that->getHoneypots()));
		}
		if($that->hasHoneypotStyleElement()){
			$this->setHoneypotStyleElement(replicate($that->getHoneypotStyleElement()));
		}
		if($that->hasImportedCollapseLabel()){
			$this->setImportedCollapseLabel(replicate($that->getImportedCollapseLabel()));
		}
		if($that->hasSoftDisabledInputIds()){
			$this->setSoftDisabledInputIds(replicate($that->getSoftDisabledInputIds()));
		}
		if($that->hasSuccessCallback()){
			$this->setSuccessCallback(replicate($that->getSuccessCallback()));
		}
		if($that->hasSuperiorFormIndex()){
			$this->setSuperiorFormIndex(replicate($that->getSuperiorFormIndex()));
		}
		if($that->hasValidator()){
			$this->setValidator(replicate($that->getValidator()));
		}
		return $ret;
	}
	
	public function echoAttributeString(bool $destroy = false): void{
		$f = __METHOD__;
		if(!$this->hasMethodAttribute()){
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
		$this->disableDeallocation();
		$func = $generator->generate($this);
		deallocate($generator);
		$this->enableDeallocation();
		return $func;
	}

	protected function beforeRenderHook():int{
		$f = __METHOD__;
		$print = false;
		$ret = parent::beforeRenderHook();
		$method = $this->getMethodAttribute();
		if(empty($method)){
			Debug::error("{$f} method attribute is undefined");
		}
		$action = $this->getActionAttribute();
		if(!empty($action) && $print){
			$debug_id = $this->getDebugId();
			Debug::print("{$f} method is \"{$method}\"; action is \"{$action}\"; debug ID is \"{$debug_id}\"");
		}
		$this->setAttributes([
			"callback_success" => $this->getSuccessCallback(),
			"callback_error" => $this->getErrorCallback()
		]);
		if($this->hasSoftDisabledInputIds()){
			$input_ids = implode(',', $this->getSoftDisabledInputIds());
			$this->setAttribute("soft_disable", $input_ids);
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($print){
			Debug::print("{$f} entered for a ".$this->getDebugString());
		}
		if($this->hasInputs()){
			if($print){
				Debug::print("{$f} about to release inputs for this ".$this->getDebugString());
			}
			$this->releaseInputs($deallocate);
		}elseif($print){
			Debug::print("{$f} inputs are undefined for this ".$this->getDebugString());
		}
		parent::dispose($deallocate);
		$this->release($this->columnName, $deallocate);
		$this->release($this->directives, $deallocate);
		$this->release($this->errorCallback, $deallocate);
		$this->release($this->formDispatchId, $deallocate);
		$this->release($this->formInputManifest, $deallocate);
		$this->release($this->generateFormButtonsCommandClass, $deallocate);
		$this->release($this->honeypots, $deallocate);
		$this->release($this->honeypotStyleElement, $deallocate);
		$this->release($this->importedCollapseLabel, $deallocate);
		$this->release($this->permissionGateway, $deallocate);
		if($this->hasPermissions()){
			$this->releasePermissions($deallocate);
		}
		$this->release($this->singlePermissionGateways, $deallocate);
		$this->release($this->softDisabledInputIds, $deallocate);
		$this->release($this->successCallback, $deallocate);
		if($this->hasSuperiorForm()){
			$this->releaseSuperiorForm($deallocate);
		}
		$this->release($this->superiorFormIndex, $deallocate);
		$this->release($this->validator, $deallocate);
	}
}
