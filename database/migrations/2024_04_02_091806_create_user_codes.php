<?php

use App\Models\UserCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_codes', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('code');
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
        });
    }

    public function generateCodes()
    {
        $code = rand(1000, 9999);
        UserCode::updateOrCreate(
            ['user_id' => auth()->user()->id],
            ['code' => $code]
        );
        $receiver_number = auth()->user()->phone;
        $message = "2FA login code is " . $code;

        try {
            $account_sid = getenv("TWILIO_SID");
            $auth_token = getenv("TWILIO_TOKEN");
            $twilio_number = getenv("TWILIO_FROM");
            $client = new Client($account_sid, $auth_token);
            $client->messages->create($receiver_number, [
                'from' => $twilio_number,
                'body' => $message
            ]);
        } catch (Exception $e) {
            info("error:" . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_codes');
    }
};
