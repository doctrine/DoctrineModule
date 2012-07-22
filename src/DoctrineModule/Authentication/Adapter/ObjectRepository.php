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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace DoctrineModule\Authentication\Adapter;

use Doctrine\Common\Persistence\ObjectRepository as DoctrineRepository;
use DoctrineModule\Options\Authentication as AuthenticationOptions;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\Exception;
use Zend\Authentication\Result as AuthenticationResult;

/**
 * Authentication adapter that uses a Doctrine object for verification.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.5.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ObjectRepository implements AdapterInterface
{
    /**
     * Doctrine ObjectRepository instance
     *
     * @var DoctrineRepository
     */
    protected $objectRepository;

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
     * @var AuthenticationOptions
     */
    protected $options;

    /**
     * Contains the authentication results.
     *
     * @var array
     */
    protected $authenticationResultInfo = null;

    /**
     * Constructor
     *
     * @param array|AuthenticationOptions $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @param  array|AuthenticationOptions $options
     * @return ObjectRepository
     */
    public function setOptions($options)
    {
        if (!$options instanceof AuthenticationOptions) {
            $options = new AuthenticationOptions($options);
        }

        if ($options->getObjectManager()) {
            $objectRepository = $options->getObjectManager()->getRepository($options->getIdentityClass());
            $this->objectRepository = $objectRepository;
        }

        $this->options = $options;
        return $this;
    }

    /**
     * Sets the object repository where to look for identities
     *
     * @param  DoctrineRepository $objectRepository
     * @return ObjectRepository
     */
    public function setObjectRepository(DoctrineRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getObjectRepository()
    {
        return $this->objectRepository;
    }

    /**
     * Set the value to be used as the identity
     *
     * @param  mixed $identityValue
     * @return ObjectRepository
     */
    public function setIdentityValue($identityValue)
    {
        $this->identityValue = $identityValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentityValue()
    {
        return $this->identityValue;
    }

    /**
     * Set the credential value to be used.
     *
     * @param  mixed $credentialValue
     * @return ObjectRepository
     */
    public function setCredentialValue($credentialValue)
    {
        $this->credentialValue = $credentialValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialValue()
    {
        return $this->credentialValue;
    }

    /**
     * Set the credential callable to be used to transform the password before checking.
     *
     * @param  $credentialCallable
     * @return ObjectRepository
     */
    public function setCredentialCallable($credentialCallable)
    {
        $this->options->setCredentialCallable($credentialCallable);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCredentialCallable()
    {
        return $this->options->getCredentialCallable();
    }

    /**
     * Set the property name to be used as the identity property
     *
     * @param  string $identityProperty
     * @return ObjectRepository
     */
    public function setIdentityProperty($identityProperty)
    {
        $this->options->setIdentityProperty($identityProperty);
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentityProperty()
    {
        return $this->options->getIdentityProperty();
    }

    /**
     * Set the property name to be used as the credential property
     *
     * @param  string           $credentialProperty
     * @return ObjectRepository
     */
    public function setCredentialProperty($credentialProperty)
    {
        $this->options->setCredentialProperty($credentialProperty);
        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialProperty()
    {
        return $this->options->getCredentialProperty();
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate()
    {
        $this->setup();
        $identity = $this->getObjectRepository()->findOneBy(array($this->getIdentityProperty() => $this->identityValue));

        if (!$identity) {
            $this->authenticationResultInfo['code'] = AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticationResultInfo['messages'][] = 'A record with the supplied identity could not be found.';

            return $this->createAuthenticationResult();
        }

        $authResult = $this->validateIdentity($identity);

        return $authResult;
    }

    /**
     * This method attempts to validate that the record in the resultset is indeed a
     * record that matched the identity provided to this adapter.
     *
     * @param  object                              $identity
     * @throws Exception\UnexpectedValueException
     * @return AuthenticationResult
     */
    protected function validateIdentity($identity)
    {
        $credentialProperty = $this->getCredentialProperty();
        $getter = 'get' . ucfirst($credentialProperty);
        $documentCredential = null;

        if (method_exists($identity, $getter)) {
            $documentCredential = $identity->$getter();
        } elseif (property_exists($identity, $credentialProperty)) {
            $documentCredential = $identity->{$credentialProperty};
        } else {
            throw new Exception\UnexpectedValueException(sprintf(
                'Property (%s) in (%s) is not accessible. You should implement %s::%s()',
                $credentialProperty,
                get_class($identity),
                get_class($identity),
                $getter
            ));
        }

        $credentialValue = $this->credentialValue;
        $callable = $this->getCredentialCallable();

        if ($callable) {
            $credentialValue = call_user_func($callable, $identity, $credentialValue);
        }

        if ($credentialValue !== true && $credentialValue != $documentCredential) {
            $this->authenticationResultInfo['code'] = AuthenticationResult::FAILURE_CREDENTIAL_INVALID;
            $this->authenticationResultInfo['messages'][] = 'Supplied credential is invalid.';

            return $this->createAuthenticationResult();
        }

        $this->authenticationResultInfo['code']       = AuthenticationResult::SUCCESS;
        $this->authenticationResultInfo['identity']   = $identity;
        $this->authenticationResultInfo['messages'][] = 'Authentication successful.';

        return $this->createAuthenticationResult();
    }

    /**
     * This method abstracts the steps involved with making sure that this adapter was
     * indeed setup properly with all required pieces of information.
     *
     * @throws Exception\RuntimeException - in the event that setup was not done properly
     */
    protected function setup()
    {
        if (null === $this->identityValue) {
            throw new Exception\RuntimeException(
                'A value for the identity was not provided prior to authentication with ObjectRepository authentication '
                    . 'adapter'
            );
        }

        if (null === $this->credentialValue) {
            throw new Exception\RuntimeException(
                'A credential value was not provided prior to authentication with ObjectRepository authentication adapter'
            );
        }

        $this->authenticationResultInfo = array(
            'code' => AuthenticationResult::FAILURE,
            'identity' => $this->identityValue,
            'messages' => array()
        );
    }

    /**
     * Creates a Zend\Authentication\Result object from the information that has been collected
     * during the authenticate() attempt.
     *
     * @return \Zend\Authentication\Result
     */
    protected function createAuthenticationResult()
    {
        return new AuthenticationResult(
            $this->authenticationResultInfo['code'],
            $this->authenticationResultInfo['identity'],
            $this->authenticationResultInfo['messages']
        );
    }
}