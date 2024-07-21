<?php

class SSH2 {

    // SSH Host

    private $ssh_host = 'myserver.example.com';

    // SSH Port

    private $ssh_port = 22;

    // SSH Server Fingerprint

    private $ssh_server_fingerprint = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    // SSH Username

    private $ssh_auth_user = 'username';

    // SSH Public Key File

    private $ssh_auth_public_key = '/home/username/.ssh/id_rsa.pub';

    // SSH Private Key File

    private $ssh_auth_private_key = '/home/username/.ssh/id_rsa';

    // SSH Private Key Passphrase (null == no passphrase)

    private $ssh_auth_private_key_passphrase;

    // SSH Connection

    private $connection;

    public function __construct(array $config) {
        $this->ssh_host = $config['ssh_host'];
        $this->ssh_port = $config['ssh_port'];
        $this->ssh_server_fingerprint = $config['ssh_server_fingerprint'];
        $this->ssh_auth_user = $config['ssh_auth_user'];
        $this->ssh_auth_public_key = $config['ssh_auth_public_key'];
        $this->ssh_auth_private_key = $config['ssh_auth_private_key'];
        $this->ssh_auth_private_key_passphrase = $config['ssh_auth_private_key_passphrase'];
        $this->connect();
    }

    public function connect() {

        if (!($this->connection = \ssh2_connect($this->ssh_host, $this->ssh_port))) {

            throw new Exception('Cannot connect to server');

        }

        $fingerprint = \ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);


        if (strcmp($this->ssh_server_fingerprint, $fingerprint) !== 0) {

            throw new Exception('Unable to verify server identity!');

        }

        if (!\ssh2_auth_pubkey_file($this->connection, $this->ssh_auth_user, $this->ssh_auth_public_key, $this->ssh_auth_private_key, $this->ssh_auth_private_key_passphrase)) {

            throw new Exception('Autentication rejected by server');

        }

    }

    public function exec($cmd) {

        if (!($stream = ssh2_exec($this->connection, $cmd))) {

            throw new Exception('SSH command failed');

        }

        stream_set_blocking($stream, true);

        $data = "";

        while ($buf = fread($stream, 4096)) {

            $data .= $buf;

        }

        fclose($stream);

        return $data;

    }

    public function disconnect() {

        $this->exec('echo "EXITING" && exit;');

        $this->connection = null;

    }

    public function __destruct() {

        $this->disconnect();

    }

}

?>