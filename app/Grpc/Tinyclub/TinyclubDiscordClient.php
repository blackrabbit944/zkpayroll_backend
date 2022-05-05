<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Tinyclub;

/**
 */
class TinyclubDiscordClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Tinyclub\CheckSignRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function CheckSign(\Tinyclub\CheckSignRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/tinyclub.TinyclubDiscord/CheckSign',
        $argument,
        ['\Tinyclub\CheckSignReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Tinyclub\InitDiscordGuildRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function InitDiscordGuild(\Tinyclub\InitDiscordGuildRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/tinyclub.TinyclubDiscord/InitDiscordGuild',
        $argument,
        ['\Tinyclub\InitDiscordGuildReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Tinyclub\AddDiscordRoleRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function AddDiscordRole(\Tinyclub\AddDiscordRoleRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/tinyclub.TinyclubDiscord/AddDiscordRole',
        $argument,
        ['\Tinyclub\AddDiscordRoleReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Tinyclub\RemoveDiscordRoleRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function RemoveDiscordRole(\Tinyclub\RemoveDiscordRoleRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/tinyclub.TinyclubDiscord/RemoveDiscordRole',
        $argument,
        ['\Tinyclub\RemoveDiscordRoleReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Tinyclub\UpdateChannelNameRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function UpdateChannelName(\Tinyclub\UpdateChannelNameRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/tinyclub.TinyclubDiscord/UpdateChannelName',
        $argument,
        ['\Tinyclub\UpdateChannelNameReply', 'decode'],
        $metadata, $options);
    }

}
