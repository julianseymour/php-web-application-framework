<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\firewall;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\guest\AnonymousUser;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ValidConfirmationCodeUseCase;
use JulianSeymour\PHPWebApplicationFramework\command\element\UpdateElementCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;

class ClassifyIpAddressUseCase extends ValidConfirmationCodeUseCase
{

	public function execute(): int
	{
		$f = __METHOD__; //ClassifyIpAddressUseCase::getShortClass()."(".static::getShortClass().")->execute()";
		try {
			$confirmation_code = $this->getPredecessor()->getConfirmationCodeObject();
			if (! $confirmation_code->hasIpAddressObject()) {
				Debug::error("{$f} confirmation code lacks a listed IP address object");
			}
			$server_cmd = directive();
			if ($server_cmd !== DIRECTIVE_VALIDATE) {
				Debug::error("{$f} invalid server command");
			}
			$listed_ip = $confirmation_code->getIpAddressObject();
			$validate = getInputParameter(DIRECTIVE_VALIDATE);
			switch ($validate) {
				case 'authorize':
					Debug::print("{$f} about to authorize IP address");
					$list = POLICY_ALLOW;
					break;
				case 'ban':
					Debug::print("{$f} about to ban IP address");
					$list = POLICY_BLOCK;
					if (! user() instanceof AnonymousUser) {
						$datum = $listed_ip->getColumn("list");
						$backup_status = $datum->getObjectStatus();
						$datum->setObjectStatus(SUCCESS);
						$status = $datum->validate($list);
						// $datum->getObjectStatus();
						$datum->setObjectStatus($backup_status);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} datum->dispatchEvent() returned error status \"{$err}\"");
							return $this->setObjectStatus($status);
						}
						Debug::print("{$f} list \"{$list}\" approved");
					}
					break;
				default:
					Debug::printPost("{$f} nothing meaningful was posted");
					return $this->setObjectStatus(ERROR_DISPATCH_NOTHING);
			}
			$listed_ip->setList($list);
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			if (! isset($mysqli)) {
				$err = ErrorMessage::getResultMessage($this->setObjectStatus(ERROR_MYSQL_CONNECT));
				Debug::warning("{$f} {$err}");
				return $this->getObjectStatus();
			}
			$status = $listed_ip->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} updating list returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			switch ($validate) {
				case 'authorize':
					Debug::print("{$f} authorized IP address");
					$status = $this->setObjectStatus(RESULT_IP_AUTHORIZED);
					break;
				case 'ban':
					Debug::print("{$f} banned IP address");
					$status = $this->setObjectStatus(RESULT_IP_BANNED);
					break;
				default:
					Debug::printPost("{$f} nothing meaningful was posted");
					return $this->setObjectStatus(ERROR_DISPATCH_NOTHING);
			}
			if (Request::isXHREvent()) {
				$updated_element = ErrorMessage::getVisualError($status);
				$updated_element->setIdAttribute("confirm_ip_list_form");
				$command = new UpdateElementCommand($updated_element);
				$this->pushCommand($command);
			}
			Debug::print("{$f} returning normally");
			return $this->setObjectStatus(SUCCESS);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isPageUpdatedAfterLogin(): bool
	{
		return false;
	}

	public function getUseCaseId()
	{
		return USE_CASE_AUTHORIZE_UNLISTED_IP;
	}

	public function getActionAttribute(): ?string
	{
		return "/authorize_ip";
	}
}
