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

    // Set to 100 for maximum acceleration, recommended for benchmark
    // set to 10000 to accelerate time by 100
    // Set to 1000000 for real time
    'simulation_wait_microseconds' => 10000,

    // Write log of simulation
    // Set to false for benchmark
    'write_log' => true,

    // Display simulation
    // Set to false for benchmark
    'display_simulation' => true,

];