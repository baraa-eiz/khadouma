<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - الصفحة غير موجودة</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap');
        :root {
            --bg-color: #faf8f5;
            --text-color: #4a3e3d;
            --primary-color: #c05c46;
            --border-color: #e6dfd5;
            --muted-color: #8a7768;
        }
        body {
            font-family: 'Cairo', Tahoma, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            text-align: center;
            padding: 80px 20px;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 50px 30px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(138, 119, 104, 0.05);
        }
        .error-code {
            font-size: 96px;
            font-weight: 900;
            color: var(--primary-color);
            line-height: 1;
            margin: 0 0 20px 0;
        }
        h1 {
            font-size: 24px;
            margin: 0 0 15px 0;
            font-weight: 700;
        }
        p {
            color: var(--muted-color);
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 700;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #a64c37;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="error-code">٤٠٤</div>
        <h1>الصفحة غير موجودة</h1>
        <p>عذراً، يبدو أن الرابط الذي حاولت الوصول إليه قد تم نقله، حذفه، أو لم يكن موجوداً في الأساس.</p>
        <a href="/" class="btn">العودة للرئيسية</a>
    </div>
</body>
</html>
