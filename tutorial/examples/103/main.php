<?php
/**
 * Fibonacci
 * Mimics the Go "select" statement with event-loop
 * Variant with non-blocking loop and $events->poll()
 * 
 * Adapted from https://go.dev/tour/concurrency/5
 */

use \parallel\{Channel,Events,Events\Event,Events\Input};

function fibonacci (Channel $channel, Channel $quit) {
    $x = 0;
    $y = 1;

    $events = new Events();
    $events->addChannel($channel);
    $events->addChannel($quit);
    $input = new Input();
    $input->add('fibonacci', $x);
    $events->setInput($input);
    $events->setBlocking(false);

    while (true) {
        if ($event = $events->poll()) {
            switch ($event->source) {
                case 'fibonacci':
                    if ($event->type == Event\Type::Write) {
                        $c = $x;
                        $x = $y;
                        $y += $c;

                        $events->addChannel($channel);
                        $input->add('fibonacci', $x);
                        $events->setInput($input);
                    }
                    break;

                case 'quit':
                    echo "quit\n";
                    return;
            }
        }
    }
}

$channel = Channel::make('fibonacci', 1);
$quit = Channel::make('quit');

\parallel\run(function (Channel $channel, Channel $quit) {
    for ($i=0; $i < 10; $i++) {
        $e = $channel->recv();
        echo "$e\n";
    }
    $quit->send(0);
}, [$channel, $quit]);

fibonacci($channel, $quit);
