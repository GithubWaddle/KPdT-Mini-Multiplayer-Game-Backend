<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Matchmaking;

/**
 */
class RankingServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Matchmaking\UpdateRankingRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Matchmaking\UpdateRankingResponse>
     */
    public function UpdateRanking(\Matchmaking\UpdateRankingRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/matchmaking.RankingService/UpdateRanking',
        $argument,
        ['\Matchmaking\UpdateRankingResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Matchmaking\GetPlayerRankRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Matchmaking\GetPlayerRankResponse>
     */
    public function GetPlayerRank(\Matchmaking\GetPlayerRankRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/matchmaking.RankingService/GetPlayerRank',
        $argument,
        ['\Matchmaking\GetPlayerRankResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Matchmaking\CompensateRankingRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall<\Matchmaking\CompensateRankingResponse>
     */
    public function CompensateRanking(\Matchmaking\CompensateRankingRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/matchmaking.RankingService/CompensateRanking',
        $argument,
        ['\Matchmaking\CompensateRankingResponse', 'decode'],
        $metadata, $options);
    }

}
