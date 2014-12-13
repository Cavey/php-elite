<?php
/*
 * Code for connecting to and controlling the Hornby Elite controller
 */

/**
 * Class for controlling a train.  This might need to be changed into some 
 * persistent singleton or something.
 *
 * @author Caveman
 */
class hornby_train 
{
	const FORWARD = 0;
	const REVERSE = 1;
	const STOPPED = 0;
	const SLOW = 50;
	const NORMAL = 70;
	const FAST = 90;
	// Max is actually 128, but that's just too fast.
	const MAX_SPEED = 100;
	
	const OFF = 0;
	const ON = 1;
	
	/**
	 * Elite object for connections
	 * @var object 
	 */
	protected $_device;
	/**
	 * Numeric id of the train 
	 * @var integer 
	 */
	protected $_id;
	
	protected $_function_group = array(0,0,0);
	
	/**
	 * Byte addresses for functions on the train
	 *	# function table columns
	 *	# 0 = group 0 - 2
	 *	# 1 = group code 0x20, 0x21, 0x22
	 *	# 2 = on mask
	 *	# 3 = off mask
	 * @var array
	 */
	protected $_function_table = array(
						array( 0,0x20,0x10,0xEF),
						array( 0,0x20,0x01,0xFE),
						array( 0,0x20,0x02,0xFD),
						array( 0,0x20,0x04,0xFB),
						array( 0,0x20,0x08,0xF7),
						array( 1,0x21,0x01,0xFE),
						array( 1,0x21,0x02,0xFD),
						array( 1,0x21,0x04,0xFB),
						array( 1,0x21,0x08,0xF7),
						array( 2,0x22,0x01,0xFE),
						array( 2,0x22,0x02,0xFD),
						array( 2,0x22,0x04,0xFB),
						array( 2,0x22,0x08,0xF7)
						 );
	
	public function __construct($device, $id=NULL)
	{
		
	}
	public static function factory($id=NULL)
	{
		
	}
	public function throttle($speed, $direction)
	{
		$message = new bytearray('E400000000');
		// Throttle?
		$message->set_byte(2, 0x13);
		// Set the train ID
		$message->set_byte(3, $this->_id);
		// Speed
		if($speed < 0)
		{
			$speed = 0;
		} 
		else if($speed > self::MAX_SPEED)
		{
			$speed = self::MAX_SPEED;
		}
		if( $direction == self::FORWARD )
		{
			$movement = $speed | 0x80;
		}
		else if( $direction == self::REVERSE )
		{
			$movement = $speed & 0x7F;
		}
		$message->set_byte(5, $movement);
		$message->append_parity();
		$response = $device->send($message->as_bin())->read();
		return $this;
	}
	/**
	 * 
	 * @param integer $id
	 * @param bool $state
	 */
	public function train_function($num, $state)
	{
		$message = new bytearray('E400000000');
		// What function?
		$message->set_byte(2, $this->_function_table[$num][2]);
		// Set the train ID
		$message->set_byte(3, $this->_id);
		// Function
		// Need to retrieve this form memory.  Memcache?
		$st = $this->_function_group[$this->_function_table[$num][1]];
		if($state == self::ON)
		{
			$st = $st | $this->_function_table[$num][3];
		}
		else 
		{
			$st = $st & $this->_function_table[$num][4];
		}
		$this->_function_group[$this->_function_table[$num][1]] = $st;
		$message->set_byte(5, $st);
		$message->append_parity();
		$response = $device->send($message->as_bin())->read();
		return $this;
	}
}


