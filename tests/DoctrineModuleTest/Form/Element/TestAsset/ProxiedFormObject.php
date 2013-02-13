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

namespace DoctrineModuleTest\Form\Element\TestAsset;

use Doctrine\Common\Persistence\Proxy as PersistenceProxy;

/**
 * Simple mock object for form element adapter tests
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Matt Pinkston <matt@pinkston.org>
 */
class ProxiedFormObject extends FormObject implements PersistenceProxy
{

    public $isInitialized = false;

    protected $proxiedData = array();

    /**
     * An array of values that will be used to hydrate this object
     * when __load is called.
     *
     * @param $data
     */
    public function setProxiedData($data)
    {
        $this->proxiedData = $data;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Initialize this proxy if its not yet initialized.
     *
     * Acts as a no-op if already initialized.
     *
     * @return void
     */
    public function __load()
    {
        if (!$this->isInitialized) {
            $this->isInitialized = true;

            foreach ($this->proxiedData as $field => $value) {
                $setterMethod = sprintf('set%s', ucfirst($field));
                if (method_exists($this, $setterMethod)) {
                    $this->{$setterMethod}($value);
                }
            }
        }
    }

    /**
     * Is this proxy initialized or not.
     *
     * @return bool
     */
    public function __isInitialized()
    {
        return $this->isInitialized;
    }
}