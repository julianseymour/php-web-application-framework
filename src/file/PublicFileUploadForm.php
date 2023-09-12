<?php
namespace JulianSeymour\PHPWebApplicationFramework\file;

use function JulianSeymour\PHPWebApplicationFramework\user;
use JulianSeymour\PHPWebApplicationFramework\admin\Administrator;
use JulianSeymour\PHPWebApplicationFramework\input\TextareaInput;

class PublicFileUploadForm extends FileUploadForm
{

	public static function getFormDispatchIdStatic(): ?string
	{
		return "public_file";
	}

	public static function getActionAttributeStatic(): ?string
	{
		return "/files";
	}

	public function getDirectives(): ?array
	{
		return user() instanceof Administrator ? parent::getDirectives() : [];
	}

	public function getFormDataIndices(): ?array
	{
		$context = $this->getContext();
		if($context->isInitialized()) {
			$arr = parent::getFormDataIndices();
		}else{
			$arr = [];
		}
		$arr['description'] = TextareaInput::class;
		return $arr;
	}
}
