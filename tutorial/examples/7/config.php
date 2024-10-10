<?php

$config = [
    'ssh_host' => 'g1.nuaje.fr',
    'ssh_port' => 2024,
    'ssh_server_fingerprint' => 'C84E59DA405A26A7DA6340D2EA9E43CF',
    'ssh_auth_user' => 'afup',
    'ssh_auth_private_key' => __DIR__ . '/id_ed25519',
    'ssh_auth_public_key' => __DIR__ . '/id_ed25519.pub',
    'ssh_auth_private_key_passphrase' => "afup2024!",
    'concurrency' => 2,
    'commands_count' => 20,
    'command' => 'echo Hello World',
];