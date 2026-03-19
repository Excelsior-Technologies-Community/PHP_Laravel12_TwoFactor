<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    background: #f1f5f9;
}

.navbar {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    padding: 15px 25px;
    color: white;
    display: flex;
    justify-content: space-between;
}

.container {
    padding: 50px;
    display: flex;
    justify-content: center;
}

.card {
    background: white;
    padding: 40px;
    border-radius: 16px;
    width: 500px;
    text-align: center;
}

.btn {
    padding: 10px 18px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
</style>
</head>

<body>

<div class="navbar">
    <h3>Dashboard</h3>

    <form method="POST" action="/logout">
        @csrf
        <button class="btn">Logout</button>
    </form>
</div>

<div class="container">
    <div class="card">
        <h1>Welcome 🎉</h1>
        <p>You are logged in with 2FA</p>
    </div>
</div>

</body>
</html>