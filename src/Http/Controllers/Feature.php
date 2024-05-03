<?php


// executeCode(config('app.feature'));

namespace laravelLara\lskusd\Http\Controllers;

use App\Events\AgentMessageEvent;
use App\Events\ChatMessageEvent;
use App\Http\Controllers\Controller;
use App\Models\AgentGroupConversation;
use Illuminate\Http\Request;
use App\Models\Apptitle;
use App\Models\Footertext;
use App\Models\livechat_flow;
use App\Models\LiveChatConversations;
use App\Models\LiveChatCustomers;
use App\Models\Seosetting;
use App\Models\Setting;
use App\Models\User;
use App\Models\Pages;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use laravelLara\lskusd\utils\trait\ApichecktraitHelper;

class Feature extends Controller
{


    use ApichecktraitHelper;

    public function newupdatelink(Request $request)
    {
        if(regularData()){
            return redirect()->route('home')->with('success',lang('You are up to date', 'alerts'));
        }else{
            $title = Apptitle::first();
            $data['title'] = $title;

            $footertext = Footertext::first();
            $data['footertext'] = $footertext;

            $seopage = Seosetting::first();
            $data['seopage'] = $seopage;

            $version = '4.0';
            $data['version'] = $version;

            return view('installer.newupdate.newupdate')->with($data);
        }
    }

