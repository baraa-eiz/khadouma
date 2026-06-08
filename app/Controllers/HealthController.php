<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Config;
use App\Core\Env;
use App\Core\Database;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\Logger;
use App\Core\Cache;
use App\Core\Storage;
use App\Core\View;

class HealthController extends Controller
{
    public function index(Request $request): Response
    {
        // Enforce development mode visibility only
        if (Config::get('app.env') !== 'development') {
            return Response::redirect('/');
        }

        $results = [];

        // 1. Router check
        $results['Router'] = [
            'status' => class_exists('App\Core\Router') ? 'PASS' : 'FAIL',
            'detail' => 'Router engine instantiated successfully.'
        ];

        // 2. Config check
        $appName = Config::get('app.name');
        $results['Config'] = [
            'status' => !empty($appName) ? 'PASS' : 'FAIL',
            'detail' => 'Config loaded. APP_NAME: ' . $appName
        ];

        // 3. Environment check
        $appEnv = Env::get('APP_ENV');
        $results['Environment'] = [
            'status' => !empty($appEnv) ? 'PASS' : 'FAIL',
            'detail' => 'Env variables loaded. APP_ENV: ' . $appEnv
        ];

        // 4. Database check
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            $queryResult = $db->fetchColumn("SELECT 1");
            $results['Database'] = [
                'status' => ($queryResult == 1) ? 'PASS' : 'FAIL',
                'detail' => 'PDO connection active. Query verified.'
            ];
        } catch (\Throwable $e) {
            $results['Database'] = [
                'status' => 'FAIL',
                'detail' => 'Database connection failed: ' . $e->getMessage()
            ];
        }

        // 5. Sessions check
        $results['Sessions'] = [
            'status' => (session_status() === PHP_SESSION_ACTIVE) ? 'PASS' : 'FAIL',
            'detail' => 'Session ID: ' . session_id()
        ];

        // 6. CSRF check
        $csrfToken = CSRF::getToken();
        $results['CSRF'] = [
            'status' => !empty($csrfToken) ? 'PASS' : 'FAIL',
            'detail' => 'CSRF Token: ' . substr($csrfToken, 0, 10) . '...'
        ];

        // 7. Logger check
        try {
            Logger::info('Health check diagnostic message.');
            $logFile = Storage::path('logs/app.log');
            $logExists = file_exists($logFile);
            $results['Logger'] = [
                'status' => $logExists ? 'PASS' : 'FAIL',
                'detail' => 'Logged successfully to ' . $logFile
            ];
        } catch (\Throwable $e) {
            $results['Logger'] = [
                'status' => 'FAIL',
                'detail' => $e->getMessage()
            ];
        }

        // 8. Cache check
        try {
            $cacheKey = 'health_test_key';
            $cacheVal = 'diagnostic_value_' . time();
            Cache::set($cacheKey, $cacheVal, 10);
            $retrieved = Cache::get($cacheKey);
            $deleted = Cache::delete($cacheKey);

            $results['Cache'] = [
                'status' => ($retrieved === $cacheVal && $deleted) ? 'PASS' : 'FAIL',
                'detail' => 'Cache read/write/delete sequence completed.'
            ];
        } catch (\Throwable $e) {
            $results['Cache'] = [
                'status' => 'FAIL',
                'detail' => $e->getMessage()
            ];
        }

        // 9. Storage check
        $storageWritable = Storage::ensureWritable('secure_uploads');
        $results['Storage'] = [
            'status' => $storageWritable ? 'PASS' : 'FAIL',
            'detail' => 'Storage root secure directories are writable.'
        ];

        // 10. Upload directories check
        $publicAvatars = is_writable(Config::get('app.paths.public') . '/uploads-public/avatars') 
            || Storage::ensureWritable('../public/uploads-public/avatars');
        $publicGalleries = is_writable(Config::get('app.paths.public') . '/uploads-public/galleries')
            || Storage::ensureWritable('../public/uploads-public/galleries');
        $results['Upload directories'] = [
            'status' => ($publicAvatars && $publicGalleries) ? 'PASS' : 'FAIL',
            'detail' => 'Public avatars and galleries folders are writable.'
        ];

