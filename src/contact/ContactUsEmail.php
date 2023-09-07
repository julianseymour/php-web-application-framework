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
		array_push($columns, $body);
	}
	
	public function getPlaintextBody(){
		return $this->getColumnValue("plaintextBody");
	}
	
	public function getSubjectLine():string{
		return "Question/comment from ".$this->getSenderEmailAddress();
	}
}

