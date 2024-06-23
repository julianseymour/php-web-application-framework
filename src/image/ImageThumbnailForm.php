<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

use JulianSeymour\PHPWebApplicationFramework\file\FileUploadForm;

class ImageThumbnailForm extends FileUploadForm{

	public function getFormDataIndices(): ?array{
		return [
			"originalFilename" => ImageThumbnailFileInput::class
		];
	}

	public static function getFormDispatchIdStatic(): ?string{
		return "image_thumbnail";
	}

	public static function getActionAttributeStatic(): ?string{
		return null;
	}

	public static function getNewFormOption(): bool{
		return true;
	}
}
