<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .otp-code { font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #2d7d46; text-align: center; padding: 20px; background: #f0faf5; border-radius: 12px; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <h2>Mã OTP đặt lại mật khẩu</h2>
    <p>Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
    <p>Mã OTP của bạn là:</p>
    <div class="otp-code">{{ $otp }}</div>
    <p>Mã này sẽ hết hạn sau <strong>60 phút</strong>.</p>
    <p>Nếu bạn không yêu cầu đặt lại mật khẩu, bạn có thể bỏ qua email này.</p>
    <div class="footer">
        <p>Mountain Booking</p>
    </div>
</body>
</html>
