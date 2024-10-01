import axios from 'axios';

const getAccessToken = async () => {
    const url = 'https://api.sandbox.co.in/authenticate';
    const headers = {
        'accept': 'application/json',
        'x-api-key': 'key_live_AAMr5ks53YRk28Ex5rzge0Ad8J375UYA',
        'x-api-version': '1.0',
        'x-api-secret': 'secret_live_UZ6nNJc3mDNaRyit0F0nox3qSSJSAG88'
    };

    try {
        const response = await axios.post(url, null, { headers, timeout: 20000 });
        return response.data.access_token;
    } catch (error) {
        console.error('Error fetching access token:', error.message);
        throw error;
    }
};

const sendOtp = async (aadhar_no, access_token) => {
    console.log('aadharno:', aadhar_no);
    const maskedAadhar = aadhar_no ? `${aadhar_no.slice(0, 4)}******${aadhar_no.slice(-2)}` : null;
    console.log('Parsed Aadhaar number (masked):', maskedAadhar);

    if (!aadhar_no || aadhar_no.length !== 12 || !access_token) {
        return {
            statusCode: 422,
            body: JSON.stringify({ message: 'Invalid Aadhaar number or access token' })
        };
    }

    try {
        const url = 'https://api.sandbox.co.in/kyc/aadhaar/okyc/otp';
        const headers = {
            'Authorization': `${access_token}`,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'x-api-key': 'key_live_AAMr5ks53YRk28Ex5rzge0Ad8J375UYA',
            'x-api-version': '1.0'
        };

        const data = { aadhaar_number: aadhar_no };
        const response = await axios.post(url, data, { headers, timeout: 20000 });
        return {
            statusCode: 200,
            body: JSON.stringify(response.data)
        };
    } catch (error) {
        console.error('Error sending OTP:', error.message);
        return {
            statusCode: error.response ? error.response.status : 500,
            body: JSON.stringify({ message: 'Error sending OTP', details: error.response ? error.response.data : error.message })
        };
    }
};

const verifyOtp = async (otp, ref_id, access_token) => {
    try {
        const url = 'https://api.sandbox.co.in/kyc/aadhaar/okyc/otp/verify';
        const headers = {
            'Authorization': access_token,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'x-api-key': 'key_live_AAMr5ks53YRk28Ex5rzge0Ad8J375UYA',
            'x-api-version': '1.0'
        };

        const data = { otp, ref_id };
        const response = await axios.post(url, data, { headers, timeout: 20000 });

        return {
            statusCode: 200,
            body: JSON.stringify({
                code: 200,
                message: 'OTP verification successful',
                data: response.data // Include author details in the response
            })
        };
    } catch (error) {
        console.error('Error verifying OTP:', error.message);
        return {
            statusCode: error.response ? error.response.status : 500,
            body: JSON.stringify({
                code: 500,
                message: 'Error verifying OTP',
                details: error.response ? error.response.data : error.message
            })
        };
    }
};

// Main Lambda handler
export const handler = async (event) => {
    console.log('Received event:', JSON.stringify(event));

    const { action, aadhar_no, otp, ref_id } = event; // action determines which function to call

    try {
        let token;

        if (action === 'getAccessToken') {
            token = await getAccessToken();
            return {
                statusCode: 200,
                body: JSON.stringify({ token })
            };
        } 
        
        if (action === 'sendOtp') {
            token = await getAccessToken();
            return await sendOtp(aadhar_no, token);
        } 
        
        if (action === 'verifyOtp') {
            const parsedBody = event.body ? JSON.parse(event.body) : event;
            return await verifyOtp(parsedBody.otp, parsedBody.ref_id, parsedBody.access_token);
        }

        return {
            statusCode: 400,
            body: JSON.stringify({ message: 'Invalid action specified' })
        };
        
    } catch (error) {
        console.error('Error:', error.message);
        return {
            statusCode: 500,
            body: JSON.stringify({ message: 'An error occurred', details: error.message })
        };
    }
};
