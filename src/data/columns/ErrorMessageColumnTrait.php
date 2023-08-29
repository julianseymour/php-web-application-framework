<?php
namespace JulianSeymour\PHPWebApplicationFramework\data\columns;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use mysqli;

trait ErrorMessageColumnTrait{

	public function setErrorMessage(string $value): string{
		return $this->setColumnValue("errorMessage", $value);
	}

	public function hasErrorMessage(): bool{
		return $this->hasColumnValue("errorMessage");
	}

	public function getErrorMessage(): string{
		return $this->getColumnValue("errorMessage");
	}

	public function ejectErrorMessage(): ?string{
		return $this->ejectColumnValue("errorMessage");
	}

	public function updateErrorMessage(mysqli $mysqli, ?string $msg): int{
		$f = __METHOD__; //"ErrorMessageColumnTrait(".static::getShortClass().")->updateErrorMessage()";
		if ($msg == null) {
			$this->ejectErrorMessage();
		} else {
			$this->setErrorMessage($msg);
		}
		$status = $this->update($mysqli);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} updating error message returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		return SUCCESS;
	}
}
