<?php
/* 
 * Molière !
 */

$sentence = "marquise vos beaux yeux me font mourir d'amour";
$words = explode(" ", $sentence);

foreach($words as $word) {
    \parallel\run(
        function($word) {
            usleep(rand(1, 10000000));
            echo("$word ");
        },
        [$word]
    );
}

echo("$sentence". PHP_EOL);

