<?php

use App\Models\Org;
use App\Models\User;

// USER
use App\Mail\TestMail;
use App\Events\Example;
use App\Livewire\Inbox;
use Livewire\Volt\Volt;

// ADMIN / SUPERADMIN

use App\Models\GroupChat;
use App\Events\RoomExpired;
use App\Livewire\User\Chat;
use App\Livewire\User\Feed;
use App\Livewire\OrgProfile;

// GLOBAL

use App\Livewire\VotersList;


// MODELS


use App\Livewire\VotingRoom;
use Illuminate\Http\Request;
use App\Livewire\LandingPage;
use App\Livewire\User\Voting;
use App\Events\UserRegistered;
use App\Events\VotedCandidate;
use App\Events\ChatJoinRequest;
use App\Livewire\Admin\Archive;
use App\Events\RecentActivities;
use App\Events\GroupUserApproved;
use App\Models\Feed as FeedModel;
use App\Livewire\GuestLandingPage;
use App\Models\User as  UserModel;
use App\Livewire\OrgFollowRequests;
use App\Livewire\User\Advertisement;
use Illuminate\Support\Facades\Mail;
use App\Livewire\Admin\Org\ManageOrg;
use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Chat\ManageChat;
use App\Livewire\Admin\Feed\ManageFeed;
use App\Livewire\Admin\User\ManageUsers;
use App\Livewire\DashboardRecentActivity;
use App\Events\ManageFeed as BroadcastFeed;
use App\Livewire\Admin\Voting\ManageVoting;
use App\Events\GroupChat as  GroupChatEvent;
use App\Notifications\UniversalNotification;
use App\Models\VotingRoom as  VotingRoomModel;
use App\Events\ManageVoting as BroadcastVotingRoom;
use App\Models\Advertisement as  AdvertisementModel;
use App\Livewire\Admin\Advertisement\ManageAdvertisement;
use App\Events\ManageAdvertisement as BroadcastAdvertisement;

Route::get('/test-email', function () {
    Mail::raw('This is a raw test email from Laravel.', function ($message) {
        $message->to('carreon.carll@gmail.com')
                ->subject('Laravel Test Email');
    });

    return 'Test email sent!';
});

Route::get('/test-inbox', function () {
    $user = User::find(5);
    Notification::send($user, new UniversalNotification(
                'Group Chat',
                " Your request from group \"test\" was approved by " . auth()->user()->name,
                auth()->id()
            ));

        });



// Route::get('redirectAfter_LoginOrRegister', function () {

//     if (!auth()->check()) {
//         return redirect()->route('login'); 
//     }

//     if (auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin' ) {
//         return redirect()->route('dashboard');
//     } elseif(auth()->user()->role === 'user' ) {
//         return redirect()->route('landing-page');
//     } elseif(auth()->user()->role === 'org') {
//         return redirect()->route('landing-page');
//     }

// })->name('redirectToPage');

// Route::get('/', function () {


//     if (auth()->check()) {
//         return redirect()->route('landing-page');
//     } else {
//         return redirect()->route('guest.home');
//     }
    


// })->name('home');

// Route::get('/', LandingPage::class)->name('home');

// Route::get('checkStatus', function(){

//     if(auth()->user()->status === 'pending')
//     {
//         return redirect()->route('not-verified');
//     }else{

//         return redirect()->route('home');
//     }

// })->name('checkStatus');

// Route::get('/', GuestLandingPage::class)->name('home');
Route::get('/', LandingPage::class)->name('home');

Route::middleware(['auth', 'approved'])->group(function () {

    // Route::get('/home', LandingPage::class)->name('landing-page');



    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/avatar', 'settings.avatar')->name('settings.avatar');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    

    Route::middleware('admin.only')->group(function () {
        Route::get('/dashboard', function () {

            return view('dashboard');
        })->name('dashboard');
        

        Route::get('admin/user/manage-users', ManageUsers::class)->name('admin.user.manage-users');
        Route::get('admin/org/manage-org', ManageOrg::class)->name('admin.org.manage-org');
        Route::get('admin/archive', Archive::class)->name('admin.archive');
    });

    Route::middleware('sharedRole')->group(function() {

        
        // Route::get('admin/voting/manage-voting', ManageVoting::class)->name('admin.voting.manage-voting');
        Route::get('admin/chat/manage-chat', ManageChat::class)->name('admin.chat.manage-chat');
        // Route::get('admin/advertisement/manage-advertisement', ManageAdvertisement::class)->name('admin.advertisement.manage-advertisement');
        // Route::get('admin/feed/manage-feed', ManageFeed::class)->name('admin.feed.manage-feed');
        

    });


    Route::middleware('user.only')->group(function () {
            // Route::get('user/feed', Feed::class)->name('user.feed');
            // Route::get('user/advertisement', Advertisement::class)->name('user.advertisement');
        // routes/web.php

            Route::get('user/chat/{groupCode?}', Chat::class)->name('user.chat');

            // Route::get('user/voting', Voting::class)->name('user.voting');
            // });

            
            // Route::get('/chat/{groupChat}', Chat::class)->name('chat.room');

    });

    // ALL
    Route::get('/voting-room/{id}', VotingRoom::class)->name('voting.room');
    Route::get('/voters-list', VotersList::class)->name('voters.list');
    Route::get('org-profile/{orgId}', OrgProfile::class)->name('org.profile');
    Route::get('org/follow-request', OrgFollowRequests::class)->name('org.follow-request');
    // Route::get('org-profile', OrgProfile::class)->name('org.profile');

    Route::view('/registered-success', 'registered-successfully')->name('registered-success');
    Route::view('/not-verified', 'not-verified')->name('not-verified');


    Route::get('voting', ManageVoting::class)->name('voting');
    Route::get('advertisement', ManageAdvertisement::class)->name('advertisement');
    Route::get('feed', ManageFeed::class)->name('feed');
    Route::get('inbox', Inbox::class)->name('inbox');




});

// Route::get('/', GuestLandingPage::class)->name('guest-landing-page');

Route::get('/test-broadcast', function() {
    broadcast(new GroupUserApproved(6));
    return 'event sent';
});

    Route::get('test-notif', function () {
    $authId = Auth::id();

$otherUsers = User::where('id', '!=', $authId)->get();

    $user = User::find(2);

    Notification::send($otherUsers, new UniversalNotification(
         'feed',
         "$user->name posted a feed!",
          $user->id,
    ));


    dd('sent at ' . $user->name);



if ($authId) {
    // Get all users whose ID is not equal to the authenticated user's ID
    

    Notification::send($otherUsers, new UniversalNotification(
        'feed',
         "$user->name posted a feed!",
          $user->id,
    ));
}

return 'sent';
    });

Route::get('expire', function(){
    $user = User::find(2);

              Notification::send($user, new UniversalNotification(
                'Group Chat',
                "Group \"group\" has expired!",
                null // sender_id, you can use owner or system
            ));
        return 'sent';
});

require __DIR__.'/auth.php';
