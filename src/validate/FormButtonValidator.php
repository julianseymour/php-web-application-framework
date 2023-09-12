<?php
namespace JulianSeymour\PHPWebApplicationFramework\validate;

use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\form\FormTrait;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenValidator;
use Exception;

class FormButtonValidator extends Validator
{

	use FormTrait;

	public function __construct(AjaxForm $form)
	{
		parent::__construct();
		$this->setForm($form);
		if($form->getMethodAttribute() === HTTP_REQUEST_METHOD_POST && ! $form->getNestedFlag()) {
			$this->pushCovalidators(new AntiXsrfTokenValidator($form->getActionAttribute()));
			$this->setCovalidateWhen(CONST_BEFORE);
		}
	}

	public function evaluate(&$validate_me): int
	{
		$f = __METHOD__; //FormButtonValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
		try{
			$print = false;
			/*
			 * $status = parent::evaluate($validate_me);
			 * if($status !== SUCCESS){
			 * $err = ErrorMessage::getResultMessage($status);
			 * Debug::warning("{$f} parent function returned error status \"{$err}\"");
			 * return $this->setObjectStatus($status);
			 * }
			 */
			$form = $this->getForm();
			$directive = directive();
			$submits = $form->getDirectives();
			if($print) {
				Debug::print("{$f} form returned the following valid button names:");
				Debug::printArray($submits);
			}
			if(! array_key_exists("directive", $validate_me)) {
				Debug::warning("{$f} directive was not posted");
				return $this->getSpecialFailureStatus();
			}elseif($directive === DIRECTIVE_NONE) {
				Debug::warning("{$f} no directive");
				return $this->getSpecialFailureStatus();
			}elseif($directive === DIRECTIVE_DELETE_FOREIGN) {
				$delete_me = $validate_me["directive"][DIRECTIVE_DELETE_FOREIGN];
				if(is_array($delete_me)) {
					$context = $form->getContext();
					foreach($delete_me as $column_name => $child_key) {
						$datum = $context->getColumn($column_name);
						if(is_array($child_key)) { // invoices => [lineItems => $child_key]
							Debug::error("{$f} unimplemented: arbitrary depth delete data structure");
						}elseif($datum instanceof KeyListDatum) { // lineItems => $child_key
							if($print) {
								Debug::print("{$f} column \"{$column_name}\" is a keylist");
							}
							$struct = $context->getForeignDataStructureListMember($column_name, $child_key);
						}elseif($datum instanceof ForeignKeyDatum) {
							if($print) {
								Debug::print("{$f} column \"{$column_name}\" is a singular foreign key");
							}
							$struct = $context->getForeignDataStructure($column_name);
						}else{
							Debug::error("{$f} none of the above");
						}
						$subform = $form->getSubordinateForm($column_name, $struct);
						$subvalidator = $subform->getValidator();
						$sub_arr = [
							"directive" => [
								DIRECTIVE_DELETE_FOREIGN => $child_key
							],
							'xsrf_token' => $validate_me['xsrf_token'],
							'secondary_hmac' => $validate_me['secondary_hmac']
						];
						if($print) {
							Debug::print("{$f} about to validate with subvalidator on the following content");
							Debug::printArray($sub_arr);
						}
						$status = $subvalidator->validate($sub_arr);
						if($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::printStackTrace("{$f} subordinate form validation returned error status \"{$err}\"");
							return $this->setObjectStatus($status);
						}
					}
					if($print) {
						Debug::printStackTrace("{$f} successfully validated subordinate struct deletion");
					}
				}else{
					$buttons = $form->generateButtons($directive);
					$found = false;
					foreach($buttons as $button) {
						if($delete_me === $button->getValueAttribute()) {
							if($print) {
								Debug::print("{$f} match found for value attribute \"{$delete_me}\"");
							}
							$found = true;
							break;
						}
					}
					if(!$found) {
						Debug::warning("{$f} failed to find value \"{$delete_me}\" in any of the buttons generated for this form");
						return $this->getSpecialFailureStatus();
					}
				}
			}elseif(false === array_search($directive, $submits)) {
				Debug::warning("{$f} user has tampered with post -- directive \"{$directive}\" not found");
				Debug::printArray($validate_me);
				Debug::printStackTraceNoExit();
				return $this->setObjectStatus(ERROR_TAMPER_POST);
			}
			if($print) {
				Debug::print("{$f} returning normally");
			}
			return $this->setObjectStatus(SUCCESS);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
