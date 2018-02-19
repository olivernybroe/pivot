<?php

namespace App\Http\Controllers;

use Mail;
use App\Election;
use App\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyController extends Controller
{
    public function __construct()
    {
        $this->middleware('web');
    }

    public function send_verify_email(Request $request)
    {
        // don't even try if the mail driver is not configured
        if (env('MAIL_DRIVER', null) == null) {
            return 'mail driver not configured';
        }

        $email = $request->json()->get('email');

        $verification = EmailVerification::where(['email' => $email])->first();
        if (empty($verification))
        {
            $verification = new EmailVerification();
            $verification->email = $email;
        }
        $verification->token = bin2hex(random_bytes(32));
        $verification->save();

        try {
            // TODO: send a real code
            $msg = 'Your Pivot Libre code is '.($verification->token);

            Mail::raw($msg, function ($message) use ($email) {
                $name = $email;
                $message->to($email, $name)->subject('Pivot Libre Email Confirmation');
            });
            return 'confirmation email sent';
        } catch(\Exception $e) {
            return $e->getMessage();
        }
    }
}