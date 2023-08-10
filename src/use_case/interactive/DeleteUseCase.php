<?php
namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use Exception;

class DeleteUseCase extends SubsequentUseCase
{

	public function execute(): int
	{
		$f = __METHOD__;
		try {
			$print = false;
			$object = $this->getPredecessor()->getDataOperandObject();
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if (! isset($mysqli)) {
				$err = ErrorMessage::getResultMessage(ERROR_MYSQL_CONNECT);
				Debug::error("{$f} {$err}");
			}
			$status = $object->delete($mysqli);
			switch ($status) {
				case SUCCESS:
					// case STATUS_DELETED:
					break;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} deleting object returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
			}
			if ($print) {
				Debug::print("{$f} deletion successful");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
