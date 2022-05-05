<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: tinyclub.proto

namespace Tinyclub;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>tinyclub.UpdateChannelNameRequest</code>
 */
class UpdateChannelNameRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>uint64 guild_id = 1;</code>
     */
    protected $guild_id = 0;
    /**
     * Generated from protobuf field <code>string data_string = 2;</code>
     */
    protected $data_string = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int|string $guild_id
     *     @type string $data_string
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Tinyclub::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>uint64 guild_id = 1;</code>
     * @return int|string
     */
    public function getGuildId()
    {
        return $this->guild_id;
    }

    /**
     * Generated from protobuf field <code>uint64 guild_id = 1;</code>
     * @param int|string $var
     * @return $this
     */
    public function setGuildId($var)
    {
        GPBUtil::checkUint64($var);
        $this->guild_id = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string data_string = 2;</code>
     * @return string
     */
    public function getDataString()
    {
        return $this->data_string;
    }

    /**
     * Generated from protobuf field <code>string data_string = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setDataString($var)
    {
        GPBUtil::checkString($var, True);
        $this->data_string = $var;

        return $this;
    }

}
