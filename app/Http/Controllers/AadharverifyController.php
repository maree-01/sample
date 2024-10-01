<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Aws\Lambda\LambdaClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class AadharverifyController extends Controller
{

    protected $lambdaClient;

    public function __construct()
    {
        $this->lambdaClient = new LambdaClient([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION'), // e.g., 'us-east-1'
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    public function getAccessToken()
{
    $params = [
        'FunctionName' => 'aadhar_validation', // Replace with your Lambda function name
        'Payload' => json_encode([
            'action' => 'getAccessToken' // Adjust action name to match your Lambda function
        ]),
    ];

    try {
        $result = $this->lambdaClient->invoke($params);
        $responsePayload = json_decode($result['Payload']->getContents(), true);
        Log::info('Access token:', $responsePayload);
        return response()->json($responsePayload);
    } catch (AwsException $e) {
        return response()->json(['message' => 'Error invoking Lambda function to get access token'], 500);
    }
}

public function sendOtp(Request $request)
{
    // Log the incoming request data
    Log::info('Incoming request to send OTP:', $request->all());

    $aadharNo = $request->input('aadhar_no');
    Log::info('Aadhaar number received:', ['aadhar_no' => $aadharNo]);
    
    // Call to get access token
    $accessTokenResponse = $this->getAccessToken();
    Log::info('Access token request sent.');

    if ($accessTokenResponse->getStatusCode() === 200) {
        $accessTokenData = json_decode($accessTokenResponse->getContent(), true);
        Log::info('Access token data retrieved successfully:', ['token_data' => $accessTokenData]);
    
        // Decode the body, which is a JSON string, to access the token
        $bodyData = json_decode($accessTokenData['body'], true);
        $accessToken = $bodyData['token'] ?? null;
    
        Log::info('Access token retrieved successfully:', ['token' => $accessToken]);
    }else {
        Log::error('Failed to retrieve access token.', [
            'status_code' => $accessTokenResponse->getStatusCode(),
            'response_content' => $accessTokenResponse->getContent(),
        ]);
        return response()->json(['message' => 'Failed to retrieve access token.'], 500);
    }
    
    // Validate inputs
    if (!$aadharNo || !$accessToken) {
        Log::warning('Validation failed: Aadhaar number or access token is missing.', [
            'aadhar_no' => $aadharNo,
            'access_token' => $accessToken,
        ]);
        return response()->json(['message' => 'Aadhaar number and access token are required.'], 400);
    }

    $params = [
        'FunctionName' => 'aadhar_validation',
        'Payload' => json_encode([
            'aadhar_no' => $aadharNo,
            'access_token' => $accessToken,
            'action' => 'sendOtp'
        ]),
    ];
    Log::info('Preparing to invoke Lambda function with parameters:', $params);

    try {
        $result = $this->lambdaClient->invoke($params);
        Log::info('Lambda function invoked successfully.', ['result' => $result]);

        $responsePayload = json_decode($result['Payload']->getContents(), true);
        Log::info('Lambda invocation result payload:', $responsePayload);

        // Decode the body from the Lambda response
        $body = json_decode($responsePayload['body'], true);
        Log::info('Decoded response body from Lambda:', $body);

        // Check for the expected response structure
        if (isset($body['code']) && $body['code'] === 200) {
            Log::info('Successful response from Lambda function.', $body);
            return response()->json($body);
        } else {
            Log::error('Unexpected response from Lambda function', ['response' => $body]);
            return response()->json(['message' => 'Unexpected response from Lambda function.'], 500);
        }
    } catch (AwsException $e) {
        Log::error('AWS Exception occurred while invoking Lambda function.', [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'aws_response' => $e->getAwsErrorMessage(),
        ]);
        return $this->handleLambdaError($e);
    }
}


public function verifyOtp(Request $request)
{
    $otp = $request->input('otp');
    $refId = $request->input('ref_id');

    // Get access token from the Lambda function
    $accessTokenResponse = $this->getAccessToken();
    
    if ($accessTokenResponse->getStatusCode() !== 200) {
        return response()->json(['message' => 'Failed to get access token.'], 500);
    }

    $accessTokenData = json_decode($accessTokenResponse->getContent(), true);
        Log::info('Access token data retrieved successfully:', ['token_data' => $accessTokenData]);
    
        // Decode the body, which is a JSON string, to access the token
        $bodyData = json_decode($accessTokenData['body'], true);
        $accessToken = $bodyData['token'] ?? null;
    
        Log::info('Access token retrieved successfully:', ['token' => $accessToken]);

    // Validate inputs
    if (!$otp || !$refId || !$accessToken) {
        return response()->json(['message' => 'OTP, ref_id, and access token are required.'], 400);
    }

    $params = [
        'FunctionName' => 'aadhar_validation', // Same Lambda function name
        'Payload' => json_encode([
            'otp' => $otp,
            'ref_id' => $refId,
            'access_token' => $accessToken,
            'action' => 'verifyOtp' // Adjust action name
        ]),
    ];

    try {
        $result = $this->lambdaClient->invoke($params);
        $responsePayload = json_decode($result['Payload']->getContents(), true);

        Log::info('Aadhar Verification Result:', ['result' => $result]);

        // Decode the body from the Lambda response
        $body = json_decode($responsePayload['body'], true);

        Log::info('Response Payload:', $body);

        // Check for the expected response structure
        if (isset($body['code']) && $body['code'] === 200) {
            return response()->json($body);
        } else {
            Log::error('Unexpected response from Lambda function', ['response' => $body]);
            return response()->json(['message' => 'Unexpected response from Lambda function.'], 500);
        }
    } catch (AwsException $e) {
        return $this->handleLambdaError($e);
    }
}


// Utility function to handle Lambda errors
private function handleLambdaError(AwsException $e)
{
    $response = $e->getResponse();
    Log::error('Error invoking Lambda function: ' . $e->getMessage(), [
        'code' => $e->getCode(),
        'requestId' => $e->getAwsRequestId(),
        'response' => $response ? json_decode($response->getBody()->getContents(), true) : null,
    ]);
    return response()->json(['message' => 'Error invoking Lambda function: ' . $e->getMessage()], 500);
}



    public function view() {
        return view('aadhar-verify');
    }
}
