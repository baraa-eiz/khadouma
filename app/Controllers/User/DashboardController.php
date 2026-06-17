<?php
/**
 * DashboardController.php
 * Khadomeh User Portal Dashboard Controller
 */

namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Flash;
use App\Core\Database;
use App\Modules\Users\UsersRepository;
use App\Modules\Users\UsersService;

class DashboardController extends Controller
{
    private UsersService $usersService;

    public function __construct()
    {
        $this->usersService = new UsersService(new UsersRepository());
    }

    /**
     * Dashboard home.
     */
    public function index(Request $request): Response
    {
        $userId = Session::get('user_id');
        $user = $this->usersService->getUserById($userId);
        
        if (!$user) {
            Flash::error('المستخدم غير موجود. يرجى تسجيل الدخول مرة أخرى.');
            Session::remove('user_id');
            return Response::redirect('/user/login');
        }

        $completionScore = $this->usersService->calculateCompletionScore($user);
        $favorites = $this->usersService->getFavorites($userId);

        // Fetch detailed favorites data (e.g. provider details)
        $favoriteProviders = [];
        $db = Database::getInstance();
        foreach ($favorites as $fav) {
            if ($fav['entity_type'] === 'provider') {
                $provider = $db->fetch("
                    SELECT p.*, c.display_name_ar as city_name, s.display_name_ar as service_name 
                    FROM `providers` p
                    LEFT JOIN `cities` c ON p.city_id = c.id
                    LEFT JOIN `services` s ON p.primary_service_id = s.id
                    WHERE p.public_id = :public_id AND p.is_active = 1 AND p.deleted_at IS NULL
                    LIMIT 1
                ", ['public_id' => $fav['entity_public_id']]);
                if ($provider) {
                    $favoriteProviders[] = $provider;
                }
            }
        }

        return $this->render('user/dashboard', [
            'user' => $user,
            'completionScore' => $completionScore,
            'favoriteProviders' => $favoriteProviders
        ]);
    }

    /**
     * Profile edit page.
     */
    public function edit(Request $request): Response
    {
        $userId = Session::get('user_id');
        $user = $this->usersService->getUserById($userId);

        if (!$user) {
            Flash::error('المستخدم غير موجود.');
            return Response::redirect('/user/login');
        }

        $db = Database::getInstance();
        $cities = $db->fetchAll("SELECT * FROM `cities` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC");
        $areas = $db->fetchAll("SELECT * FROM `areas` WHERE `is_active` = 1 AND `is_deleted` = 0 ORDER BY `sort_order` ASC");

        return $this->render('user/edit', [
            'user' => $user,
            'cities' => $cities,
            'areas' => $areas,
            'errors' => Session::get('validation_errors', [])
        ]);
    }

    /**
     * Handle profile update submit.
     */
    public function update(Request $request): Response
    {
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/user/profile/edit');
        }

        $userId = Session::get('user_id');
        $user = $this->usersService->getUserById($userId);

        if (!$user) {
            Flash::error('المستخدم غير موجود.');
            return Response::redirect('/user/login');
        }

        $data = $request->input();

        // Handle avatar upload if any
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                Flash::error('صيغة الصورة غير صالحة. يرجى رفع صورة بصيغة JPEG أو PNG أو WEBP.');
                return Response::redirect('/user/profile/edit');
            }

            $uploadDir = dirname(dirname(dirname(__DIR__))) . '/public/uploads/users/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $avatarName = 'user_' . $user->public_id . '_' . time() . '.' . $ext;
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $avatarName)) {
                $data['avatar'] = 'public/uploads/users/' . $avatarName;
            } else {
                Flash::error('فشل تحميل الصورة. يرجى المحاولة مرة أخرى.');
            }
        }

        [$errors, $success] = $this->usersService->updateProfile($userId, $data);

        if ($errors) {
            Session::set('validation_errors', $errors);
            Flash::error('فشل تحديث البيانات. يرجى التحقق من المدخلات.');
            return Response::redirect('/user/profile/edit');
        }

        // Clean validation errors
        Session::remove('validation_errors');

        // Update basic session display attributes
        $updatedUser = $this->usersService->getUserById($userId);
        if ($updatedUser) {
            Session::set('user_display_name', $updatedUser->display_name);
            Session::set('user_email', $updatedUser->email);
            Session::set('user_phone', $updatedUser->phone);
            Session::set('user_avatar', $updatedUser->avatar);
        }

        Flash::success('تم تحديث الملف الشخصي بنجاح.');
        return Response::redirect('/user/dashboard');
    }

    /**
     * Toggle provider favorite state.
     */
    public function toggleFavorite(Request $request): Response
    {
        try {
            $this->validateCsrf($request);
        } catch (\RuntimeException $e) {
            Flash::error('خطأ في حماية الجلسة (CSRF). يرجى المحاولة مرة أخرى.');
            return Response::redirect('/user/dashboard');
        }

        $userId = Session::get('user_id');
        $providerPublicId = trim($request->input('provider_id', ''));

        if ($providerPublicId === '') {
            Flash::error('معرّف الحرفي غير صالح.');
            return Response::redirect('/');
        }

        if ($this->usersService->isFavorite($userId, 'provider', $providerPublicId)) {
            $this->usersService->removeFavorite($userId, 'provider', $providerPublicId);
            Flash::success('تمت إزالة الحرفي من المفضلة.');
        } else {
            $this->usersService->addFavorite($userId, 'provider', $providerPublicId);
            Flash::success('تمت إضافة الحرفي إلى المفضلة.');
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '/user/dashboard';
        return Response::redirect($referer);
    }
}
