<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\confirm_code;

use JulianSeymour\PHPWebApplicationFramework\email\EmailNotificationData;

abstract class ConfirmationCodeEmail extends EmailNotificationData{

	protected abstract function getDefaultActionPrompt():string;

	public function getActionURIPromptMap():?array{
		return [
			$this->getSubjectData()->getConfirmationUri() => $this->getDefaultActionPrompt()
		];
	}
}
