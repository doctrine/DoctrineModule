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
 * and is licensed under the MIT license.
 */

namespace DoctrineModule\Component\Console\Input;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Zend\Console\Request;

/**
 * RequestInput represents an input provided as an console request.
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
class RequestInput extends ArgvInput
{
    /**
     * Constructor
     *
     * @param \Zend\Console\Request $request
     * @param \Symfony\Component\Console\Input\InputDefinition $definition
     */
    public function __construct(Request $request, InputDefinition $definition = null)
    {
        $parameters = array(
            null,
        );

        foreach ($request->getParams() as $key => $param) {
            if (is_numeric($key)) {
                $parameters[] = $param;
            }
        }

        parent::__construct($parameters, $definition);
    }
}
