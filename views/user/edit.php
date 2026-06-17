<?php
/**
 * edit.php
 * User Portal Profile Edit Form
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الحساب - منصة خدومة</title>
    <!-- Tajawal Font -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border: #cbd5e1;
            --border-focus: #3b82f6;
            --danger: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 650px;
            margin: 0 auto;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 40px;
            box-shadow: var(--shadow-sm);
        }

        .header-section {
            margin-bottom: 30px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title {
            font-size: 1.4rem;
            font-weight: 800;
        }

        .back-btn {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: right;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--border-focus);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
        }

        .invalid-feedback {
            color: var(--danger);
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 5px;
            display: block;
        }

        .avatar-preview-container {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .avatar-preview {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #dbeafe;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-weight: 500 !important;
        }

        .checkbox-label input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="card">
            <!-- Header -->
            <div class="header-section">
                <h1 class="title">تعديل الملف الشخصي</h1>
                <a href="/user/dashboard" class="back-btn">← العودة للوحة التحكم</a>
            </div>

            <!-- Form -->
            <form action="/user/profile/edit" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <!-- Avatar Section -->
                <div class="avatar-preview-container">
                    <?php if ($user->avatar): ?>
                        <img src="/<?= htmlspecialchars($user->avatar) ?>" alt="Avatar" class="avatar-preview">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?= mb_substr($user->display_name, 0, 1) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <label for="avatar" style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 5px;">الصورة الشخصية</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*">
                    </div>
                </div>

                <!-- Display Name -->
                <div class="form-group">
                    <label for="display_name">اسم المستخدم *</label>
                    <input type="text" id="display_name" name="display_name" class="form-control <?= isset($errors['display_name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($user->display_name) ?>" required>
                    <?php if (isset($errors['display_name'])): ?>
                        <span class="invalid-feedback"><?= htmlspecialchars($errors['display_name'][0]) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($user->email ?? '') ?>">
                    <?php if (isset($errors['email'])): ?>
                        <span class="invalid-feedback"><?= htmlspecialchars($errors['email'][0]) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label for="phone">رقم الهاتف السوري</label>
                    <input type="text" id="phone" name="phone" placeholder="مثال: 09xxxxxxxx" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($user->phone ?? '') ?>">
                    <?php if (isset($errors['phone'])): ?>
                        <span class="invalid-feedback"><?= htmlspecialchars($errors['phone'][0]) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Location Dropdowns -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="city_id">المدينة</label>
                        <select id="city_id" name="city_id" class="form-control <?= isset($errors['city_id']) ? 'is-invalid' : '' ?>">
                            <option value="">-- اختر المدينة --</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= $city['id'] ?>" <?= $user->city_id == $city['id'] ? 'selected' : '' ?>><?= htmlspecialchars($city['display_name_ar']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['city_id'])): ?>
                            <span class="invalid-feedback"><?= htmlspecialchars($errors['city_id'][0]) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="area_id">المنطقة</label>
                        <select id="area_id" name="area_id" class="form-control <?= isset($errors['area_id']) ? 'is-invalid' : '' ?>">
                            <option value="">-- اختر المنطقة --</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= $area['id'] ?>" data-city="<?= $area['city_id'] ?>" <?= $user->area_id == $area['id'] ? 'selected' : '' ?>><?= htmlspecialchars($area['display_name_ar']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['area_id'])): ?>
                            <span class="invalid-feedback"><?= htmlspecialchars($errors['area_id'][0]) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Default Address -->
                <div class="form-group">
                    <label for="default_address">العنوان بالتفصيل</label>
                    <textarea id="default_address" name="default_address" rows="3" class="form-control <?= isset($errors['default_address']) ? 'is-invalid' : '' ?>"><?= htmlspecialchars($user->default_address ?? '') ?></textarea>
                    <?php if (isset($errors['default_address'])): ?>
                        <span class="invalid-feedback"><?= htmlspecialchars($errors['default_address'][0]) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Preferences Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="preferred_contact_method">طريقة التواصل المفضلة</label>
                        <select id="preferred_contact_method" name="preferred_contact_method" class="form-control">
                            <option value="phone" <?= $user->preferred_contact_method === 'phone' ? 'selected' : '' ?>>الهاتف</option>
                            <option value="email" <?= $user->preferred_contact_method === 'email' ? 'selected' : '' ?>>البريد الإلكتروني</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="preferred_language">اللغة المفضلة</label>
                        <select id="preferred_language" name="preferred_language" class="form-control">
                            <option value="ar" <?= $user->preferred_language === 'ar' ? 'selected' : '' ?>>العربية</option>
                            <option value="en" <?= $user->preferred_language === 'en' ? 'selected' : '' ?>>English</option>
                        </select>
                    </div>
                </div>

                <!-- Timezone -->
                <div class="form-group">
                    <label for="timezone">التوقيت المحلي</label>
                    <select id="timezone" name="timezone" class="form-control">
                        <option value="Asia/Damascus" <?= $user->timezone === 'Asia/Damascus' ? 'selected' : '' ?>>دمشق (UTC+3)</option>
                        <option value="Asia/Riyadh" <?= $user->timezone === 'Asia/Riyadh' ? 'selected' : '' ?>>الرياض (UTC+3)</option>
                        <option value="UTC" <?= $user->timezone === 'UTC' ? 'selected' : '' ?>>التوقيت العالمي (UTC)</option>
                    </select>
                </div>

                <!-- Marketing Opt In -->
                <div class="form-group" style="margin-top: 15px;">
                    <label class="checkbox-label">
                        <input type="checkbox" name="marketing_opt_in" value="1" <?= $user->marketing_opt_in ? 'checked' : '' ?>>
                        <span>أوافق على تلقي العروض الترويجية والرسائل البريدية</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary" style="margin-top: 15px;">حفظ التغييرات 💾</button>
            </form>
        </div>
    </div>

    <!-- Dynamic Location Filtering Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const citySelect = document.getElementById('city_id');
            const areaSelect = document.getElementById('area_id');
            const areaOptions = Array.from(areaSelect.options).slice(1); // skip placeholder option

            function filterAreas() {
                const selectedCityId = citySelect.value;
                areaSelect.innerHTML = '<option value="">-- اختر المنطقة --</option>';
                
                const filtered = areaOptions.filter(opt => opt.getAttribute('data-city') === selectedCityId);
                
                filtered.forEach(opt => areaSelect.appendChild(opt));
                
                // Keep selected value if it's still available in filtered options
                const prevValue = "<?= $user->area_id ?>";
                if (filtered.some(opt => opt.value === prevValue)) {
                    areaSelect.value = prevValue;
                }
            }

            citySelect.addEventListener('change', filterAreas);
            if (citySelect.value) {
                filterAreas();
            }
        });
    </script>

</body>
</html>
