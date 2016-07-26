<?php
	/**
	 * Cronjobs
	 * @version  1.0.0
	 * @author Barry Dam info@barrydam.nl
	 *
	 * Easy to use PHP class to make and execute scheduled tasks (cronjobs)
	 * 
	 * --- HOW TO USE --
	 * 
	 * Make a new class like 'Example' which extends \BD\Cronjobs
	 *
	 * add a method to your class along with a const name:
	 * 
	 * const EXAMPLE_METHOD = 'EXAMPLE_METHOD';
	 * public static function example_method() {
	 * 		// do some
	 * 		return 'done ';
	 * }
	 * 
	 * then in your cron.php file add the task and set the time to execute  
	 * Example::addTask(
	 * 		Example::EXAMPLE_METHOD, // the task to run
	 * 	 	'10', // every 10th minute
	 * 	 	'*',  // every hour
	 * 	 	'*',  // every day of the month
	 * 	 	'*',  // every month
	 * 	 	'*'  // every day of the week
	 * );
	 *
	 * And finally run the tasks 
	 * Example::runTasks();
	 * Above will return an array where you can see whichs tasks are run 
	 * 
	 * 
	 */
	namespace BD;
	abstract class Cronjobs {

		protected static $arrTasks = array();

		/**
		 * Add a task to the crontasklist
		 * @param string  $strTask       the taskname / methodname to execute
		 * @param integer $minute     Minutes with leading zeros 00 -59
		 * @param integer $hour       24-hour format of an hour with leading zeros 00 - 23
		 * @param string  $dayOfmonth Day of the month without leading zeros 1 - 31
		 * @param string  $month      Numeric representation of a month, without leading zeros 1 - 12
		 * @param string  $dayOfWeek  SO-8601 numeric representation of the day of the week 1 (monday) - 7 (sunday)
		 */
		public static function addTask($strTask, $minute='*', $hour='*', $dayOfmonth='*', $month='*', $dayOfWeek='*')
		{
			$strMethod = strtolower($strTask);
			if(! method_exists(get_called_class(), $strMethod))
				throw new \Exception("Unkown method $strMethod", 1);
			static::$arrTasks[] = array(
				'strTask'    => $strMethod,
				'minute'     => $minute,
				'hour'       => $hour,
				'dayOfmonth' => $dayOfmonth,
				'month'      => $month,
				'dayOfWeek'  => $dayOfWeek
			);	
		}

		/**
		 * Run the tasks added by addTask method
		 * @return array array with executed tasks 
		 */
		public static function runTasks()
		{
			$DateTime      = new \DateTime();
			$hour       = $DateTime->format('H');
			$minute     = $DateTime->format('i');
			$dayOfmonth = $DateTime->format('j');
			$month      = $DateTime->format('n');
			$dayOfWeek  = $DateTime->format('N');

			$arrTasksExecuted = array();

			foreach (static::$arrTasks as $arrTask) {
				// check month
				if (! array_intersect(
					self::parseCronTimeToArray($arrTask['month'], 'month'), 
					array('*', $month))
				) {
					continue;
				}
				// check day of month
				if (! array_intersect(
					self::parseCronTimeToArray($arrTask['dayOfmonth'], 'dayOfmonth'), 
					array('*', $dayOfmonth))
				) {
					continue;
				}
				// check day of week
				if (! array_intersect(
					self::parseCronTimeToArray($arrTask['dayOfWeek'], 'dayOfWeek'), 
					array('*', $dayOfWeek))
				) {
					continue;
				}
				// check hour
				if (! array_intersect(
					self::parseCronTimeToArray($arrTask['hour'], 'hour'), 
					array('*', $hour))
				) {
					continue;
				}
				// check minute
				if (! array_intersect(
					self::parseCronTimeToArray($arrTask['minute'], 'minute'), 
					array('*', $minute))
				) {
					continue;
				}
				// run task
				$arrTasksExecuted[] = array(
					'task'			=> $arrTask['strTask'],
					'logmessage'	=> forward_static_call(array(get_called_class(), $arrTask['strTask'])),
					'timestamp'			=> $DateTime->getTimestamp()
				);				
			}
			return $arrTasksExecuted;
		}

		/**
		 *  Private function which creates an comparisation array
		 *  
		 *  2	the second minute, hour, month, etc
		 *  1,2	the first and second minute, hour, month, etc
		 * 	*	every minute, hour, month, etc
		 *  STAR/2	every seoncd minute, hour, month, etc (REPLACE STAR WITH * )
		 *  1-3	the first to third minute, hour, month, etc
		 *  1,STAR/6,5-9	all combined
		 * @param  string	$str       
		 * @param  string	$strType	hour, minute, dayOfWeek, dayOfMonth, month
		 * @return array 	$arrReturn	Possible number matches
		 */
		private static function parseCronTimeToArray($str, $strType) 
		{
			$arrExploded = explode(',', $str);
			$arrReturn = array();
			if (! count($arrExploded))
				return $arrReturn;
			foreach ($arrExploded as $str) {
				$arrRange = explode('-', $str);
				if (count($arrRange) == 2) {
					$arrReturn = array_merge($arrReturn, range($arrRange[0], $arrRange[1]));
				} else if (is_numeric($str) || $str == '*') {
					$arrReturn[] = $str;
				} else if (strpos($str,'*/')===0) {
					$intMax = 0;
					switch ($strType) {
						case 'month':
							$intMax = 12;
							break;

						case 'dayOfmonth':
							$intMax = 31;
							break;
						
						case 'dayOfWeek':
							$intMax = 7;
							break;

						case 'hour':
						case 'minute':
							$intMax = 60;
							break;
					}
					$str = str_replace('*/', '', $str);
					$iCount = 0;
					for ($i = 1; $i <= $intMax; $i++) {						
						$iCount++;
						if ($iCount == $str) {
							$arrReturn[] = $i;
							$iCount = 0;
						}
					}
						
				}
				//$arrReturn[] = $str;
			}

			return $arrReturn;
		}

	};
?>