<!DOCTYPE html>
<html>
<head>
    <title>Tender Status Updated</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: rgb(30, 60, 123); padding: 30px; margin: 0;">
    <div class="container" style="max-width: 90%; margin: auto; background-color: #FFFFFF; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; margin-top: 20px;">
        <div class="logo" style="margin-bottom: 20px;">
            <img src="{{ asset('https://app.morgantigcc.com:3001/static/media/logo.816d4fa33f65392f4000.png') }}" alt="Morganti GCC Logo" style="width: 150px;">
        </div>
        <div class="title" style="color: rgb(30, 60, 123); font-size: 24px; font-weight: bold;">Tender STATUS UPDATED TO: {{ $emailData['newStatus'] }}</div>
        <div class="subtitle" style="color: rgb(30, 60, 123); font-size: 18px; margin-bottom: 20px;">PLEASE NOTICE THE FOLLOWING UPDATE</div>
        <div class="content" style="color: rgb(30, 60, 123); font-size: 16px; line-height: 1.5; margin-bottom: 20px; text-align: left;">
            <p>Dear Mr. {{ $emailData['targetedUser']->name }},</p>
            
            <p>We would like to inform you that the status of the tender <strong>{{ $emailData['tender']->name }}</strong> has been updated.</p>
            
            <p><strong>Previous Status:</strong> {{ $emailData['previousStatus'] }}</p>
            <p><strong>New Status:</strong> {{ $emailData['newStatus'] }}</p>

            <p>Below are the details of the tender:</p>
            <ul>
                <li><strong>Tender Name:</strong> {{ $emailData['tender']->tenderTitle }}</li>
                <li><strong>Project Type:</strong> {{ $emailData['tender']->selectedOption }}</li>
                <li><strong>Tender Value:</strong> {{ number_format($emailData['tender']->tender_value, 2) }}</li>
            </ul>
            
            <p>You can view the full details and any additional actions required by logging into the tendering dashboard link below: </p>
            
            <p>
                <a href="https://app.morgantigcc.com:3001/tenderingDashboard" style="color: rgb(30, 60, 123); font-weight: bold;">Login to Tendering Dashboard</a>
            </p>

            <p>Thank you for your attention to this matter.</p>

            <p>Best regards,</p>
            <p>Morganti</p>
        </div>
    </div>
</body>
</html>
