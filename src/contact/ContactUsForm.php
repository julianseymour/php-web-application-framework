<?php

namespace JulianSeymour\PHPWebApplicationFramework\contact;

use JulianSeymour\PHPWebApplicationFramework\input\EmailInput;
use JulianSeymour\PHPWebApplicationFramework\input\TextareaInput;

class ContactUsForm extends AbstractContactForm{

	public function getFormDataIndices(): ?array{
		return [
			"senderEmailAddress" => EmailInput::class,
			"plaintextBody" => TextareaInput::class
		];
	}
	
	public function reconfigureInput($input):int{
		switch($input->getColumnName()){
			case "plaintextBody":
				$input->setLabelString(_("Enter body of your question/comment"));
				$input->require();
				break;
			case "senderEmailAddress":
				$input->setLabelString(_("Enter your email address"));
				$input->removeValueAttribute();
				$input->require();
				break;
			default:
		}
		return parent::reconfigureInput($input);
	}
	
	public static function getActionAttributeStatic():?string{
		return "/contact";
	}
}
