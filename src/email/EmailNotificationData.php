<?php

namespace JulianSeymour\PHPWebApplicationFramework\email;

use function JulianSeymour\PHPWebApplicationFramework\substitute;
use function JulianSeymour\PHPWebApplicationFramework\x;
use function JulianSeymour\PHPWebApplicationFramework\db;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\columns\SubjectiveTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\UnsignedIntegerDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\notification\NotificationSubjectClassResolver;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;

abstract class EmailNotificationData extends SpamEmail implements StaticSubtypeInterface, StaticTableNameInterface{
	
	use SubjectiveTrait;
	use StaticSubtypeTrait;
	use StaticTableNameTrait;
	
	public abstract function getSubjectLine():string;
	
	public abstract function getPlaintextBody():string;
	
	public abstract function isOptional():bool;
	
	public abstract function getActionURIPromptMap();
	
	public function __construct(?int $mode=ALLOCATION_MODE_EAGER){
		parent::__construct($mode);
		$this->setSenderEmailAddress("noreply@".DOMAIN_LOWERCASE);
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$recipientLanguagePreference = new StringEnumeratedDatum("recipientLanguagePreference");
		$recipientLanguagePreference->setNullable(true);
		$recipientLanguagePreference->setDefaultValue(LANGUAGE_DEFAULT);
		$subject = new ForeignMetadataBundle("subject", $ds);
		$subject->setForeignDataStructureClassResolver(NotificationSubjectClassResolver::class);
		$subject->constrain();
		$subject->setNullable(true);
		$subject->setOnDelete(REFERENCE_OPTION_SET_NULL);
		$subject->setOnUpdate(REFERENCE_OPTION_CASCADE);
		$subject->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
		$subjectNumber = new UnsignedIntegerDatum("subjectNumber", 64);
		$subjectNumber->volatilize();
		array_push($columns, $recipientLanguagePreference, $subject, $subjectNumber);
	}
	
	public function getVirtualColumnValue(string $column_name){
		$f = __METHOD__;
		try{
			switch($column_name){
				case 'subtype':
					return $this->getSubtypeStatic();
				default:
					return parent::getVirtualColumnValue($column_name);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function hasVirtualColumnValue(string $column_name): bool{
		switch($column_name){
			case 'subtype':
				return true;
			default:
				return parent::hasVirtualColumnValue($column_name);
		}
	}
	
	protected final function getPlaintextPromptHyperlinks(){
		$string = "";
		$prompts = $this->getActionURIPromptMap();
		if(empty($prompts)){
			return null;
		}
		foreach($prompts as $uri => $prompt){
			$string .= "\n\n";
			$string .= substitute(_("Visit the following link to %1%"), $prompt);
			$string .= ": {$uri}";
		}
		return $string;
	}
	
	public function setSubjectNumber(int $num):int{
		return $this->setColumnValue("subjectNumber", $num);
	}
	
	public function setSubjectData($subject){
		if($subject->hasSerialNumber()){
			$this->setSubjectNumber($subject->getSerialNumber());
		}
		return $this->setForeignDataStructure("subjectKey", $subject);
	}
	
	public function isEmailNotificationWarranted():bool{
		return $this->getSubjectData()->isEmailNotificationWarranted($this->getRecipient());
	}
	
	protected function getPlaintextContent():string{
		$eol = "\r\n";
		$ret = parent::getPlaintextContent() . "{$eol}";
		$ret .= $this->getPlaintextPromptHyperlinks();
		return $ret;
	}
	
	public function send():int{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->isOptional()){
				if(!$this->isEmailNotificationWarranted()){
					Debug::error("{$f} the subject does not warrant an email notification");
					return $this->setObjectStatus(SUCCESS);
				}elseif(!$this->getRecipient()->getEmailNotificationStatus($this->getNotificationType())){
					if($print){
						Debug::print("{$f} email notifications are disabled");
					}
					return $this->setObjectStatus(SUCCESS);
				}elseif($print){
					Debug::print("{$f} email notification is warranted, and this notification type is enabled");
				}
			}elseif($print){
				Debug::print("{$f} this email is not optional");
			}
			return parent::send();
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function hasSenderEmailAddress():bool{
		return true;
	}
	
	public function getSenderEmailAddress():string{
		if(!parent::hasSenderEmailAddress()){
			return $this->setSenderEmailAddress("noreply@".DOMAIN_LOWERCASE);
		}
		return parent::getSenderEmailAddress();
	}
	
	public static function getTableNameStatic():string{
		return "email_notifications";
	}
	
	public static function getDataType():string{
		return DATATYPE_EMAIL_NOTIFICATION;
	}
	
	public function setRecipientLanguagePreference(string $value):string{
		return $this->setColumnValue("recipientLanguagePreference", $value);
	}
	
	public function getRecipientLanguagePreference():string{
		return $this->getColumnValue("recipientLanguagePreference");
	}
	
	/**
	 *
	 * @param UserData $user
	 * @return UserData
	 */
	public function setRecipient(UserData $user):UserData{
		$f = __METHOD__;
		$print = false;
		if($user->hasLanguagePreference()){
			$this->setRecipientLanguagePreference($user->getLanguagePreference());
		}
		return parent::setRecipient($user);
	}
	
	public static function getElementClassStatic(?StaticElementClassInterface $that = null):string{
		return EmailNotificationElement::class;
	}
	
	public function getNotificationType():string{
		return $this->getSubtypeStatic();
	}
}
