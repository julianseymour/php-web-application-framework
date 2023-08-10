<?php
namespace JulianSeymour\PHPWebApplicationFramework\json;

interface EchoJsonInterface
{

	function echoInnerJson(bool $destroy = false);

	function echoJson(bool $destroy = false);

	function skipJson(): bool;

	function getAllocatedFlag(): bool;
}
