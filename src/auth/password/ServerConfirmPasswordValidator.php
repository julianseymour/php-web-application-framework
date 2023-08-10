<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\input\InputInterface;
use JulianSeymour\PHPWebApplicationFramework\input\PasswordInput;
use Exception;

/**
 * This validator generates an input which is armed with its client-side counterpart ClientConfirmPasswordValidator.
 * The javascript event handler must be applied to the confirm password field to present as expected by the user.
 * However because the confirm password field is not part of the data structure it does not get validated server side;
 * instead, this validator handles server-side validation.
 *
 * @author j
 */
class ServerConfirmPasswordValidator extends ConfirmPasswordValidator{

	public function setInput(InputInterface $input){
		$f = __METHOD__;
		try{
			$print = false;
			// create confirm password input
			$mode = $input->getAllocationMode();
			$confirm_input = new PasswordInput($mode);
			$cpname = $this->getCounterpartNameAttribute();
			$confirm_input->setNameAttribute($confirm_input->setColumnName($cpname));
			$confirm_input->setAutocompleteAttribute("off");
			$confirm_input->setMinimumLengthAttribute($input->getMinimumLengthAttribute());
			$confirm_input->setRequiredAttribute(true);
			$confirm_input->pushValidator(new ClientConfirmPasswordValidator($input->getNameAttribute()));
			// oninvalid event
			$input->setAttribute("counterpartName", $cpname);
			$input->setOnInputAttribute("ClientConfirmPasswordValidator.changePassword(event, this)");
			if ($input->hasPlaceholderMode()) {
				$confirm_input->setPlaceholderMode($input->getPlaceholderMode());
			}
			if ($input->hasLabelString()) {
				if ($print) {
					Debug::print("{$f} input has a label string");
				}
				$confirm_input->setLabelString(substitute(_("Confirm %1%"), $input->getLabelString()));
			} elseif ($print) {
				Debug::print("{$f} input lacks a label string");
			}
			if ($input->hasForm()) {
				if ($print) {
					Debug::print("{$f} input has a form");
				}
				$confirm_input->setForm($input->getForm());
				$input->getForm()->reconfigureInput($confirm_input);
			} elseif ($print) {
				$decl = $input->getDeclarationLine();
				Debug::print("{$f} input does not have a form, instantiated {$decl}");
			}
			// validity indicator light
			$suffix = new DivElement($mode);
			$suffix->addClassAttribute("js_valid_light");
			$suffix->setIdAttribute("js_valid_confirm");
			$suffix->setAllowEmptyInnerHTML(true);
			$confirm_input->pushSuccessor($suffix);
			if($input->hasWrapperElement()){
				$input->getWrapperElement()->pushSuccessor($confirm_input);
			}else{
				$input->pushSuccessor($confirm_input);
			}
			return parent::setInput($input);
		}catch(Exception $x){
			x($f, $x);
		}
	}
}