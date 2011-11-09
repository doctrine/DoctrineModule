<?php
namespace SpiffyDoctrine\Factory;
use SpiffyDoctrine\Service\Doctrine;

class EntityManager
{
	public static function get(Doctrine $doctrineService)
	{
		return $doctrine->getEntityManager();
	}
}