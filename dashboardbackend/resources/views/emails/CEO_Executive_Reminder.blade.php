<!DOCTYPE html>
<html>
<head>
    <title>Reminder: President’s action required on tender</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: rgb(30, 60, 123); padding: 30px; margin: 0;">
    <div class="container" style="max-width: 90%; margin: auto; background-color: #FFFFFF; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; margin-top: 20px;">
        
        <div class="logo" style="margin-bottom: 20px;">
            <img src="{{ asset('https://app.morgantigcc.com:3001/static/media/logo.816d4fa33f65392f4000.png') }}" alt="Morganti GCC Logo" style="width: 150px;">
        </div>

        <div class="subtitle" style="color: rgb(30, 60, 123); font-size: 18px; margin-bottom: 20px; text-align: center;">REMINDER</div>
        
        <div class="content" style="color: rgb(30, 60, 123); font-size: 16px; line-height: 1.5; margin-bottom: 20px; text-align: left;">
            <p>Dear Mr. {{ $executiveDirectorName }} and Mr. {{ $ceoName }},</p>

            <p>This is a reminder that the President has not taken action on the following tender:</p>

            <p><strong>Tender:</strong> {{ $tender->tenderTitle }}</p>
            <p><strong>Status:</strong> {{ $tender->status }}</p>

            <p>Please remind the President to take the necessary action.</p>

            <p>Thank you,</p>
            <p>Your Tendering System</p>

            <p style="text-align: center;">
                <a href="https://app.morgantigcc.com:3001/tenderingDashboard" style="color: rgb(30, 60, 123); font-weight: bold;">Login to Tendering Dashboard</a>
            </p>
        </div>

    </div>
</body>
</html>
