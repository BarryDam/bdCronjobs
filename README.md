# Cronjobs
Easy to use PHP class to create and execute scheduled tasks (cronjobs)
 
##HOW TO USE##
Make a new class like 'Example' which extends \BD\Cronjobs
and add a method to your class along with a const name:
```php
class Example extends \BD\Cronjobs {

	const EXAMPLE_METHOD = 'EXAMPLE_METHOD';
	public static function example_method() {
			// do some
			return 'done ';
	}

}
```

then in your cron.php file add the task and set the time to execute  
```php
Example::addTask(
		Example::EXAMPLE_METHOD, // the task to run
	 	'10', // every 10th minute
	 	'*',  // every hour
	 	'*',  // every day of the month
	 	'*/2',  // every 2nd month (feb, apr, jun, aug, okt, dec)
	 	'*'  // every day of the week
);
```
And finally run the tasks in cron.php
```php
Example::runTasks();
```
Above will return an array where you can see whichs tasks are run 

P.S. Set your crontab to to cron cron.php every minute