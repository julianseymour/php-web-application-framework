<?php
namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;

use function JulianSeymour\PHPWebApplicationFramework\db;

use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\confirm_code\ConfirmationCodeGeneratingFormInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use Exception;

class EmailConfirmationCodeUseCase extends SubsequentUseCase
{

	public function execute(): int
	{
		$f = __METHOD__;
		try{
			$print = false;
			/*
			 * request() = request();
			 * $files = null;
			 * if(request()->hasRepackedIncomingFiles()){
			 * $files = request()->getRepackedIncomingFiles();
			 * }
			 */
			$form = $this->getPredecessor()->getProcessedFormObject();
			if(!$form instanceof ConfirmationCodeGeneratingFormInterface) {
				Debug::error("{$f} form is not an instanceof ConfirmationCodeGeneratingFormInterface");
			}
			/*
			 * $post = getInputParameters();
			 * $status = user()->processForm($form, $post, $files);
			 * if($status !== SUCCESS){
			 * if($print){
			 * $err = ErrorMessage::getResultMessage($status);
			 * Debug::print("{$f} processForm returned error status \"{$err}\"");
			 * }
			 * return $this->setObjectStatus($status);
			 * }
			 */
			$ccc = $form->getConfirmationCodeClass();
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = $ccc::submitStatic($mysqli, user());
			if($print) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} submitStatic returned error status \"{$err}\"");
			}
			return $this->setObjectStatus($status);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}