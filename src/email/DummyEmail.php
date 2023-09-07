<?php

namespace JulianSeymour\PHPWebApplicationFramework\email;

class DummyEmail extends EmailNotificationData{

	public function getSubjectLine():string{
		return "";
	}

	public static function getSubtypeStatic():string{
		return NOTIFICATION_TYPE_TEST;
	}

	public function isOptional():bool{
		return true;
	}

	public function getActionURIPromptMap():?array{
		return [];
	}

	public function getPlaintextBody():string{
		return "";
	}
}

