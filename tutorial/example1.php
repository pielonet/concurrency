<?php

\parallel\run(
    function() {
        sleep(5);
        echo(" la mouche");
    },
    []
);

\parallel\run(
    function() {
        sleep(2);
        echo("zobi");
    },
    []
);

// Result : "zobi la mouche"