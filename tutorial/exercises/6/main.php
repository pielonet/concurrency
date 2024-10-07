<?php

/**
 * - Create two parallel tasks that exchange "ping"
 *   and "pong" messages every second
 *   over an unbuffered channel, indefinitely, 
 * - DO NOT FORGET to close the channel in the main thread
 *   after 10 seconds to interrupt the script
 * 
 * - Modify your script to smoothly handle the error that arises
 */