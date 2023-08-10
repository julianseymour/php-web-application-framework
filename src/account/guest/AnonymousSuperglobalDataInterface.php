<?php
namespace JulianSeymour\PHPWebApplicationFramework\account\guest;

interface AnonymousSuperglobalDataInterface
{

	public function hasAnonSessionToken();

	public function getAnonSessionToken();

	public function setAnonSessionToken($value);

	public function ejectAnonSessionToken();
}
