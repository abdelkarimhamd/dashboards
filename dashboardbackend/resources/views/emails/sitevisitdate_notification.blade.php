<!DOCTYPE html>
<html>
<head>
    <title>Site Visit Date Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: rgb(30, 60, 123); padding: 30px; margin: 0;">
    <div class="container" style=" max-width: 90%; margin: auto; background-color: #FFFFFF; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;margin-top:20px">
        <div class="logo" style="margin-bottom: 20px;">
            <img src="{{ asset('https://app.morgantigcc.com:3001/static/media/logo.816d4fa33f65392f4000.png') }}" alt="Morganti GCC Logo" style="width: 150px;">
        </div>
        <div class="icon" style="margin-bottom: 20px;">
            {{-- <img src="{{ asset('public/images/notification_icon.png') }}" alt="Notification Icon" style="width: 50px;"> --}}
        </div>
        <div class="title" style="color: rgb(30, 60, 123); font-size: 24px; font-weight: bold;">REMINDER</div>
        <div class="subtitle" style="color: rgb(30, 60, 123); font-size: 18px; margin-bottom: 20px;">PLEASE NOTICE THAT</div>
        <div class="content" style="color: rgb(30, 60, 123); font-size: 16px; line-height: 1.5; margin-bottom: 20px;text-align:left">
            <p>Dear  Mr. {{ $userName }},</p>
            <p>This is a reminder that the site visit date for the tender titled "{{ $tenderTitle }}" is scheduled for {{ \Carbon\Carbon::parse($siteVisitDate)->format('F j, Y') }}.</p>
            <p>Thank you,</p>
            <p>Morganti</p>
        </div>
    </div>
</body>
</html>
