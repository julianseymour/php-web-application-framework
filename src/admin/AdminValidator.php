<?php
namespace JulianSeymour\PHPWebApplicationFramework\admin;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\security\xsrf\AntiXsrfTokenValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;

class AdminValidator extends Validator
{

	public function __construct($uri)
	{
		parent::__construct();
		$this->setSpecialFailureStatus(ERROR_EMPLOYEES_ONLY);
		$this->setCovalidateWhen(COVALIDATE_AFTER);
		$this->pushCovalidators(new AntiXsrfTokenValidator($uri));
	}

	public function evaluate(&$validate_me)
	{
		$f = __METHOD__; //AdminValidator::getShortClass()."(".static::getShortClass().")->evaluate()";
		$user = user();
		if($user instanceof Administrator) {
			return SUCCESS;
		}
		return $this->getSpecialFailureStatus();
	}
}
