<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>2FA</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

body {
    background: radial-gradient(circle, #0f172a, #020617);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card {
    background: #1e293b;
    padding: 30px;
    border-radius: 16px;
    width: 360px;
    text-align: center;
}

h2 {
    color: #fff;
}

p {
    color: #94a3b8;
    font-size: 13px;
    margin-bottom: 15px;
}

.qr-box {
    background: white;
    padding: 20px;
    border-radius: 12px;
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    overflow: hidden;
}

.qr-box svg,
.qr-box img {
    max-width: 100%;
}

input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: none;
    margin-bottom: 15px;
    text-align: center;
}

button {
    width: 100%;
    padding: 12px;
    background: #22c55e;
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
}

button:hover {
    background: #16a34a;
}

.error {
    color: red;
    margin-bottom: 10px;
}
</style>
</head>

<body>

<div class="card">
    <h2>Two-Factor Authentication</h2>
    <p>Scan QR with Google Authenticator</p>

    <div class="qr-box">
        {!! $qr !!}
    </div>

    <form method="POST" action="/2fa/verify">
        @csrf

        @error('code')
            <div class="error">{{ $message }}</div>
        @enderror

        <input type="text" name="code" placeholder="Enter OTP" required>

        <button type="submit">Verify</button>
    </form>
</div>

</body>
</html>