<?php

return [

    /*
    | Signed verification link lifetime (minutes).
    */
    'expire' => (int) env('EMAIL_VERIFICATION_EXPIRE', 60),

    /*
    | Resend cooldown is enforced via route throttle (verification.send).
    */
    'resend_throttle' => env('EMAIL_VERIFICATION_RESEND_THROTTLE', '3,1'),

];
