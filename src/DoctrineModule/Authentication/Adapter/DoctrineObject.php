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

use Doctrine\Common\Persistence\ObjectManager,
    Zend\Authentication\Adapter\AdapterInterface,
    Zend\Authentication\Adapter\Exception,
    Zend\Authentication\Result as AuthenticationResult;

/**
 * Abstract authentication adapter that uses a Doctrine object for verification.
 *
 * @license  http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link     www.doctrine-project.org
 * @since    1.0
 * @version  $Revision$
 * @author   Tim Roediger <superdweebie@gmail.com>
 */
class DoctrineObject implements AdapterInterface
{
    /**
     * Doctrine ObjectManager instance
     * 
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;
    
    /**
     * Doctrine object class that holds the identity.
     * 
     * @var string
     */
    protected $identityClassName;
    
    /**
     * Identity property to check credential against.
     * 
     * @var string
     */
    protected $identityProperty;
    
    /**
     * Credential property to check credential against.
     * 
     * @var string
     */
    protected $credentialProperty;
    
    /**
     * User supplied identity.
     * 
     * @var string
     */
    protected $identityValue;
    
    /**
     * User supplied credential.
     * 
     * @var string
     */
    protected $credentialValue;
    
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
     * @param  \Doctrine\Common\Persistence\ObjectManager $om
     * @param  string                       $identityClassName
     * @param  string                       $identityProperty
     * @param  string                       $credentialProperty
     * @param  null|array|Closure	        $credentialCallable
     * @return void
     */
    public function __construct(
        ObjectManager $objectManager, 
        $identityClassName, 
        $identityProperty = 'username',
        $credentialProperty = 'password', 
        $credentialCallable = null
    ) {
        $this->setObjectManager($objectManager);
        $this->setIdentityClassName($identityClassName);
        $this->setIdentityProperty($identityProperty);
        $this->setCredentialProperty($credentialProperty);       
        if (null !== $credentialCallable) {
            $this->setCredentialCallable($credentialCallable);
        }        
    }
    
    /**
     * {@inheritDoc}
     * 
     * @return  \Zend\Authentication\Result
     */
    public function authenticate()
    {              
        $this->authenticateSetup();        
        $repository = $this->om->getRepository($this->identityClassName);        
        $identity = $repository->findOneBy(array($this->identityProperty => $this->identityValue));
        if(!$identity)
        {
            $this->authenticateResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticateResultInfo['messages'][] = 'A record with the supplied identity could not be found.';
            return $this->authenticateCreateAuthResult();           
        }        
        $authResult = $this->authenticateValidateIdentity($identity);
        return $authResult;
    }
   
    /**
     * Sets the object manager to use.
     * 
     * @param  \Doctrine\Common\Persistence\ObjectManager $om
     * @return \DoctrineModule\Authentication\Adapater\DoctrineObject
     */
    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
        return $this;
    }
    
    /**
     * Sets the identity class to use for authentication.
     * 
     * @param  string $identityClassName
     * @return \DoctrineModule\Authentication\Adapater\DoctrineObject
     */
    public function setIdentityClassName($identityClassName)
    {
        $this->identityClassName = (string) $identityClassName;
        return $this;
    }
    
    /**
     * Set the value to be used as the identity
     *
     * @param  mixed $value
     * @return \DoctrineModule\Authentication\Adapater\DoctrineObject
     */
    public function setIdentityValue($identityValue)
    {
        $this->identityValue = $identityValue;
        return $this;
    }

    /**
     * Set the credential value to be used.
     *
     * @param  mixed $credentialValue
     * @return \DoctrineModule\Authentication\Adapater\DoctrineObject
     */
    public function setCredentialValue($credentialValue)
    {
        $this->credentialValue = $credentialValue;
        return $this;
    }
    
    /**
     * Set the credential callable to be used to transform the password
     * before checking.
     *
     * @param  string $callable
     * @throws \InvalidArgumentException if argument is not a callable function 
     * @return \DoctrineModule\Authentication\Adapater\DoctrineObject
     */
    public function setCredentialCallable($callable)
    {
        if(is_callable($callable)){
            $this->credentialCallable = $callable;
        } else {
            throw new \InvalidArgumentException(sprintf(
                '%s is not a callable fuction',
                $callable
            ));
        }
        return $this;
    }
    
    /**
     * Set the property name to be used as the identity property
     *
     * @param  string $identityProperty
     * @return \DoctrineModule\Authentication\Adapater\DoctrineObject
     */
    public function setIdentityProperty($identityProperty)
    {
        $this->identityProperty = (string) $identityProperty;
        return $this;
    }

    /**
     * Set the property name to be used as the credential property
     *
     * @param  string $credentialField
     * @return \DoctrineModule\Authentication\Adapater\DoctrineObject
     */
    public function setCredentialProperty($credentialProperty)
    {
        $this->credentialProperty = (string) $credentialProperty;
        return $this;
    }
       
    /**
     * This method attempts to validate that the record in the resultset is indeed a 
     * record that matched the identity provided to this adapter.
     *
     * @param  object $identity
     * @throws \UnexpectedValueException - if the identity is not the class expected
     * @throws \BadMethodCallException - if the credentialProperty cannot be accessed on identity  
     * @return \Zend\Authentication\Result
     */
    protected function authenticateValidateIdentity($identity)
    {
        if (!$identity instanceof $this->identityClassName){
            throw new \UnexpectedValueException(sprintf(
                'Identity class type expected was %s, but got %s',
                $this->identityClassName,
                get_class($identity)
            ));
        }
           
        $getter = 'get' . ucfirst($this->credentialProperty);
        $vars = get_object_vars($identity);
        $documentCredential = null;
        
        if (method_exists($identity, $getter)) {
            $documentCredential = $identity->$getter();
        } else if (isset($identity->{$this->credentialProperty}) || isset($vars[$this->credentialProperty])) {
            $documentCredential = $identity->{$this->credentialProperty};
        } else {
            throw new \BadMethodCallException(sprintf(
                'Property (%s) in (%s) is not accessible. You should implement %s::%s()',
                $this->credentialProperty,
                get_class($identity),
                get_class($identity),
                $getter
            ));
        }
        
        $credentialValue = $this->credentialValue;
        $callable   = $this->credentialCallable;
        if ($callable) {
            $credentialValue = call_user_func($callable, $identity, $credentialValue);
        }
        
        if ($credentialValue != $documentCredential) {
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
     * This method abstracts the steps involved with making sure that this adapter was 
     * indeed setup properly with all required pieces of information.
     *
     * @throws \Zend\Authentication\Adapter\Exception\RuntimeException - in the event that setup was not done properly
     * @return bool
     */
    protected function authenticateSetup()
    {
        $exception = null;

        if ($this->identityClassName == '') {
            $exception = 'An identityClassName  must be supplied for the DoctrineObject authentication adapter.';
        } elseif ($this->identityProperty == '') {
            $exception = 'An identity property must be supplied for the DoctrineObject authentication adapter.';
        } elseif ($this->credentialProperty == '') {
            $exception = 'A credential property must be supplied for the DoctrineObject authentication adapter.';
        } elseif ($this->identityValue == '') {
            $exception = 'A value for the identity was not provided prior to authentication with '
                .'DoctrineObject authentication adapter.';
        } elseif ($this->credentialValue === null) {
            $exception = 'A credential value was not provided prior to authentication with '
                .'DoctrineObject authentication adapter.';
        }

        if (null !== $exception) {
            throw new Exception\RuntimeException($exception);
        }

        $this->authenticateResultInfo = array(
            'code'     => AuthenticationResult::FAILURE,
            'identity' => $this->identityValue,
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