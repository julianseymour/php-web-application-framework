<?php

namespace JulianSeymour\PHPWebApplicationFramework\image;

use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

class PublicImageData extends ImageData{
	
	public static function getSubtypeStatic():string{
		return IMAGE_TYPE_PUBLIC;
	}
	
	public function getWebFileDirectory():string{
		return "/images/public";
	}
	
	public static function reconfigureColumns(array &$columns, ?DataStructure $ds=null):void{
		$f = __METHOD__;
		parent::reconfigureColumns($columns, $ds);
		$fields = [
			"userKey",
			"userAccountType",
			"name",
			"focalLineRatio"
		];
		foreach($fields as $field){
			$columns[$field]->volatilize();
		}
	}
	
	public static function getPermissionStatic(string $name, $data){
		switch($name){
			case DIRECTIVE_INSERT:
			case DIRECTIVE_UPDATE:
			case DIRECTIVE_DELETE:
				return new AdminOnlyAccountTypePermission($name);
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}
}