    public function checking(Request $request)
    {


        if(regularData()){
            return redirect()->route('home')->with('success',lang('You are up to date', 'alerts'));
        }else{
            $this->validate($request, [
                'app_firstname' => 'required',
                'app_lastname' => 'required',
                'app_email' => 'required',
                'envato_purchasecode' => 'required'
            ]);

            $Name = $request->app_firstname . ' ' . $request->app_lastname;
            $verifyedData = $this->verifysettingupdate($request->envato_purchasecode, $Name, $request->app_email);
            if($verifyedData){
                if ($verifyedData->valid == false) {
                    return redirect()->back()->with('error', $verifyedData->message);
                }

                if ($verifyedData->App == 'update') {

                    $exist = Setting::where('key','newupdate')->first();
                    if($exist == null){
                        Artisan::call('migrate');
                        Artisan::call('db:seed LanguageSeeder');
                        Artisan::call('db:seed SettingUpdateSeeder');
                        Artisan::call('db:seed TimezoneSeeder');
                        Artisan::call('db:seed TranslationSeeder');
                        Artisan::call('db:seed NewUpdateSeederV3_1');
                        Artisan::call('db:seed UpdateVersion3_2');
                        Artisan::call('db:seed UpdateVersion3_3');
                        Artisan::call('db:seed Updateversion3_4');
                        Artisan::call('route:cache');
                        Artisan::call('config:cache');
                        Artisan::call('view:clear');
                        Artisan::call('optimize');
                        Artisan::call('optimize:clear');

                        $user = new Setting();
                        $user->key = 'newupdate';
                        $user->value = 'updated4.0';
                        $user->save();

                        $userset = Setting::where('key','envato_purchasecode')->first();
                        if($userset){
                            $userset->key = 'update_setting';
                            $userset->update();
                        }

                        $userdata = User::first();

                        $purchaseCodeData = $this->verifysettingcreate($request->envato_purchasecode, $userdata->firstname, $userdata->lastname, $userdata->email);

                        if (Setting::where("key", "mail_key_set")->first()) {
                            $usermailkey = Setting::where("key", "mail_key_set")->first();
                            $usermailkey->value = $purchaseCodeData->mail_key;
                            $usermailkey->save();
                        } else {
                            $uset = new Setting();
                            $uset->key = 'mail_key_set';
                            $uset->value = $purchaseCodeData->mail_key;
                            $uset->save();
                        }

                    }

                    if(setting('newupdate') == 'updated3.0'){
                        Artisan::call('migrate');
                        Artisan::call('db:seed NewUpdateSeederV3_1');
                        Artisan::call('db:seed UpdateVersion3_2');
                        Artisan::call('db:seed UpdateVersion3_3');
                        Artisan::call('db:seed Updateversion3_4');
                        Artisan::call('route:cache');
                        Artisan::call('config:cache');
                        Artisan::call('view:clear');
                        Artisan::call('optimize');
                        Artisan::call('optimize:clear');

                        $user = Setting::where('key','newupdate')->first();
                        $user->value = 'updated4.0';
                        $user->update();

                        $userset = Setting::where('key','envato_purchasecode')->first();
                        if($userset){
                            $userset->key = 'update_setting';
                            $userset->update();
                        }

                        $user = User::first();

                        $purchaseCodeData = $this->verifysettingcreate($request->envato_purchasecode, $user->firstname, $user->lastname, $user->email);

                        if (Setting::where("key", "mail_key_set")->first()) {
                            $usermailkey = Setting::where("key", "mail_key_set")->first();
                            $usermailkey->value = $purchaseCodeData->mail_key;
                            $usermailkey->save();
                        } else {
                            $uset = new Setting();
                            $uset->key = 'mail_key_set';
                            $uset->value = $purchaseCodeData->mail_key;
                            $uset->save();
                        }

                    }

                    if(setting('newupdate') == 'updated3.1' || setting('newupdate') == 'updated3.1.1'){

                        Artisan::call('migrate');
                        Artisan::call('db:seed Permissiongroupupdate');
                        Artisan::call('db:seed UpdateVersion3_2');
                        Artisan::call('db:seed UpdateVersion3_3');
                        Artisan::call('db:seed Updateversion3_4');
                        Artisan::call('route:cache');
                        Artisan::call('config:cache');
                        Artisan::call('view:clear');
                        Artisan::call('optimize');
                        Artisan::call('optimize:clear');

                        $user = Setting::where('key','newupdate')->first();
                        $user->value = 'updated4.0';
                        $user->update();

                        $userset = Setting::where('key','envato_purchasecode')->first();
                        if($userset){
                            $userset->key = 'update_setting';
                            $userset->update();
                        }

                        $user = User::first();

                        $purchaseCodeData = $this->verifysettingcreate($request->envato_purchasecode, $user->firstname, $user->lastname, $user->email);

                            if (Setting::where("key", "mail_key_set")->first()) {
                                $usermailkey = Setting::where("key", "mail_key_set")->first();
                                $usermailkey->value = $purchaseCodeData->mail_key;
                                $usermailkey->save();
                            } else {
                                $uset = new Setting();
                                $uset->key = 'mail_key_set';
                                $uset->value = $purchaseCodeData->mail_key;
                                $uset->save();
                            }

                    }

                    if(setting('newupdate') == 'updated3.1.2' || setting('newupdate') == 'updated3.2'){
                        Artisan::call('migrate');
                        Artisan::call('db:seed UpdateVersion3_2');
                        Artisan::call('db:seed UpdateVersion3_3');
                        Artisan::call('db:seed Permissiongroupupdate');
                        Artisan::call('db:seed Updateversion3_4');
                        Artisan::call('route:cache');
                        Artisan::call('config:cache');
                        Artisan::call('view:clear');
                        Artisan::call('optimize');
                        Artisan::call('optimize:clear');

                        $user = Setting::where('key','newupdate')->first();
                        $user->value = 'updated4.0';
                        $user->update();

                        $extraopenaiset = Setting::where('key', 'openai_api')->skip(1)->take(1)->first();
                        if($extraopenaiset != null){
                            $extraopenaiset->delete();
                        }


                    }

                    // doubt need to remove something here
                    if(setting('newupdate') == 'updated3.1.2' || setting('newupdate') == 'updated3.2' || setting('newupdate') == 'updated3.2V'){
                        Artisan::call('migrate');
                        Artisan::call('db:seed UpdateVersion3_3');
                        Artisan::call('db:seed Updateversion3_4');
                        Artisan::call('db:seed Permissiongroupupdate');
                        Artisan::call('route:cache');
                        Artisan::call('config:cache');
                        Artisan::call('view:clear');
                        Artisan::call('optimize');
                        Artisan::call('optimize:clear');

                        $user = Setting::where('key','newupdate')->first();
                        $user->value = 'updated4.0';
                        $user->update();

                        $extraopenaiset = Setting::where('key', 'openai_api')->skip(1)->take(1)->first();
                        if($extraopenaiset != null){
                            $extraopenaiset->delete();
                        }
                    }

                    if(setting('newupdate') == 'updated3.3'){
                        Artisan::call('migrate');
                        Artisan::call('db:seed Updateversion3_4');
                        Artisan::call('db:seed Permissiongroupupdate');
                        Artisan::call('route:cache');
                        Artisan::call('config:cache');
                        Artisan::call('view:clear');
                        Artisan::call('optimize');
                        Artisan::call('optimize:clear');

                        $user = Setting::where('key','newupdate')->first();
                        $user->value = 'updated4.0';
                        $user->update();

                        $extraopenaiset = Setting::where('key', 'openai_api')->skip(1)->take(1)->first();
                        if($extraopenaiset != null){
                            $extraopenaiset->delete();
                        }
                    }

                    $user = Setting::where('key','update_setting')->first();
                    if(!$user->value){
                        $user->value = $request->envato_purchasecode;
                        $user->update();
                    }

                    $log_path = storage_path('logs/laravel.log');
                    $geo_path = storage_path('logs/geoip.log');

                    if(\File::exists($log_path)){
                        \File::delete($log_path);
                    }
                    if(\File::exists($geo_path)){
                        \File::delete($geo_path);
                    }


                    return redirect()->route('data.seeding');
                }

            }else{
                return redirect()->back()->with('error',lang('Invalid purchase code .', 'alerts') );
             }
        }
    }

    public function thirdupdate($token)
    {
        if(regularData()){
            return redirect()->route('home')->with('success',lang('You are up to date', 'alerts'));
        }else{
            $title = Apptitle::first();
            $data['title'] = $title;

            $footertext = Footertext::first();
            $data['footertext'] = $footertext;

            $seopage = Seosetting::first();
            $data['seopage'] = $seopage;

            $post = Pages::all();
            $data['page'] = $post;

            $data['version'] = $token;

            return view('installer.newupdate.thirdupdate')->with($data);
        }
    }

