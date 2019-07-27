<!DOCTYPE html>
<html>
<head>
    <title>Device Authorization</title>
    @include('device_auth::_styles')
</head>
<body>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-6 ml-auto mr-auto">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Device Verification</h5>
                            <h6 class="card-subtitle mb-3 text-muted">We haven't seen you using this device before</h6>
                            <p class="card-text">
                                You're trying to sign-in on a new device or browser, as a security measure we require you to confirm this action.
                            </p>
                            <p class="card-text">
                                <strong>We have sent an e-mail to the address associated with your account, please click on the link in the email to verify this device.</strong>
                            </p>
                            <p class="card-text">
                                Thanks for helping us keep your account secure!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</body>
</html>