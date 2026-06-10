<?php
/**
 * ProviderController.php
 * Khadomeh Public Provider Discovery & Contact Tracking Controller
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\ProviderRepository;
use App\Repositories\ServiceRepository;
use App\Modules\Locations\CitiesRepository;
use App\Modules\Locations\AreasRepository;

class ProviderController extends Controller
{
    private ProviderRepository $providerRepo;
    private ServiceRepository $serviceRepo;
    private CitiesRepository $citiesRepo;
    private AreasRepository $areasRepo;

    public function __construct()
    {
        $this->providerRepo = new ProviderRepository();
        $this->serviceRepo = new ServiceRepository();
        $this->citiesRepo = new CitiesRepository();
        $this->areasRepo = new AreasRepository();
    }

    /**
     * Search & Filter Provider listings with 301 Redirection
     */
    public function search(Request $request): Response
    {
        $serviceSlug = $request->query('service');
        $citySlug = $request->query('city');
        $areaSlug = $request->query('area');
        $keyword = $request->query('keyword');
        $page = (int)$request->query('page', 1);

        // 301 Redirect duplicate query-param URLs to clean SEO routes
        if (empty($keyword) && empty($areaSlug) && $page <= 1) {
            if (!empty($serviceSlug) && !empty($citySlug)) {
                $this->redirect(base_url($citySlug . '/' . $serviceSlug), 301);
                return new Response();
            } elseif (!empty($serviceSlug)) {
                $this->redirect(base_url('services/' . $serviceSlug), 301);
                return new Response();
            } elseif (!empty($citySlug)) {
                $this->redirect(base_url('cities/' . $citySlug), 301);
                return new Response();
            }
        }

        $filters = [
            'service' => $serviceSlug,
            'city' => $citySlug,
            'area' => $areaSlug,
            'keyword' => $keyword
        ];

        $breadcrumbs = [
            'الرئيسية' => base_url(),
            'البحث' => base_url('search')
        ];

        return $this->renderListingPage($request, $filters, $breadcrumbs, 'search');
    }

    /**
     * Service Landing Page (/services/{service})
     */
    public function serviceLanding(Request $request, string $serviceSlug): Response
    {
        $selectedService = $this->serviceRepo->findBySlug($serviceSlug);
        if (!$selectedService) {
            $res = $this->render('404');
            $res->setStatusCode(404);
            return $res;
        }

        $filters = [
            'service' => $serviceSlug,
            'city' => null,
            'area' => null,
            'keyword' => $request->query('keyword')
        ];

        $breadcrumbs = [
            'الرئيسية' => base_url(),
            'الخدمات' => base_url('search'),
            $selectedService['display_name_ar'] => base_url('services/' . $serviceSlug)
        ];

        return $this->renderListingPage($request, $filters, $breadcrumbs, 'services/' . $serviceSlug);
    }

    /**
     * City Landing Page (/cities/{city})
     */
    public function cityLanding(Request $request, string $citySlug): Response
    {
        $selectedCity = $this->citiesRepo->findBySlug($citySlug);
        if (!$selectedCity) {
            $res = $this->render('404');
            $res->setStatusCode(404);
            return $res;
        }

        $filters = [
            'service' => null,
            'city' => $citySlug,
            'area' => $request->query('area'),
            'keyword' => $request->query('keyword')
        ];

        $breadcrumbs = [
            'الرئيسية' => base_url(),
            'المدن' => base_url('search'),
            $selectedCity['display_name_ar'] => base_url('cities/' . $citySlug)
        ];

        return $this->renderListingPage($request, $filters, $breadcrumbs, 'cities/' . $citySlug);
    }

    /**
     * City + Service Landing Page (/{city}/{service})
     */
    public function cityServiceLanding(Request $request, string $citySlug, string $serviceSlug): Response
    {
        $selectedCity = $this->citiesRepo->findBySlug($citySlug);
        if (!$selectedCity) {
            $res = $this->render('404');
            $res->setStatusCode(404);
            return $res;
        }

        $selectedService = $this->serviceRepo->findBySlug($serviceSlug);
        if (!$selectedService) {
            $res = $this->render('404');
            $res->setStatusCode(404);
            return $res;
        }

        $filters = [
            'service' => $serviceSlug,
            'city' => $citySlug,
            'area' => $request->query('area'),
            'keyword' => $request->query('keyword')
        ];

        $breadcrumbs = [
            'الرئيسية' => base_url(),
            $selectedCity['display_name_ar'] => base_url('cities/' . $citySlug),
            $selectedService['display_name_ar'] => base_url($citySlug . '/' . $serviceSlug)
        ];

        return $this->renderListingPage($request, $filters, $breadcrumbs, $citySlug . '/' . $serviceSlug);
    }

    /**
     * Helper to render paginated listing views
     */
    private function renderListingPage(Request $request, array $filters, array $breadcrumbs, string $canonicalPath): Response
    {
        $page = max(1, (int)$request->query('page', 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        // Perform repository search
        $providers = $this->providerRepo->search($filters, 'sort_weight', 'DESC', $perPage, $offset);
        $totalCount = $this->providerRepo->countSearch($filters);
        $totalPages = (int)ceil($totalCount / $perPage);

        // Single search result -> redirect directly to provider (on first page, without offset)
        if ($totalCount === 1 && $page === 1 && empty($request->query('no_redirect')) && empty($filters['keyword'])) {
            $this->redirect(url('provider/' . $providers[0]['slug']));
            return new Response();
        }

        // Fetch auxiliary details for the cards
        foreach ($providers as &$p) {
            $p['profile_image'] = $this->providerRepo->getProviderProfileImage($p['id']);
            $p['areas'] = $this->providerRepo->getProviderAreas($p['id']);
        }
        unset($p);

        // Fetch active services & cities for the selectors
        $services = $this->serviceRepo->getAllActive();
        $cities = $this->citiesRepo->search(['is_active' => 1, 'is_deleted' => 0], 'sort_order', 'ASC', 100);

        // Selected entities
        $selectedService = null;
        if (!empty($filters['service'])) {
            $selectedService = $this->serviceRepo->findBySlug($filters['service']);
        }
        $selectedCity = null;
        $areas = [];
        if (!empty($filters['city'])) {
            $selectedCity = $this->citiesRepo->findBySlug($filters['city']);
            if ($selectedCity) {
                $areas = $this->areasRepo->search([
                    'city_id' => $selectedCity['id'],
                    'is_active' => 1,
                    'is_deleted' => 0
                ], 'sort_order', 'ASC', 100);
            }
        }
        $selectedArea = null;
        if (!empty($filters['area'])) {
            $selectedArea = $this->areasRepo->findBySlug($filters['area']);
        }

        // Compile dynamic SEO titles and meta descriptions
        $seoTitle = '';
        $seoDesc = '';

        if ($selectedService && $selectedCity) {
            $seoTitle = $selectedService['display_name_ar'] . ' في ' . $selectedCity['display_name_ar'];
            if ($selectedArea) {
                $seoTitle .= ' - ' . $selectedArea['display_name_ar'];
            }
            $seoDesc = 'ابحث عن أفضل ' . $selectedService['display_name_ar'] . ' في ' . $selectedCity['display_name_ar'] . ($selectedArea ? ' (' . $selectedArea['display_name_ar'] . ')' : '') . '. تواصل مباشر، صور أعمال، ومعلومات اتصال فورا.';
        } elseif ($selectedService) {
            $seoTitle = $selectedService['display_name_ar'] . ' في سوريا';
            $seoDesc = 'دليل أفضل فنيي وصيانة ' . $selectedService['display_name_ar'] . ' في سوريا. تواصل مباشر بدون أي عمولات.';
        } elseif ($selectedCity) {
            $seoTitle = 'خدمات وصيانة في ' . $selectedCity['display_name_ar'];
            $seoDesc = 'تواصل مع أفضل الفنيين والعمال في ' . $selectedCity['display_name_ar'] . ' مباشرة. خدمات صيانة منزلية مجانية بالكامل.';
        } else {
            $seoTitle = 'نتائج البحث عن الحرفيين ومزودي الخدمات';
            $seoDesc = 'ابحث عن أفضل الحرفيين والعمال للخدمات المنزلية والصيانة في سوريا. تواصل مباشر مجاناً وبدون عمولات.';
        }

        if (!empty($filters['keyword'])) {
            $seoTitle .= ' | ' . $filters['keyword'];
        }

        // Canonical URL including page parameter if > 1
        $canonicalUrl = base_url($canonicalPath);
        $queryParams = [];
        if (!empty($filters['keyword'])) {
            $queryParams['keyword'] = $filters['keyword'];
        }
        if (!empty($filters['area'])) {
            $queryParams['area'] = $filters['area'];
        }
        if ($canonicalPath === 'search') {
            if (!empty($filters['service'])) $queryParams['service'] = $filters['service'];
            if (!empty($filters['city'])) $queryParams['city'] = $filters['city'];
        }
        
        $canonicalParams = $queryParams;
        if ($page > 1) {
            $canonicalParams['page'] = $page;
        }
        if (!empty($canonicalParams)) {
            $canonicalUrl .= '?' . http_build_query($canonicalParams);
        }

        // Prev / Next Page URLs
        $prevPageUrl = null;
        if ($page > 1) {
            $prevParams = $queryParams;
            if ($page - 1 > 1) {
                $prevParams['page'] = $page - 1;
            }
            $prevPageUrl = base_url($canonicalPath);
            if (!empty($prevParams)) {
                $prevPageUrl .= '?' . http_build_query($prevParams);
            }
        }
        
        $nextPageUrl = null;
        if ($page < $totalPages) {
            $nextParams = $queryParams;
            $nextParams['page'] = $page + 1;
            $nextPageUrl = base_url($canonicalPath);
            if (!empty($nextParams)) {
                $nextPageUrl .= '?' . http_build_query($nextParams);
            }
        }

        // Query Contextual FAQs from database
        $db = Database::getInstance();
        $faqEntries = [];
        if ($selectedService && $selectedCity) {
            $faqEntries = $db->fetchAll(
                "SELECT * FROM `faq_entries` 
                 WHERE (((`service_id` = :service_id AND `city_id` = :city_id) 
                    OR (`service_id` = :service_id AND `city_id` IS NULL)
                    OR (`city_id` = :city_id AND `service_id` IS NULL))
                   AND `is_active` = 1 AND `deleted_at` IS NULL) 
                 ORDER BY `sort_order` ASC",
                ['service_id' => $selectedService['id'], 'city_id' => $selectedCity['id']]
            );
        } elseif ($selectedService) {
            $faqEntries = $db->fetchAll(
                "SELECT * FROM `faq_entries` 
                 WHERE `service_id` = :service_id AND `city_id` IS NULL 
                   AND `is_active` = 1 AND `deleted_at` IS NULL 
                 ORDER BY `sort_order` ASC",
                ['service_id' => $selectedService['id']]
            );
        } elseif ($selectedCity) {
            $faqEntries = $db->fetchAll(
                "SELECT * FROM `faq_entries` 
                 WHERE `city_id` = :city_id AND `service_id` IS NULL 
                   AND `is_active` = 1 AND `deleted_at` IS NULL 
                 ORDER BY `sort_order` ASC",
                ['city_id' => $selectedCity['id']]
            );
        }

        $seoData = [
            'title' => $seoTitle,
            'description' => $seoDesc,
            'canonical' => $canonicalUrl
        ];

        return $this->render('public/results', [
            'providers' => $providers,
            'services' => $services,
            'cities' => $cities,
            'areas' => $areas,
            'filters' => $filters,
            'selectedService' => $selectedService,
            'selectedCity' => $selectedCity,
            'selectedArea' => $selectedArea,
            'pageTitle' => $seoTitle,
            'metaDesc' => $seoDesc,
            'canonicalUrl' => $canonicalUrl,
            'prevPageUrl' => $prevPageUrl,
            'nextPageUrl' => $nextPageUrl,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalProviders' => $totalCount,
            'perPage' => $perPage,
            'faqEntries' => $faqEntries,
            'breadcrumbs' => $breadcrumbs,
            'seoData' => $seoData
        ]);
    }

    /**
     * Show Provider Profile Details
     */
    public function show(Request $request, string $slug): Response
    {
        $provider = $this->providerRepo->getBySlug($slug);
        if (!$provider) {
            $res = $this->render('404');
            $res->setStatusCode(404);
            return $res;
        }

        $providerId = (int)$provider['id'];
        // Retrieve all related assets/relations
        $profileImage = $this->providerRepo->getProviderProfileImage($providerId);
        $secondaryServices = $this->providerRepo->getProviderSecondaryServices($providerId);
        $areasCovered = $this->providerRepo->getProviderAreas($providerId);
        $workPhotos = $this->providerRepo->getProviderWorkPhotos($providerId);
        $reviews = $this->providerRepo->getProviderReviews($providerId);

        // Generate breadcrumbs path using clean SEO urls
        $breadcrumbs = [
            'الرئيسية' => base_url()
        ];
        if (!empty($provider['service_slug']) && !empty($provider['city_slug'])) {
            $breadcrumbs[$provider['service_name']] = base_url($provider['city_slug'] . '/' . $provider['service_slug']);
        } elseif (!empty($provider['service_slug'])) {
            $breadcrumbs[$provider['service_name']] = base_url('services/' . $provider['service_slug']);
        }
        $breadcrumbs[$provider['display_name_ar']] = '';

        return $this->render('public/provider', [
            'provider' => $provider,
            'profileImage' => $profileImage,
            'secondaryServices' => $secondaryServices,
            'areasCovered' => $areasCovered,
            'workPhotos' => $workPhotos,
            'reviews' => $reviews,
            'breadcrumbs' => $breadcrumbs,
            'pageTitle' => $provider['display_name_ar'] . ' | ' . $provider['service_name'] . ' في ' . $provider['city_name'],
            'metaDesc' => $provider['short_description_ar'] ?? 'تفاصيل وتقييمات وصور أعمال ' . $provider['display_name_ar'] . ' في ' . $provider['city_name']
        ]);
    }

    /**
     * AJAX endpoint to log contact clicks and reveal numbers
     */
    public function trackContact(Request $request): Response
    {
        $providerId = (int)$request->input('provider_id');
        $method = $request->input('method'); // 'phone_call' or 'whatsapp_message'
        $sourcePage = $request->input('source_page', 'provider_profile');

        if (!$providerId || !in_array($method, ['phone_call', 'whatsapp_message'])) {
            return $this->json(['success' => false, 'message' => 'بيانات الطلب غير صالحة.'], 400);
        }

        // Retrieve provider details
        $db = Database::getInstance();
        $provider = $db->fetch(
            "SELECT * FROM `providers` WHERE `id` = :id AND `is_active` = 1 AND `status` = 'approved' AND `deleted_at` IS NULL LIMIT 1",
            ['id' => $providerId]
        );

        if (!$provider) {
            return $this->json(['success' => false, 'message' => 'مزود الخدمة غير موجود.'], 404);
        }

        // Hashing visitor details
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipHash = hash('sha256', $ip . date('Y-m-d')); // Daily rotating hash signature
        $uaHash = hash('sha256', $userAgent);

        // Rate limit: Max 5 unique providers per visitor/day
        $alreadyClicked = (int)$db->fetchColumn(
            "SELECT COUNT(*) FROM `contact_events` 
             WHERE `provider_id` = :provider_id AND `user_ip_hash` = :ip_hash AND DATE(`created_at`) = CURRENT_DATE()",
            ['provider_id' => $providerId, 'ip_hash' => $ipHash]
        );

        if ($alreadyClicked === 0) {
            $uniqueCount = (int)$db->fetchColumn(
                "SELECT COUNT(DISTINCT `provider_id`) FROM `contact_events` 
                 WHERE `user_ip_hash` = :ip_hash AND DATE(`created_at`) = CURRENT_DATE()",
                ['ip_hash' => $ipHash]
            );

            if ($uniqueCount >= 5) {
                return $this->json([
                    'success' => false,
                    'message' => 'لقد تجاوزت الحد الأقصى اليومي المسموح به للاتصال بمزودي الخدمات (5 مزودين).'
                ], 429);
            }

            // Get first mapped area to log in the event (optional, for analytics resolution)
            $areaId = $db->fetchColumn(
                "SELECT `area_id` FROM `provider_area_map` WHERE `provider_id` = :provider_id LIMIT 1",
                ['provider_id' => $providerId]
            );

            // Log event
            $db->execute(
                "INSERT INTO `contact_events` 
                (`provider_id`, `service_id`, `city_id`, `area_id`, `method`, `source_page`, `user_ip_hash`, `user_agent_hash`) 
                VALUES 
                (:provider_id, :service_id, :city_id, :area_id, :method, :source_page, :ip_hash, :ua_hash)",
                [
                    'provider_id' => $providerId,
                    'service_id' => $provider['primary_service_id'],
                    'city_id' => $provider['city_id'],
                    'area_id' => $areaId ?: null,
                    'method' => $method,
                    'source_page' => $sourcePage,
                    'ip_hash' => $ipHash,
                    'ua_hash' => $uaHash
                ]
            );
        }

        // Return connection strings
        $phone = $provider['phone'];
        $whatsapp = !empty($provider['whatsapp']) ? $provider['whatsapp'] : $phone;

        // Clean numbers
        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
        $telUrl = 'tel:' . $cleanPhone;

        $cleanWa = preg_replace('/[^\d]/', '', $whatsapp);
        if (strpos($cleanWa, '09') === 0) {
            $cleanWa = '963' . substr($cleanWa, 1);
        } elseif (strpos($cleanWa, '963') !== 0) {
            $cleanWa = '963' . $cleanWa;
        }
        $waUrl = 'https://wa.me/' . $cleanWa;

        return $this->json([
            'success' => true,
            'tel' => $telUrl,
            'whatsapp' => $waUrl,
            'phone' => $phone
        ]);
    }
}
