<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - الصفحة غير موجودة</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');
        :root {
            --bg-color: #faf8f5;
            --text-color: #4a3e3d;
            --primary-color: #c05c46;
            --primary-hover: #a64c37;
            --border-color: #e6dfd5;
            --muted-color: #8a7768;
            --bg-hover: #fcfbfa;
            --radius-md: 12px;
        }
        body {
            font-family: 'Cairo', Tahoma, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            box-sizing: border-box;
            padding: 24px;
        }
        .card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px 30px;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(138, 119, 104, 0.05);
        }
        .error-code {
            font-size: 88px;
            font-weight: 900;
            color: var(--primary-color);
            line-height: 1;
            margin: 0 0 12px 0;
            letter-spacing: -2px;
        }
        h1 {
            font-size: 22px;
            margin: 0 0 12px 0;
            font-weight: 700;
        }
        p {
            color: var(--muted-color);
            font-size: 15px;
            line-height: 1.6;
            margin: 0 0 24px 0;
        }
        .search-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 24px;
        }
        .form-control {
            width: 100%;
            box-sizing: border-box;
            padding: 12px 16px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            text-align: center;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary-color);
        }
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            transition: background-color 0.2s;
            text-align: center;
        }
        .btn:hover {
            background-color: var(--primary-hover);
        }
        .suggestions-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-color);
            margin: 20px 0 10px 0;
            text-align: center;
        }
        .suggestions-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }
        .suggestion-badge {
            font-size: 12px;
            color: var(--muted-color);
            text-decoration: none;
            padding: 6px 14px;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            background: var(--bg-color);
            transition: all 0.2s;
        }
        .suggestion-badge:hover {
            background: white;
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        .home-link {
            display: inline-block;
            margin-top: 24px;
            font-size: 14px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        .home-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="error-code">٤٠٤</div>
        <h1>عذراً، الصفحة غير موجودة</h1>
        <p>يبدو أن الرابط الذي طلبته قد تم نقله أو حذفه. يمكنك البحث عما تريد مباشرة أدناه:</p>
        
        <form action="/search" method="GET" class="search-form">
            <input type="text" name="keyword" placeholder="ابحث عن: كهربائي، سباك، دهان..." class="form-control" required>
            <button type="submit" class="btn">ابحث الآن</button>
        </form>

        <div class="suggestions-title">قد تهمك الخدمات التالية:</div>
        <div class="suggestions-grid">
            <a href="/search/electricity" class="suggestion-badge">⚡ كهرباء منازل</a>
            <a href="/search/plumbing" class="suggestion-badge">🚰 سباكة وصيانة</a>
            <a href="/search/cleaning" class="suggestion-badge">🧹 تنظيف وتعقيم</a>
            <a href="/search/painting" class="suggestion-badge">🎨 دهان وديكور</a>
            <a href="/search/moving" class="suggestion-badge">📦 نقل وتغليف</a>
        </div>

        <div>
            <a href="/" class="home-link">العودة للصفحة الرئيسية &larr;</a>
        </div>
    </div>
</body>
</html>
