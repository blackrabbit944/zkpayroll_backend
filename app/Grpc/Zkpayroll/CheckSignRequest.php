<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: zkpayroll.proto

namespace Zkpayroll;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * EthSign
 *
 * Generated from protobuf message <code>zkpayroll.CheckSignRequest</code>
 */
class CheckSignRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string sign = 1;</code>
     */
    protected $sign = '';
    /**
     * Generated from protobuf field <code>string wallet_address = 2;</code>
     */
    protected $wallet_address = '';
    /**
     * Generated from protobuf field <code>string sign_params = 3;</code>
     */
    protected $sign_params = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $sign
     *     @type string $wallet_address
     *     @type string $sign_params
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Zkpayroll::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string sign = 1;</code>
     * @return string
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * Generated from protobuf field <code>string sign = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setSign($var)
    {
        GPBUtil::checkString($var, True);
        $this->sign = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string wallet_address = 2;</code>
     * @return string
     */
    public function getWalletAddress()
    {
        return $this->wallet_address;
    }

    /**
     * Generated from protobuf field <code>string wallet_address = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setWalletAddress($var)
    {
        GPBUtil::checkString($var, True);
        $this->wallet_address = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string sign_params = 3;</code>
     * @return string
     */
    public function getSignParams()
    {
        return $this->sign_params;
    }

    /**
     * Generated from protobuf field <code>string sign_params = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setSignParams($var)
    {
        GPBUtil::checkString($var, True);
        $this->sign_params = $var;

        return $this;
    }

}

