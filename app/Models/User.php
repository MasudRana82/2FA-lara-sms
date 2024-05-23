<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Twilio\Rest\Client;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * generate OTP and send sms
     *
     * @return response()
     */
    public function generateCode()
    {
        $code = rand(100000, 999999);

        UserCode::updateOrCreate([
            'user_id' => auth()->user()->id,
            'code' => $code
        ]);

        $receiverNumber = auth()->user()->phone;
        $message = "Your 6 Digit OTP Number is " . $code;

        try {
            $account_sid = env("TWILIO_SID");
            $auth_token = env("TWILIO_TOKEN");
            $from_number = env("TWILIO_FROM");

            $client = new Client($account_sid, $auth_token);
            // dd($client);
            $client->messages->create($receiverNumber, [
                'from' => $from_number,
                'body' => $message
            ]);
        } catch (\Exception $e) {
            dd($e);
        }
    }
}
