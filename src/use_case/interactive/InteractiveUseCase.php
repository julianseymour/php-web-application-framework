<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\account\owner\UserOwned;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\DatabaseManager;
use JulianSeymour\PHPWebApplicationFramework\db\load\Loadout;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\search\SearchUseCase;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;
use JulianSeymour\PHPWebApplicationFramework\validate\ValidatorTrait;
use Exception;
use mysqli;

abstract class InteractiveUseCase extends UseCase{

	use ValidatorTrait;

	protected $originalOperand;

	protected $processedFormObject;

	protected $searchUseCase;

	public abstract function getDataOperandClass():?string;

	public abstract function getProcessedDataType():?string;

	public abstract function getConditionalDataOperandClasses():?array;

	public abstract function getConditionalProcessedFormClasses():?array;

	public abstract function getConditionalElementClasses():?array;

	public abstract function acquireDataOperandOwner(mysqli $mysqli, UserOwned $owned_object):?UserData;

	/**
	 * mechanism to prevent users from tampering with forms that update their own settings by changing key
	 *
	 * @return boolean
	 */
	public abstract function isCurrentUserDataOperand(): bool;

	public function hasOriginalOperand(): bool{
		return isset($this->originalOperand);
	}

	public function setOriginalOperand(?DataStructure $struct): ?DataStructure{
		if($this->hasOriginalOperand()){
			$this->release($this->originalOperand);
		}
		return $this->originalOperand = $this->claim($struct);
	}

	public function getOriginalOperand(): DataStructure{
		$f = __METHOD__;
		if(!$this->hasOriginalOperand()){
			Debug::error("{$f} original operand is undefined");
		}
		return $this->originalOperand;
	}

	public function createDataOperandObject(): DataStructure{
		$doc = $this->getDataOperandClass();
		$ds = new $doc();
		return $ds;
	}

	public function getProcessedFormClass(): ?string{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$classes = $this->getConditionalProcessedFormClasses();
		if(empty($classes)){
			if($print){
				Debug::print("{$f} getConditionalProcessedFormClasses returned null");
			}
			return null;
		}
		$forms = [];
		foreach($classes as $class){
			$forms[$class::getFormDispatchIdStatic()] = $class;
		}
		if(! hasInputParameter("dispatch", $this)){
			if($print){
				Debug::warning("{$f} no form posted");
				Debug::printArray(getInputParameters());
			}
			return null;
		}
		$dispatch = getInputParameter('dispatch');
		if(!array_key_exists($dispatch, $forms)){
			if($print){
				Debug::print("{$f} processed for class is undefined");
			}
			return null;
		}
		$form_class = $forms[$dispatch];
		if($print){
			Debug::print("{$f} form class is \"{$form_class}\"");
		}
		return $form_class;
	}

	public function setProcessedFormObject(?AjaxForm $form): ?AjaxForm{
		if($this->hasProcessedFormObject()){
			$this->release($this->processedFormObject);
		}
		return $this->processedFormObject = $this->claim($form);
	}

	public function hasProcessedFormObject(): bool{
		return !empty($this->processedFormObject) && $this->processedFormObject instanceof AjaxForm;
	}

	public function getProcessedFormObject(): AjaxForm{
		$f = __METHOD__;
		if(!$this->hasProcessedFormObject()){
			if($this->hasPredecessor()){
				$predecessor = $this->getPredecessor();
				if($predecessor instanceof InteractiveUseCase && $predecessor->hasProcessedFormObject()){
					return $this->setProcessedFormObject($predecessor->getProcessedFormObject());
				}
			}
			Debug::error("{$f} processed form object is undefined");
		}
		return $this->processedFormObject;
	}

