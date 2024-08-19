<?php

use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostSubcategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\TeamsCategoryController;
use App\Http\Controllers\TeamsController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\WhatwedoController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\MBCategoryController;
use App\Http\Controllers\MBSubcategoryController;
use App\Http\Controllers\MBLearnerController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\MBProfessionalController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\MBPdfController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\MBProfessionalLearningController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    // return $request->user();
});

//authentication
Route::post("login", [UserController::class, 'login']);
Route::post('forgot-password', [UserController::class, 'forgotPassword']);
Route::post('reset-password', [UserController::class, 'reset']);

#Admin Role
Route::middleware(['auth:api', 'isAdmin'])->group(function () {
    //authentication
    Route::post('auth/register', [UserController::class, 'registerUser']);
    Route::post('auth/updateuser/{id}', [UserController::class, 'updateUser']);
});

Route::middleware(['auth:api'])->group(function () {
    //authentication
    Route::post('auth/logout', [UserController::class, 'logout']);
    Route::post('auth/change_password', [UserController::class, 'changePassword']);
    Route::get('auth/show_info', [UserController::class, 'showUserInfo']);
    Route::post('email/verification-notification', [UserController::class, 'sendVerificationEmail']);
    Route::get('verify-email/{id}/{hash}', [UserController::class, 'verify'])->name('verification.verify');
    //subscriber
    Route::get('subscriber', [SubscriberController::class, 'showSubscriber']);
    Route::get('subscriber/{id}', [SubscriberController::class, 'showSubscriberById']);
    Route::delete('subscriber/{id}', [SubscriberController::class, 'deleteSubscriber']);
    //contact us
    Route::get('contact_us', [ContactUsController::class, 'showContactUs']);
    Route::get('contact_us/{id}', [ContactUsController::class, 'showContactUsById']);
    Route::delete('contact_us/{id}', [ContactUsController::class, 'deleteContactUs']);
    //Team category 
    Route::post('teams_category', [TeamsCategoryController::class, 'createTeamsCategory']);
    Route::delete('teams_category/{id}', [TeamsCategoryController::class, 'deleteTeamsCategory']);
    Route::put('teams_category/{id}', [TeamsCategoryController::class, 'updateTeamsCategory']);
    //Teams
    Route::post('teams', [TeamsController::class, 'createTeams']);
    Route::delete('teams/{id}', [TeamsController::class, 'deleteTeams']);
    Route::put('teams/{id}', [TeamsController::class, 'updateTeams']);
    //Slider
    Route::post('slider', [SliderController::class, 'createSlider']);
    Route::delete('slider/{id}', [SliderController::class, 'deleteSlider']);
    Route::put('slider/{id}', [SliderController::class, 'updateSlider']);
    //Partner
    Route::post('partner', [PartnerController::class, 'createPartner']);
    Route::delete('partner/{id}', [PartnerController::class, 'deletePartner']);
    Route::put('partner/{id}', [PartnerController::class, 'updatePartner']);
    //Service
    Route::post('service', [ServiceController::class, 'createService']);
    Route::delete('service/{id}', [ServiceController::class, 'deleteService']);
    Route::put('service/{id}', [ServiceController::class, 'updateService']);
    //What we do
    Route::post('whatwedo', [WhatwedoController::class, 'createWhatwedo']);
    Route::delete('whatwedo/{id}', [WhatwedoController::class, 'deleteWhatwedo']);
    Route::put('whatwedo/{id}', [WhatwedoController::class, 'updateWhatwedo']);
    //Branch
    Route::post('branch', [BranchController::class, 'createBranch']);
    Route::delete('branch/{id}', [BranchController::class, 'deleteBranch']);
    Route::put('branch/{id}', [BranchController::class, 'updateBranch']);
    //Post category
    Route::post('post_category', [PostCategoryController::class, 'createPostCategory']);
    Route::delete('post_category/{id}', [PostCategoryController::class, 'deletePostCategory']);
    Route::put('post_category/{id}', [PostCategoryController::class, 'updatePostCategory']);
    //Post subcategory
    Route::post('post_subcategory', [PostSubcategoryController::class, 'createPostSubcategory']);
    Route::delete('post_subcategory/{id}', [PostSubcategoryController::class, 'deletePostSubcategory']);
    Route::put('post_subcategory/{id}', [PostSubcategoryController::class, 'updatePostSubcategory']);
    //Post
    Route::post('post', [PostController::class, 'createPost']);
    Route::delete('post/{id}', [PostController::class, 'deletePost']);
    Route::put('post/{id}', [PostController::class, 'updatePost']);
    Route::get('post_admin', [PostController::class, 'adminSearchFunction']);
    Route::get('post_admin/{slug}', [PostController::class, 'adminGetArticle']);
    //Comment
    Route::delete('comment/{id}', [CommentController::class, 'deleteComment']);
    Route::put('comment/{id}', [CommentController::class, 'updateComment']);
    //Method bank category
    Route::post('method_bank_category', [MBCategoryController::class, 'createMBCategory']);
    Route::delete('method_bank_category/{id}', [MBCategoryController::class, 'deleteMBCategory']);
    Route::put('method_bank_category/{id}', [MBCategoryController::class, 'updateMBCategory']);
    //Method bank category
    Route::post('method_bank_subcategory', [MBSubcategoryController::class, 'createMBSubcategory']);
    Route::delete('method_bank_subcategory/{id}', [MBSubcategoryController::class, 'deleteMBSubcategory']);
    Route::put('method_bank_subcategory/{id}', [MBSubcategoryController::class, 'updateMBSubcategory']);
    //Method bank learner
    Route::post('mb_learner', [MBLearnerController::class, 'createMB']);
    Route::delete('mb_learner/{id}', [MBLearnerController::class, 'deleteMB']);
    Route::put('mb_learner/{id}', [MBLearnerController::class, 'updateMB']);
    Route::get('mb_learner_admin', [MBLearnerController::class, 'adminSearchFunction']);
    Route::get('mb_learner_admin/{slug}', [MBLearnerController::class, 'adminGetArticle']);
    //Method bank profestional
    Route::post('mb_professional', [MBProfessionalController::class, 'createMBProfessional']);
    Route::delete('mb_professional/{id}', [MBProfessionalController::class, 'deleteMBProfessional']);
    Route::put('mb_professional/{id}', [MBProfessionalController::class, 'updateMBProfessional']);
    Route::get('mb_professional_admin', [MBProfessionalController::class, 'adminSearchFunction']);
    Route::get('mb_professional_admin/{slug}', [MBProfessionalController::class, 'adminGetArticle']);
    //Gallery
    Route::post('mb_pdf', [MBPdfController::class, 'createMBPdf']);
    Route::delete('mb_pdf/{id}', [MBPdfController::class, 'deleteMBPdf']);
    Route::put('mb_pdf/{id}', [MBPdfController::class, 'updateMBPdf']);
    //FAQ
    Route::post('faq', [FAQController::class, 'createFAQ']);
    Route::delete('faq/{id}', [FAQController::class, 'deleteFAQ']);
    Route::put('faq/{id}', [FAQController::class, 'updateFAQ']);
    //Gallery
    Route::post('gallery', [GalleryController::class, 'createGallery']);
    Route::delete('gallery/{id}', [GalleryController::class, 'deleteGallery']);
    Route::put('gallery/{id}', [GalleryController::class, 'updateGallery']);
    //Publication
    Route::post('publication', [PublicationController::class, 'createPublication']);
    Route::delete('publication/{id}', [PublicationController::class, 'deletePublication']);
    Route::put('publication/{id}', [PublicationController::class, 'updatePublication']);
    //Video
    Route::post('video', [VideoController::class, 'createVideo']);
    Route::delete('video/{id}', [VideoController::class, 'deleteVideo']);
    Route::put('video/{id}', [VideoController::class, 'updateVideo']);
    //Method Bank Professional Learning
    Route::post('mb_professional_learning', [MBProfessionalLearningController::class, 'creatembLearning']);
    Route::delete('mb_professional_learning/{id}', [MBProfessionalLearningController::class, 'deletembLearning']);
    Route::put('mb_professional_learning/{id}', [MBProfessionalLearningController::class, 'updatembLearning']);
});
//subscriber
Route::post('subscriber', [SubscriberController::class, 'createSubscriber']);
//contact us
Route::post('contact_us', [ContactUsController::class, 'createContactUs']);
//Team category 
Route::get('teams_category', [TeamsCategoryController::class, 'showTeamsCategory']);
Route::get('teams_category/{id}', [TeamsCategoryController::class, 'showTeamsCategoryById']);
//Teams 
Route::get('teams', [TeamsController::class, 'showTeams']);
Route::get('teams/{id}', [TeamsController::class, 'showTeamsById']);
Route::get('teams_by_type', [TeamsController::class, 'getTeamsByType']);
Route::get('teams_filter', [TeamsController::class, 'searchFilter']);
//Slider
Route::get('slider', [SliderController::class, 'showSlider']);
Route::get('slider/{id}', [SliderController::class, 'showSliderById']);
//Partner
Route::get('partner', [PartnerController::class, 'showPartner']);
Route::get('partner/{id}', [PartnerController::class, 'showPartnerById']);
//Service
Route::get('service', [ServiceController::class, 'showService']);
Route::get('service/{id}', [ServiceController::class, 'showServiceById']);
//What we do
Route::get('whatwedo', [WhatwedoController::class, 'showWhatwedo']);
Route::get('whatwedo/{id}', [WhatwedoController::class, 'showWhatwedoById']);
//Branch
Route::get('branch', [BranchController::class, 'showBranch']);
Route::get('branch/{id}', [BranchController::class, 'showBranchById']);
//Post category
Route::get('post_category', [PostCategoryController::class, 'showPostCategory']);
Route::get('post_category/{id}', [PostCategoryController::class, 'showPostCategoryById']);
//Post subcategory
Route::get('post_subcategory/{id}', [PostSubcategoryController::class, 'showPostSubcategoryById']);
Route::get('post_subcategory', [PostSubcategoryController::class, 'getPostSubcategoryByPostCategory']);
//Post
Route::get('post', [PostController::class, 'customerSearchFunction']);
Route::get('post_and_video', [PostController::class, 'getArticleAndVideo']);
Route::get('post/{slug}', [PostController::class, 'customerGetArticle']);
Route::get('all_post', [PostController::class, 'searchFilterAllPost']);
//Comment
Route::post('comment', [CommentController::class, 'createComment']);
Route::get('comment', [CommentController::class, 'getComment']);
Route::get('comment/{id}', [CommentController::class, 'getCommentById']);
//Method bank category
Route::get('method_bank_category', [MBCategoryController::class, 'showMBCategory']);
Route::get('method_bank_category/{id}', [MBCategoryController::class, 'showMBCategoryById']);
//Method bank category
Route::get('method_bank_subcategory', [MBSubcategoryController::class, 'getMBSubcategory']);
Route::get('method_bank_subcategory/{id}', [MBSubcategoryController::class, 'getMBSubcategoryById']);
//Method bank
Route::get('mb_learner', [MBLearnerController::class, 'customerSearchFunction']);
Route::get('mb_learner_and_video', [MBLearnerController::class, 'getArticleAndVideo']);
Route::get('mb_learner/{slug}', [MBLearnerController::class, 'customerGetArticle']);
Route::get('search_mb', [MBLearnerController::class, 'searchFilterAllMBArticles']);
//Method bank profestional
Route::get('mb_professional', [MBProfessionalController::class, 'customerSearchFunction']);
Route::get('mb_professional_and_video', [MBProfessionalController::class, 'getArticleAndVideo']);
Route::get('mb_professional/{slug}', [MBProfessionalController::class, 'customerGetArticle']);
//Method Bank Pdf
Route::get('mb_pdf', [MBPdfController::class, 'searchFilter']);
Route::get('mb_pdf/{id}', [MBPdfController::class, 'showMBPdfById']);
//FAQ
Route::get('faq', [FAQController::class, 'searchFilter']);
Route::get('faq/{id}', [FAQController::class, 'showFAQById']);
Route::get('faq_by_type', [FAQController::class, 'getFAQByType']);
//Gallery
Route::get('gallery', [GalleryController::class, 'showGallery']);
Route::get('gallery/{id}', [GalleryController::class, 'showGalleryById']);
//Publication
Route::get('publication', [PublicationController::class, 'searchFilter']);
Route::get('publication/{id}', [PublicationController::class, 'showPublicationById']);
//Video
Route::get('video', [VideoController::class, 'showVideo']);
Route::get('video/{id}', [VideoController::class, 'getVideoById']);
//Methhod Bank Professional Learning
Route::get('mb_professional_learning', [MBProfessionalLearningController::class, 'searchFilterForMbLearning']);
Route::get('mb_professional_learning/{id}', [MBProfessionalLearningController::class, 'getmbLearningById']);
