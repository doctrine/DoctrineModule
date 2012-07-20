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
        $objectRepository = $om->getRepository($options->getIdentityClass());
        $metadataFactory  = $om->getMetadataFactory();

        $storage = new ObjectRepositoryStorage($objectRepository, $metadataFactory, new SessionStorage());

        $adapter = new ObjectRepositoryAdapter($objectRepository);
        $adapter->setIdentityProperty($options->getIdentityProperty());
        $adapter->setCredentialProperty($options->getCredentialProperty());

        if ($options->getCredentialCallable()) {
            $adapter->setCredentialCallable($options->getCredentialCallable());
        }

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
