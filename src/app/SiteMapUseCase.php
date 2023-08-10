<?php
namespace JulianSeymour\PHPWebApplicationFramework\app;

use JulianSeymour\PHPWebApplicationFramework\app\workflow\SimpleWorkflow;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class SiteMapUseCase extends UseCase{
	
	public function getActionAttribute(): ?string{
		return "/sitemap.xml";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
	
	public static function getDefaultWorkflowClass():string{
		return SimpleWorkflow::class;
	}
	
	public function echoResponse():void{
		$sitemap = new SiteMapElement(ALLOCATION_MODE_LAZY);
		echo $sitemap;
	}
}

