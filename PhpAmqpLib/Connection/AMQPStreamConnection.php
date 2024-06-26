<?php

namespace PhpAmqpLib\Connection;

use PhpAmqpLib\Wire\IO\StreamIO;

class AMQPStreamConnection extends AbstractConnection
{
    /**
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $password
     * @param string $vhost
     * @param bool $insist
     * @param string $login_method
     * @param null $login_response @deprecated
     * @param string $locale
     * @param float $connection_timeout
     * @param float $read_write_timeout
     * @param resource|array|null $context
     * @param bool $keepalive
     * @param int $heartbeat
     * @param float $channel_rpc_timeout
     * @param string|AMQPConnectionConfig|null $ssl_protocol @deprecated
     * @param AMQPConnectionConfig|null $config
     * @throws \Exception
     */
    public function __construct(
        $host,
        $port,
        $user,
        $password,
        $vhost = '/',
        $insist = false,
        $login_method = 'AMQPLAIN',
        $login_response = null,
        $locale = 'en_US',
        $connection_timeout = 3.0,
        $read_write_timeout = 3.0,
        $context = null,
        $keepalive = false,
        $heartbeat = 0,
        $channel_rpc_timeout = 0.0,
        $ssl_protocol = null,
        ?AMQPConnectionConfig $config = null
    ) {
        if ($ssl_protocol !== null && $ssl_protocol instanceof AMQPConnectionConfig === false) {
            trigger_error(
                '$ssl_protocol parameter is deprecated, use stream_context_set_option($context, \'ssl\', \'crypto_method\', $ssl_protocol) instead (see https://www.php.net/manual/en/function.stream-socket-enable-crypto.php for possible values)',
                E_USER_DEPRECATED
            );
        } elseif ($ssl_protocol instanceof AMQPConnectionConfig) {
            $config = $ssl_protocol;
        }

        if ($channel_rpc_timeout > $read_write_timeout) {
            throw new \InvalidArgumentException('channel RPC timeout must not be greater than I/O read-write timeout');
        }

        $io = new StreamIO(
            $host,
            $port,
            $connection_timeout,
            $read_write_timeout,
            $context,
            $keepalive,
            $heartbeat
        );

        parent::__construct(
            $user,
            $password,
            $vhost,
            $insist,
            $login_method,
            $login_response,
            $locale,
            $io,
            $heartbeat,
            $connection_timeout,
            $channel_rpc_timeout,
            $config
        );

        // save the params for the use of __clone, this will overwrite the parent
        $this->construct_params = func_get_args();
    }

    /**
     * @deprecated Use AmqpConnectionFactory
     * @throws \Exception
     */
    protected static function try_create_connection($host, $port, $user, $password, $vhost, $options)
    {
        $insist = isset($options['insist']) ?
                        $options['insist'] : false;
        $login_method = isset($options['login_method']) ?
                              $options['login_method'] : 'AMQPLAIN';
        $login_response = isset($options['login_response']) ?
                                $options['login_response'] : null;
        $locale = isset($options['locale']) ?
                        $options['locale'] : 'en_US';
        $connection_timeout = isset($options['connection_timeout']) ?
                                    $options['connection_timeout'] : 3.0;
        $read_write_timeout = isset($options['read_write_timeout']) ?
                                    $options['read_write_timeout'] : 3.0;
        $context = isset($options['context']) ?
                         $options['context'] : null;
        $keepalive = isset($options['keepalive']) ?
                           $options['keepalive'] : false;
        $heartbeat = isset($options['heartbeat']) ?
                           $options['heartbeat'] : 60;
        $channel_rpc_timeout = isset($options['channel_rpc_timeout']) ?
                                    $options['channel_rpc_timeout'] : 0.0;
        return new static(
            $host,
            $port,
            $user,
            $password,
            $vhost,
            $insist,
            $login_method,
            $login_response,
            $locale,
            $connection_timeout,
            $read_write_timeout,
            $context,
            $keepalive,
            $heartbeat,
            $channel_rpc_timeout
        );
    }
}
