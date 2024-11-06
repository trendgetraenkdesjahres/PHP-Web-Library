<?php

namespace PHP_Library\WebSocketServer;

class WebSocketServer
{
    /** @var array Array callables for handling receiving a sending messages.*/
    protected array $actions = [];

    /** @var array Array of connected clients */
    protected array $clients = [];

    /** @var \Socket Main server socket */
    protected \Socket $socket;

    /** @var int Timestamp when listening started */
    protected int $listening_started;

    /** @var string File path for process locking */
    protected string $lock_file;

    /** @var int Maximum server uptime in seconds */
    protected int $max_duration;

    /**
     * Constructor to initialize the WebSocket server.
     */
    public function __construct()
    {
        $this->lock_file = strtolower(str_replace("\\", '', __CLASS__));
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    }

    /**
     * Destructor to shutdown the server socket.
     */
    public function __destruct()
    {
        socket_close($this->socket);
    }

    /**
     * Starts the WebSocket server to listen for incoming connections.
     *
     * @param string $host Host address to bind to.
     * @param int $port Port to listen on.
     * @param int $max_duration Maximum duration to keep the server running.
     */
    public function listen(string $host, int $port, int $max_duration = 60, bool $force_restart = false): void
    {
        $this->max_duration = $max_duration;
        if (!$force_restart && $this->has_listening_subprocess()) {
            return;
        }
        if (! $this->daemonize()) {
            return;
        }
        if (!socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
            throw new \Error("Failed to setup socket: " . socket_strerror(socket_last_error($this->socket)));
        }
        if (!socket_bind($this->socket, $host, $port)) {
            throw new \Error("Failed to bind $host:$port to socket: " . socket_strerror(socket_last_error($this->socket)));
        }
        if (!socket_listen($this->socket)) {
            throw new \Error("Failed to listen to socket: " . socket_strerror(socket_last_error($this->socket)));
        };
        while ($this->is_within_time_limit()) {
            $this->process_perception();
        }
    }
    /**
     * Adds a callable action to be executed for incoming messages.
     *
     * The callable should accept two parameters:
     * 1. string $message: The decoded message received from the client.
     * 2. \Socket $socket: The socket associated with the client.
     *
     * The callable can return a string or null. If it returns anything but null, it will be sent back to the client.
     *
     * @param callable $action The callable action to be executed on message receipt.
     * @param bool $return_json (optional) Whether to return the response as JSON (default: true).
     * @return static Returns the instance of the WebSocketServer for method chaining.
     */
    public function add_action(callable $action, bool $return_json = true): static
    {
        $this->actions[] = ['callable' => $action, 'return_json' => $return_json];
        return $this;
    }

    /**
     * Processes incoming data from clients and manages new connections.
     *
     * @return static
     */
    protected function process_perception(): static
    {
        $read_sockets = $this->get_active_read_sockets();
        $this->connect_client($read_sockets);

        foreach ($this->clients as $host_name => $socket) {
            $payload = socket_read($socket, 1024, PHP_NORMAL_READ);




            $this->execute_actions($payload, $socket);
        }
        return $this;
    }

    /**
     * Executes the actions for the given payload and socket.
     *
     * @param string $payload The received payload from the client.
     * @param \Socket $socket The socket associated with the client.
     * @return static
     */
    protected function execute_actions(string $payload, \Socket $socket): static
    {
        foreach ($this->actions as $action) {
            $message = static::unmask($payload);
            if (is_null($message = call_user_func($action['callable'], $message, $socket))) {
                continue;
            }
            $message = $action['json_decode'] ? (string) json_decode($message) : (string) $message;
            $message = static::mask($message);
            socket_write($socket, $message, strlen($message));
        }
        return $this;
    }

    /**
     * Gets active sockets ready for reading within a set timeout.
     *
     * @param int $max_timeout Maximum wait time in milliseconds.
     * @return array List of sockets ready for reading.
     */
    protected function get_active_read_sockets(int $max_timeout = 10): array
    {
        $read_sockets = $this->clients + [$this->socket];
        $null = [];
        socket_select($read_sockets, $null, $null, 0, $max_timeout);
        return $read_sockets;
    }

    /**
     * Accepts new client connections.
     *
     * @param array $sockets Array of sockets to check for new connections.
     * @return static
     * @throws Error If the connection cannot be accepted.
     */
    protected function connect_client(array $sockets): static
    {
        if (! in_array($this->socket, $sockets)) {
            return $this;
        }
        if (! $new_client = socket_accept($this->socket)) {
            throw new \Error(socket_last_error());
        }
        static::handshake($new_client);
        socket_getpeername($new_client, $host_name = '');
        $this->clients[$host_name] = $new_client;
        return $this;
    }

    /**
     * Handles client disconnection
     *
     * @param \Socket $socket The socket of the disconnected client
     * @return void
     */
    protected function disconnect_client(string $host_name): static
    {
        // Close the client's socket
        socket_close($this->clients[$host_name]);

        // Remove the client from the list of connected clients
        unset($this->clients[$host_name]);
        return $this;
    }

