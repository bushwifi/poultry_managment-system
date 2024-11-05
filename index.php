<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image and Login Button Layout</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .image-section {
            flex: 1;
            background-color: #f0f0f0;
        }

        .image-section img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .login-section {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 0.3;
            background-color: #fff;
        }

        .login-button {
            padding: 10px 20px;
            font-size: 18px;
            color: white;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        .login-button:hover {
            background-color: #45a049;
        }

        /* Style anchor inside the button */
        .login-button a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-section">
            <img src="chick.jpg" alt="Sample Image">
        </div>
        <div class="login-section">
            <button class="login-button">
                <a href="login.php">Login</a>
            </button>
        </div>
    </div>
</body>
</html>

