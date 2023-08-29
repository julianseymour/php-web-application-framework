<?php

namespace JulianSeymour\PHPWebApplicationFramework\file\pdf;

use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\file\OpenFileUseCase;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\file\FileData;
use mysqli;

abstract class PDFUseCase extends OpenFileUseCase{

	public abstract function getPDFElementClass();

	public abstract static function getDataStructureClass();

	public function getLoadoutGeneratorClass(?DataStructure $object = null): ?string{
		if(request()->hasInputParameter("uniqueKey", $this)){
			return PdfLoadoutGenerator::class;
		}
		return null;
	}
	
	public function sendHeaders(Request $request): bool{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			$this->setRequiredMimeType(MIME_TYPE_PDF);
			if(!$request->hasInputParameter("uniqueKey", $this)){
				if($print){
					Debug::warning("{$f} uniqueKey is undefined");
				}
				$this->setObjectStatus(ERROR_KEY_UNDEFINED);
			}elseif($print){
				Debug::print("{$f} returning parent function");
			}
			return parent::sendHeaders($request);
		} catch (Exception $x) {
			x($f, $x);
		}
	}
	
	public function acquireFileObject(mysqli $mysqli){
		$f = __METHOD__;
		$user = user();
		if(!$user->hasForeignDataStructure("context")){
			return null;
		}
		$pdf = new PDFData();
		$context = $user->getFirstRelationship("context");
		$ec = $this->getPDFElementClass($context);
		if (! class_exists($ec)) {
			Debug::error("{$f} class \"{$ec}\" does not exist");
		}
		$element = new $ec(ALLOCATION_MODE_DOMPDF_COMPATIBLE, $context);
		$pdf->setElement($element);
		return $pdf;
	}

	public function getUriSegmentParameterMap(): ?array{
		return [
			"action",
			'uniqueKey'
		];
	}

	public function isPageUpdatedAfterLogin(): bool{
		return true;
	}

	public function reconfigureDataStructure(DataStructure $ds): int{
		$ds->setAutoloadFlags(true);
		return SUCCESS;
	}
}
