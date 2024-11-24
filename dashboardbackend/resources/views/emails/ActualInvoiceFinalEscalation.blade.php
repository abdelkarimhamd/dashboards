<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body style="font-family: Arial, sans-serif; background-color: rgb(30, 60, 123); padding: 30px; margin: 0;">
    <div class="container" style=" max-width: 90%; margin: auto; background-color: #FFFFFF; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;margin-top:20px">
        <div class="logo" style="margin-bottom: 20px;">
            <img src="{{ asset('https://app.morgantigcc.com:3001/static/media/logo.816d4fa33f65392f4000.png') }}" alt="Morganti GCC Logo" style="width: 150px;">
        </div>
        <div class="icon" style="margin-bottom: 20px;">
            {{-- <img src="{{ asset('public/images/notification_icon.png') }}" alt="Notification Icon" style="width: 50px;"> --}}
        </div>
        <div class="subtitle" style="color: rgb(30, 60, 123); font-size: 18px; margin-bottom: 20px;text-align:center">REMINDER</div>
        <div class="content" style="color: rgb(30, 60, 123); font-size: 16px; line-height: 1.5; margin-bottom: 20px;text-align:left">
        <p>Dear Mr. {{ $projectManagerName }},</p>
        <p>This is a gentle and third reminder regarding the project "{{ $projectName }}".</p>
        <p>We noticed that the actual invoice value for this month hasn't been filled in yet.</p> 
        <p>We kindly ask for your prompt attention to this matter. </p>
        <p>Please take the necessary action. You can log in to the Operation dashboard to review the values by clicking the link below:</p>
            
        <p>
            <a href="https://app.morgantigcc.com:3001/operationDashboard" style="color: rgb(30, 60, 123); font-weight: bold;">Login to Morgantigcc Dashboard</a>
        </p>
    </div>
</body>
</html>