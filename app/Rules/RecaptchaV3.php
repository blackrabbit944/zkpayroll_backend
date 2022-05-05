<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;

class RecaptchaV3 implements Rule
{

    protected $action_name;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($action_name,$threshold = 0.3)
    {
        //
        $this->action_name = $action_name;
        $this->threshold = $threshold;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {


        $client = new RecaptchaEnterpriseServiceClient();

        $site_key = config('recaptcha.server_key');
        // $assessment_name = 'login_validate';
        $action_name = $this->action_name;
        $threshold = $this->threshold;
        $parent_project = 'projects/dreamcog';


        Log::debug('debug:recaptcha等待验证的token:'.$value);
        Log::debug('debug:recaptcha，网站的key:'.$site_key);
        Log::debug('debug:recaptcha，设置要求的阈值:'.$threshold);

        $event = (new Event())
             ->setSiteKey($site_key)
             ->setExpectedAction($action_name)
             ->setToken($value);

        $assessment = (new Assessment())
             ->setEvent($event);

         try {
             $response = $client->createAssessment(
                 $parent_project,
                 $assessment
             );


             if ($response->getTokenProperties()->getValid() == false) {
                 Log::info('The CreateAssessment() call failed because the token was invalid for the following reason: ');
                 Log::info(InvalidReason::name($response->getTokenProperties()->getInvalidReason()));
                return false;
             } else {
                 if ($response->getEvent()->getExpectedAction() == $action_name) {
                     Log::info('The score for the protection action is:');
                     Log::info($response->getRiskAnalysis()->getScore());
                     $score = $response->getRiskAnalysis()->getScore();
                     if ($score >= $threshold) {
                        return true;
                     }else {
                        return false;
                     }
                 }
                 else
                 {
                    return false;
                    Log::info('The action attribute in your reCAPTCHA tag does not match the action you are expecting to score');
                 }
             }
         } catch (exception $e) {
             Log::info('CreateAssessment() call failed with the following error: ');
             Log::info($e);
             return false;
         }

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The recaptcha token is not allowed.';
    }
}
