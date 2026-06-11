<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>410 - Gone | {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <style>
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #FDFDFC;
            color: #1b1b18;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 1.5rem;
        }
        .container {
            text-align: center;
            max-width: 28rem;
        }
        .error-code {
            font-size: 4rem;
            font-weight: 600;
            color: #706f6c;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #706f6c;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .back-link {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background-color: #1b1b18;
            color: white;
            text-decoration: none;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .back-link:hover {
            background-color: #000;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #0a0a0a;
                color: #EDEDEC;
            }
            .error-code {
                color: #A1A09A;
            }
            .error-message {
                color: #A1A09A;
            }
            .back-link {
                background-color: #eeeeec;
                color: #1C1C1A;
            }
            .back-link:hover {
                background-color: #fff;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">410</div>
        <h1 class="error-title">Gone</h1>
        <p class="error-message">
            {{ $message ?? 'This resource is no longer available.' }}
        </p>
        <a href="{{ url('/') }}" class="back-link">Return Home</a>
    </div>
</body>
</html>
