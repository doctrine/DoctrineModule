<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModule\Authentication\Adapter;

use Doctrine\ORM\EntityManager,
    Doctrine\ORM\NoResultException,
    Doctrine\ORM\NonUniqueResultException,
    Doctrine\ORM\Query,
    Zend\Authentication\Adapter as AuthenticationAdapter,
    Zend\Authentication\Adapter\Exception,
    Zend\Authentication\Result as AuthenticationResult;

/**
 * Authentication adapter that uses a Doctrine Entity for verification.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   1.0
 * @version $Revision$
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class DoctrineEntity implements AuthenticationAdapter
{
    /**
     * Doctrine EntityManager instance
     * 
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    
    /**
     * Entity class to use.
     * 
     * @var string
     */
    protected $entity;
    
    /**
     * Identity column to check credential against.
     * 
     * @var string
     */
    protected $identityColumn;
    
    /**
     * Credential column to check credential against.
     * 
     * @var string
     */
    protected $credentialColumn;
    
    /**
     * Use supplied identity.
     * 
     * @var string
     */
    protected $identity;
    
    /**
     * User supplied credential.
     * 
     * @var string
     */
    protected $credential;
    
    /**
     * User supplied credential.
     * 
     * @var mixed
     */
    protected $credentialCallable;
    
    /**
     * Contains the authentication results.
     * 
     * @var array
     */
    protected $authenticationResultInfo = null;
    
    /**
     * __construct() - Sets configuration options
     *
     * @param  Doctrine\ORM\EntityManager $em
     * @param  string                     $tableName
     * @param  string                     $identityColumn
     * @param  string                     $credentialColumn
     * @param  null|array|Closure		  $credentialCallable
     * @return void
     */
    public function __construct(EntityManager $em, $entity, $identityColumn = 'username',
                                $credentialColumn = 'password', $credentialCallable = null)
    {
        $this->setEntityManager($em);
        $this->setEntity($entity);
        $this->setIdentityColumn($identityColumn);
        $this->setCredentialColumn($credentialColumn);
        $this->setCredentialCallable($credentialCallable);
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
        $this->authenticateSetup();
        $query = $this->authenticateCreateQuery();
        
        if (!($identity = $this->authenticateValidateQuery($query))) {
            return $this->authenticateCreateAuthResult();
        }
        
        $authResult = $this->authenticateValidateIdentity($identity);
        return $authResult;
    }
    
   
    /**
     * Sets the entity manager to use.
     * 
     * @param Doctrine\ORM\EntityManager $em
     * @return DoctrineModule\Authentication\Adapater\DoctrineEntity
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }
    
    /**
     * Sets the entity to use for authentication.
     * 
     * @param string $entity
     * @return DoctrineModule\Authentication\Adapater\DoctrineEntity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }
    
    /**
     * Set the value to be used as the identity
     *
     * @param  string $value
     * @return DoctrineModule\Authentication\Adapater\DoctrineEntity
     */
    public function setIdentity($value)
    {
        $this->identity = $value;
        return $this;
    }

    /**
     * Set the credential value to be used.
     *
     * @param  string $credential
     * @return DoctrineModule\Authentication\Adapater\DoctrineEntity
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;
        return $this;
    }
    
    /**
     * Set the credential callable to be used to transform the password
     * before checking.
     *
     * @param  string $callable
     * @return DoctrineModule\Authentication\Adapater\DoctrineEntity
     */
    public function setCredentialCallable($callable)
    {
        $this->credentialCallable = $callable;
        return $this;
    }
    
    /**
     * Set the column name to be used as the identity column
     *
     * @param  string $identityColumn
     * @return DoctrineModule\Authentication\Adapater\DoctrineEntity
     */
    public function setIdentityColumn($identityColumn)
    {
        $this->identityColumn = $identityColumn;
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
        $this->credentialColumn = $credentialColumn;
        return $this;
    }
    
    /**
     * Prepares the query by building it from QueryBuilder based on the 
     * entity, credentialColumn and identityColumn.
     * 
     * @return Doctrine\ORM\Query
     */
    protected function authenticateCreateQuery()
    {
        $mdata = $this->em->getClassMetadata($this->entity);
        $qb = $this->em->createQueryBuilder();
        
        $qb->select('q')
           ->from($this->entity, 'q')
           ->where($qb->expr()->eq(
                'q.' . $this->identityColumn,
                $qb->expr()->literal($this->identity)
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
    protected function authenticateValidateIdentity($identity)
    {
        $getter           = 'get' . ucfirst($this->credentialColumn);
        $vars             = get_object_vars($identity);
        $entityCredential = null;
        
        if (method_exists($identity, $getter)) {
            $entityCredential = $identity->$getter();
        } else if (isset($identity->{$this->credentialColumn}) || isset($vars[$this->credentialColumn])) {
            $entityCredential = $identity->{$this->credentialColumn};
        } else {
            throw new \BadMethodCallException(sprintf(
                'Property (%s) in (%s) is not accessible. You should implement %s::%s()',
                $this->credentialColumn,
                get_class($identity),
                get_class($identity),
                $getter
            ));
        }
        
        $credential = $this->credential;
        $callable   = $this->credentialCallable;
        if ($callable) {
        	if (!is_callable($callable)) {
	    		throw new RuntimeException(sprintf(
	    			'failed to call algorithm function %s::%s(), does it exist?',
	    			$algorithm[0],
	    			$algorithm[1]
	    		));
        	}
        	$credential = call_user_func($callable, $identity, $credential);
        }
        
        if ($credential != $entityCredential) {
            $this->authenticateResultInfo['code'] = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticateResultInfo['messages'][] = 'Supplied credential is invalid.';
            return $this->authenticateCreateAuthResult();
        }

        $this->authenticateResultInfo['code'] = AuthenticationResult::SUCCESS;
        $this->authenticateResultInfo['identity'] = $identity;
        $this->authenticateResultInfo['messages'][] = 'Authentication successful.';
        return $this->authenticateCreateAuthResult();
    }
    
    /**
     * Validates the query. Catches exceptions from Doctrine and populates authenticate results
     * appropriately.
     * 
     * @return false|object
     */
    protected function authenticateValidateQuery(Query $query)
    {
        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            $this->authenticateResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
        } catch (NonUniqueResultException $e) {
            $this->authenticateResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS;
            $this->authenticateResultInfo['messages'][] = 'More than one record matches the supplied identity.';
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
    protected function authenticateSetup()
    {
        $exception = null;

        if ($this->entity == '') {
            $exception = 'An entity  must be supplied for the DoctrineEntity authentication adapter.';
        } elseif ($this->identityColumn == '') {
            $exception = 'An identity column must be supplied for the DoctrineEntity authentication adapter.';
        } elseif ($this->credentialColumn == '') {
            $exception = 'A credential column must be supplied for the DoctrineEntity authentication adapter.';
        } elseif ($this->identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with DoctrineEntity.';
        } elseif ($this->credential === null) {
            $exception = 'A credential value was not provided prior to authentication with DoctrineEntity.';
        }

        if (null !== $exception) {
            throw new Exception\RuntimeException($exception);
        }

        $this->authenticateResultInfo = array(
            'code'     => AuthenticationResult::FAILURE,
            'identity' => $this->identity,
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
    protected function authenticateCreateAuthResult()
    {
        return new AuthenticationResult(
            $this->authenticateResultInfo['code'],
            $this->authenticateResultInfo['identity'],
            $this->authenticateResultInfo['messages']
        );
    }
}
