<?php

namespace App\Services;

class BaseService 
{
    public function __construct($model)
    {
        $this->model = $model;
    }

}
