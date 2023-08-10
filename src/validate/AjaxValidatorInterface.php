<?php
namespace JulianSeymour\PHPWebApplicationFramework\validate;

/**
 * Interface for client-side validators that send a request for sever side validation
 *
 * @author j
 */
interface AjaxValidatorInterface
{

	public function getSuccessCommand();

	public function getFailureCommand();
}
