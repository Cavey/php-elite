<?php
/*
 * Code for connecting to and controlling the Hornby Elite controller
 */

/**
 * Class for controlling the Elite controller
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
	
	public function __construct($device) 
	{
		
	}
	
	public function get_version()
	{
		// Send 212100
		$data = array(0x21, 0x21, 0x00);
		// First byte is the version
		return $version;
	}
	
	public function check_init()
	{
		// Send 212405
		// array(0x21,0x24,0x05);
		$response = $device->send($message)->read();
		// If it is 010203, not init return false
		
	}
	
	public function init()
	{
		// Send 3a36344a4b44383942535439
		//$data = array(0x3a, 0x36, 0x34, 0x4a, 0x4b, 0x44, 0x38, 0x39, 0x42, 0x53, 0x54, 0x39);
		$message = pack('H*', '3a36344a4b44383942535439');
		// Recieve reply such as 35a3680bc56353
		$response = $device->send($message)->read();
		// 35 at the start stays the same.
		$dat = unpack("C*", $response);
		// Add 0x39 to each of the middle bytes to get 35dca144fe63
		for($i=2; $i<count($dat); $i++)
		{
			$dat[$i] += 0x39;
		}
		// Add parity to get 35dca144fe63f2
		unset($dat[count($dat)]);
		$par = 0;
		for($i=2; $i<count($dat); $i++)
		{
			$par ^= $dat[$i];
		}
		$dat[count($dat)+1] = $par;
		// Recollapse
		$message = '';
		foreach($dat as $d)
		{
			$message .= dechex($d);
		}
		// Send it back
		$response = $device->send($message)->read();
		// Should recieve 010405
		// array(0x01, 0x04, 0x05);
		if( $response != '010405')
		{
			throw new Kohana_Exception('Failed to initialise eLink.');
		}
	}
}


