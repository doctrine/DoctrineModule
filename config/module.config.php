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

return array(
    'di' => array(
        'instance' => array(
            'alias' => array(
                // Services
                'doctrine_service' => 'DoctrineModule\Service\Service',

                // Caching
                'doctrine_memcache'       => 'Memcache',
                'doctrine_cache_apc'      => 'Doctrine\Common\Cache\ApcCache',
                'doctrine_cache_array'    => 'Doctrine\Common\Cache\ArrayCache',
                'doctrine_cache_memcache' => 'Doctrine\Common\Cache\MemcacheCache',

                // CLI tools
                'doctrine_cli' => 'Symfony\Component\Console\Application',
                'doctrine_cli_helperset' => 'Symfony\Component\Console\Helper\HelperSet',
            ),

            // Defaults for CLI
            'doctrine_cli' => array(
                'parameters' => array(
                    'name' => 'DoctrineModule Command Line Interface',
                    'version' => 'dev-master',
                ),
                'injections' => array(
                    'doctrine_cli_helperset',
                ),
            ),
            'doctrine_cli_helperset' => array(
                'parameters' => array(
                    'helpers' => array(),
                ),
                'injections' => array(
                    'set' => array(
                        array(
                            'helper' => 'Symfony\Component\Console\Helper\DialogHelper',
                            'alias' => 'dialog',
                        ),
                    ),
                ),
            ),

            // Defaults for memcache
            'doctrine_memcache' => array(
                'parameters' => array(
                    'host' => '127.0.0.1',
                    'port' => '11211',
                ),
            ),
            'doctrine_cache_memcache' => array(
                'parameters' => array(
                    'memcache' => 'doctrine_memcache',
                ),
            ),
        ),

        // Definitions (enforcing DIC behavior)
        'definition' => array(
            'class' => array(
                // Enforcing Memcache to behave correctly (methods are not always discovered correctly by DIC)
                'Memcache' => array(
                    'methods' => array(
                        'addServer' => array(
                            'host' => array(
                                'type' => false,
                                'required' => true,
                            ),
                            'port' => array(
                                'type' => false,
                                'required' => true,
                            ),
                        ),
                    ),
                ),

                // CLI Application setup
                'Symfony\Component\Console\Application' => array(
                    'methods' => array(
                        'add' => array(
                            'command' => array(
                                'type' => 'Symfony\Component\Console\Command\Command',
                                'required' => true,
                            ),
                        ),
                    ),
                ),
                'Symfony\Component\Console\Helper\HelperSet' => array(
                    'methods' => array(
                        'set' => array(
                            'helper' => array(
                                'type' => 'Symfony\Component\Console\Helper\HelperInterface',
                                'required' => true,
                            ),
                            'alias' => array(
                                'type' => false,
                                'required' => false,
                            ),
                        ),
                    ),
                ),

                // Enforcing hints for the DoctrineObject auth adapter
                'DoctrineModule\Authentication\Adapter\DoctrineObject' => array(
                    'methods' => array(
                        '__construct' => array(
                            'objectManager' => array(
                                'type' => 'Doctrine\Common\Persistence\ObjectManager',
                                'required' => true
                            ),
                            'identityClassName' => array(
                                'type' => false,
                                'required' => true
                            ),
                            'identityProperty' => array(
                                'type' => false,
                                'required' => true
                            ),
                            'credentialProperty' => array(
                                'type' => false,
                                'required' => true
                            ),
                            'credentialCallable' => array(
                                'type' => false,
                                'required' => false
                            ),
                        ),
                        'setIdentityClassName' => array(
                            'identityClassName' => array(
                                'type' => false,
                                'required' => false
                            ),
                        )
                    )
                )
            ),
        ),
    ),
);