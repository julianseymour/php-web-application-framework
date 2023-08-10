<?php
namespace JulianSeymour\PHPWebApplicationFramework\email\change;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\AuthenticatedUser;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\AuthenticatedConfirmationCode;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\email\EmailAddressDatum;
use Exception;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\hasInputParameter;

class ChangeEmailAddressConfirmationCode extends AuthenticatedConfirmationCode
{

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::declareColumns($columns, $ds);
		$newEmailAddress = new EmailAddressDatum("newEmailAddress");
		$newEmailAddress->volatilize();
		static::pushTemporaryColumnsStatic($columns, $newEmailAddress);
	}

	public static function getSentEmailStatus()
	{
		return RESULT_CHANGEMAIL_SUBMIT;
	}

	public function setEmailAddress($email)
	{
		$f = __METHOD__; //ChangeEmailAddressConfirmationCode::getShortClass()."(".static::getShortClass().")->setEmailAddress()";
		return $this->setNewEmailAddress($email);
	}

	protected function getAdditionalDataArray(): array
	{
		$arr = parent::getAdditionalDataArray();
		$arr['emailAddress'] = $this->getNewEmailAddress();
		return $arr;
	}

	public function hasNewEmailAddress()
	{
		return $this->hasColumnValue("newEmailAddress");
	}

	public function processDecryptedAdditionalDataArray($data_arr)
	{
		$f = __METHOD__; //ChangeEmailAddressConfirmationCode::getShortClass()."(".static::getShortClass().")->processAdditionalDataArray()";
		try {
			parent::processDecryptedAdditionalDataArray($data_arr);
			/*if (! $this->hasConfirmationCode()) {
				Debug::error("{$f} confirmation code still missing");
			} else*/
			if (! array_key_exists("emailAddress", $data_arr)) {
				Debug::error("{$f} email address is undefined");
			}
			$new_email = $data_arr['emailAddress'];
			$this->setNewEmailAddress($new_email);
			return;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setNewEmailAddress($email)
	{
		$f = __METHOD__; //ChangeEmailAddressConfirmationCode::getShortClass()."(".static::getShortClass().")->setNewEmailAddress()";
		// Debug::printStackTraceNoExit("{$f} entered");
		$print = false;
		if ($email == null) {
			Debug::error("{$f} email address is null");
		}elseif($print){
			Debug::print("{$f} setting new email address to \"{$email}\"");
		}
		$this->setColumnValue("newEmailAddress", $email);
		return $this->getNewEmailAddress();
	}

	protected function extractAdditionalDataFromUser($user){
		$f = __METHOD__; //ChangeEmailAddressConfirmationCode::getShortClass()."(".static::getShortClass().")->extractAdditionalDataFromUser()";
		$print = false;
		if(!hasInputParameter("emailAddress")){
			Debug::printPost("{$f} no email address input parameter");
		}
		$email = getInputParameter("emailAddress"); //)$user->getEmailAddress();
		if($print){
			Debug::print("{$f} new email address is \"{$email}\"");
		}
		$this->setNewEmailAddress($email);
		return SUCCESS;
	}

	public function getNewEmailAddress(){
		$f = __METHOD__; //ChangeEmailAddressConfirmationCode::getShortClass()."(".static::getShortClass().")->getNewEmailAddress()";
		try {
			$print = false;
			if(!$this->hasNewEmailAddress()){
				if($print){
					Debug::print("{$f} new email address is undefined; about to get it from user");
				}
				if(! user() instanceof AuthenticatedUser){
					Debug::error("{$f} user is not logged in");
				} elseif (! user()->hasEmailAddress()){
					Debug::error("{$f} current user lacks an email address");
				}
				$email = user()->getEmailAddress();
				if($email == null){
					Debug::error("{$f} email address is undefined");
					$this->setObjectStatus(ERROR_EMAIL_UNDEFINED);
					return null;
				} elseif ($print){
					Debug::print("{$f} setting email address to \"{$email}\"");
				}
				return $this->setNewEmailAddress($email);
			}
			$email = $this->getColumnValue("newEmailAddress");
			return $email;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getEmailAddress():string{
		$f = __METHOD__; //ChangeEmailAddressConfirmationCode::getShortClass()."(".static::getShortClass().")->getEmailAddress()";
		$email = $this->getNewEmailAddress();
		return $this->getNewEmailAddress();
	}

	public function isSecurityNotificationWarranted()
	{
		return false;
	}

	public static function getConfirmationUriStatic($suffix)
	{
		return WEBSITE_URL . "/confirm_email/{$suffix}";
	}

	public static function getEmailNotificationClass()
	{
		return ChangeEmailAddressEmail::class;
	}

	public static function getConfirmationCodeTypeStatic()
	{
		return ACCESS_TYPE_CHANGE_EMAIL;
	}

	public static function getIpLogReason()
	{
		return BECAUSE_CHANGE_EMAIL;
	}

	protected function getConfirmationUriGetParameters(): array
	{
		$f = __METHOD__; //AuthenticatedConfirmationCode::getShortClass()."(".static::getShortClass().")->getConfirmationUriGetParameters()";
		try {
			$url_arr = parent::getConfirmationUriGetParameters();
			$url_arr['emailAddress'] = $this->getNewEmailAddress(); // change email only
			return $url_arr;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
