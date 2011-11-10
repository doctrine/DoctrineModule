<?php
namespace SpiffyDoctrine\Instance;
use Doctrine\Common\EventManager as DoctrineEventManager;

class EventManager extends Instance
{
	/**
	 * @var array
	 */
	protected $definition = array(
        'optional' => array(
            'subscribers' => 'array'
        )
    );
	
	/**
	 * (non-PHPdoc)
	 * @see SpiffyDoctrine\Instance.Instance::loadInstance()
	 */
	protected function loadInstance()
	{
		$opts = $this->getOptions();
		$evm = new DoctrineEventManager;
        
        foreach($opts['subscribers'] as $subscriber) {
            if (is_string($subscriber)) {
                if (!class_exists($subscriber)) {
                    throw new \InvalidArgumentException(sprintf(
                       'failed to register subscriber "%s" because the class does not exist.',
                       $subscriber 
                    ));
                }
                $subscriber = new $subscriber;
            }
            
            $evm->addEventSubscriber($subscriber);
        }
        
        $this->instance = $evm;
	}
}