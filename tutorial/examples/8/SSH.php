<?php

class SSH {

    // SSH Host

    private $ssh_host = 'myserver.example.com';

    // SSH Port

    private $ssh_port = 22;

    // SSH Username

    private $ssh_auth_user = 'username';

    // SSH Private Key File

    private $ssh_auth_private_key = '/home/username/.ssh/id_rsa';

    // SSH Connection

    private $connection;

    public function __construct(array $config) {
        $this->ssh_host = $config['ssh_host'];
        $this->ssh_port = $config['ssh_port'];
        $this->ssh_auth_user = $config['ssh_auth_user'];
        $this->ssh_auth_private_key = $config['ssh_auth_private_key'];
    }


    public function exec($cmd) {
        $ssh_option = "-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ConnectTimeout=2";
        exec("timeout 3 ssh $ssh_option -i {$this->ssh_auth_private_key} -p{$this->ssh_port} {$this->ssh_auth_user}@{$this->ssh_host} $cmd 2>/dev/null", $output, $result_code);
        return implode("\n", $output);
    }
}

?>