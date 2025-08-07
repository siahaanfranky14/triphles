<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            color: #333333;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
            color: #555555;
        }
        .code {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #999999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Forgot Password</h1>
        <p>Hello,</p>
        <p>You have requested to reset your password. Use the following code to reset your password for the account associated with this email: {{$email}}.</p>
        <p class="code">{{$kode}}</p>
        <p>Please change your password immediately after logging in.</p>
        <div class="footer">
            <p>If you didn't request a password reset, please ignore this email.</p>
            <p>Thank you!</p>
        </div>
    </div>
</body>
</html>
