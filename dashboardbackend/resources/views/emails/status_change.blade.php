<!DOCTYPE html>
<html>
<head>
    <title>TRF Status Updated</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: rgb(30, 60, 123); padding: 30px; margin: 0;">
    <div class="container" style="max-width: 90%; margin: auto; background-color: #FFFFFF; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; margin-top: 20px;">
        <div class="logo" style="margin-bottom: 20px;">
            <img src="{{ asset('https://app.morgantigcc.com:3001/static/media/logo.816d4fa33f65392f4000.png') }}" alt="Morganti GCC Logo" style="width: 150px;">
        </div>
        <div class="title" style="color: rgb(30, 60, 123); font-size: 24px; font-weight: bold;">TRF STATUS UPDATED TO: {{ $newStatus }}</div>
        <div class="subtitle" style="color: rgb(30, 60, 123); font-size: 18px; margin-bottom: 20px;">PLEASE NOTICE THE FOLLOWING UPDATE</div>
        <div class="content" style="color: rgb(30, 60, 123); font-size: 16px; line-height: 1.5; margin-bottom: 20px; text-align: left;">
            <p>Dear Mr. {{ $targetedUser->name }},</p>
            
            <p>We would like to inform you that the status of the tender <strong>{{ $tender->name }}</strong> has been updated.</p>
            
            <p><strong>Previous Status:</strong> {{ $previousStatus }}</p>
            <p><strong>New Status:</strong> {{ $newStatus }}</p>

            <p>Below are the details of the tender:</p>
            <ul>
                <li><strong>Tender Name:</strong> {{ $tender->tenderTitle }}</li>
                <li><strong>Project type:</strong> {{ $tender->selectedOption }}</li>
                <li><strong>Tender Value:</strong> {{ number_format($tender->tender_value, 2) }}</li>
                <li><strong>Last Updated By:</strong> {{ $tender->approved_by }}</li>
            </ul>
            
            <p>Please take the necessary action based on the new status. You can log in to the tendering dashboard to approve or edit the TRF form by clicking the link below:</p>
            
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
