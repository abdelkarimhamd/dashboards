<!DOCTYPE html>
<html>
<head>
    <title>TRF Process Completed</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: rgb(30, 60, 123); padding: 30px; margin: 0;">
    <div class="container" style="max-width: 90%; margin: auto; background-color: #FFFFFF; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; margin-top: 20px;">
        <div class="logo" style="margin-bottom: 20px;">
            <img src="{{ asset('https://app.morgantigcc.com:3001/static/media/logo.816d4fa33f65392f4000.png') }}" alt="Morganti GCC Logo" style="width: 150px;">
        </div>
        <div class="title" style="color: rgb(30, 60, 123); font-size: 24px; font-weight: bold;">TRF PROCESS COMPLETED</div>
        <div class="subtitle" style="color: rgb(30, 60, 123); font-size: 18px; margin-bottom: 20px;">PLEASE NOTICE THE FOLLOWING COMPLETION</div>
        <div class="content" style="color: rgb(30, 60, 123); font-size: 16px; line-height: 1.5; margin-bottom: 20px; text-align: left;">
            <p>Dear Mr.{{ $targetedUser->name }},</p>

            <p>We are pleased to inform you that the tender process for <strong>{{ $tender->name }}</strong> has been completed successfully.</p>

            <p>Below are the details of the completed tender:</p>
            <ul>
        
                <li><strong>Tender Name:</strong> {{ $tender->tenderTitle }}</li>
                <li><strong>Project Type:</strong> {{ $tender->selectedOption }}</li>
                <li><strong>Tender Value:</strong> {{ number_format($tender->tender_value, 2) }}</li>
               
            </ul>

            <p>You can view the full details and any additional actions required by logging into the tendering dashboard:</p>

            <p>
                <a href="https://app.morgantigcc.com:3001/tenderingDashboard" style="color: rgb(30, 60, 123); font-weight: bold;">Login to  Dashboard</a>
            </p>

            <p>Thank you for your efforts throughout this process.</p>

            <p>Best regards,</p>
            <p>Morganti</p>
        </div>
    </div>
</body>
</html>
