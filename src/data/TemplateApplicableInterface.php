<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;

use mysqli;

interface TemplateApplicableInterface
{

	function applyTemplate(mysqli $mysqli, DataStructure $subject): ?DataStructure;
}