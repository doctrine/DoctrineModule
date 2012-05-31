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

namespace DoctrineModule\Doctrine\Common;

use InvalidArgumentException;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use DoctrineModule\Doctrine\Instance;

/**
 * Wrapper for Doctrine DriverChain that helps setup configuration without relying
 * entirely on Di.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.1.0
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class DriverChain extends Instance
{
    protected $annotationDriverClass = 'Doctrine\ORM\Mapping\Driver\AnnotationDriver';
    protected $driverChainClass      = 'Doctrine\ORM\Mapping\Driver\DriverChain';

	/**
	 * @var array
	 */
	protected $driverChainDefinition = array(
        'required' => array(
            'class' 	=> 'string',
            'namespace' => 'string',
            'paths' 	=> 'array',
        ),
        'optional' => array(
            'file_extension' => 'string'
        )
    );

	/**
	 * @var Doctrine\Common\Annotations\CachedReader
	 */
	protected static $cachedReader;

	/**
	 * @var Doctrine\Common\Cache\Cache
	 */
	protected $cache;

	/**
	 * Constructor.
	 *
	 * @param array $drivers
	 * @param Cache $cache
	 */
	public function __construct(array $drivers = array(), Cache $cache = null)
	{
		$this->cache = $cache ? $cache : new ArrayCache();
		parent::__construct($drivers);
	}

	protected function loadInstance()
	{
		$drivers = $this->getOptions();

        $wrapperClass = $this->driverChainClass;
        if (isset($opts['wrapperClass'])) {
            if (is_subclass_of($opts['wrapperClass'], $wrapperClass)) {
               $wrapperClass = $opts['wrapperClass'];
            } else {
                throw InvalidArgumentException(sprintf(
                	'wrapperClass must be an instance of %s, %s given',
                	$this->driverChainClass,
                	$wrapperClass
                ));
            }
        }

        $chain = new $wrapperClass;

        foreach($drivers as $driverOpts) {
            $this->validateOptions($driverOpts, $this->driverChainDefinition);

            if (($driverOpts['class'] == $this->annotationDriverClass) ||
            	(is_subclass_of($driverOpts['class'], $this->annotationDriverClass))
			) {
                $cachedReader = $this->getCachedReader();
                $driver = new $driverOpts['class']($cachedReader, $driverOpts['paths']);
            } else {
                $driver = new $driverOpts['class']($driverOpts['paths']);
            }

            if ($driverOpts['file_extension'] && method_exists($driver, 'setFileExtension')) {
                $driver->setFileExtension($driverOpts['file_extension']);
            }

            $chain->addDriver($driver, $driverOpts['namespace']);
        }

        $this->instance = $chain;
    }

    /**
     * Get the cached reader instance for annotation readers.
     *
     * @todo investigate use cases for indexed reader
     * @return Doctrine\Common\Annotations\CachedReader
     */
    protected function getCachedReader()
    {
        if (null === self::$cachedReader) {
            $reader = new SimpleAnnotationReader;
            $reader->addNamespace('Doctrine\ORM\Mapping');
            self::$cachedReader = new CachedReader($reader, $this->cache);
        }
    	return self::$cachedReader;
    }
}