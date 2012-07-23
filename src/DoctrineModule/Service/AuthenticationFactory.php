<?php

namespace DoctrineModule\Service;

use DoctrineModule\Authentication\Storage\ObjectRepository as ObjectRepositoryStorage;
use DoctrineModule\Authentication\Adapter\ObjectRepository as ObjectRepositoryAdapter;
use DoctrineModule\Options\Authentication as AuthenticationOptions;
use DoctrineModule\Service\AbstractFactory;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationFactory extends AbstractFactory
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return AuthenticationService
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        /** @var $options AuthenticationOptions */
        $options = $this->getOptions($sl, 'authentication');

        $om = $options->getObjectManager();

        if (is_string($om)) {
            $om = $sl->get($om);
        }

        $objectRepository = $om->getRepository($options->getIdentityClass());
        $metadataFactory  = $om->getMetadataFactory();

        $storage = new ObjectRepositoryStorage($objectRepository, $metadataFactory, new SessionStorage());

        $adapter = new ObjectRepositoryAdapter($options);

        $authenticationService = new AuthenticationService($storage, $adapter);

        return $authenticationService;
    }

    /**
     * Get the class name of the options associated with this factory.
     *
     * @return string
     */
    public function getOptionsClass()
    {
        return 'DoctrineModule\Options\Authentication';
    }
}
