<?php
namespace SpiffyDoctrine\Factory;
use Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\ORM\EntityManager as DoctrineEntityManager,
	SpiffyDoctrine\Doctrine\ORM\Connection;

class EntityManager
{
    protected static $loaded = false;
    
	public static function get(Connection $conn)
	{
	    if (false === self::$loaded) {
	        $libfile = __DIR__ . '/../../../vendor/doctrine-orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';
            if (file_exists($libfile)) {
                AnnotationRegistry::registerFile($libfile);
            } else {
                @include_once 'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';
                if (!class_exists('Doctrine\ORM\Mapping\Entity', false)) {
                    throw new \Exception(
                        'Failed to register annotations. Ensure Doctrine can be autoloaded or initalize' .
                        'submodules in SpiffyDoctrine'
                    );
                }
            }
            
            self::$loaded = true;
	    }
        
		return DoctrineEntityManager::create(
			$conn->getInstance(),
			$conn->getInstance()->getConfiguration(),
			$conn->getInstance()->getEventManager()
		);
	}
}