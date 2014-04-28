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

namespace DoctrineModule\Validator;

/**
 * Class that validates if objects does not exist in a given repository with a given list of matched fields
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.4.0
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class NoObjectExists extends ObjectExists
{
    /**
     * Error constants
     */
    const ERROR_OBJECT_FOUND    = 'objectFound';
    
    /**
     * @var array
     */
    protected $exclude = null;

    /**
     *
     * @param array $options
     */
    public function __construct($options)
    {
        parent::__construct($options);
        if (array_key_exists('exclude', $options)) {
            $this->setExclude($options['exclude']);
        }
    }
    
    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::ERROR_OBJECT_FOUND    => "An object matching '%value%' was found",
    );

    /**
     * {@inheritDoc}
     */
    public function isValid($value)
    {
        $value = $this->cleanSearchValue($value);
        $criteria = $this->makeCriteria($value);

        /** @var Doctrine\Common\Collections\ArrayCollection */
        $collection = $this->objectRepository->matching($criteria);

        if ($collection->count()) {
            $this->error(self::ERROR_OBJECT_FOUND, $value);

            return false;
        }

        return true;
    }
    
    /**
     * Sets a new exclude clause
     *
     * @param array $exclude
     * @return self Provides a fluent interface
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;
        return $this;
    }

    /**
     *
     * @param array $value
     * @return \Doctrine\Common\Collections\Criteria
     */
    public function makeCriteria($value)
    {
        $criteria = Criteria::create();
        $criteria->andWhere(Criteria::expr()->eq(key($value), current($value)));

        if($this->exclude) {
            $criteria->andWhere(Criteria::expr()->neq($this->exclude['field'], $this->exclude['value']));
        }

        return $criteria;
    }
}