        // 11. View renderer check
        try {
            // Check if template can render
            $testRender = View::render('health_template', ['testVar' => 'render_ok']);
            $results['View renderer'] = [
                'status' => ($testRender === 'render_ok') ? 'PASS' : 'FAIL',
                'detail' => 'View rendering engine functioning correctly.'
            ];
        } catch (\Throwable $e) {
            $results['View renderer'] = [
                'status' => 'FAIL',
                'detail' => $e->getMessage()
            ];
        }

        // 12. Helper loading check
        $helpersLoaded = function_exists('e') && function_exists('url') && function_exists('normalize_arabic') && function_exists('get_placeholder_svg');
        $results['Helper loading'] = [
            'status' => $helpersLoaded ? 'PASS' : 'FAIL',
            'detail' => 'All core security, url, text, and image helpers loaded.'
        ];

        // 13. Repository loading check
        $results['Repository loading'] = [
            'status' => class_exists('App\Core\Repository') ? 'PASS' : 'FAIL',
            'detail' => 'Base Repository loaded.'
        ];

        // 14. Service loading check
        $results['Service loading'] = [
            'status' => class_exists('App\Core\Service') ? 'PASS' : 'FAIL',
            'detail' => 'Base Service loaded.'
        ];

        // 15. Authentication status
        try {
            $db = Database::getInstance();
            $adminCount = $db->fetchColumn("SELECT COUNT(*) FROM `admin_users` WHERE `email` = 'admin@khadomeh.local'");
            $results['Authentication status'] = [
                'status' => ($adminCount > 0) ? 'PASS' : 'FAIL',
                'detail' => ($adminCount > 0) ? 'Seed admin credentials loaded (admin@khadomeh.local).' : 'Seed admin user not found in database.'
            ];
        } catch (\Throwable $e) {
            $results['Authentication status'] = [
                'status' => 'FAIL',
                'detail' => 'Failed to check admin_users table: ' . $e->getMessage()
            ];
        }

        // 16. Session status
        $timeoutVal = Config::get('app.admin.session_timeout');
        $results['Session status'] = [
            'status' => (session_status() === PHP_SESSION_ACTIVE && is_int($timeoutVal) && $timeoutVal > 0) ? 'PASS' : 'FAIL',
            'detail' => 'Session is active. Admin timeout configured to ' . $timeoutVal . ' seconds.'
        ];

        // 17. Middleware registration
        $authMiddlewareExists = class_exists('App\Middleware\AdminAuth') && is_subclass_of('App\Middleware\AdminAuth', 'App\Core\Middleware');
        $guestMiddlewareExists = class_exists('App\Middleware\AdminGuest') && is_subclass_of('App\Middleware\AdminGuest', 'App\Core\Middleware');
        $results['Middleware registration'] = [
            'status' => ($authMiddlewareExists && $guestMiddlewareExists) ? 'PASS' : 'FAIL',
            'detail' => 'AdminAuth and AdminGuest middlewares registered and verified.'
        ];

        // 18. Layout loading
        $layoutPath = Config::get('app.paths.root') . '/views/layouts/admin.php';
        $layoutExists = file_exists($layoutPath) && is_readable($layoutPath);
        $results['Layout loading'] = [
            'status' => $layoutExists ? 'PASS' : 'FAIL',
            'detail' => $layoutExists ? 'Admin master layout views/layouts/admin.php is loaded.' : 'Admin layout template file not found or unreadable.'
        ];

        // 19. Component registration
        $componentsDir = Config::get('app.paths.root') . '/views/components/';
        $requiredComponents = ['breadcrumb.php', 'flash.php', 'confirm_dialog.php', 'empty_state.php', 'loading_state.php', 'pagination.php'];
        $missingComponents = [];
        foreach ($requiredComponents as $comp) {
            if (!file_exists($componentsDir . $comp)) {
                $missingComponents[] = $comp;
            }
        }
        $results['Component registration'] = [
            'status' => empty($missingComponents) ? 'PASS' : 'FAIL',
            'detail' => empty($missingComponents) 
                ? 'All base design components registered in views/components/.' 
                : 'Missing components: ' . implode(', ', $missingComponents)
        ];

