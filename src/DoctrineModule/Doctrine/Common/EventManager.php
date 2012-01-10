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

namespace DoctrineModule\Doctrine\Common;

use Doctrine\Common\EventManager as DoctrineEventManager,
    DoctrineModule\Doctrine\Instance;

/**
 * Wrapper for Doctrine EventManager that helps setup configuration without relying
 * entirely on Di.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   1.0
 * @version $Revision$
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 */
class EventManager extends Instance
{
	/**
	 * @var array
	 */
	protected $definition = array(
        'optional' => array(
            'subscribers' => 'array'
        )
    );
	
	protected function loadInstance()
	{
		$opts = $this->getOptions();
		$evm = new DoctrineEventManager;
        
        foreach($opts['subscribers'] as $subscriber) {
            if (is_string($subscriber)) {
                if (!class_exists($subscriber)) {
                    throw new \InvalidArgumentException(sprintf(
                       'failed to register subscriber "%s" because the class does not exist.',
                       $subscriber 
                    ));
                }
                $subscriber = new $subscriber;
            }
            
            $evm->addEventSubscriber($subscriber);
        }
        
        $this->instance = $evm;
	}
}