	public function getConditionalDataOperandClass(string $type): string{
		$f = __METHOD__;
		try{
			$classes = $this->getConditionalDataOperandClasses();
			if(false === array_key_exists($type, $classes)){
				Debug::error("{$f} invalid datatype \"{$type}\"");
			}
			return $classes[$type];
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function showNewItemForm(): bool{
		return true;
	}

	public function getConditionalElementClass(string $type): string{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} type \"{$type}\"");
			}
			$classes = $this->getConditionalElementClasses();
			if(!is_array($classes)){
				Debug::error("{$f} getConditionalElementClasses returned something that is not an array");
			}elseif($print){
				Debug::print("{$f} we have the following conditional element classes:");
				Debug::printArray($classes);
			}
			if(false === array_key_exists($type, $classes)){
				Debug::warning("{$f} invalid datatype \"{$type}\"");
				Debug::printArray($classes);
				Debug::printStackTrace();
			}elseif($print){
				Debug::print("{$f} returning \"{$classes[$type]}\"");
			}
			return $classes[$type];
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function acquireDataOperandObject(mysqli $mysqli): ?DataStructure{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($print){
				Debug::print("{$f} entered");
			}
			$directive = directive();
			if(user() == null){
				Debug::error("{$f} user data is undefined");
				$this->setObjectStatus(ERROR_NULL_USER_OBJECT);
				return null;
			}
			$doc = $this->getDataOperandClass();
			if($doc === null){
				if($print){
					Debug::print("{$f} data operand class is null");
				}
				return null;
			}elseif($print){
				Debug::print("{$f} data operand class is \"{$doc}\"");
			}
			switch($directive){
				case DIRECTIVE_INSERT:
				case DIRECTIVE_UPLOAD:
				case DIRECTIVE_PROCESS:
				case DIRECTIVE_MASS_DELETE:
					$ds = new $doc();
					if($ds instanceof UserOwned){
						if($print){
							Debug::print("{$f} {$doc} has a user data");
						}
						$owner = $this->acquireDataOperandOwner($mysqli, $ds);
						$ds->setUserData($owner);
					}
					if(!is_object($ds)){
						Debug::error("{$f} before reconfigureDataOperand, operand must be an object");
					}
					$this->reconfigureDataOperand($mysqli, $ds);
					if(!is_object($ds)){
						Debug::error("{$f} defore setDataOperandObject, data operand must be an object");
					}
					return $this->setDataOperandObject($ds);
				default:
					$idn = $doc::getIdentifierNameStatic();
					if(!hasInputParameter($idn)){
						if($print){
							Debug::print("{$f} key was not posted");
							Debug::printArray($_POST);
						}
						$ds = new $doc();
						$this->reconfigureDataOperand($mysqli, $ds);
						return $this->setDataOperandObject($ds);
					}elseif($print){
						Debug::print("{$f} {$idn} is ".getInputParameter($idn));
					}
					$user = user();
					$type = $this->getProcessedDataType();
					if($type === DATATYPE_USER && $this->isCurrentUserDataOperand()){
						$this->reconfigureDataOperand($mysqli, $user);
						return $this->setDataOperandObject($user);
					}elseif($print){
						Debug::print("{$f} datatype is not user, or current user is not data operand");
					}
					$key = getInputParameter($idn);
					if(registry()->hasObjectRegisteredToKey($key)){
						if($print){
							Debug::print("{$f} object was already mapped to key \"{$key}\"");
						}
						$ds = registry()->getRegisteredObjectFromKey($key);
					}else{
						if($print){
							Debug::print("{$f} registry does not know about something with key \"{$key}\"");
						}
						$doc = $this->getDataOperandClass($this);
						if($print){
							Debug::print("{$f} {$doc} with key {$key} was not already mapped");
						}
						$ds = new $doc();
						if($print){
							Debug::print("{$f} about to load {$doc} with key \"{$key}\"");
						}
						$status = $ds->load($mysqli, $idn, $key);
						if($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} loading {$doc} with key {$key} returned error status \"{$err}\"");
							$this->setObjectStatus($status);
							return null;
						}elseif($print){
							Debug::print("{$f} successfully loaded {$doc} with key {$key}");
						}
					}
			}
			$ds->setAutoloadFlags(true);
			$status = $ds->loadForeignDataStructures($mysqli, false, 3);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loadForeignDataStructures returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}elseif($print){
				Debug::print("{$f} successfully loaded foreign data structures");
			}
			if(!$ds->getFlag("expandForeign")){
				if($print){
					Debug::print("{$f} about to call expandForeignDataStructures");
				}
				$status = Loadout::expandForeignDataStructures($ds, $mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} expandForeignDataStructures returned error status \"{$err}\"");
					$this->setObjectStatus($status);

					return null;
				}
			}elseif($print){
				Debug::print("{$f} object has already expanded foreign data structures");
			}
			// $this->reconfigureDataOperand($mysqli, $replica);
			$this->setOriginalOperand($ds);
			$this->reconfigureDataOperand($mysqli, $ds);
			return $this->setDataOperandObject($ds);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	protected function reconfigureDataOperand(mysqli $mysqli, DataStructure &$ds): int{
		return SUCCESS;
	}

	public function getValidator(): ?Validator{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasValidator()){
			if($print){
				Debug::print("{$f} we don't have a validator yet, attempting to create one");
			}
			if($this->hasPredecessor()){
				if($print){
					Debug::print("{$f} returning validator from predecessor");
				}
				$predecessor = $this->getPredecessor();
				if($predecessor instanceof InteractiveUseCase && $predecessor->hasValidator()){
					return $this->setValidator($predecessor->getValidator());
				}
			}elseif($this->hasProcessedFormObject()){
				$form = $this->getProcessedFormObject();
				if($print){
					$fc = $form->getClass();
					Debug::print("{$f} no predecessor, going to ask processed form of class \"{$fc}\" for a validator");
				}
				if($form->hasValidator()){
					$validator = $form->getValidator();
					if($print){
						$vc = $validator->getClass();
						Debug::print("{$f} returning processed form validator of class \"{$vc}\"");
					}
					return $validator;
				}
			}
			$directive = directive();
			Debug::error("{$f} validator is undefined; directive is \"{$directive}\"");
		}
		return $this->validator;
	}

	public function getElementForInsertion(?DataStructure $ds): Element{
		$datatype = $ds->getDataType();
		$ciec = $this->getConditionalElementClass($datatype);
		$ds_element = new $ciec(ALLOCATION_MODE_LAZY);
		$ds_element->bindContext($ds);
		return $ds_element;
	}

	public function getInsertHereElement(?DataStructure $ds = null){
		$f = __METHOD__;
		try{
			$new_object = $this->createDataOperandObject($this);
			if(isset($ds)){
				$indices = [
					'parentKey',
					'correspondentKey'
				];
				foreach($indices as $index){
					if($ds->hasColumn($index)){
						$value = $ds->getColumnValue($index);
						if(isset($value)){
							$new_object->setColumnValue($index, $value);
						}else{
							Debug::warning("{$f} {$index} is undefined");
						}
					}
				}
			}
			$datatype = $new_object->getDataType();
			$ciec = $this->getConditionalElementClass($datatype);
			$new_element = new $ciec(ALLOCATION_MODE_LAZY);
			$new_element->bindContext($new_object);
			return $new_element;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getElementForUpdate(?DataStructure $backup){
		$f = __METHOD__;
		$print = false;
		$updated_object = $this->getDataOperandObject();
		$type = $updated_object->getDataType();
		if(!isset($type)){
			$class = $updated_object->getClass();
			Debug::error("{$f} type is undefined for object of class {$class}");
		}
		$iec = $this->getConditionalElementClass($type);
		if($print){
			Debug::print("{$f} about to create an element of class \"{$iec}\"");
		}
		$updated_element = new $iec(ALLOCATION_MODE_LAZY);
		if($updated_element instanceof AjaxForm){
			$updated_element->setValidator($this->getValidator());
		}
		// $updated_element->setCatchReportedSubcommandsFlag(true);
		$updated_element->bindContext($updated_object);
		return $updated_element;
	}

	/**
	 * this function exists to override/extend in classes where e.g.
	 * the form needs a new action attribute
	 *
	 * @return AjaxForm
	 */
	protected function generateProcessedForm(): ?AjaxForm{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$form_class = $this->getProcessedFormClass();
		if(empty($form_class)){
			Debug::warning("{$f} getProcessedFormClass returned empty string");
			return null;
		}elseif(!class_exists($form_class)){
			Debug::error("{$f} class \"{$form_class}\" does not exist");
			return null;
		}elseif($print){
			Debug::print("{$f} processed form class is \"{$form_class}\"");
		}
		$form = new $form_class(ALLOCATION_MODE_FORM);
		$form->disableRendering();
		return $form;
	}

	public function getResponder(int $status): ?Responder{
		$f = __METHOD__;
		$print = false;
		if($status !== SUCCESS){
			if($print){
				Debug::print("{$f} use case was not executed successfully");
			}
			return parent::getResponder($status);
		}elseif($print){
			Debug::print("{$f} use case was executed successfully");
		}
		switch(directive()){
			case DIRECTIVE_REGENERATE:
			case DIRECTIVE_UNSET:
			case DIRECTIVE_UPDATE:
				if($print){
					Debug::print("{$f} returning udate responder");
				}
				return new UpdateResponder();
			case DIRECTIVE_DELETE:
				if($print){
					Debug::print("{$f} returning delete responder");
				}
				return new DeleteResponder();
			default:
		}
		if($print){
			Debug::print("{$f} calling parent function");
		}
		return parent::getResponder($status);
	}

	protected function getInteractorClass(): ?string{
		$f = __METHOD__;
		$print = false;
		switch(directive()){
			case DIRECTIVE_DELETE: //moved before update as a hacky workaround for CIDR IP address form
				return DeleteUseCase::class;
			case DIRECTIVE_DELETE_FOREIGN:
				return DeleteForeignDataStructureUseCase::class;
			case DIRECTIVE_IMPORT_CSV:
				return ImportCsvFilesUseCase::class;
			case DIRECTIVE_INSERT:
			case DIRECTIVE_UPLOAD:
				return InsertUseCase::class;
			case DIRECTIVE_PROCESS:
				return ProcessFormUseCase::class;
			case DIRECTIVE_UNSET:
				return UnsetUseCase::class;
			case DIRECTIVE_UPDATE:
				return UpdateUseCase::class;
			case DIRECTIVE_REGENERATE:
				return RegenerateUseCase::class;
			case DIRECTIVE_MASS_DELETE:
				return MassDeleteUseCase::class;
			case DIRECTIVE_EMAIL_CONFIRMATION_CODE:
				return EmailConfirmationCodeUseCase::class;
			case DIRECTIVE_VALIDATE:
				return ValidateUseCase::class;
			default:
				if($print){
					Debug::print("{$f} no directive");
				}
				return null;
		}
	}

	public function execute(): int{
		$f = __METHOD__;
		try{
			$print = false;
			if(SearchUseCase::isSearchEvent()){
				if($print){
					Debug::print("{$f} this is a search event");
				}
				$search = new SearchUseCase($this);
				$search->setSearchClasses($this->getConditionalDataOperandClasses());
				$search->execute(true);
				$this->setSearchUseCase($search);
				return SUCCESS;
			}elseif(empty($_POST) && empty($_GET)){
				if($print){
					Debug::print("{$f} nothing to do here");
				}
				return SUCCESS;
			}elseif($print){
				Debug::print("{$f} entered");
			}
			$mysqli = db()->reconnect(DatabaseManager::detectCredentialsClass());
			if(!isset($mysqli)){
				$err = ErrorMessage::getResultMessage(ERROR_MYSQL_CONNECT);
				Debug::error("{$f} {$err}");
			}
			$ds = $this->acquireDataOperandObject($mysqli);
			if($ds && $print){
				$key = $ds->hasIdentifierValue() ? $ds->getIdentifierValue() : "[undefined]";
				Debug::print("{$f} data operand has key \"{$key}\"");
			}
			$directive = directive();
			if($directive !== DIRECTIVE_NONE){
				$form = $this->generateProcessedForm();
				if($form !== null){
					if($print){
						Debug::print("{$f} form is not null");
					}
					$form->submitHook();
					if($ds == null){
						if($print){
							Debug::print("{$f} acquireDataOperandObject returned null");
						}
					}else{
						if($print && $ds->isUninitialized()){
							Debug::print("{$f} binding form to an uninitialized context");
						}
						$form->bindContext($ds);
					}
					if($print && ! $form->hasActionAttribute()){
						Debug::warning("{$f} form lacks an action attribute after binding");
					}
					$this->setProcessedFormObject($form);
				}elseif($print){
					Debug::print("{$f} processed form is null");
				}
			}
			$interactor_class = $this->getInteractorClass();
			if($interactor_class === null){
				if($print){
					Debug::print("{$f} nothing to do here");
				}
				return SUCCESS;
			}elseif($print){
				Debug::print("{$f} interactor class is \"{$interactor_class}\"");
			}
			$interactor = new $interactor_class($this);
			if($form instanceof AjaxForm){
				$validator = $form->getValidator();
				$params = getInputParameters();
				if($print){
					Debug::printArray($params);
				}
				$valid = $validator->validate($params);
				$form->setValidator($validator);
				$this->setValidator($validator);
				if($valid !== SUCCESS){
					$err = ErrorMessage::getResultMessage($valid);
					Debug::warning("{$f} processed form validation returned error status \"{$err}\"");
					return $this->setObjectStatus($valid);
				}elseif($print){
					Debug::print("{$f} validation successful");
				}
			}elseif($print){
				Debug::printArray(getInputParameters());
				Debug::print("{$f} form is undefined");
			}
			$status = $interactor->execute();
			app()->setUseCase($this);
			return $status;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasSearchUseCase():bool{
		return isset($this->searchUseCase) && $this->searchUseCase instanceof SearchUseCase;
	}

	public function hasSearchResults():bool{
		return $this->hasSearchUseCase() && $this->getSearchUseCase()->getSearchResults();
	}

	public function getSearchUseCase(): SearchUseCase{
		$f = __METHOD__;
		if(!$this->hasSearchUseCase()){
			Debug::error("{$f} search use case is undefined");
		}
		return $this->searchUseCase;
	}

	public function setSearchUseCase($search){
		if($this->hasSearchUseCase()){
			$this->release($this->searchUseCase);
		}
		return $this->searchUseCase = $this->claim($search);
	}
	
	public function getSearchResults(): ?array{
		$f = __METHOD__;
		if(!$this->hasSearchUseCase()){
			Debug::error("{$f} search use case is undefined");
		}
		return $this->getSearchUseCase()->getSearchResults();
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->originalOperand, $deallocate);
		$this->release($this->processedFormObject, $deallocate);
		$this->release($this->searchUseCase, $deallocate);
		$this->release($this->validator, $deallocate);
	}
}
