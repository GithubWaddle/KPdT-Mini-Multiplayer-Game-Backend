<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Matchmaking;

/**
 * NEW — User service contract
 */
class UserServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Matchmaking\ValidatePlayerRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Matchmaking\ValidatePlayerResponse>
     */
    public function ValidatePlayer(\Matchmaking\ValidatePlayerRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/matchmaking.UserService/ValidatePlayer',
        $argument,
        ['\Matchmaking\ValidatePlayerResponse', 'decode'],
        $metadata, $options);
    }

}
