<?php
namespace App\Events;

class CheckNftHolderEvent extends Event
{

    public $contract_address;
    public $address;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(String $contract_address,String $address)
    {
        $this->contract_address = $contract_address;
        $this->address = $address;
    }

    public function getContractAddress() {
        return $this->contract_address;
    }

    public function getAddress() {
        return $this->address;
    }

}
