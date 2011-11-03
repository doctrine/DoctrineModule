<?php
namespace SpiffyDoctrine\Authentication\Adapter;
use Doctrine\ORM\EntityManager,
    Doctrine\ORM\NoResultException,
    Doctrine\ORM\NonUniqueResultException,
    Doctrine\ORM\Query,
    Zend\Authentication\Adapter as AuthenticationAdapter,
    Zend\Authentication\Adapter\Exception,
    Zend\Authentication\Result as AuthenticationResult;

class DoctrineEntity implements AuthenticationAdapter
{
    /**
     * Doctrine EntityManager instance
     * 
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;
    
    /**
     * Entity class to use.
     * 
     * @var string
     */
    protected $_entity;
    
    /**
     * Identity column to check credential against.
     * 
     * @var string
     */
    protected $_identityColumn;
    
    /**
     * Credential column to check credential against.
     * 
     * @var string
     */
    protected $_credentialColumn;
    
    /**
     * Use supplied identity.
     * 
     * @var string
     */
    protected $_identity;
    
    /**
     * User supplied credential.
     * 
     * @var string
     */
    protected $_credential;
    
    /**
     * Contains the authentication results.
     * 
     * @var array
     */
    protected $_authenticationResultInfo = null;
    
    /**
     * __construct() - Sets configuration options
     *
     * @param  Doctrine\ORM\EntityManager $em
     * @param  string                     $tableName
     * @param  string                     $identityColumn
     * @param  string                     $credentialColumn
     * @return void
     */
    public function __construct(EntityManager $em, $entity, $identityColumn = 'username',
                                $credentialColumn = 'password')
    {
        $this->setEntityManager($em);
        $this->setEntity($entity);
        $this->setIdentityColumn($identityColumn);
        $this->setCredentialColumn($credentialColumn);
    }
    
    /**
     * Defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend\Authentication\Adapter\Exception if answering the authentication query is impossible
     * @return Zend\Authentication\Result
     */
    public function authenticate()
    {
        $this->_authenticateSetup();
        $query = $this->_authenticateCreateQuery();
        
        if (!($identity = $this->_authenticateValidateQuery($query))) {
            return $this->_authenticateCreateAuthResult();
        }
        
        $authResult = $this->_authenticateValidateIdentity($identity);
        return $authResult;
    }
    
   
    /**
     * Sets the entity manager to use.
     * 
     * @param Doctrine\ORM\EntityManager $em
     * @return SpiffyDoctrine\Authentication\Adapater\DoctrineEntity
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->_em = $em;
        return $this;
    }
    
    /**
     * Sets the entity to use for authentication.
     * 
     * @param string $entity
     * @return SpiffyDoctrine\Authentication\Adapater\DoctrineEntity
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
        return $this;
    }
    
    /**
     * Set the value to be used as the identity
     *
     * @param  string $value
     * @return SpiffyDoctrine\Authentication\Adapater\DoctrineEntity
     */
    public function setIdentity($value)
    {
        $this->_identity = $value;
        return $this;
    }

    /**
     * Set the credential value to be used.
     *
     * @param  string $credential
     * @return SpiffyDoctrine\Authentication\Adapater\DoctrineEntity
     */
    public function setCredential($credential)
    {
        $this->_credential = $credential;
        return $this;
    }
    
    /**
     * Set the column name to be used as the identity column
     *
     * @param  string $identityColumn
     * @return SpiffyDoctrine\Authentication\Adapater\DoctrineEntity
     */
    public function setIdentityColumn($identityColumn)
    {
        $this->_identityColumn = $identityColumn;
        return $this;
    }

    /**
     * Set the column name to be used as the credential column
     *
     * @param  string $credentialColumn
     * @return Zend\Authentication\Adapter\DbTable Provides a fluent interface
     */
    public function setCredentialColumn($credentialColumn)
    {
        $this->_credentialColumn = $credentialColumn;
        return $this;
    }
    
    /**
     * Prepares the query by building it from QueryBuilder based on the 
     * entity, credentialColumn and identityColumn.
     * 
     * @return Doctrine\ORM\Query
     */
    protected function _authenticateCreateQuery()
    {
        $mdata = $this->_em->getClassMetadata($this->_entity);
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('q')
           ->from($this->_entity, 'q')
           ->where($qb->expr()->eq(
                'q.' . $this->_identityColumn,
                $qb->expr()->literal($this->_identity)
            ));
           
        return $qb->getQuery();
    }
    
    /**
     * This method attempts to validate that the record in the resultset is indeed a 
     * record that matched the identity provided to this adapter.
     *
     * @param  object $identity
     * @return Zend\Authentication\Result
     */
    protected function _authenticateValidateIdentity($identity)
    {
        $getter = 'get' . ucfirst($this->_credentialColumn);
        $vars = get_object_vars($identity);
        
        if (method_exists($identity, $getter)) {
            $credential = $identity->$getter();
        } else if (isset($identity->{$this->_credentialColumn}) || isset($vars[$this->_credentialColumn])) {
            $credential = $identity->{$this->_credentialColumn};
        } else {
            throw new \BadMethodCallException(sprintf(
                'Property (%s) in (%s) is not accessible. You should implement %s::%s()',
                $this->_credentialColumn,
                get_class($identity),
                get_class($identity),
                $getter
            ));
        }
        
        if ($credential != $this->_credential) {
            $this->_authenticateResultInfo['code'] = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->_authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->_authenticateCreateAuthResult();
        }

        $this->_authenticateResultInfo['code'] = AuthenticationResult::SUCCESS;
        $this->_authenticateResultInfo['identity'] = $identity;
        $this->_authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->_authenticateCreateAuthResult();
    }
    
    /**
     * Validates the query. Catches exceptions from Doctrine and populates authenticate results
     * appropriately.
     * 
     * @return false|object
     */
    protected function _authenticateValidateQuery(Query $query)
    {
        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            $this->_authenticateResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
            $this->_authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
        } catch (NonUniqueResultException $e) {
            $this->_authenticateResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS;
            $this->_authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
        }

        return false;
    }
    
    /**
     * This method abstracts the steps involved with making sure that this adapter was 
     * indeed setup properly with all required pieces of information.
     *
     * @throws Zend\Authentication\Adapter\Exception - in the event that setup was not done properly
     * @return true
     */
    protected function _authenticateSetup()
    {
        $exception = null;

        if ($this->_entity == '') {
            $exception = 'An entity  must be supplied for the DoctrineEntity authentication adapter.';
        } elseif ($this->_identityColumn == '') {
            $exception = 'An identity column must be supplied for the DoctrineEntity authentication adapter.';
        } elseif ($this->_credentialColumn == '') {
            $exception = 'A credential column must be supplied for the DoctrineEntity authentication adapter.';
        } elseif ($this->_identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with DoctrineEntity.';
        } elseif ($this->_credential === null) {
            $exception = 'A credential value was not provided prior to authentication with DoctrineEntity.';
        }

        if (null !== $exception) {
            throw new Exception\RuntimeException($exception);
        }

        $this->_authenticateResultInfo = array(
            'code'     => AuthenticationResult::FAILURE,
            'identity' => $this->_identity,
            'messages' => array()
            );

        return true;
    }

    /**
     * Creates a Zend_Auth_Result object from the information that has been collected 
     * during the authenticate() attempt.
     *
     * @return \Zend\Authentication\Result
     */
    protected function _authenticateCreateAuthResult()
    {
        return new AuthenticationResult(
            $this->_authenticateResultInfo['code'],
            $this->_authenticateResultInfo['identity'],
            $this->_authenticateResultInfo['messages']
        );
    }
}
