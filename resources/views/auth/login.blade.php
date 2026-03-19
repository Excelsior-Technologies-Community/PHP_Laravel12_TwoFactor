<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #4f46e5, #9333ea);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card {
    background: white;
    padding: 40px;
    border-radius: 16px;
    width: 360px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
    text-align: center;
}

h2 {
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

button {
    width: 100%;
    padding: 12px;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

button:hover {
    background: #4338ca;
}
</style>
</head>

<body>

<div class="card">
    <h2>Login</h2>

    <form method="POST" action="/login">
        @csrf

        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>