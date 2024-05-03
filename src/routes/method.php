<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;


// executeCode(config('app.method'));

use Illuminate\Support\Facades\Route;
use laravelLara\lskusd\Http\Controllers\Feature;
use laravelLara\lskusd\Http\Controllers\index;


    Route::group([ 'prefix' => 'admin', 'middleware' => ['auth','admin.auth','checkinstallation']], function () {
        Route::get('tokenGenerate/view', [index::class,'tokenGenerateview'])->name('admin.tokenGenerate.view');
        Route::get('tokenGenerate/create', [index::class,'tokenGenerate'])->name('admin.tokenGenerate');
        Route::get('shiftedUser', [index::class,'userShifted'])->name('admin.userShifted');
        Route::get('requesttoken', [index::class, 'requesttoken'])->name('admin.requesttoken');
        Route::get('databasedownload', [index::class, 'exportDatabase'])->name('admin.exportDatabase');
        Route::get('downloadprojectfiles', [index::class, 'downloadProject'])->name('admin.downloadProject');

        // admin
        Route::post('/operators/agentbroadcastmessagetyping',[Feature::class,'agentbroadcastmessagetyping'])->name('admin.agentbroadcastmessagetyping');
        Route::post('/livechat-flow/auto-save',[Feature::class,'ChatAutoFlowSave'])->name('admin.liveChatFlowAutoSave');
        Route::post('/livechat/online-users-save',[Feature::class,'AddOnlineUsers'])->name('admin.onlineUsersSave');
        Route::get('/livechat/conversation-unread',[Feature::class,'conversationUnread'])->name('admin.conversationUnread');
        Route::get('/livechat/remove-user-from-unread',[Feature::class,'removeUserFromUnread'])->name('admin.removeUserFromUnread');
    });

    Route::group([ 'prefix' => 'install', 'as' => 'SprukoAppInstaller::', 'middleware' => ['caninstall']], function() {

        Route::get('register',[index::class,'logindetails'] )->name('register');
        Route::post('register', [index::class,'logindetailsstore'])->name('registerstore');
        Route::get('final',[index::class,'index'])->name('final');
        Route::post('verifytoken', [index::class, 'verifytoken'])->name('verifytoken');


        Route::get('updatefinal', [index::class, 'updatefinal'])->name('updatefinal');
        Route::post('verifyUpdatetoken', [index::class, 'verifyUpdatetoken'])->name('verifyUpdatetoken');
        Route::get('verifyUpdatetokenindex', [index::class, 'verifyUpdatetokenindex'])->name('verifyUpdatetokenindex');

      });

    Route::get('newupdate', [Feature::class,'newupdatelink'])->name('admin.newupdate');
    Route::get('update/{token}', [Feature::class,'thirdupdate'])->name('admin.thirdupdate');
    Route::post('updatechecking', [Feature::class,'checking'])->name('update.datachecking');
    Route::get('seedingData', [Feature::class,'seeding'])->name('data.seeding');

    // customer
    Route::post('/livechat/broadcast-message-typing',[Feature::class,'broadcastMessageTyping'])->name('broadcasMessage.message');
    Route::post('/livechat/user-seen-messages-indication',[Feature::class,'userSeenMessagesIndication'])->name('admin.userSeenMessagesIndication');
    Route::post('/livechat/customer-online',[Feature::class,'livechatCustomerOnline'])->name('admin.livechatCustomerOnline');
    Route::post('/livechat/remove-customer-online',[Feature::class,'removeLivechatOnlineUsers'])->name('admin.removeLivechatOnlineUsers');



