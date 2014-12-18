<?php
/*
 * Code for connecting to and controlling the Hornby Elite controller
 */

/**
 * Class for controlling the eLink controller
 * 
 * Note- Not a singleton as we might have multiple elinks
 * 
 * @author Caveman
 */
class hornby_elink 
{
	// No device set
	public static $NONE = 0;
	// Device setup
	public static $SETUP = 1;
	// Device configured
	public static $INIT = 3;
	
	protected $_status = 0;
	protected $_version = NULL;
	
	/**
	 * Serial device that respresents the 
	 * 
	 * @var object 
	 */
	protected $__device = NULL;
	
	/**
	 * 
	 * @param string $device
	 * @param integer $rate
	 * @return \hornby_elink
	 */
	public function __construct($device=NULL, $rate=NULL) 
	{
		if($device !== NULL)
		{
			$this->set_device($device, $rate);
		}
		return $this;
	}
	/**
	 * 
	 * @param string $device
	 * @param integer $rate
	 * @return \hornby_elink
	 */
	public function set_device($device=NULL, $rate=NULL)
	{
		if($this->status == self::$NONE)
		{
			$this->_device = new PHPSerial($device, $rate);
		}
		return $this;
	}
	
	/**
	 * 
	 * @return \hornby_elink
	 */
	public function initialise()
	{
		if($this->status == self::$SETUP)
		{
			$this->get_version();
			if(!$this->check_init())
			{
				$this->init();
			}
		}
		return $this;
	}
	
	public function get_version()
	{
		// Send 212100
		//$data = array(0x21, 0x21, 0x00);
		// First byte is the version
		if($this->_version == NULL)
		{
			$message = new bytearray('212100');
			$response = $this->_device->send($message->as_bin())->read();
			// If it is 010203, not init return false
			$response = bytearray::factory()->from_bin($response);
			$this->_version = reset($response->as_array());
		}
		return $this->_version;
	}
	/**
	 * Checks whether the device is enabled or not.
	 * @return boolean
	 */
	public function check_init()
	{
		// Send 212405
		// array(0x21,0x24,0x05);
		$message = new bytearray('212405');
		$response = $this->_device->send($message->as_bin())->read();
		// If it is 010203, not init return false
		$response = bytearray::factory()->from_bin($response);
		if($response->as_hex() == '010203')
		{
			// Not initialised.
			return false;
		}
		return true;
	}
	/**
	 * Initialises the Elite.  
	 * 
	 * @throws Kohana_Exception
	 * @return object horby_elite
	 */
	public function init()
	{
		// Send 3a36344a4b44383942535439
		//$data = array(0x3a, 0x36, 0x34, 0x4a, 0x4b, 0x44, 0x38, 0x39, 0x42, 0x53, 0x54, 0x39);
		$message = new bytearray('3a36344a4b44383942535439');
		// Recieve reply such as 35a3680bc56353
		$response = $this->_device->send($message->as_bin())->read();
		// 35 at the start stays the same.
		// Add 0x39 to each of the middle bytes to get 35dca144fe63
		$response = bytearray::factory()->from_bin($response);
		$message = $response->add('00393939393900');
		$message->update_parity();
		// Send it back
		$response = $this->_device->send($message)->read();
		// Should recieve 010405
		// array(0x01, 0x04, 0x05);
		if( $response->as_hex() != '010405')
		{
			throw new Kohana_Exception('Failed to initialise eLink.');
		}
		return $this;
	}
}


