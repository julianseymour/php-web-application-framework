<?php
namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\validate\Validator;

/**
 * validates a posted password with password_verify for the current user
 * if you want to know whether a posted string is elligible for use as a password, use DatumValidator with a PasswordDatum
 *
 * @author j
 */
class PasswordValidator extends Validator
{

	use NamedTrait;

	public function __construct($password_index = null)
	{
		parent::__construct();
		if(!empty($password_index)){
			$this->setName($password_index);
		}
	}

	public function getSpecialFailureStatus()
	{
		return ERROR_PASSWORD_UNDEFINED;
	}

	public function evaluate(&$validate_me): int
	{
		$name = $this->getName();
		if(! hasInputParameter($name)){
			return $this->getSpecialFailureStatus();
		}
		$user = user();
		if(password_verify(getInputParameter($name), $user->getPasswordHash())){
			return SUCCESS;
		}
		return $this->getSpecialFailureStatus();
	}
}
