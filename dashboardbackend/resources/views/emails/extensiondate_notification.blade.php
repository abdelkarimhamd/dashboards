<!DOCTYPE html>
<html>
<head>
    <title>Extension Date Notification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: rgb(30, 60, 123); padding: 30px; margin: 0;">
    <div class="container" style=" max-width: 90%; margin: auto; background-color: #FFFFFF; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;margin-top:20px">
        <div class="logo" style="margin-bottom: 20px;">
            <img src="{{ asset('https://app.morgantigcc.com:3001/static/media/logo.816d4fa33f65392f4000.png') }}" alt="Morganti GCC Logo" style="width: 150px;">
        </div>
        <div class="icon" style="margin-bottom: 20px;">
            {{-- <img src="{{ asset('public/images/notification_icon.png') }}" alt="Notification Icon" style="width: 50px;"> --}}
        </div>
        <div class="subtitle" style="color: rgb(30, 60, 123); font-size: 18px; margin-bottom: 20px;text-align:left">PLEASE NOTICE THAT</div>
        <div class="content" style="color: rgb(30, 60, 123); font-size: 16px; line-height: 1.5; margin-bottom: 20px;text-align:left">
            <p>Dear  Mr.{{ $userName }},</p>
            <p>This email is to notify you that the extension date for the tender titled "{{ $tenderTitle }}" has been set to {{ \Carbon\Carbon::parse($extinsionDate)->format('F j, Y') }}.</p>
            <p>Please take the necessary actions.</p>
            <p>Thank you,</p>
            <p>Morganti</p>
        </div>
    </div>
</body>
</html>