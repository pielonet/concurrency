<?php
/**
 * 
 * Alice and Bob are hungry : Launch one parallel task
 *  with the given task and get its return values.
 * 
 * https://www.php.net/manual/en/class.parallel-future.php
 * 
 * public parallel\Future::value(): mixed
 * Shall return (and if necessary wait for) return from task 
 * @ref https://www.php.net/manual/en/parallel-future.value.php
 */

$courses = ['gazpacho', 'tortilla', 'pizza', 'burger', 'grilled tofu'];

$task = function (string $who, int $min_eat_time_seconds, int $max_eat_time_seconds, array $courses) {
    $eat_time = rand($min_eat_time_seconds, $max_eat_time_seconds);
    sleep($eat_time);
    $course_id = array_rand($courses, 1);
    return [$who, $eat_time, $courses[$course_id]];
};




