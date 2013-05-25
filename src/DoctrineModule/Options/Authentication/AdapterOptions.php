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

namespace DoctrineModule\Options\Authentication;

use Zend\Authentication\Adapter\Exception;

/**
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.5.0
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class AdapterOptions extends AbstractAuthenticationOptions
{
    /**
     * Property to use for the identity
     *
     * @var string
     */
    protected $identityProperty;

    /**
     * Property to use for the credential
     *
     * @var string
     */
    protected $credentialProperty;

    /**
     * Callable function to check if a credential is valid
     *
     * @var Callable
     */
    protected $credentialCallable;

    /**
     * @param string $identityClass
     * @return Authentication
     */
    public function setIdentityClass($identityClass)
    {
        $this->identityClass = $identityClass;
    }

    /**
     * @return string
     */
    public function getIdentityClass()
    {
        return $this->identityClass;
    }

    /**
     * @param  string $identityProperty
     * @throws Exception\InvalidArgumentException
     * @return Authentication
     */
    public function setIdentityProperty($identityProperty)
    {
        if (!is_string($identityProperty) || $identityProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $identityProperty is invalid, %s given', gettype($identityProperty))
            );
        }

        $this->identityProperty = $identityProperty;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentityProperty()
    {
        return $this->identityProperty;
    }

    /**
     * @param  string $credentialProperty
     * @throws Exception\InvalidArgumentException
     * @return Authentication
     */
    public function setCredentialProperty($credentialProperty)
    {
        if (!is_string($credentialProperty) || $credentialProperty === '') {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided $credentialProperty is invalid, %s given', gettype($credentialProperty))
            );
        }

        $this->credentialProperty = $credentialProperty;

        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialProperty()
    {
        return $this->credentialProperty;
    }

    /**
     * @param  Callable $credentialCallable
     * @throws Exception\InvalidArgumentException
     * @return Authentication
     */
    public function setCredentialCallable($credentialCallable)
    {
        if (!is_callable($credentialCallable)) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '"%s" is not a callable',
                    is_string($credentialCallable) ? $credentialCallable : gettype($credentialCallable)
                )
            );
        }

        $this->credentialCallable = $credentialCallable;

        return $this;
    }

    /**
     * @return Callable
     */
    public function getCredentialCallable()
    {
        return $this->credentialCallable;
    }
}
