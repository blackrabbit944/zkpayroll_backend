<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Log;

use App\Rules\ContractAddress;

use Illuminate\Support\Arr;

class UploadRequest extends BaseRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        //暂时让所有的验证都通过
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $action =  get_current_method_name();

        switch ($action) {
            case 'img':

                // Log::debug('request img:');
                $image_size = $this->getImageSize();

                // Log::debug('调用以后我们测试代码的是:'.$request_width.'.+.'.$request_height);
                return [
                    'template'  =>  ['required_without_all:width,height',Rule::in(['avatar', 'post_image','cover'])],
                    'file'      =>  ['required','mimes:jpeg,bmp,png,gif',Rule::dimensions(['min_width'=>$image_size['request_width'], 'min-height'=>$image_size['request_height']])],
                    'width'     =>  ['required_without:template','integer'],
                    'height'    =>  ['required_without:template','integer'],
                ];
                break;

            default:
                return [
                ];
        }


    }

    public function getImageSize() {
        $template_config = config('image.template');
        $flattened_temp_config = Arr::dot($template_config);

        $request_width = 10;
        $request_height = 10;
        if ($this->input('template')) {
            $t = $this->input('template');
            if (isset($flattened_temp_config[$t .'.min_width'])) {
                $request_width = $flattened_temp_config[$t .'.min_width'];
            }
            if (isset($flattened_temp_config[$t .'.min_height'])) {
                $request_height = $flattened_temp_config[$t .'.min_height'];
            }
        }

        if ($this->input('width') && $this->input('height')) {
            $request_width = $this->input('width');
            $request_height = $this->input('height');
        }
        return [
            'request_height'    =>  $request_height,
            'request_width'     =>  $request_width
        ];
    }


    public function messages(): array
    {
        
        $image_size = $this->getImageSize();
        $min_size = sprintf('%d px * %d px',$image_size['request_width'],$image_size['request_height']);

        return [
            // 'file.mimes' =>'image must be jpeg, bmp, png, gif',
            'file.dimensions'   =>  'image is too small, Minimum '.$min_size.' is required.'
        ];
    }


}

