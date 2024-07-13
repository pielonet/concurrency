<?php

$config = [
    /*
     * Queue simulation
     */
    // Number of customer desks
    'desks_count' => 4,

    // Maximum number of clients
    'clients_count_max' => 120,

    // Time in seconds the office is open
    'office_open_duration_seconds' => 1 * 60 * 60,

    // The law by which arriving customer probability is computed
    // Possible values : (linear|normal)
    'arrival_probability_law' => 'normal', 

    // Parameters for normal probability
    // Distribution is centered at $peak_time_minutes and has a standard deviation of $standard_deviation_minutes
    // peak_time_minutes*60 must reside inside the boundaries of office_open_duration_seconds
    'peak_time_minutes' => 30,
    'standard_deviation_minutes' => 20,
    // linear check

    // Minimum duration in seconds a client remains at desk
    'clients_min_desk_duration_seconds' => 30,
    // Maximum duration in seconds a client remains at desk
    'clients_max_desk_duration_seconds' => 200,

    /*
     * Benchmark
     */
    // Set to 0 for no wait (maximum acceleration, recommended for benchmark), set to 10000 to accelerate time by 100
    'simulation_wait_microseconds' => 0,

    // Set to false for benchmark
    'write_log' => false,

    // Number of simulations to run
    'iterations_count' => 100,
    
    // Number of threads to use
    // Set to 0 for sequential processing
    // Set to a value greater than 2 for parallel processing
    // 10 parallel threads is a good starting point
    'simulate_threads_count' => 10,

];