        // Metrics calculations
        $memoryUsage = round(memory_get_usage() / 1024 / 1024, 2) . ' MB';
        $bootstrapTime = round((microtime(true) - KHADOMEH_START) * 1000, 2) . ' ms';
        $loadedFiles = count(get_included_files());

        // We render an HTML diagnostics report
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>لوحة تشخيص خادومة - Health Check</title>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;800&display=swap');
                :root {
                    --bg-color: #faf8f5;
                    --text-color: #4a3e3d;
                    --primary-color: #c05c46;
                    --border-color: #e6dfd5;
                    --card-bg: #ffffff;
                    --pass-color: #2e7d32;
                    --fail-color: #c62828;
                }
                body {
                    font-family: 'Cairo', sans-serif;
                    background-color: var(--bg-color);
                    color: var(--text-color);
                    margin: 0;
                    padding: 40px 20px;
                    direction: rtl;
                }
                .container {
                    max-width: 900px;
                    margin: 0 auto;
                }
                .header {
                    text-align: center;
                    margin-bottom: 40px;
                }
                .header h1 {
                    color: var(--primary-color);
                    font-size: 32px;
                    margin-bottom: 10px;
                }
                .header p {
                    color: #8a7768;
                    margin: 0;
                    font-size: 16px;
                }
                .metrics-grid {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 20px;
                    margin-bottom: 30px;
                }
                .metric-card {
                    background: var(--card-bg);
                    border: 1px solid var(--border-color);
                    border-radius: 12px;
                    padding: 20px;
                    text-align: center;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.02);
                }
                .metric-title {
                    font-size: 14px;
                    color: #8a7768;
                    margin-bottom: 8px;
                }
                .metric-value {
                    font-size: 24px;
                    font-weight: 800;
                    color: var(--primary-color);
                }
                .health-table {
                    width: 100%;
                    border-collapse: collapse;
                    background: var(--card-bg);
                    border: 1px solid var(--border-color);
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
                }
                .health-table th, .health-table td {
                    padding: 16px 20px;
                    text-align: right;
                    border-bottom: 1px solid var(--border-color);
                }
                .health-table th {
                    background: #fdfbf7;
                    font-weight: 700;
                    color: #6e615e;
                }
                .status-badge {
                    display: inline-block;
                    padding: 4px 12px;
                    border-radius: 20px;
                    font-weight: 700;
                    font-size: 13px;
                }
                .status-badge.pass {
                    background: #e8f5e9;
                    color: var(--pass-color);
                }
                .status-badge.fail {
                    background: #ffebee;
                    color: var(--fail-color);
                }
                .detail-text {
                    font-size: 14px;
                    color: #6e615e;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>لوحة تشخيص إطار عمل خدومة (Health Check)</h1>
                    <p>فحص حالة مكونات النظام وتوافق البنية التحتية</p>
                </div>

                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-title">استهلاك الذاكرة</div>
                        <div class="metric-value"><?= $memoryUsage ?></div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">وقت الإقلاع (Bootstrap)</div>
                        <div class="metric-value"><?= $bootstrapTime ?></div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">الملفات المضمنة</div>
                        <div class="metric-value"><?= $loadedFiles ?> ملفاً</div>
                    </div>
                </div>

                <table class="health-table">
                    <thead>
                        <tr>
                            <th>المكون / الميزة</th>
                            <th>الحالة</th>
                            <th>التفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $component => $info): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($component) ?></strong></td>
                                <td>
                                    <span class="status-badge <?= strtolower($info['status']) ?>">
                                        <?= $info['status'] === 'PASS' ? 'ناجح PASS' : 'فاشل FAIL' ?>
                                    </span>
                                </td>
                                <td class="detail-text"><?= htmlspecialchars($info['detail']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();

        $response = new Response();
        $response->setContent($html);
        return $response;
    }
}
