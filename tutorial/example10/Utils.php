<?php


class Utils {

    private static $config;


    public static function setConfig($config) {
        self::$config = $config;
    }

    /**
     * Generate arrival times using a linear distribution
     */
    public static function generateLinearArrivalTimes(int $clients_count_max, int $office_open_duration_seconds) {
        $client_arrive_probability = $clients_count_max / $office_open_duration_seconds;
        for ($time=0; $time <= $office_open_duration_seconds; $time++) {
                $p = mt_rand() / mt_getrandmax();
                if ($p <= $client_arrive_probability) $result[] = $time;
        }

        return $result;
    }


    /**
     * "Box–Muller transform" based random deviate generator.
     *
     * @ref https://en.wikipedia.org/wiki/Box%E2%80%93Muller_transform
     * @ref https://www.php.net/manual/en/function.stats-rand-gen-normal.php
     * 
     * @param  float|int $av Average/Mean
     * @param  float|int $sd Standard deviation
     * @return float
     */
    private static function stats_rand_gen_normal($av, $sd): float
    {
        $x = mt_rand() / mt_getrandmax();
        $y = mt_rand() / mt_getrandmax();
    
        return sqrt(-2 * log($x)) * cos(2 * pi() * $y) * $sd + $av;
    }

    /**
     * Generate arrival times using a normal distribution
     * Distribution is centered at $peak_time_minutes and has a standard deviation of $standard_deviation_minutes
     */
    public static function generateNormalArrivalTimes(int $peak_time_minutes, int $standard_deviation_minutes, int $clients_count_max): array {
        for ($i=1; $i <= $clients_count_max; $i++) {
            $result[] = round(self::stats_rand_gen_normal($peak_time_minutes*60, $standard_deviation_minutes*60));
        }

        return $result;
    }

    public static function logger(string $text) {
        if (self::$config['write_log']) echo $text;
    }

    public static function sleep(float $start_time, int $duration_seconds_start, int $duration_seconds) {
        $duration = $duration_seconds_start;
        while($duration < ($duration_seconds_start + $duration_seconds)) {
            $duration = (microtime(true) - $start_time) * self::$config['time_acceleration_factor'];
        }
    }

    private static function computeStats(array $clients) {
        $clients_count = count($clients);
        self::logger("Clients count: $clients_count\n");
        $column = array_column($clients, 'queue_wait_duration');
        $max_wait_duration = max($column);
        self::logger("Max wait duration: {$max_wait_duration}s\n");
        $average_wait_duration = round(array_sum($column) / $clients_count);
        self::logger("Average wait duration: {$average_wait_duration}s\n");

        return [$clients_count, $time, $max_wait_duration, $average_wait_duration];
    }

    public static function singleQueueToTxt(array $queue, array $desks) {

        $txt = "== Single queue ==\n";
        $txt .= "Main queue: " . implode(" ", $queue) . PHP_EOL;
        foreach ($desks as $desk_id => $client_id) {
            $txt .= "Desk $desk_id: $client_id" . PHP_EOL;
        }
        file_put_contents(__DIR__ . "/out.txt", $txt);
    }

    public static function multipleQueueToTxt(array $queues, array $desks) {

        $txt = "== Multiple queues ==\n";
        foreach ($queues as $queue_id => $client_ids) {
            $txt .= "Queue $queue_id : " . implode(" ", $client_ids) . PHP_EOL;
            $client_id = $desks[$queue_id];
            $txt .= "Desk $queue_id : $client_id"  . PHP_EOL;
        }
        file_put_contents(__DIR__ . "/out.txt", $txt);
    }

}