    /**
     * Forks the process to handle the WebSocket server in a child process
     *
     * @return bool True if running in child process, false if in parent process
     * @throws \Error if forking fails
     */
    private function daemonize(): bool
    {
        // Check if a lock file exists and handle existing processes
        if ($this->lock_file && file_exists($this->lock_file)) {
            $existing_pid = (int)file_get_contents($this->lock_file);
            if (posix_kill($existing_pid, 0)) {
                return false; // already running;
            } else {
                unlink($this->lock_file); // Remove stale lock file
            }
        }

        // First fork to create the child process
        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \Error("Failed to fork the initial process.");
        } elseif ($pid > 0) {
            // Exit the parent process so the child can proceed
            exit(0);
        }

        // Create a new session to detach the child process
        if (posix_setsid() < 0) {
            throw new \Error("Failed to create a new session.");
        }

        // Second fork to ensure the process is daemonized
        $pid = pcntl_fork();
        if ($pid < 0) {
            throw new \Error("Failed to fork the daemonized process.");
        } elseif ($pid > 0) {
            // Exit the second parent process
            exit(0);
        }

        // The child process continues from here as a daemon
        // Write the PID to the lock file for monitoring/control
        if (!file_put_contents($this->lock_file, $pid)) {
            throw new \Error("Failed to create subprocess-lockfile: '$this->lock_file'.");
        }

        // Record the start time
        $this->listening_started = time();
        return true;
    }

    /**
     * Checks if the server is within the time limit for listening
     *
     * @return bool True if within time limit, false otherwise
     * @throws \Error if not listening yet
     */
    private function is_within_time_limit(): bool
    {
        if (!isset($this->listening_started)) {
            throw new \Error("Not listening yet.");
        }
        return (time() - $this->listening_started) <= $this->max_duration;
    }

    /**
     * Checks if there is a listening subprocess
     *
     * @return bool True if a subprocess is listening, false otherwise
     */
    private function has_listening_subprocess(): bool
    {
        if (!file_exists($this->lock_file)) {
            return false;
        }
        $this->listening_started = filectime($this->lock_file);
        if (!$this->is_within_time_limit()) {
            unlink($this->lock_file);
            return false;
        }
        return true;
    }

    /**
     * Performs the WebSocket handshake with a new client
     *
     * @param \Socket $client_socket The client socket resource
     * @return bool True if handshake was successful, false otherwise
     */
    protected static function handshake(\Socket $client_socket)
    {
        $pattern = '/Sec-WebSocket-Key:\s*(.+)\r?\n/';
        preg_match($pattern, socket_read($client_socket, 1024), $matches);
        if (! isset($matches[1])) {
            return false;
        }
        $secAccept = base64_encode(pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

        // Create the upgrade response for the WebSocket handshake
        $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: $secAccept\r\n\r\n";

        // Send the handshake response to the client
        socket_write($client_socket, $upgrade, strlen($upgrade));
    }

    /**
     * Encodes a message for WebSocket transmission.
     *
     * @param string $text Message text.
     * @return string Encoded WebSocket frame.
     */
    protected static function mask($text): string
    {
        $b1 = 0x81;
        $length = strlen($text);
        $header = self::get_header($b1, $length);
        return $header . $text;
    }

    /**
     * Decodes a received WebSocket frame.
     *
     * @param string $text Encoded WebSocket frame.
     * @return string Decoded message.
     */
    protected static function unmask(string $payload): string
    {
        $header_length = self::get_mask_start($payload);
        $masks = substr($payload, $header_length, 4);
        $data = substr($payload, $header_length + 4);
        $decodedText = '';
        for ($i = 0, $dataLen = strlen($data); $i < $dataLen; ++$i) {
            $decodedText .= $data[$i] ^ $masks[$i % 4];
        }
        return $decodedText;
    }

    /**
     * Generates the WebSocket frame header
     *
     * @param int $b1 First byte of the header
     * @param int $length Length of the payload
     * @return string The constructed header
     */
    private static function get_header($b1, $length): string
    {
        if (
            $length <= 125
        ) {
            return pack('CC', $b1, $length);
        } elseif (
            $length <= 65535
        ) {
            return pack('CCn', $b1, 126, $length);
        } else {
            return pack('CCNN', $b1, 127, 0, $length);
        }
    }

    /**
     * Gets the start position of the masking key based on the payload length
     *
     * @param string $payload The raw payload data from the WebSocket frame
     * @return int The starting position for the masking key
     */
    private static function get_mask_start(string $payload): int
    {
        $length = ord($payload[1]) & 127;

        // Define positions based on length for unmasking convenience
        if ($length === 126) {
            return 4;  // Mask starts after 2 header bytes (1 byte + 1 byte for length) + 2 bytes for length itself
        } elseif ($length === 127) {
            return 10; // Mask starts after 2 header bytes + 8 bytes for length
        } else {
            return 2;  // Mask starts immediately after the first 2 header bytes
        }
    }
}