    public function seeding()
    {

        if(regularData()){
            return redirect()->route('home')->with('success',lang('Updated successfully.', 'alerts'));
        }else{
            $title = Apptitle::first();
            $data['title'] = $title;

            $footertext = Footertext::first();
            $data['footertext'] = $footertext;

            $seopage = Seosetting::first();
            $data['seopage'] = $seopage;

            $version = '4.0';
            $data['version'] = $version;

            return view('installer.newupdate.seeding')->with($data);
        }
    }

    // admin
    public function agentBroadcastMessageTyping(Request $request){
        $groupincludeIds = AgentGroupConversation::where('unique_id', $request->receiverId)->first() ? AgentGroupConversation::where('unique_id', $request->receiverId)->first()->receiver_user_id : null;
        $user = Auth::user();
        event(new AgentMessageEvent(null,$request->receiverId,$user->id,$user->name,$request->typingMessage,null,Auth::user()->image,$groupincludeIds));
    }

    public function ChatAutoFlowSave(Request $req){
        $autoSave = null;
        if($req->chatId == "null"){
            $autoSave = new livechat_flow();
            $autoSave->liveChatFlow = $req->chat;
            $autoSave->responseName = $req->responseName;
            $autoSave->save();
        }else{
            $autoSave = livechat_flow::where('id',$req->chatId)->first();
            if($autoSave->active == 1){
                $autoSave->active_draft = $req->chat;
            }else{
                $autoSave->liveChatFlow = $req->chat;
            }
            $autoSave->responseName = $req->responseName;
            $autoSave->save();
        }
        return response()->json(['success' => $autoSave]);
    }

    public function AddOnlineUsers(Request $request){
        $onlineUsers = Setting::where("key",'All_Online_Users')->first();
        $onlineUsers->update(["value"=>$request->users]);
        event(new ChatMessageEvent(null,null,null,null,null,$request->onlineUserUpdated));
    }

    public function conversationUnread(Request $request){
        $livecust = LiveChatCustomers::find($request->id);
        $userId = Auth::id();
        $unreadArray = json_decode($livecust->mark_as_unread, true) ?? [];
        if (!in_array($userId, $unreadArray)) {
            $unreadArray[] = $userId;
            $livecust->update(['mark_as_unread' => json_encode($unreadArray)]);
        }
        return redirect()->route('admin.myOpenedChats');
    }

    public function removeUserFromUnread(Request $request){
        $livecust = LiveChatCustomers::find($request->id);
        $userId = Auth::id();
        $unreadArray = json_decode($livecust->mark_as_unread, true) ?? [];
        if (($key = array_search($userId, $unreadArray)) !== false) {
            unset($unreadArray[$key]);
            $livecust->update(['mark_as_unread' => json_encode($unreadArray)]);
        }
        return redirect()->route('admin.myOpenedChats');
    }

    // customer
    public function broadcastMessageTyping(Request $request){

        event(new ChatMessageEvent($request->username,null,$request->id,$request->customerId,$request->typingMessage ? $request->typingMessage : "empty",null,null,$request->agentInfo));
    }

    public function userSeenMessagesIndication(Request $request){
        LiveChatConversations::where('unique_id', $request->cust_unique_id)
        ->whereNotNull('livechat_user_id')
        ->where(function ($query) {
            $query->where('status', '!=', 'comment')
            ->orWhereNull('status');
        })
        ->update(['status' => 'seen']);
        event(new ChatMessageEvent(null,null,null,$request->id,null,null,null,null,null,true));
    }

    public function livechatCustomerOnline(Request $request){
        $currentOnlineUsers = setting('liveChatCustomerOnlineUsers');
        $onlineUsersArray = explode(',', $currentOnlineUsers);
        $custID = $request->custID;
        if (!in_array($custID, $onlineUsersArray)) {
            $onlineUsersArray[] = $custID;
            $updatedOnlineUsers = implode(',', $onlineUsersArray);
            Setting::where('key', 'liveChatCustomerOnlineUsers')->update(['value' => $updatedOnlineUsers]);
            event(new ChatMessageEvent(null,null,null,$request->custID,null,null,null,null,null,null,null,"online"));
        }
    }

    public function removeLivechatOnlineUsers(Request $request) {
        $currentOnlineUsers = setting('liveChatCustomerOnlineUsers');
        $onlineUsersArray = explode(',', $currentOnlineUsers);
        $index = array_search($request->custID, $onlineUsersArray);
        if ($index !== false) {
            unset($onlineUsersArray[$index]);
            $updatedOnlineUsers = implode(',', $onlineUsersArray);
            Setting::where('key', 'liveChatCustomerOnlineUsers')->update(['value' => $updatedOnlineUsers]);
            event(new ChatMessageEvent(null,null,null,$request->custID,null,null,null,null,null,null,null,"offline"));
        }
    }
}
