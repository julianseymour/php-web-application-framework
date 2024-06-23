<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

interface ReplicableInterface{
	
	function replicate(...$params):?ReplicableInterface;
	
	function setReplicaFlag(bool $value = true):bool;
	
	function getReplicaFlag(): bool;
	
	function beforeReplicateHook():int;
	
	function afterReplicateHook(ReplicableInterface $replica): int;
	
	function getReplica():?ReplicableInterface;
}