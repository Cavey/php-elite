<?php
/*
 * Code for connecting to and controlling the Hornby Elite controller
 */

/**
 * Class for controlling the Elite controller
 * 
 * Note- Not a singleton as we might have multiple elinks
 * 
 * @author Caveman
 */
class hornby_elite 
{
	/**
	 * Serial device that respresents the 
	 * 
	 * @var object 
	 */
	protected $__device = NULL;
	
	public function __construct($device=NULL, $rate=NULL) 
	{
		if($device !== NULL)
		{
			$this->set_device();
		}
		return $this;
	}
	public function set_device()
	{
		return $this;
	}
	
	public function get_version()
	{
		// Send 212100
		//$data = array(0x21, 0x21, 0x00);
		// First byte is the version
		$message = bytearray('212100');
		$response = $device->send($message->as_bin())->read();
		// If it is 010203, not init return false
		$response = bytearray::factory()->from_bin($response);
		$version = reset($response->as_array());
		return $version;
	}
	/**
	 * Checks whether the device is enabled or not.
	 * @return boolean
	 */
	public function check_init()
	{
		// Send 212405
		// array(0x21,0x24,0x05);
		$message = bytearray('212405');
		$response = $device->send($message->as_bin())->read();
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
	 */
	public function init()
	{
		// Send 3a36344a4b44383942535439
		//$data = array(0x3a, 0x36, 0x34, 0x4a, 0x4b, 0x44, 0x38, 0x39, 0x42, 0x53, 0x54, 0x39);
		$message = new bytearray('3a36344a4b44383942535439');
		// Recieve reply such as 35a3680bc56353
		$response = $device->send($message->as_bin())->read();
		// 35 at the start stays the same.
		// Add 0x39 to each of the middle bytes to get 35dca144fe63
		$response = bytearray::factory()->from_bin($response);
		$message = $response->add('00393939393900');
		$message->update_parity();
		// Send it back
		$response = $device->send($message)->read();
		// Should recieve 010405
		// array(0x01, 0x04, 0x05);
		if( $response->as_hex() != '010405')
		{
			throw new Kohana_Exception('Failed to initialise eLink.');
		}
	}
}


