<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EzExam</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 40%, #3b82f6 70%, #06b6d4 100%);
        }
        .dashboard-card {
            background: white;
            padding: 3rem;
            border-radius: 1.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .welcome-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        .user-info {
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }
        .logout-form {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-card">
        <h1 class="welcome-text">Welcome to EzExam Admin!</h1>
        <p class="user-info">
            Logged in as: <strong>{{ Auth::user()->name }}</strong><br>
            Email: {{ Auth::user()->email }}
        </p>
        <p class="text-secondary">Dashboard features coming soon...</p>
        
        <form action="{{ route('logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="btn btn-secondary" style="width: 100%;">
                Logout
            </button>
        </form>
    </div>
</body>
</html>
