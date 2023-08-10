<?php
namespace JulianSeymour\PHPWebApplicationFramework\common\arr;

/**
 * Objects whose classes implement this interface will be asked to provide a key for their position in an array
 * when they are processed by ArrayPropertyTrait's set, merge, push or unshift functions
 *
 * @author j
 */
interface ArrayKeyProviderInterface
{

	function getArrayKey(int $count);
}
