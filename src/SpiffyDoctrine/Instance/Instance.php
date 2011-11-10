<?php
namespace SpiffyDoctrine\Instance;
use InvalidArgumentException;

abstract class Instance
{
	/**
	 * All instances have a required opts array. 
	 * @var array
	 */
	protected $opts;
	
	/**
	 * Provides a definition of expected option parameters.
	 * @var array
	 */
	protected $definition = array();
	
	/**
	 * The configured instance.
	 * @var mixed
	 */
	protected $instance;
	
	/**
	 * Constructor.
	 * 
	 * @param array $opts
	 */
	public function __construct(array $opts)
	{
		$this->setOptions($opts);
	}
	
	/**
	 * Set options and validate minimum requirements.
	 * 
	 * @param array $opts
	 */
	public function setOptions(array $opts)
	{
		$this->validateOptions($opts);
        $this->opts = $opts;
	}
	
	/**
	 * Get options.
	 * 
	 * @return array
	 */
	public function getOptions()
	{
		return $this->opts;
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
	
	abstract protected function loadInstance();
	
    /**
     * Validates that required options are present and of the correct type and generates
     * optional options of the correct type if missing.
     * 
     * @param array 		$opts Options to check.
     * @param null|array    $defs Definitions to use - defaults to the class.
     * @throws InvalidArgumentException on missing required arguments.
     * @throws InvalidArgumentException when arguments are of the wrong type.
     * @return void
     */
    protected function validateOptions(array &$opts, array $defs = null)
    {
    	$defs = $defs ? $defs : $this->definition;
        if (isset($defs['required']) && is_array($defs['required'])) {
            // validate and ensure required options are of the correct type
            foreach($defs['required'] as $var => $type) {
                if (!isset($opts[$var])) {
                    throw new InvalidArgumentException(sprintf(
                        'missing option: "%s" is a required parameter.',
                        $var
                    ));
                }
                
                // if class_exists of $type then instantiate new object
                if (null !== $type) {
                    $got = gettype($opts[$var]);
                    if ($got !== $type) {
                        throw new InvalidArgumentException(sprintf(
                            'invalid option: "%s" should be a %s, got %s.',
                            $var,
                            $type,
                            $got
                        ));
                    }
                }
            }
        }

        if (isset($defs['optional']) && is_array($defs['optional'])) {
            // fill in missing optional arguments
            foreach($defs['optional'] as $var => $type) {
                if (!isset($opts[$var]) || !gettype($opts[$var]) == $type) {
                    settype($opts[$var], $type);
                }
            }
        }
    }
}