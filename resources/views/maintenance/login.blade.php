<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1a2e">
    <title>Maintenance Access - Rezimainpadel Versus</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: 1rem;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 2rem;
            max-width: 400px;
            width: 100%;
        }
        
        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: rgba(233, 69, 96, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .icon svg {
            width: 40px;
            height: 40px;
            color: #e94560;
        }
        
        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        p {
            color: #a0aec0;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
        }
        
        label {
            display: block;
            color: #a0aec0;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            color: #fff;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        input:focus {
            outline: none;
            border-color: #e94560;
        }
        
        button {
            width: 100%;
            background: linear-gradient(135deg, #e94560 0%, #c73e54 100%);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(233, 69, 96, 0.4);
        }
        
        .error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #a0aec0;
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .back-link:hover {
            color: #e94560;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        
        <h1>Maintenance Access</h1>
        <p>Enter the maintenance token to access the cleanup tools.</p>
        
        @if($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif
        
        <form method="POST" action="">
            @csrf
            <label for="maintenance_token">Maintenance Token</label>
            <input type="password" id="maintenance_token" name="maintenance_token" placeholder="Enter token" required autofocus>
            
            <button type="submit">Access Maintenance</button>
        </form>
        
        <a href="/" class="back-link">← Back to tournaments</a>
    </div>
</body>
</html>
