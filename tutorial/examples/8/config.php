<?php

$config = [
    'ssh_host' => 'g1.nuaje.fr',
    'ssh_port' => 2024,
    'ssh_auth_user' => 'afup',
    'ssh_auth_private_key' => __DIR__ . '/id_ed25519',
    'commands_count' => 20,
    'command' => 'echo Hello World',
];