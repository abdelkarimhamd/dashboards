<!DOCTYPE html>
<html>
<head>
    <title>New Tender Notification</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: rgb(30, 60, 123); padding: 20px; margin: 0;">
    <div class="container" style=" max-width: 90%; margin: auto; background-color: #FFFFFF; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;margin-top:20px">
        <div class="logo" style="margin-bottom: 20px;">
            <img src="{{ asset('https://app.morgantigcc.com:3001/static/media/logo.816d4fa33f65392f4000.png') }}" alt="Morganti GCC Logo" style="width: 150px;">
        </div>
        <div  style="color: rgb(30, 60, 123); font-size: 16px; line-height: 1.5; margin-bottom: 0px; text-align:left">
            <p>Dear Mr. {{ $userName }},</p>
            <p>You have received a new tender notification for the project "{{ $data['tenderTitle'] }}".</p>
            <p><strong style="color: rgb(30, 60, 123);">Tender Title:</strong> {{ $data['tenderTitle'] }}</p>
            <p><strong style="color: rgb(30, 60, 123);">Project Main Scope:</strong> {{ $data['selectedOption'] }}</p>
            <p><strong style="color: rgb(30, 60, 123);">Location:</strong> {{ $data['location'] }}</p>
            <p><strong style="color: rgb(30, 60, 123);">Source:</strong> {{ $data['sourceOption'] }}</p>

            <p>You can view the full details and any additional actions required by logging into the tendering dashboard:</p>

            <p>
                <a href="https://app.morgantigcc.com:3001/tenderingDashboard" style="color: rgb(30, 60, 123); font-weight: bold;">Login to Tendering Dashboard</a>
            </p>

            <p>Thank you,</p>
            <p>Morganti</p>
           
        </div> 
    </div>
</body>
</html>
