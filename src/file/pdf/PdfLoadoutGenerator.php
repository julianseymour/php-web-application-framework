<?php
namespace JulianSeymour\PHPWebApplicationFramework\file\pdf;

use function JulianSeymour\PHPWebApplicationFramework\getInputParameter;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

class PdfLoadoutGenerator extends LoadoutGenerator{

	public function getRootNodeTreeSelectStatements(?PlayableUser $user=null, ?UseCase $use_case=null): ?array
	{
		$class = $use_case->getDataStructureClass();
		return [
			'context' => [
				$class => $class::selectStatic()->where(
					new WhereCondition($class::getIdentifierNameStatic(), OPERATOR_EQUALS)
				)->withParameters([
					getInputParameter("uniqueKey", $use_case)
				])
			]
		];
	}
}

