<?php 
namespace App\Models\Traits;


trait UnixTimestamp {
    public function getUpdateTimeAttribute($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        return strtotime($value);
    }
    public function getCreateTimeAttribute($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        return strtotime($value);
    }
    public function getDeleteTimeAttribute($value)
    {
        if ($value) {
            if (is_numeric($value)) {
                return $value;
            }
            return strtotime($value);
        }else {
            return null;
        }
    }
    
    
    public function getDateFormat()
    {
        return 'U';
    }
}
