<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .btn { display: inline-block; background: #2d7d46; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <h2>Đặt lại mật khẩu</h2>
    <p>Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
    <p><a href="{{ $resetUrl }}" class="btn">Đặt lại mật khẩu</a></p>
    <p>Link này sẽ hết hạn sau 60 phút.</p>
    <p>Nếu bạn không yêu cầu đặt lại mật khẩu, bạn có thể bỏ qua email này.</p>
    <div class="footer">
        <p>Mountain Booking</p>
    </div>
</body>
</html>
