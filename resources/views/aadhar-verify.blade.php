<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aadhar Verification</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/aadhar_logo.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body>
    <nav class="navbar bg-body-tertiary shadow-sm border-bottom">
        <div class="container justify-content-center">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('images/aadhar_logo.jpg') }}" alt="Logo" width="30" height="24"
                    class="d-inline-block align-text-top fw-bold">
                Aadhar Verification
            </a>
        </div>
    </nav>

    <div class="container">

        <!-- Enter Aadhar Number -->
        <div class="card col-md-5 mx-auto my-3 shadow-sm" id="enter_aadhar">
            <div class="card-body">
                <h5 class="my-3 text-center">Enter Your Aadhar Card No</h5>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-fingerprint"></i></span>
                    <input type="number" class="form-control" id="aadhar" placeholder="Enter your 12 digit Aadhar no"
                        aria-label="Aadhar Number" aria-describedby="basic-addon1" required>
                </div>
                <div id="aerrormessage" class="form-text mb-3 text-danger"></div>
                <button class="btn btn-danger w-100" id="aadhar_btn">
                    <i class="bi bi-send"></i> SEND OTP
                </button>
                <button class="btn btn-danger w-100 my-2" id="aadhar_loading_btn" style="display:none" disabled>
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div> SEND OTP
                </button>
            </div>
        </div>

        <!-- Enter OTP -->
        <div class="card col-md-5 mx-auto my-3 shadow-sm" style="display:none" id="enter_otp">
            <div class="card-body">
                <h5 class="my-3 text-center">Enter OTP Sent To Your Registered Mobile No</h5>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1"><i class="bi bi-phone"></i></span>
                    <input type="number" class="form-control" id="otp" placeholder="######" aria-label="OTP"
                        aria-describedby="basic-addon1" required>
                </div>
                <div id="oerrormessage" class="form-text mb-3 text-danger"></div>
                <button class="btn btn-primary w-100" id="otp_btn">
                    <i class="bi bi-shield-check"></i> VERIFY OTP
                </button>
                <button class="btn btn-primary w-100 my-2" id="otp_loading_btn" style="display:none" disabled>
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div> VERIFY OTP
                </button>
            </div>
        </div>

        <!-- Aadhar Details -->
        <div class="card col-md-5 mx-auto my-3 shadow-sm" style="display:none" id="aadhar_details">
            <div class="card-body">
                <h5>Aadhar Card Info</h5>
                <div class="d-flex gap-3 border rounded p-2">
                    <img id="dp" src="" width="85px" class="rounded">
                    <div>
                        <div class="fs-6"><b>Name :</b> <span id="fullname"></span></div>
                        <div class="fs-6"><b>Gender :</b> <span id="gender"></span></div>
                        <div class="fs-6"><b>DOB :</b> <span id="dob"></span></div>
                    </div>
                </div>

                <div class="border rounded p-2 mt-2">
                    <div class="fw-bold fs-5">Address</div>
                    <div id="address"></div>
                </div>
                <button class="btn btn-primary w-100 mt-3" onclick="location.reload();">
                    <i class="bi bi-shield-check"></i> VERIFY ANOTHER AADHAR CARD
                </button>
            </div>
        </div>

    </div>

    <!-- jQuery and JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script>
        var ref_id = null;

        // Send OTP
        $("#aadhar_btn").click(function () {
            $("#aerrormessage").text('');
            let aadhar_no = $("#aadhar").val();
            if (!aadhar_no || aadhar_no.length !== 12) {
                $("#aerrormessage").text('Enter a valid 12 digit Aadhar card no');
                return;
            }

            $(this).hide();
            $("#aadhar_loading_btn").show();

            $.ajax({
                url: '{{ url("/aadhar/get-access-token") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    const parsedResponse = JSON.parse(response.body); // Parse the body
                    if (response.statusCode === 200) {
                        const token = parsedResponse.token; // Get the token
                        console.log(token);
                        sendOtp(token, aadhar_no); // Call the function to send OTP
                    } else {
                        $("#aerrormessage").text(parsedResponse.message);
                        resetAadharButton();
                    }
                },
                error: function (xhr) {
                    console.log('Error getting access token:', xhr);
                    $("#aerrormessage").text('Error fetching access token.');
                    resetAadharButton();
                }
            });
        });

        function resetAadharButton() {
            $("#aadhar_btn").show();
            $("#aadhar_loading_btn").hide();
        }

        // Function to send OTP using the retrieved token
// Function to send OTP using the retrieved token
function sendOtp(token, aadhar_no) {
    $.ajax({
        url: '{{ url("/aadhar/send-otp") }}', 
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token 
        },
        data: {
            aadhar_no: aadhar_no,
            access_token: token,
            _token: '{{ csrf_token() }}' 
        },
        success: function (res) {
            console.log('payload:', res);
            if (res.code === 200) {
        ref_id = res.data.ref_id || null;
        if (ref_id) {
            // Show OTP input box
            $("#enter_aadhar").hide();
            $("#enter_otp").show();
            verifyOtp(ref_id, token);
        } else {
                    $("#aerrormessage").text(res.data.message || 'OTP sent but no ref_id received.');
                }
            } else {
                $("#aerrormessage").text(res.message || 'Error: Unrecognized response.');
            }
            resetAadharButton();
        },
        error: function (xhr) {
            console.error('Error sending OTP:', xhr);
            $("#aerrormessage").text('Error sending OTP: ' + xhr.responseJSON.message || 'Unknown error occurred.');
            resetAadharButton();
        }
    });
}

// Function to verify OTP
function verifyOtp(ref_id, access_token) {
    // Show OTP input box
    $("#enter_aadhar").hide();
    $("#enter_otp").show();

    // Example implementation for the OTP verification
    $("#otp_btn").click(function () {
        let otp = $("#otp").val();
        if (!otp || otp.length !== 6) {
            $("#oerrormessage").text('Enter a valid 6 digit OTP');
            return;
        }

        $(this).hide();
        $("#otp_loading_btn").show();

        $.ajax({
            url: '{{ url("/aadhar/verify-otp") }}',
            dataType: 'json',
            method: 'POST',
            data: {
                otp: otp,
                ref_id: ref_id,
                access_token: access_token, // Pass access_token here
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                console.log('verify:', res);
                if (res.code === 200) {
                    console.log('before:',res.data);
                    console.log('after:',res.data.data);
                    $("#fullname").text(res.data.data.name);
                    $("#gender").text(res.data.data.gender);
                    $("#dob").text(res.data.data.dob);
                    $("#dp").attr('src', 'data:image/jpeg;base64,' + res.data.data.photo_link);
                    $("#address").text(res.data.data.address); // Adjust based on actual response structure
                    $("#enter_otp").hide();
                    $("#aadhar_details").show();
                } else {
                    $("#oerrormessage").text(res.message);
                }
                resetOtpButton();
            },
            error: function (res) {
                console.log('Error verifying OTP:', res);
                $("#oerrormessage").text('Error verifying OTP.');
                resetOtpButton();
            }
        });
    });
}

function resetAadharButton() {
    $("#otp_btn").show();
    $("#otp_loading_btn").hide();
}

    </script>
</body>

</html>
