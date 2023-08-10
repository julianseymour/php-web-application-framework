<?php
namespace JulianSeymour\PHPWebApplicationFramework\app;

use JulianSeymour\PHPWebApplicationFramework\app\workflow\SimpleWorkflow;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

ErrorMessage::deprecated(__FILE__);

class WebApplicationManifestUseCase extends UseCase
{
	
	public static function getDefaultWorkflowClass(): string
	{
		return SimpleWorkflow::class;
	}
	
	public function echoResponse(){
		$manifest = [
			"short_name" => "JS",
			"name" => WEBSITE_NAME,
			"icons" => [
				[
					"src" => "/images/icons-192.png",
					"type" => "image/png",
					"sizes" => "192x192"
				],[
					"src" => "/images/icons-512.png",
					"type" => "image/png",
					"sizes" => "512x512"
				]
			],
			"start_url" => "/",
			"background_color" => "#171717",
			"display" => "standalone",
			"scope" => "/",
			"theme_color" => "#0065d1",
			"description" => WEBSITE_NAME,
			//"categories"  => [ "julian seymour" ],
			"dir" => "auto",
			"serviceworker" => [
				"src" => "/service-worker.js",
				"scope" => "/",
				"type" => "",
				"update_via_cache" => "none"
			],
			"screenshots"  => [
				[
					"src" => "screenshot1.webp",
					"sizes" => "1280x720",
					"type" => "image/webp"
				],[
					"src" => "screenshot2.webp",
					"sizes" => "1280x720",
					"type" => "image/webp"
				]
			]
		];
		Json::echo($manifest);
	}
	
	public function getActionAttribute():string{
		return "/manifest.json";
	}
}

