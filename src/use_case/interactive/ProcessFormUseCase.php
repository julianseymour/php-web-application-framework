<?php

namespace JulianSeymour\PHPWebApplicationFramework\use_case\interactive;


use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\SubsequentUseCase;
use Exception;

class ProcessFormUseCase extends SubsequentUseCase
{

	public function execute(): int
	{
		$f = __METHOD__;
		try {
			$print = false;
			$processed_data = $this->getPredecessor()->getDataOperandObject();
			$processed_data->setReceptivity(DATA_MODE_RECEPTIVE);
			// 4. process form
			$form = $this->getPredecessor()->getProcessedFormObject();
			$indices = array_keys($form->getFormDataIndices($processed_data));
			if (empty($indices)) {
				Debug::error("{$f} processed form indices array is empty");
				return FAILURE;
			}
			$operand_class = $processed_data->getClass();
			if ($print) {
				$form_class = $form->getClass();
				Debug::print("{$f} about to call {$operand_class}->processForm({$form_class}, POST)");
			}
			$files = request()->hasRepackedIncomingFiles() ? request()->getRepackedIncomingFiles() : null;
			$post = getInputParameters();
			$status = $processed_data->processForm($form, $post, $files);
			$form = null;
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processing array for insert returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}