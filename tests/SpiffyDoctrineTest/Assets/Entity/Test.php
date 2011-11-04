<?php
namespace SpiffyDoctrineTest\Assets\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="test")
 */
class Test
{
    /**
     * @ORM\Id 
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
    
    /**
     * @ORM\Column(type="string")
     */
    public $username;
    
    /**
     * @ORM\Column(type="string")
     */
    public $password;
}
