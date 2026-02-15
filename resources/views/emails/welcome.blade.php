<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ProCook</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: bold;
        }
        .header p {
            margin: 10px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome-message {
            font-size: 18px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .content p {
            margin: 15px 0;
            font-size: 16px;
            color: #555;
        }
        .features {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
        }
        .features h2 {
            color: #667eea;
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .feature-item {
            display: flex;
            align-items: start;
            margin-bottom: 15px;
        }
        .feature-icon {
            color: #667eea;
            font-size: 20px;
            margin-right: 12px;
            margin-top: 2px;
        }
        .feature-text {
            flex: 1;
        }
        .feature-text strong {
            color: #333;
            display: block;
            margin-bottom: 3px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #777;
            font-size: 14px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🍳 ProCook</h1>
            <p>Quality Cookware & Delicious Recipes</p>
        </div>
        
        <div class="content">
            <p class="welcome-message">Welcome aboard, {{ $user->name }}! 🎉</p>
            
            <p>Thank you for joining <strong>ProCook</strong> – your new home for discovering amazing recipes and sharing your culinary creations with a passionate community!</p>
            
            <p>We're thrilled to have you here and can't wait to see what delicious dishes you'll create and share.</p>
            
            <div class="features">
                <h2>What You Can Do on ProCook:</h2>
                
                <div class="feature-item">
                    <span class="feature-icon">📖</span>
                    <div class="feature-text">
                        <strong>Browse Recipes</strong>
                        Explore thousands of recipes from our vibrant community
                    </div>
                </div>
                
                <div class="feature-item">
                    <span class="feature-icon">✍️</span>
                    <div class="feature-text">
                        <strong>Share Your Recipes</strong>
                        Upload your favorite recipes and inspire others
                    </div>
                </div>
                
                <div class="feature-item">
                    <span class="feature-icon">🔍</span>
                    <div class="feature-text">
                        <strong>Discover Products</strong>
                        Find the perfect cookware and kitchen essentials
                    </div>
                </div>
                
                <div class="feature-item">
                    <span class="feature-icon">💬</span>
                    <div class="feature-text">
                        <strong>Connect with Foodies</strong>
                        Join a community of passionate home cooks
                    </div>
                </div>
            </div>
            
            <center>
                <a href="{{ config('app.url') }}" class="cta-button">Start Exploring Recipes</a>
            </center>
            
            <div class="divider"></div>
            
            <p style="font-size: 14px; color: #777;">
                <strong>Your Account Details:</strong><br>
                Email: {{ $user->email }}<br>
                Registered: {{ now()->format('F d, Y') }}
            </p>
            
            <p style="margin-top: 30px;">
                Happy cooking and enjoy sharing your passion for food! 🍽️
            </p>
            
            <p style="font-weight: bold; color: #667eea; margin-top: 20px;">
                The ProCook Team
            </p>
        </div>
        
        <div class="footer">
            <p>This email was sent to {{ $user->email }} because you registered for a ProCook account.</p>
            <p style="margin-top: 15px;">
                <a href="{{ config('app.url') }}">Visit ProCook</a> | 
                <a href="#">Help Center</a> | 
                <a href="#">Contact Support</a>
            </p>
            <p style="margin-top: 15px; color: #999; font-size: 12px;">
                © {{ date('Y') }} ProCook. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
