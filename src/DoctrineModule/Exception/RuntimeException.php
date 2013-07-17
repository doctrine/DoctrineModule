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
namespace DoctrineModule\Exception;

/**
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RuntimeException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @param  string $method
     * @param  int    $line
     * @return RuntimeException
     */
    public static function objectManagerNotSet($method, $line)
    {
        return new self(sprintf('No object manager was set in %s (%d).', $method, $line));
    }

    /**
     * @param  string $method
     * @param  int    $line
     * @return RuntimeException
     */
    public static function targetClassNotSet($method, $line)
    {
        return new self(sprintf('No target class was set in %s (%d).', $method, $line));
    }

    /**
     * @param  string $method
     * @param  int    $line
     * @return RuntimeException
     */
    public static function multipleIdentifiers($method, $line)
    {
        return new self(sprintf('Unable to handle multiple identifiers in %s (%d).', $method, $line));
    }

    /**
     * @param  string $method
     * @param  int    $line
     * @return RuntimeException
     */
    public static function findMethodNameNotSet($method, $line)
    {
        return new self(sprintf('No find method name was set in %s (%d).', $method, $line));
    }

    /**
     * @param  string $methodName
     * @param  string $repository
     * @param  string $method
     * @param  int    $line
     * @return RuntimeException
     */
    public static function invalidFindMethodName($methodName, $repository, $method, $line)
    {
        return new self(
            sprintf(
                'Method "%s" could not be found in repository "%s" in %s (%d).',
                $methodName,
                $repository,
                $method,
                $line
            )
        );
    }

    /**
     * @param  string $propertyName
     * @param  string $repository
     * @param  string $method
     * @param  int    $line
     * @return RuntimeException
     */
    public static function invalidPropertyName($propertyName, $repository, $method, $line)
    {
        return new self(
            sprintf(
                'Propery "%s" could not be found in object "%s" in %s (%d).',
                $properyName,
                $repository,
                $method,
                $line
            )
        );
    }

    /**
     * @param  string $methodName
     * @param  string $method
     * @param  int    $line
     * @return RuntimeException
     */
    public static function methodNotCallable($methodName, $method, $line)
    {
        return new self(
            sprintf(
                'Method "%s" is not callable in %s (%d).',
                $methodName,
                $method,
                $line
            )
        );
    }

    /**
     * @param  string $class
     * @param  string $method
     * @param  int    $line
     * @return RuntimeException
     */
    public static function noMethodOrToString($class, $method, $line)
    {
        return new self(
            sprintf(
                '%s must have a "__toString()" method defined if you have not set a property or method to use in %s (%d).',
                $class,
                $method,
                $line
            )
        );
    }
}
