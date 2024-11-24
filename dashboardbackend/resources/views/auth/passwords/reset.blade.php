<!-- resources/views/auth/passwords/reset.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h1>Reset Password</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div>
            <label>New Password</label>
            <input type="password" name="password" required>
        </div>

        <div>
            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <div>
            <button type="submit">Reset Password</button>
        </div>
    </form>
</body>
</html>
