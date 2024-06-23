<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

use function JulianSeymour\PHPWebApplicationFramework\base64url_decode;
use function JulianSeymour\PHPWebApplicationFramework\request;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\SimpleWorkflow;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use Throwable;

class ShowQrCodeUseCase extends UseCase{

	public function isPageUpdatedAfterLogin(): bool{
		return false;
	}

	public function getActionAttribute(): ?string{
		return '/qr';
	}


	public function sendHeaders(Request $request): bool{
		return false;
	}

	public function echoResponse(): void{
		$f = __METHOD__;
		$print = false;
		$request = request();
		$count = $request->getRequestURISegmentCount();
		if($count < 2){
			$this->setObjectStatus(ERROR_FILE_NOT_FOUND);
			parent::echoResponse();
			return;
		}
		$segments = $request->getRequestURISegments();
		if($print){
			Debug::print("{$f} segments[1] is \"{$segments[1]}\"");
		}
		$data = base64url_decode($segments[1]);
		if($print){
			Debug::print("{$f} base 64 decoded data is \"{$data}\"");
		}
		if(! interface_exists(QROutputInterface::class)){
			Debug::error("{$f} QROutputInterface does not exist");
		}else{
			Debug::print("{$f} QROutputInterface exists, congratulations");
		}
		$options = new QROptions([
			'version' => 7,
			'outputType' => 'png', // QROutputInterface::GDIMAGE_PNG,
			'eccLevel' => 0b00, // EccLevel::L,
			'scale' => 20,
			'imageBase64' => false,
			'bgColor' => [
				200,
				150,
				200
			],
			'imageTransparent' => true,
			// 'transparencyColor' => [233, 233, 233],
			'drawCircularModules' => true,
			'drawLightModules' => true,
			'circleRadius' => 0.4
			/*
		 * 'keepAsSquare' => [
		 * QRMatrix::M_FINDER_DARK,
		 * QRMatrix::M_FINDER_DOT,
		 * QRMatrix::M_ALIGNMENT_DARK,
		 * ],
		 * /*'moduleValues' => [
		 * // finder
		 * QRMatrix::M_FINDER_DARK => [0, 63, 255], // dark (true)
		 * QRMatrix::M_FINDER_DOT => [0, 63, 255], // finder dot, dark (true)
		 * QRMatrix::M_FINDER => [233, 233, 233], // light (false), white is the transparency color and is enabled by default
		 * // alignment
		 * QRMatrix::M_ALIGNMENT_DARK => [255, 0, 255],
		 * QRMatrix::M_ALIGNMENT => [233, 233, 233],
		 * // timing
		 * QRMatrix::M_TIMING_DARK => [255, 0, 0],
		 * QRMatrix::M_TIMING => [233, 233, 233],
		 * // format
		 * QRMatrix::M_FORMAT_DARK => [67, 159, 84],
		 * QRMatrix::M_FORMAT => [233, 233, 233],
		 * // version
		 * QRMatrix::M_VERSION_DARK => [62, 174, 190],
		 * QRMatrix::M_VERSION => [233, 233, 233],
		 * // data
		 * QRMatrix::M_DATA_DARK => [0, 0, 0],
		 * QRMatrix::M_DATA => [233, 233, 233],
		 * // darkmodule
		 * QRMatrix::M_DARKMODULE => [0, 0, 0],
		 * // separator
		 * QRMatrix::M_SEPARATOR => [233, 233, 233],
		 * // quietzone
		 * QRMatrix::M_QUIETZONE => [233, 233, 233],
		 * // logo (requires a call to QRMatrix::setLogoSpace()), see QRImageWithLogo
		 * QRMatrix::M_LOGO => [233, 233, 233],
		 * ],
		 */
			// the version of the qr code library on packagist is out of date by several years
		]);
		try{
			$im = (new QRCode($options))->render($data);
		}catch(Throwable $e){
			exit($e->getMessage());
		}
		header('Content-type: image/png');
		echo $im;
		exit();
	}

	protected function getExecutePermissionClass()
	{
		return SUCCESS;
	}

	public static function getDefaultWorkflowClass(): string
	{
		return SimpleWorkflow::class;
	}
}

