<?php
declare(strict_types=1);

namespace kaycn\PhpNats\stream;

use kaycn\PhpNats\config\ConnectOption;

/**
 * 实现连接
 **/
class NatsTransport
{
    protected const CR_LF = "\r\n";
    /**
     * @var null|resource
     **/
    protected $stream;
    protected array $serverInfo;
    protected int $chunkSize = 1500;

    protected int $timeout;

    protected string $url;

    protected ConnectOption $connectOption;

    public function __construct(string $url, ConnectOption $connectOption, int $timeout = 5)
    {
        $this->url = $url;
        $this->connectOption = $connectOption;
        $this->timeout = $timeout;
        $context = stream_context_get_default();

        // Create stream
        $errorCode = null;
        $errorMessage = null;

        $stream = stream_socket_client(
            $url,
            $errorCode,
            $errorMessage,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($stream === false) {
            throw new \RuntimeException(sprintf('Could not connect to %s with an timeout of %d secondes', $url, $timeout));
        }

        stream_set_timeout($stream, $timeout, 0);

        $this->stream = $stream;
        $str = substr(trim($this->receive()), 5);
        $serverInfo = json_decode($str, true);
        if (!isset($serverInfo['server_id'])) {
            throw new \RuntimeException('server connect failed');
        }
        $this->serverInfo = $serverInfo;
    }

    public function getServerInfo(){
        return $this->serverInfo;
    }

    public function connect(): string
    {
        //send connect message
//        $connectOptions = json_encode([
//            'verbose' => true,
//            'lang' => 'php',
//            'version' => '1.0.0',
//            'name' => 'kaycn-phpnats'
//        ]);
        $payload = sprintf('%s %s%s', NatsProtocolOperation::CONNECT, $this->connectOption->__toString(), self::CR_LF);
        $this->write($payload);
        return $this->receive();
    }

    protected function write(string $payload): void
    {
        $length = strlen($payload);

        while (true) {
            $written = @fwrite($this->getStream(), $payload);

            if ($written === false) {
                throw new \RuntimeException('Error sending data');
            }

            if ($written === 0) {
                throw new \RuntimeException('Broken pipe or closed connection');
            }

            $length -= $written;

            if ($length > 0) {
                $payload = substr($payload, (0 - $length));
            } else {
                break;
            }
        }
    }

    public function receive(int $length = 0): string
    {
        if ($length > 0) {
            $chunkSize = $this->chunkSize;
            $line = null;
            $receivedBytes = 0;

            while ($receivedBytes < $length) {
                $bytesLeft = ($length - $receivedBytes);

                if ($bytesLeft < $this->chunkSize) {
                    $chunkSize = $bytesLeft;
                }

                $readChunk = fread($this->getStream(), $chunkSize);
                $receivedBytes += strlen($readChunk);
                $line .= $readChunk;
            }
        } else {
            $line = fgets($this->getStream());
        }

        return $line;
    }

    protected function getStream()
    {
        if ($this->stream == null) {
            throw new \RuntimeException('stream is null');
        }
        return $this->stream;
    }

    public function ping()
    {
        $payload = sprintf('%s %s%s', 'PING', null, self::CR_LF);
        $this->write($payload);
    }

    public function close(): void
    {
        fclose($this->getStream());
        $this->stream = null;
    }

    public function publish($subject, string $payload)
    {
        $message = sprintf('%s %s', $subject, strlen($payload)) . self::CR_LF . $payload;
        $payload = sprintf('%s %s%s', NatsProtocolOperation::PUB, $message, self::CR_LF);
        $this->write($payload);
        return $this->receive();
    }

    public function subscribe(string $subject, int $uid = 1)
    {
        $message = sprintf('%s %s', $subject, $uid);
        $payload = sprintf('%s %s%s', NatsProtocolOperation::SUB, $message, self::CR_LF);
        $this->write($payload);
        return $this->receive();
    }

}