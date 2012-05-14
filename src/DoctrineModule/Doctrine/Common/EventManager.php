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
    Doctrine\Common\EventSubscriber;

/**
 * Wrapper for Doctrine EventManager that helps setup configuration without relying
 * entirely on Di.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   1.0
 * @version $Revision$
 * @author  Kyle Spraggs <theman@spiffyjr.me>
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class EventManager
{

	/**
	 * The configured instance.
	 * @var mixed
	 */
	protected $instance;
    
	/**
	 * An array of event listeners.
	 * @var array
	 */    
	protected $subscribers = array();
    
	/**
	 * Add an event listener.
	 * @var Doctrine\Common\EventSubscriber subscriber
	 */     
    public function addSubscriber(EventSubscriber $subscriber){
        $this->subscribers[] = $subscriber;
    }
    
	/**
	 * Get the configured instance.
	 * 
	 * @return mixed
	 */
	public function getInstance()
	{
		if (null === $this->instance) {
			$this->loadInstance();
		}
		return $this->instance;
	}
    
	/**
	 * Instanate the event manager, and attatch event listeners
	 */      
	protected function loadInstance()
	{
		$subscribers = $this->subscribers;
		$evm = new DoctrineEventManager;
        
        foreach($subscribers as $subscriber) {
            $evm->addEventSubscriber($subscriber);
        }
        
        $this->instance = $evm;
	}
}