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

namespace DoctrineModule\Stdlib\Hydrator\Strategy;


use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;

/**
 * Composite strategy - composes two {@see \Zend\Stdlib\Hydrator\Strategy\StrategyInterface} into
 * a single one
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.8.0
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class CompositeStrategy implements StrategyInterface
{
    /**
     * @var StrategyInterface
     */
    private $baseStrategy;

    /**
     * @var StrategyInterface
     */
    private $additionalStrategy;

    /**
     * @param StrategyInterface $baseStrategy
     * @param StrategyInterface $additionalStrategy
     */
    public function __construct(StrategyInterface $baseStrategy, StrategyInterface $additionalStrategy)
    {
        $this->baseStrategy       = $baseStrategy;
        $this->additionalStrategy = $additionalStrategy;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value, $object = null)
    {
        return $this->additionalStrategy->extract(
            $this->baseStrategy->extract($value, $object),
            $object
        );
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate($value, $data = null)
    {
        return $this->additionalStrategy->hydrate(
            $this->baseStrategy->hydrate($value, $data),
            $data
        );
    }
}
