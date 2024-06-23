<?php

namespace JulianSeymour\PHPWebApplicationFramework\contact;

class ContactUsUseCase extends AbstractContactUseCase{
	
	public function getEmailClass():string{
		return ContactUsEmail::class;
	}
	
	public function getFormClass():string{
		return ContactUsForm::class;
	}
	
	public function getActionAttribute():?string{
		return "/contact";
	}
}

