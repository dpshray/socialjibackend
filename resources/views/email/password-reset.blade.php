<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
</head>
<body>
    <h2>Password Reset Request</h2>

    <p>Hello {{ $user->nick_name }},</p>

    <p>We received a request to reset your password. Below is the token to used to reset password(expires at {{ $token_expires_at }}):</p>
    <div style="text-align: center;font-weight:bolder;font-size: x-large;">
        <p>{{ $token }}</p>
    </div>

    <p>If you didn’t request a password reset, you can safely ignore this email.</p>

    <p>Thanks,<br>{{ env('APP_NAME') }}</p>
</body>
</html>
