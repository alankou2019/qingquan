
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
    </style>
</head>
<body>
    <div class="changepwd-container">
        <h2>修改密码</h2>
        <form method="post" action="{{helper.createUrl(['p':'wp/changepassword'])}}" enctype="multipart/form-data" id='login-form'>
            <div class="form-group">
                <label for="username">用户名:</label>
                <input type="text" id="mobile" name="mobile" placeholder="请输入手机号" required value='{{mobile}}'>
            </div>
            <div class="form-group">
                <label for="password">新密码:</label>
                <input type="password" id="password" name="pass_word" placeholder="请输入新密码" required>
            </div>
            <button type="submit" class="btn-login">登录</button>
        </form>
    </div>

    <script>
    </script>
</body>
</html>
