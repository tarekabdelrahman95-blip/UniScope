<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Default to English
}

// Change language if requested
if (isset($_GET['lang'])) {
    if ($_GET['lang'] == 'ar') {
        $_SESSION['lang'] = 'ar';
    } elseif ($_GET['lang'] == 'en') {
        $_SESSION['lang'] = 'en';
    }
    // Redirect to remove lang from URL
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

// Language translations
$lang = [];

if ($_SESSION['lang'] == 'ar') {
    // Arabic translations
    $lang = [
        // General
        'site_title' => 'نظام تقييم الكليات',
        'welcome' => 'مرحباً',
        'logout' => 'تسجيل خروج',
        'login' => 'تسجيل دخول',
        'register' => 'تسجيل جديد',
        'back_to_home' => 'العودة للرئيسية',
        'search' => 'بحث',
        'clear_search' => 'مسح البحث',
        
        // Navigation
        'home' => 'الرئيسية',
        'colleges' => 'الكليات',
        'majors' => 'التخصصات',
        'professors' => 'أعضاء هيئة التدريس',
        'reviews' => 'التقييمات',
        
        // Homepage
        'find_college' => 'ابحث عن كليتك',
        'search_placeholder' => 'ابحث عن الكليات بالاسم...',
        'top_rated_colleges' => 'أفضل الكليات تقييماً',
        'no_results' => 'لا توجد نتائج',
        'no_results_message' => 'لا توجد كليات تطابق بحثك',
        'try_different' => 'جرب كلمات بحث مختلفة أو تصفح الاقتراحات أدناه',
        'suggested_colleges' => 'كليات مقترحة',
        'view_details' => 'عرض التفاصيل',
        
        // College page
        'college_details' => 'تفاصيل الكلية',
        'location' => 'الموقع',
        'website' => 'الموقع الرسمي',
        'majors_offered' => 'التخصصات المتاحة',
        'professors_list' => 'أعضاء هيئة التدريس',
        'student_reviews' => 'تقييمات الطلاب',
        'write_review' => 'اكتب تقييماً',
        'your_rating' => 'تقييمك',
        'your_review' => 'مراجعتك',
        'submit_review' => 'إرسال التقييم',
        'no_reviews' => 'لا توجد تقييمات بعد',
        'be_first_review' => 'كن أول من يقيم',
        'reviews_count' => 'تقييم',
        
        // Major page
        'major_details' => 'تفاصيل التخصص',
        'program_overview' => 'نظرة عامة',
        'career_opportunities' => 'الفرص الوظيفية',
        'academic_timeline' => 'الخط الزمني الأكاديمي',
        'duration' => 'المدة',
        'years' => 'سنوات',
        'bachelor' => 'بكالوريوس',
        'masters' => 'ماجستير',
        'phd' => 'دكتوراه',
        'total_duration' => 'المدة الإجمالية',
        
        // Professor page
        'professor_profile' => 'ملف الأستاذ',
        'department' => 'القسم',
        'about_professor' => 'عن الأستاذ',
        'total_reviews' => 'إجمالي التقييمات',
        'average_rating' => 'متوسط التقييم',
        'performance' => 'الأداء',
        'excellent' => 'ممتاز',
        'very_good' => 'جيد جداً',
        'good' => 'جيد',
        'needs_improvement' => 'يحتاج تحسين',
        
        // Filters
        'all' => 'الكل',
        'filter_by' => 'تصفية حسب',
        'engineering' => 'الهندسة',
        'medical' => 'الطب',
        'law' => 'القانون',
        'business' => 'إدارة الأعمال',
        'arts' => 'الفنون',
        'computer_science' => 'علوم الحاسب',
        
        // Messages
        'review_success' => 'شكراً لك! تم إرسال تقييمك بنجاح',
        'already_reviewed' => 'لقد قمت بتقييم هذا بالفعل',
        'login_required' => 'يجب تسجيل الدخول أولاً',
        'no_data' => 'لا توجد بيانات',
        
        // Admin Panel
        'admin_panel' => 'لوحة التحكم',
        'dashboard' => 'لوحة المعلومات',
        'manage_colleges' => 'إدارة الكليات',
        'manage_majors' => 'إدارة التخصصات',
        'manage_professors' => 'إدارة أعضاء هيئة التدريس',
        'manage_users' => 'إدارة المستخدمين',
        'add_new' => 'إضافة جديد',
        'edit' => 'تعديل',
        'delete' => 'حذف',
        'confirm_delete' => 'هل أنت متأكد؟',
        
        // Ratings
        'rating' => 'التقييم',
        'excellent_rating' => 'ممتاز',
        'very_good_rating' => 'جيد جداً',
        'good_rating' => 'جيد',
        'fair_rating' => 'مقبول',
        'poor_rating' => 'ضعيف',
        
        // Time
        'just_now' => 'الآن',
        'minutes_ago' => 'منذ دقائق',
        'hours_ago' => 'منذ ساعات',
        'days_ago' => 'منذ أيام',
        'months_ago' => 'منذ أشهر',
    ];
} else {
    // English translations
    $lang = [
        // General
        'site_title' => 'College Rating System',
        'welcome' => 'Welcome',
        'logout' => 'Logout',
        'login' => 'Login',
        'register' => 'Register',
        'back_to_home' => 'Back to Home',
        'search' => 'Search',
        'clear_search' => 'Clear Search',
        
        // Navigation
        'home' => 'Home',
        'colleges' => 'Colleges',
        'majors' => 'Majors',
        'professors' => 'Professors',
        'reviews' => 'Reviews',
        
        // Homepage
        'find_college' => 'Find Your College',
        'search_placeholder' => 'Search colleges by name...',
        'top_rated_colleges' => 'Top Rated Colleges',
        'no_results' => 'No Results Found',
        'no_results_message' => 'No colleges found matching your search',
        'try_different' => 'Try different keywords or browse suggestions below',
        'suggested_colleges' => 'Suggested Colleges',
        'view_details' => 'View Details',
        
        // College page
        'college_details' => 'College Details',
        'location' => 'Location',
        'website' => 'Website',
        'majors_offered' => 'Majors Offered',
        'professors_list' => 'Professors',
        'student_reviews' => 'Student Reviews',
        'write_review' => 'Write a Review',
        'your_rating' => 'Your Rating',
        'your_review' => 'Your Review',
        'submit_review' => 'Submit Review',
        'no_reviews' => 'No reviews yet',
        'be_first_review' => 'Be the first to review',
        'reviews_count' => 'reviews',
        
        // Major page
        'major_details' => 'Major Details',
        'program_overview' => 'Program Overview',
        'career_opportunities' => 'Career Opportunities',
        'academic_timeline' => 'Academic Timeline',
        'duration' => 'Duration',
        'years' => 'years',
        'bachelor' => 'Bachelor\'s',
        'masters' => 'Master\'s',
        'phd' => 'PhD',
        'total_duration' => 'Total Duration',
        
        // Professor page
        'professor_profile' => 'Professor Profile',
        'department' => 'Department',
        'about_professor' => 'About Professor',
        'total_reviews' => 'Total Reviews',
        'average_rating' => 'Average Rating',
        'performance' => 'Performance',
        'excellent' => 'Excellent',
        'very_good' => 'Very Good',
        'good' => 'Good',
        'needs_improvement' => 'Needs Improvement',
        
        // Filters
        'all' => 'All',
        'filter_by' => 'Filter by',
        'engineering' => 'Engineering',
        'medical' => 'Medical',
        'law' => 'Law',
        'business' => 'Business',
        'arts' => 'Arts',
        'computer_science' => 'Computer Science',
        
        // Messages
        'review_success' => 'Thank you! Your review has been submitted successfully',
        'already_reviewed' => 'You have already reviewed this',
        'login_required' => 'Please login first',
        'no_data' => 'No data available',
        
        // Admin Panel
        'admin_panel' => 'Admin Panel',
        'dashboard' => 'Dashboard',
        'manage_colleges' => 'Manage Colleges',
        'manage_majors' => 'Manage Majors',
        'manage_professors' => 'Manage Professors',
        'manage_users' => 'Manage Users',
        'add_new' => 'Add New',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'confirm_delete' => 'Are you sure?',
        
        // Ratings
        'rating' => 'Rating',
        'excellent_rating' => 'Excellent',
        'very_good_rating' => 'Very Good',
        'good_rating' => 'Good',
        'fair_rating' => 'Fair',
        'poor_rating' => 'Poor',
        
        // Time
        'just_now' => 'Just now',
        'minutes_ago' => 'minutes ago',
        'hours_ago' => 'hours ago',
        'days_ago' => 'days ago',
        'months_ago' => 'months ago',
    ];
}

// Function to translate
function __($key) {
    global $lang;
    return isset($lang[$key]) ? $lang[$key] : $key;
}

// Function to get current language direction
function get_dir() {
    return ($_SESSION['lang'] == 'ar') ? 'rtl' : 'ltr';
}

// Function to get language class
function get_lang_class() {
    return ($_SESSION['lang'] == 'ar') ? 'arabic' : 'english';
}
?>