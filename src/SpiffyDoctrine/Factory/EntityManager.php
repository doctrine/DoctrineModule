<?php
namespace SpiffyDoctrine\Factory;
use Doctrine\ORM\EntityManager as DoctrineEntityManager,
	SpiffyDoctrine\Instance\Connection;

class EntityManager
{
	public static function get(Connection $conn)
	{
		return DoctrineEntityManager::create(
			$conn->getInstance(),
			$conn->getInstance()->getConfiguration(),
			$conn->getInstance()->getEventManager()
		);
	}
}