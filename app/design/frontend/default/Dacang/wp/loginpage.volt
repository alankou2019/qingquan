<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户登录</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            max-width: 300px;
            width: 90%;
            text-align: center;
        }

        .logo {
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 100%;
            height: auto;
        }

        .login-container h2 {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn-login {
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 18px;
            cursor: pointer;
        }

        .note {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
        }

    </style>
</head>
<body>


    <div class="login-container">
        <div class="logo">
            <img src="/logo.jpg" alt="网站 Logo">
        </div>
        <h2>用户登录</h2>
        <form method="post" action="{{helper.createUrl(['p':'wp/login'])}}" enctype="multipart/form-data" id='login-form'>
            <div class="form-group">
                <label for="username">用户名:</label>
                <input type="text" id="mobile" name="mobile" placeholder="请输入手机号" required>
            </div>
            <div class="form-group">
                <label for="password">密码:</label>
                <input type="password" id="password" name="pass_word" placeholder="请输入密码" required>
            </div>
            <button type="submit" class="btn-login">登录</button>
        </form>
        <div class="note">
            第一次登陆用初始密码登陆后修改密码再次登陆，平台会自动保存密码登陆状态一年，中途换手机会失效。
        </div>


    </div>

    <script>
    </script>
</body>
</html>

