<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $sid;
    protected $token;
    protected $twilioNumber;

    public function __construct()
    {
        $this->sid = env('TWILIO_SID');
        $this->token = env('TWILIO_AUTH_TOKEN');
        $this->twilioNumber = env('TWILIO_PHONE_NUMBER');
    }

    public function sendWhatsAppMessage($recipientNumber, $message)
    {
        try {
            $client = new Client($this->sid, $this->token);

            $client->messages->create(
                "+917867891000", // to
                array(
                "from" => "+13204000816",
                "body" => "Your Payment Successful"
                )
            );

            // Message sent successfully
            return true;
        } catch (\Exception $e) {
            // Log Twilio error
            \Log::error('Twilio Error: ' . $e->getMessage());
            // Handle the error as needed
            // For example: throw new Exception($e->getMessage());
            return false; // Return false indicating failure
        }
    }
}
