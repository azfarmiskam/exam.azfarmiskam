<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - {{ $classroom->name }}</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .register-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <h2 class="text-center mb-3">{{ $classroom->name }}</h2>
        <p class="text-center text-secondary mb-4">Student registration form coming soon...</p>
        <div class="card mb-4">
            <div class="card-body">
                <p><strong>Classroom Code:</strong> {{ $classroom->code }}</p>
                <p><strong>Questions:</strong> {{ $classroom->questions_per_exam }}</p>
                @if($classroom->timer_minutes)
                    <p><strong>Time Limit:</strong> {{ $classroom->timer_minutes }} minutes</p>
                @endif
            </div>
        </div>
        <a href="/" class="btn btn-secondary" style="width: 100%;">← Back to Home</a>
    </div>
</body>
</html>
