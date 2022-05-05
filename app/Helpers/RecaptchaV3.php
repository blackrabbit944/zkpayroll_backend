<?php
namespace App\Helpers;

use App\Models\Verify;
use Illuminate\Support\Facades\Log;


use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;

/**
 * 
 */
class RecaptchaV3
{
    ///检查用户是否是club的用户
    static function getRank($action_name,$value) {

        $client = new RecaptchaEnterpriseServiceClient();

        $site_key = config('recaptcha.server_key');
        $assessment_name = 'login_validate';
        $parent_project = 'projects/dreamcog';


        $event = (new Event())
             ->setSiteKey($site_key)
             ->setExpectedAction($action_name)
             ->setToken($value);

        $assessment = (new Assessment())
             ->setEvent($event)
             ->setName($assessment_name);

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
                     return $score;
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

         return false;
    }
}