<?php
namespace JulianSeymour\PHPWebApplicationFramework\contact;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\email\SpamEmail;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;

class ContactUsEmail extends SpamEmail{
	
	public static function declareColumns(array &$columns, ?DataStructure $ds=null):void{
		parent::declareColumns($columns, $ds);
		$body = new TextDatum("plaintextBody");
		$body->volatilize();
		static::pushTemporaryColumnsStatic($columns, $body);
	}
	
	public function getPlaintextBody(){
		return $this->getColumnValue("plaintextBody");
	}
	
	public function getSubjectLine(){
		return "Question/comment from ".$this->getSenderEmailAddress();
	}
}

