<?php 
namespace App\Models\Traits;

use Illuminate\Support\Facades\DB;


trait ErrorMessage {
    protected $error_message = '';

    private function setErrorMessage($msg) {
        $this->error_message = $msg;
    }

    public function getErrorMessage() {
        return $this->error_message;
    }
}
