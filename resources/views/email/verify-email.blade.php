<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
</head>
<body>
    <h2>Email Verification</h2>

    <p>Hello,</p>

    <p>Your email: <strong>{{ $user->email }}</strong></p>

    <p>Please click the link below to verify your email address:</p>

    <p><a href="{{ $verificationUrl }}">Verify Email</a></p>

    <p>If you did not request this, you can ignore this email.</p>

    <p>Thank you!</p>
</body>
</html>