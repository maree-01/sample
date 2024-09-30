<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Services\TwilioService;

class PaymentController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function index()
    {
        return view('payment');
    }

    public function checkout(Request $request)
    {
        $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));

        $order = $api->order->create([
            'amount' => 50000, // Amount in paise (Rs. 500)
            'currency' => 'INR',
            'receipt' => 'order_rcptid_11',
            'payment_capture' => 1
        ]);

        return view('checkout', compact('order'));
    }

    public function callback(Request $request)
    {
        // Validate the request
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        // Retrieve the payment details from the request
        $paymentId = $request->razorpay_payment_id;
        $orderId = $request->razorpay_order_id;
        $signature = $request->razorpay_signature;

        // Verify the payment signature
        $success = false;
        $error = null;

        try {
            $api = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ];

            $api->utility->verifyPaymentSignature($attributes);
            $success = true;
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            $error = 'Razorpay Signature Verification Failed';
        }

        // Process the payment based on success or failure
        if ($success) {
            // Payment successful logic

            // Send WhatsApp message
            $recipientNumber = '+917867891000'; // Ensure the correct format
            $message = 'Payment received successfully!';
            $msgSent = $this->twilioService->sendWhatsAppMessage($recipientNumber, $message);

            if ($msgSent) {
                // Message sent successfully
                \Log::info('WhatsApp message sent successfully after payment.');
            } else {
                // Message sending failed
                \Log::error('Failed to send WhatsApp message after payment.');
            }

            // Return success response
            return response()->json(['success' => true, 'message' => 'Payment successful']);
        } else {
            // Payment failed logic
            // Log the error or perform necessary actions

            // Return failure response
            return response()->json(['success' => false, 'message' => $error]);
        }
    }
}
