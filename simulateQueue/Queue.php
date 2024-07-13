<?php

class Queue {

    private $write_log;

    public function __construct(bool $write_log = false)
    {
        $this->write_log = $write_log;
    }

    /**
     * Generate arrival times using a linear distribution
     */
    private function generateLinearArrivalTimes($clients_count_max, $office_open_duration_seconds) {
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
    private function stats_rand_gen_normal($av, $sd): float
    {
        $x = mt_rand() / mt_getrandmax();
        $y = mt_rand() / mt_getrandmax();
    
        return sqrt(-2 * log($x)) * cos(2 * pi() * $y) * $sd + $av;
    }

    /**
     * Generate arrival times using a normal distribution
     * Distribution is centered at $peak_time_minutes and has a standard deviation of $standard_deviation_minutes
     */
    private function generateNormalArrivalTimes(int $peak_time_minutes, int $standard_deviation_minutes, int $clients_count_max): array {
        for ($i=1; $i <= $clients_count_max; $i++) {
            $result[] = round($this->stats_rand_gen_normal($peak_time_minutes*60, $standard_deviation_minutes*60)) . "\n";
        }

        return $result;
    }

    private function logger(string $text) {
        if ($this->write_log) echo $text;
    }

    public function singleQueue(array $config) {

        $time = 0;
        $queue = [];
        $clients = [];
        $clients_entered_count = 0;

        $desks = array_fill(1, $config['desks_count'], null);

        if ($config['arrival_probability_law'] == 'normal') {
            $arrival_times = $this->generateNormalArrivalTimes($config['peak_time_minutes'], $config['standard_deviation_minutes'], $config['clients_count_max']);
        } else {
            $arrival_times = $this->generateLinearArrivalTimes($config['clients_count_max'], $config['office_open_duration_seconds']);
        }

        while (true) {
            $this->logger("Time: {$time}s\n");
            //$client_enters_queue = (checkLinearArrivalTimes($client_arrive_probability) and $time < $office_open_duration_seconds) ? true : false;
            $client_enters_queue = (in_array($time, $arrival_times) and $time < $config['office_open_duration_seconds']) ? true : false;
            if ($client_enters_queue) {
                $clients_entered_count++;
                $this->logger("ClientId $clients_entered_count enters queue. ");
                $clients[$clients_entered_count]['enters_queue_time'] = $time;
                $queue[] = $clients_entered_count;
                $this->logger(count($queue) . " client(s) in queue". PHP_EOL);
            }
            foreach ($desks as $desk_id => &$client_id) {
                if ($client_id) {
                    $client = $clients[$client_id];
                    if ($time == $client['leaves_desk_time']) {
                        $this->logger("ClientId $client_id leaves deskId $desk_id\n");
                        // Empty desk
                        $client_id = null;
                    }
                }
                if (is_null($client_id)) {
                    $new_client_id = array_shift($queue);
                    if (!is_null($new_client_id)) {
                        // Update desk
                        $client_id = $new_client_id;
                        // Update client
                        $client = &$clients[$new_client_id];
                        $client['queue_wait_duration'] = $time - $client['enters_queue_time'];
                        $client['desk_visited'] = $desk_id;
                        $desk_duration = rand($config['clients_min_desk_duration_seconds'], $config['clients_max_desk_duration_seconds']);
                        $client['leaves_desk_time'] = $time + $desk_duration;
                        $this->logger("ClientId $client_id enters deskId $desk_id for {$desk_duration}s. ");
                        $this->logger("Client waited {$client['queue_wait_duration']}s in queue. ");
                        $this->logger(count($queue) . " client(s) in queue". PHP_EOL);
                        unset($client);
                    }
                }
            }
            unset($client_id);
            $time++;
            usleep($config['simulation_wait_microseconds']);
            if ($config['display_simulation']) $this->singleQueueToTxt($queue, $desks);

            // End simulation
            //if (count($clients) == $clients_count_max) {
            if ($time >= $config['office_open_duration_seconds']) {
                foreach($desks as $client_id) {
                    if (!is_null($client_id)) continue 2;
                }
                break;
            }
            //}
        }

        // Compute statistics

        $clients_count = count($clients);
        $this->logger("Clients count: $clients_count\n");
        $column = array_column($clients, 'queue_wait_duration');
        $max_wait_duration = max($column);
        $this->logger("Max wait duration: {$max_wait_duration}s\n");
        $average_wait_duration = round(array_sum($column) / $clients_count);
        $this->logger("Average wait duration: {$average_wait_duration}s". PHP_EOL);

        return [$clients_count, $time, $max_wait_duration, $average_wait_duration];
    }

    private function singleQueueToTxt(array $queue, array $desks) {

        $txt = "== Single queue ==\n";
        $txt .= "Main queue :" . implode(" ", $queue) . PHP_EOL;
        foreach ($desks as $desk_id => $client_id) {
            $txt .= "Desk $desk_id : $client_id" . PHP_EOL;
        }
        file_put_contents(__DIR__ . "/out.txt", $txt);
    }

    public function multipleQueue(array $config) {

        $time = 0;
        $clients = [];
        $clients_entered_count = 0;

        $desks = array_fill(1, $config['desks_count'], null);
        $queues = array_fill(1, $config['desks_count'], []);

        if ($config['arrival_probability_law'] == 'normal') {
            $arrival_times = $this->generateNormalArrivalTimes($config['peak_time_minutes'], $config['standard_deviation_minutes'], $config['clients_count_max']);
        } else {
            $arrival_times = $this->generateLinearArrivalTimes($config['clients_count_max'], $config['office_open_duration_seconds']);
        }
        
        while (true) {
            $this->logger("Time: {$time}s\n");
            //$client_enters_queue = (checkWithLinearProbability($client_arrive_probability) and $time < $office_open_duration_seconds) ? true : false;
            $client_enters_queue = (in_array($time, $arrival_times) and $time < $config['office_open_duration_seconds']) ? true : false;
            if ($client_enters_queue) {
                $clients_entered_count++;
                $clients[$clients_entered_count]['enters_queue_time'] = $time;
                // Select queue with least number of clients
                foreach ($desks as $desk_id => $client_id) {
                    $queues_counts[$desk_id] = (is_null($client_id) ? 0 : 1) + count($queues[$desk_id]);
                }
                $queue_id = current(array_keys($queues_counts, min($queues_counts)));
                $queues[$queue_id][] = $clients_entered_count;
                $this->logger("ClientId $clients_entered_count enters QueueId $queue_id. Queue length is " . count($queues[$queue_id]) . "." . PHP_EOL);
            }
            foreach ($desks as $desk_id => &$client_id) {
                if ($client_id) {
                    $client = $clients[$client_id];
                    if ($time == $client['leaves_desk_time']) {
                        $this->logger("ClientId $client_id leaves deskId $desk_id" . PHP_EOL);
                        // Empty desk
                        $client_id = null;
                    }
                }
                if (is_null($client_id)) {
                    $new_client_id = array_shift($queues[$desk_id]);
                    if (!is_null($new_client_id)) {
                        // Update desk
                        $client_id = $new_client_id;
                        // Update client
                        $client = &$clients[$new_client_id];
                        $client['queue_wait_duration'] = $time - $client['enters_queue_time'];
                        $client['desk_visited'] = $desk_id;
                        $desk_duration = rand($config['clients_min_desk_duration_seconds'], $config['clients_max_desk_duration_seconds']);
                        $client['leaves_desk_time'] = $time + $desk_duration;
                        $this->logger("ClientId $client_id enters deskId $desk_id for {$desk_duration}s. ");
                        $this->logger("Client waited {$client['queue_wait_duration']}s in queue. ");
                        $this->logger("Queue length is " . count($queues[$desk_id]) . "." . PHP_EOL);
                        unset($client);
                    }
                }
            }
            unset($client_id);
            $time++;
            usleep($config['simulation_wait_microseconds']);
            if ($config['display_simulation']) $this->multipleQueueToTxt($queues, $desks);

            // End simulation
            //if (count($clients) == $clients_count_max) {
            if ($time >= $config['office_open_duration_seconds']) {
                foreach($desks as $client_id) {
                    if (!is_null($client_id)) continue 2;
                }
                break;
            }
            //}
        }

        // Compute statistics

        $clients_count = count($clients);
        $this->logger("Clients count: $clients_count" . PHP_EOL);
        $column = array_column($clients, 'queue_wait_duration');
        $max_wait_duration = max($column);
        $this->logger("Max wait duration: {$max_wait_duration}s" . PHP_EOL);
        $average_wait_duration = round(array_sum($column) / $clients_count);
        $this->logger("Average wait duration: {$average_wait_duration}s" . PHP_EOL);

        return [$clients_count, $time, $max_wait_duration, $average_wait_duration];
    }

    private function multipleQueueToTxt(array $queues, array $desks) {

        $txt = "== Multiple queues ==\n";
        foreach ($queues as $queue_id => $client_ids) {
            $txt .= "Queue $queue_id : " . implode(" ", $client_ids) . PHP_EOL;
            $client_id = $desks[$queue_id];
            $txt .= "Desk $queue_id : $client_id"  . PHP_EOL;
        }
        file_put_contents(__DIR__ . "/out.txt", $txt);
    }
}