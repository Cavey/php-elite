<?php
/**
 * Minion task for setting the throttle on the train.
 * @author Caveman
 */
class task_train_throttle 
{
	protected $_options = array(
        'train_id' => '3',
		'device' => '/dev/ttyACM0',
        'speed' => '75',
    );
	
	/**
	 * 
	 * @param array $params
	 * @return null 
	 */
	protected function _execute(array $params)
	{
		
		return NULL;
	}
}


