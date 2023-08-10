<?php
namespace JulianSeymour\PHPWebApplicationFramework\app;

use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use JulianSeymour\PHPWebApplicationFramework\app\workflow\SimpleWorkflow;

class RobotsDotTxtUseCase extends UseCase
{
	public function getActionAttribute(): ?string{
		return "/robots.txt";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}
	
	public static function getDefaultWorkflowClass():string{
		return SimpleWorkflow::class;
	}
	
	public function echoResponse():void{
		echo "User-agent: *\n";
		echo "Allow: /\n";
		echo "Sitemap: ".WEBSITE_URL."/sitemap.xml\n";
	}
}

