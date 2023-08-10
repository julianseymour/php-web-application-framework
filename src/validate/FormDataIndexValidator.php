<?php
namespace JulianSeymour\PHPWebApplicationFramework\validate;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\file\FileUploadForm;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\form\FormTrait;
use Exception;

class FormDataIndexValidator extends Validator{

	use FormTrait;

	public function __construct(AjaxForm $form){
		parent::__construct();
		$this->setForm($form);
		if (! $form->getNestedFlag()) {
			$this->pushCovalidators(new FormButtonValidator($form));
			$this->setCovalidateWhen(CONST_BEFORE);
		}
	}

	public function evaluate(&$validate_me): int{
		$f = __METHOD__; 
		try {
			if (! is_array($validate_me)) {
				Debug::error("{$f} received something that is not an array");
			}
			$print = false;
			$form = $this->getForm();
			foreach ($form->getValidateInputNames() as $name) {
				if ($print) {
					Debug::print("{$f} about to validate input at index \"{$name}\"");
				}
				$input = $form->getInput($name);
				if (is_array($input)) {
					foreach ($input as $nested_form) {
						if (! $form instanceof AjaxForm) {
							Debug::error("{$f} the only time this should be appropriate is if there is a nested form");
						} elseif ($nested_form instanceof FileUploadForm) {
							if ($print) {
								Debug::print("{$f} nested form \"{$name}\" is a file upload");
							}
							// continue;
						}
						$nested_form->nested();
						$validator = $nested_form->getValidator();
						if (! array_key_exists($name, $validate_me)) {
							if ($print) {
								Debug::print("{$f} nested form \"{$name}\" wat not posted");
							}
							continue;
						} elseif (! is_array($validate_me[$name])) {
							Debug::error("{$f} array key \"{$name}\" is does not have an array value");
						} elseif ($print) {
							Debug::print("{$f} about to validate nested form \"{$name}\"");
						}
						$status = $validator->validate($validate_me[$name]);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} validating nested form \"{$name}\" returned error status \"{$err}\"");
							return $this->setObjectStatus($status);
						} elseif ($print) {
							Debug::print("{$f} successfully validated nested form \"{$name}\"");
						}
					}
				} else {
					$status = $input->processArray($validate_me);
					if ($status !== SUCCESS && $status !== STATUS_UNCHANGED) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} processArray returned error status \"{$err}\" for input \"{$name}\"");
						return $this->setObjectStatus($status);
					} elseif ($input->hasValidators()) {
						$validators = $input->getValidators();
						if (empty($validators)) {
							Debug::error("{$f} input \"{$name}\" validator array is empty");
						}
						foreach ($validators as $validator) {
							$valid = $validator->validate($validate_me);
							if ($valid !== SUCCESS) {
								$err = ErrorMessage::getResultMessage($valid);
								Debug::warning("{$f} validateIndex returned error status \"{$err}\"");
								return $this->setObjectStatus($valid);
							}
						}
					} elseif ($print) {
						Debug::print("{$f} input \"{$name}\" does not have any validators");
					}
				}
			}
			if ($print) {
				Debug::print("{$f} returning successfuully");
			}
			return $this->setObjectStatus(SUCCESS);
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
