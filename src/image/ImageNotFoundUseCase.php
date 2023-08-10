<?php
namespace JulianSeymour\PHPWebApplicationFramework\image;

use function JulianSeymour\PHPWebApplicationFramework\f;
use function JulianSeymour\PHPWebApplicationFramework\request;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\SimpleWorkflow;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class ImageNotFoundUseCase extends UseCase{

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return '/images';
	}

	public function getUseCaseId(){
		return USE_CASE_IMAGE_404;
	}

	protected function getContentTypeString(){
		$f = __METHOD__;
		$request = request();
		$segments = $request->getRequestURISegments();
		$filename = $segments[count($segments) - 1];
		// FileData::sendHeader($filename);
		$splat = explode(".", $filename);
		$extension = strtolower($splat[count($splat) - 1]);
		switch ($extension) {
			case "gif":
				return "image/gif";
			case "jpg":
			case "jpeg":
				return "image/jpeg";
			case "png":
				return "image/png";
			default:
				Debug::error("{$f} invalid extension \"{$extension}\"");
		}
	}

	public function sendHeaders(Request $request): bool{
		$f = __METHOD__;
		$print = false;
		$count = $request->getRequestURISegmentCount();
		if($count < 2){
			iF($print){
				Debug::print("{$f} URI segment parameter count is {$count}. Returning the parent function.");
			}
			return parent::sendHeaders($request);
		}elseif($print){
			Debug::print("{$f} URI segment parameter count is {$count}");
		}
		$type = $this->getContentTypeString();
		header('Content-Type:' . $type);
		return false;
	}

	public function echoResponse(): void{
		$f = __METHOD__;
		$request = request();
		if($request->getRequestURISegmentCount() < 2){
			$this->setObjectStatus(ERROR_FILE_NOT_FOUND);
			parent::echoResponse();
			return;
		}
		$type = $this->getContentTypeString();
		$filename = "/var/www/html/images/";
		switch ($type) {
			case "image/gif":
				$filename .= 'rca.gif';
				break;
			case "image/jpeg":
				$filename .= 'rca.jpg';
				break;
			case "image/png":
				$filename .= 'rca.png';
				break;
			default:
				Debug::error("{$f} invalid type \"{$type}\"");
		}
		// Debug::print("{$f} about to call readfile({$filename})");
		readfile($filename);
		// exit();
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public function blockCrossOriginRequest(){
		return $this->handleRequest(request());
	}

	public static function getDefaultWorkflowClass(): string{
		return SimpleWorkflow::class;
	}
}
