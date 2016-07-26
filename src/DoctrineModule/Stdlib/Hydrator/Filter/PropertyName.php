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

namespace DoctrineModule\Stdlib\Hydrator\Filter;

use Zend\Hydrator\Filter\FilterInterface;

/**
 * Provides a filter to restrict returned fields by whitelisting or
 * blacklisting property names.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Liam O'Boyle <liam@ontheroad.net.nz>
 */
class PropertyName implements FilterInterface
{
    /**
     * The propteries to exclude.
     *
     * @var array
     */
    protected $properties = array();

    /**
     * Either an exclude or an include.
     *
     * @var bool
     */
    protected $exclude = null;

    /**
     * @param [ string | array ] $properties The properties to exclude or include.
     * @param bool               $exclude    If the method should be excluded
     */
    public function __construct($properties, $exclude = true)
    {
        $this->exclude    = $exclude;
        $this->properties = is_array($properties)
            ? $properties
            : array($properties);
    }

    public function filter($property)
    {
        return in_array($property, $this->properties)
            ? !$this->exclude
            : $this->exclude;
    }
}
