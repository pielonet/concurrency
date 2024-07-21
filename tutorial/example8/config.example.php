<?php

$config = [
    'ssh_host' => 'myhost.example.com',
    'ssh_port' => 22,
    'ssh_auth_user' => 'testuser',
    'ssh_auth_private_key' => __DIR__ . '/id_ed25519',
    'concurrency' => 2,
    'commands_count' => 20,
    'command' => "echo hello world",
];