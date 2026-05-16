<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodieExpress — Be right back</title>
    @vite(['resources/css/app.css'])
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; font-family:var(--font-body, sans-serif); background:var(--color-cream, #FFF8F2); }
    </style>
</head>
<body>
    <div class="text-center px-6" style="max-width:420px">
        <div style="font-size:4rem;line-height:1">🍕</div>
        <h1 style="font-family:var(--font-display,sans-serif);font-size:1.75rem;font-weight:800;color:#1a0a00;margin-top:1rem">
            We'll be right back
        </h1>
        <p class="mt-3" style="color:var(--color-warm-muted, #8c6a4a);line-height:1.6">
            FoodieExpress is temporarily unable to connect to the database.<br>
            Please try again in a moment.
        </p>
        <a href="/" class="btn-brand inline-block mt-6" style="text-decoration:none;padding:0.65rem 2rem;border-radius:9999px">
            Try again
        </a>
    </div>
</body>
</html>
