<?php
namespace SpiffyDoctrine\Factory;
use Doctrine\Common\Annotations\AnnotationRegistry,
    Doctrine\ODM\MongoDB\DocumentManager as MongoDocumentManager,
    SpiffyDoctrine\Doctrine\ODM\MongoDB\Connection;

class DocumentManager
{
    protected static $loaded = false;
    
    public static function get(Connection $conn)
    {
        if (false === self::$loaded) {
            $libfile = __DIR__ . '/../../../vendor/doctrine-odm/lib/Doctrine/ODM/MongoDB/Mapping/Annotations/DoctrineAnnotations.php';
            if (file_exists($libfile)) {
                AnnotationRegistry::registerFile($libfile);
            } else {
                @include_once 'Doctrine/ODM/MongoDB/Mapping/Annotations/DoctrineAnnotations.php';
                if (!class_exists('Doctrine\ODM\MongoDB\Mapping\Document', false)) {
                    throw new \Exception(
                        'Failed to register annotations. Ensure Doctrine can be autoloaded or initalize' .
                        'submodules in SpiffyDoctrine'
                    );
                }
            }
            
            self::$loaded = true;
        }
        
        return MongoDocumentManager::create(
            $conn->getInstance(),
            $conn->getInstance()->getConfiguration(),
            $conn->getInstance()->getEventManager()
        );
    }
}