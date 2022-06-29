<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OTP;
use App\Models\App_User;
use App\Models\App_User_surrogate;
use App\Models\Community;
use App\Models\Contact;
use App\Models\MilestoneUser;
use App\Models\Milestone;
use App\Models\User;
use App\Models\Milestone_Image;
use App\Models\Admin_Question;
use App\Models\Admin_Question_Ans;
use App\Models\Pregnancy_Milestone_Status;
use App\Models\Pregnancy_Milestone;
use Illuminate\Support\Facades\Hash;
use Auth;
use Response;
use DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppapiController extends Controller
{
    // ********************************************* For user registration process *******************************************
    public function sendotp(Request $request){
        
        $digits = 4;
        $otp = rand(pow(10, $digits-1), pow(10, $digits)-1);
        
        $getotp = OTP::where('email',$request->email)->get();

        if(count($getotp)>0){

            $otp_id = $getotp[0]['id'];
            $otpss = OTP::find($otp_id);
            $otpss->otp = $otp;
            $otpss->save();

        }else{
            
            $otps = new OTP;
            $otps->otp = $otp;
            $otps->email = $request->email;
            $otps->save();

        }
        $parent = App_User::where('parent_email',$request->email)->get();
        if(count($parent)>0){

        }else{
            $parent = new App_User;
            $parent->parent_email = $request->email;
            $parent->save();
        }
        $surrogate = App_User_surrogate::where('email',$request->email)->get();
        if(count($surrogate)>0){

        }else{
            $surrogate = new App_User_surrogate;
            $surrogate->email = $request->email;
            $surrogate->save();
        }

        
        return Response::json([
            'error_code' => '1002',
            'status' => '200',
            'message' => 'Otp sent successfully'
        ], 200);
    }
    public function checkotp(Request $request){

        $getotp = OTP::where('email',$request->email)->get();
        if(count($getotp)>0){
            if($getotp[0]['otp'] == $request->otp){
                
                $otp_id = $getotp[0]['id'];
                $otpss = OTP::find($otp_id);
                $otpss->delete();
    
                return Response::json([
                    'error_code' => '1002',
                    'status' => '200',
                    'message' => 'otp match successfully'
                ], 200);
    
            }else{
                return Response::json([
                    'error_code' => '1001',
                    'status' => '401',
                    'message' => 'otp has been not match'
                ], 401); 
            }
        }else{
            return Response::json([
                'error_code' => '1005',
                'status' => '404',
                'message' => 'Record not found'
            ], 404);
        }

    }
    public function registration(Request $request){

        $version = $request->header('version');

        if($request->type == 'surrogate'){
            $chek_email = App_User_surrogate::where('email',$request->email)->get();
            if(count($chek_email)>0){

                $id =  $chek_email[0]['id'];

                $user = App_User_surrogate::find($id);
                $user->name = $request->name;
                $user->password = Hash::make($request->password);
                $user->version = $version;
                $user->status = 'active';
                $user->save();

                $milestone = new Pregnancy_Milestone_Status;
                $milestone->user_id = $id;
                $milestone->type = 'surrogate';
                $milestone->status = 'inactive';
                $milestone->save();
                
            }
        }else{
            $chek_email = App_User::where('parent_email',$request->email)->get();
            if(count($chek_email)>0){

                $id =  $chek_email[0]['id'];

                $user = App_User::find($id);
                $user->parent_name = $request->name;
                $user->parent_password = Hash::make($request->password);
                $user->parent_version = $version;
                $user->parent_status = 'active';
                $user->save();

                $milestone = new Pregnancy_Milestone_Status;
                $milestone->user_id = $id;
                $milestone->type = 'parent';
                $milestone->status = 'inactive';
                $milestone->save();
            
            }
        }
        if($user){
            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'User register successfully'
            ], 201);
        }else{
            return Response::json([
                'error_code' => '1001',
                'status' => '401',
                'message' => 'User registration request fail'
            ], 401);
        }
        
    }
    public function resend_otp(Request $request){

        $getotp = OTP::where('email',$request->email)->get();
        if(count($getotp)>0){
            $otp = $getotp[0]['otp'];
            return Response::json([
                'error_code' => '1002',
                'status' => '200',
                'message' => 'Otp resent successfully'
            ], 200);
        }else{
            return Response::json([
                'error_code' => '1005',
                'status' => '404',
                'message' => 'Record not found'
            ], 404);
        }

    }
    public function get_user_detail(Request $request){

        $id = $request->userId;

        $type = $request->type;

        if($type == 'parent'){
            $user = App_User::find($id);
        }else{
            $user = App_User_surrogate::find($id);
        }
        return json_encode($user);
    }
    public function user_profile_update(Request $request,$id){

        if($request->image1){

            $image = $request->file('image1');

            $name1 = time().'1'.'.'.$image1->getClientOriginalExtension();

            $destinationPath = public_path('/images/profile');

            $image->move($destinationPath,$name1);
        }

        if($request->image2){
            
            $image = $request->file('image2');

            $name2 = time().'2'.'.'.$image2->getClientOriginalExtension();

            $destinationPath = public_path('/images/profile');

            $image->move($destinationPath,$name2);
        }

        if($request->type == 'surrogate'){
            $user = App_User_surrogate::find($id);
            $user->name = $request->name;
            $user->number = $request->number;
            $user->email = $request->email;
            $user->date_of_birth = $request->date_of_birth;
            $user->partner_name = $request->partner_name;
            $user->address = $request->address;
            if($request->image1)
            {
                $user->image1 = $name1;
            }
            $user->save();

            if($user){

                return Response::json([
                    'error_code' => '1004',
                    'status' => '201',
                    'message' => 'User updated successfully'
                ], 201);

            }else{

                return Response::json([
                    'error_code' => '1001',
                    'status' => '401',
                    'message' => 'User update request fail'
                ], 401);

            }
        }else{
            $user = App_User::find($id);
            $user->parent_name = $request->name;
            $user->parent_number = $request->number;
            $user->parent_email = $request->email;
            $user->parent_date_of_birth = $request->date_of_birth;
            $user->parent_partner_name = $request->partner_name;
            $user->parent_address = $request->address;
            if($request->image1)
            {
                $user->parent_image1 = $name1;
            }
            if($request->image2)
            {
                $user->parent_image2 = $name2;
            }
            $user->save();

            if($user){

                return Response::json([
                    'error_code' => '1004',
                    'status' => '201',
                    'message' => 'User updated successfully'
                ], 201);

            }else{

                return Response::json([
                    'error_code' => '1001',
                    'status' => '401',
                    'message' => 'User update request fail'
                ], 401);

            }
        }
        
    }
    public function login(Request $request){

    if($request->email && $request->password){
         
        $user = App_User::where('parent_email', $request->email)->where('parent_status','active')->first();
        if($user){
            $check_pass = Hash::check(request('password'), $user->parent_password);
            if($check_pass){
                $user_id = $user->id;
                $partner_id = $user->parent_partner_id;
                return Response::json([
                    'error_code' => '1002',
                    'status' => '200',
                    'message' => 'Login successfully',
                    'user_id' => $user_id,
                    'partner_id'=>$partner_id,
                    'type' => 'parent'
                ], 200);
            }else{
                return Response::json([
                    'error_code' => '1001',
                    'status' => '401',
                    'message' => 'Login faild'
                ], 401);
            }
        }else{
        $user = App_User_surrogate::where('email', $request->email)->where('status','active')->first();
            if($user){
                $check_pass = Hash::check(request('password'), $user->password);
                if($check_pass){
                    $user_id = $user->id;
                    $partner_id = $user->partner_id;
                    return Response::json([
                        'error_code' => '1002',
                        'status' => '200',
                        'message' => 'Login successfully',
                        'user_id' => $user_id,
                        'partner_id' => $partner_id,
                        'type' => 'surrogate'
                    ], 200);
                }else{
                    return Response::json([
                        'error_code' => '1001',
                        'status' => '401',
                        'message' => 'Login faild'
                    ], 401);
                }
            }else{
                return Response::json([
                    'error_code' => '1001',
                    'status' => '401',
                    'message' => 'Login faild'
                ], 401);
            }
        }
        
    }else{
        return Response::json([
            'error_code' => '1006',
            'status' => '500',
            'message' => 'Login faild, username & password are required'
        ], 500);
    }
    }
    // *************************************************** For community ***************************************************
    public function community_create(Request $request){

        $image = $request->file('image');

        $name = time().'.'.$image->getClientOriginalExtension();

        $destinationPath = public_path('/images/community');

        $image->move($destinationPath,$name);

        $community = new Community;
        $community->title = $request->title;
        $community->description = $request->description;
        $community->forum_link = $request->forum_link;
        $community->insta_link = $request->insta_link;
        $community->image = $name;
        $community->user_id = '1';
        $community->save();

        if($community){
            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'Community created successfully'
            ], 201); 
        }else{
            return Response::json([
                'error_code' => '1001',
                'status' => '401',
                'message' => 'Community has been not created'
            ], 401);
        }
    }
    // ****************************************************** For contact **************************************************
    public function contact_create(Request $request){

        $image = $request->file('image');

        $name = time().'.'.$image->getClientOriginalExtension();

        $destinationPath = public_path('/images/contact');

        $image->move($destinationPath,$name);

        $contact = new Contact;
        $contact->title = $request->title;
        $contact->agency_name = $request->agency_name;
        $contact->agency_email = $request->agency_email;
        $contact->agency_number = $request->agency_number;
        $contact->image = $name;
        $contact->user_id = '1';
        $contact->save();

        if($contact){
            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'Contact created successfully'
            ], 201); 
        }else{
            return Response::json([
                'error_code' => '1001',
                'status' => '401',
                'message' => 'Contact has been not created'
            ], 401);
        }
    }
    // **************************************************** For milestone *************************************************
    public function get_milestone(Request $request){
        $type = $request->type;
        $id = $request->user_id;
        if($type == 'parent'){
            $commen_milestone = Milestone::where('user_type','common')->get();
            $milestone = Milestone::where('user_type',$type)->where('user_id',$id)->get();
        }
        return json_encode([
            'common_milestone' => $commen_milestone,
            'milestone' => $milestone
        ]);
    }
    public function assign_question(Request $request){

        $id = $request->milestone_id;

        if($request->type == 'parent'){
            $question = MilestoneUser::find($id);
            $question->surrogate_id = $request->surrogate_id;
            $question->save();
        }else{
            $question = MilestoneUser::find($id);
            $question->parent_id = $request->parent_id;
            $question->save();
        }

        if($question){
            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'Assign Milestone successfully'
            ], 201);
        }else{
            return Response::json([
                'error_code' => '1001',
                'status' => '401',
                'message' => 'Milestone has been not assign'
            ], 401);
        }
    }
    public function get_assign_milestone(Request $request){
        $surrogateUserId = $request->userId;
        $user = DB::table('milestone_users')
                ->join('milestones','milestone_users.milestone_id','=', 'milestones.id')
                ->where('milestone_users.surrogate_id', '=', $surrogateUserId)
                ->get();

        return $user;
    }
    public function create_milestone(Request $request){
        $milestone = new Milestone;
        $milestone->milestone = $request->milestone;
        $milestone->user_type = $request->user_type;
        $milestone->user_id = $request->user_id;
        $milestone->save();

        if($milestone){

            if($request->user_type == 'parent'){
                $mileston = new MilestoneUser;
                $mileston->parent_id = $request->user_id;
                $mileston->title = $request->milestone;
                $mileston->milestone_id = $milestone->id;
                $mileston->date = $request->date;
                $mileston->time = $request->time;
                $mileston->location = $request->location;
                $mileston->longitude = $request->longitude;
                $mileston->latitude = $request->latitude;
                $mileston->save();
            }else{
                $mileston = new MilestoneUser;
                $mileston->surrogate_id = $request->user_id;
                $mileston->title = $request->milestone;
                $mileston->milestone_id = $milestone->id;
                $mileston->date = $request->date;
                $mileston->time = $request->time;
                $mileston->location = $request->location;
                $mileston->langitude = $request->longitude;
                $mileston->latitude = $request->latitude;
                $mileston->save();
            }
            if($mileston){
                return Response::json([
                    'error_code' => '1004',
                    'status' => '201',
                    'message' => 'Milestone created successfully'
                ], 201);
            }else{
                return Response::json([
                'error_code' => '1001',
                'status' => '401',
                'message' => 'Milestone has been not created'
            ], 401); 
            }
        }else{
            return Response::json([
                'error_code' => '1001',
                'status' => '401',
                'message' => 'Milestone has been not created'
            ], 401);
        }
    }
    public function store_milestone_ans(Request $request){
        
        $id = $request->milestone_id;
        $type = $request->type;
        $user_id = $request->user_id;

        if($type == "parent"){
            $milestone = MilestoneUser::where('parent_id',$user_id)->where('milestone_id',$id)->get();
            $milestone_id = $milestone[0]['id'];
        }else{
            $milestone = MilestoneUser::where('surrogate_id',$user_id)->where('milestone_id',$id)->get();
            $milestone_id = $milestone[0]['id'];
        }

        foreach ($request->image as $key => $file) {

            $image = $file;

            $name = time().$key.'.'.$image->getClientOriginalExtension();
            
            $destinationPath = public_path('/images/milestone');
            
            $image->move($destinationPath,$name);

            $milestone_image = new Milestone_Image;
            $milestone_image->milestone_user_id = $milestone_id;
            $milestone_image->image = $name;
            $milestone_image->type = $type;
            $milestone_image->user_id = $user_id;
            $milestone_image->save();

        }

        $milestones = MilestoneUser::find($milestone_id);
        $milestones->note = $request->note;
        $milestones->save();

        if($milestone_image){
            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'Milestone image added successfully'
            ], 201);
        }else{
            return Response::json([
                'error_code' => '1001', 
                'status' => '401',
                'message' => 'Milestone image has been not added'
            ], 401);
        }
    }
    public function edit_milestone(Request $request){
        $type = $request->type;
        $user_id = $request->user_id;
        $milestone_id = $request->milestone_id;
        if($type == 'parent'){
            $milestone = MilestoneUser::where('milestone_id',$milestone_id)->where('parent_id',$user_id)->get();
            $milestoneId = $milestone[0]['id'];
            if($type == 'parent'){
                $milestone_image = Milestone_Image::where('milestone_user_id',$milestoneId)->where('user_id',$user_id)->where('type','parent')->get();
            }
        }else{
            $milestone = MilestoneUser::where('milestone_id',$milestone_id)->where('surrogate_id',$user_id)->get();
            $milestoneId = $milestone[0]['id'];
            if($type == 'surrogate'){
                $milestone_image = Milestone_Image::where('milestone_user_id',$milestoneId)->where('user_id',$user_id)->where('type','surrogate')->get();
            } 
        }
        return json_encode([
        'milestone' => $milestone,
        'milestone_image' => $milestone_image
        ]);
        
    }
    public function update_milestone_image(Request $request){

        $image = $request->image;

        $id = $request->image_id;

        $name = time().'.'.$image->getClientOriginalExtension();
        
        $destinationPath = public_path('/images/milestone');
        
        $image->move($destinationPath,$name);

        $milestone_image = Milestone_Image::find($id);
        $milestone_image->image = $name;
        $milestone_image->save();

        if($milestone_image){
            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'Milestone image updated successfully'
            ], 201);
        }else{
            return Response::json([
                'error_code' => '1001',
                'status' => '401',
                'message' => 'Milestone image has been not updated'
            ], 401);
        }
    }
    public function delete_milestone_image(Request $request){

        $id = $request->image_id;

        $milestone_image = Milestone_Image::find($id);
        $milestone_image->delete();

        if($milestone_image){
            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'Milestone image deleted successfully'
            ], 201);
        }else{
            return Response::json([
                'error_code' => '1001',
                'status' => '401',
                'message' => 'Milestone image has been not deleted'
            ], 401);
        }
    }
    public function save_note(Request $request){

        $id = $request->milestone_id;

        $milestones = MilestoneUser::find($id);
        $milestones->note = $request->note;
        $milestones->save();

        if($milestones){
            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'Note saved successfully'
            ], 201);
        }else{
            return Response::json([
                'error_code' => '1001', 
                'status' => '401',
                'message' => 'Note has been not saved'
            ], 401);
        }
    }
    public function update_milestone_ans_info(Request $request){
      
        $type = $request->type;
        $user_id = $request->user_id;
        $id = $request->milestone_id;

        if($type == 'parent'){
            $milestone = MilestoneUser::where('milestone_id',$id)->where('parent_id',$user_id)->get();
            $milestone_id = $milestone[0]['id'];
        }else{
            $milestone = MilestoneUser::where('milestone_id',$id)->where('surrogate_id',$user_id)->get();
            $milestone_id = $milestone[0]['id'];
        }

        $milestones = MilestoneUser::find($milestone_id);   
        $milestones->title = $request->title;
        $milestones->date = $request->date;
        $milestones->time = $request->time;
        $milestones->location = $request->location;
        $milestones->longitude = $request->longitude;
        $milestones->latitude = $request->latitude;
        $milestones->save();

        $milestone = Milestone::find($id);
        $milestone->milestone = $request->title;
        $milestone->save();

        if($milestones){
            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'Milestone updated successfully'
            ], 201);
        }else{
            return Response::json([
                'error_code' => '1001',
                'status' => '401',
                'message' => 'Milestone has been not updated'
            ], 401);
        }
    }
    // ************************************* For surrogate milestone ***************************************

    public function get_surrogate_milestone(Request $request){
        $id = $request->user_id;
        $milestone = $user = DB::table('milestone_users')
            ->join('milestones','milestone_users.milestone_id','=', 'milestones.id')
            ->where('milestone_users.surrogate_id', '=', $id)
            ->get();
        
            return json_encode($milestone);
        
    }

    // ********************************** For reset all milestone ******************************************
    public function reset_all_milsestone($id){
        $milestone = MilestoneUser::where('parent_id',$id)->delete();

        return Response::json([ 
            'status' => '205',
            'message' => 'Milestone reset successfully'
        ], 205);
    }
    // ******************************* For connect parent to surrogate *************************************
    public function invite_surrogate(Request $request,$id){
        $user = App_User_surrogate::where('email',$request->email)->get();

        if(count($user)>0){
            $surrogate_id = $user[0]['id'];
            $parent = App_User::find($id);
            $parent->parent_partner_id = $surrogate_id;
            $parent->save();

            $surrogate = App_User_surrogate::find($surrogate_id);
            $surrogate->partner_id = $id;
            $surrogate->save();

            $parent_id = $id; // This is a parent id
            $surrogate_id = $surrogate_id; // This is a surrogate id

            $array_one = MilestoneUser::where('parent_id',$parent_id)->where('surrogate_id',$surrogate_id)->get('milestone_id');
            
            $items = array();
            foreach($array_one as $i => $username) {
                array_push($items,$username->milestone_id);
            }
            for($i=1; $i <= 9; $i++) {
                if(in_array($i, $items)){
                    $status = 'true';
                }else{
                    $status = 'false';
                }
            }
            if($status == 'false'){
                $milestones = Milestone::where('user_type','common')->get();
                
                foreach ($milestones as $key => $milestone) {
                    $mile = new MilestoneUser;
                    $mile->parent_id = $id;
                    $mile->surrogate_id = $surrogate_id;
                    $mile->milestone_id = $milestone->id;
                    $mile->title = $milestone->milestone;
                    $mile->save();  
                }
            }
            return Response::json([ 
                'status' => '200',
                'message' => 'Parent & surrogate connected successfully'
            ], 200);

        }else{
            return Response::json([ 
                'error_code' => '1005',
                'status' => '404',  
                'message' => 'Email has been not match'
            ], 200);
        }
    }
    // ********************************* get question base scree **************************************

    // for first screen
    public function get_month_question(){
        $question = Admin_Question::where('category_id',14)->get();
        echo json_encode($question);
    }
    public function store_question_ans(Request $request){

        $questionnum = count($request->answer);
        for($i=0;$i<$questionnum;$i++){
            $ans = new Admin_Question_Ans;
            $ans->type = $request->type;
            $ans->user_id = $request->user_id;
            $ans->partner_id = $request->partner_id;
            $ans->category_id = $request->category_id;
            $ans->question_id = $request->answer[$i]['question_id'];
            $ans->answer = $request->answer[$i]['answer'];
            $ans->save();
        }   

        if($ans){
            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'Answer store successfully'
            ], 201);
        }else{
            return Response::json([
                'error_code' => '1001',
                'status' => '401',
                'message' => 'Answer store fail'
            ], 401);
        }

    }
    // for second screen
    public function get_imposrtant_question(){
        $month = 6;
        $question = Milestone::find($month);
        echo json_encode($question);
    }

    // **************************************** pregnancy milestone ***********************************
    public function get_pregnancy_milestone(Request $request){
        $user_id = $request->user_id;
        $type = $request->type;
        $milestone_id = 5;

        $user_status = Pregnancy_Milestone_Status::where('user_id',$user_id)->where('type',$type)->get();
        $status = $user_status[0]['status'];

        if($status == 'active'){
            if($type == 'parent'){
                $milestone = MilestoneUser::where('parent_id',$user_id)->where('milestone_id',$milestone_id)->get();
                $milestone_dates = $milestone[0]['date'];
            }else{
                $milestone = MilestoneUser::where('surrogate_id',$user_id)->where('milestone_id',$milestone_id)->get();
                $milestone_dates = $milestone[0]['date'];
            }
                $milestone_date = Carbon::parse($milestone_dates);
                $current_date = Carbon::parse(now())->format('Y/m/d');
                $daydiff = $milestone_date->diffInDays($current_date)/7;
                
                if($daydiff < 1)
                {
                    $week = 1; //echo 'week 4';
                }elseif ($daydiff > 1 && $daydiff <= 2) {
                    $week = 2; //echo 'week 5';
                }elseif ($daydiff > 2 && $daydiff <= 3) {
                    $week = 3; //echo 'week 6';
                }elseif ($daydiff > 3 && $daydiff <= 4) {
                    $week = 4; //echo 'week 7';
                }elseif ($daydiff > 4 && $daydiff <= 5) {
                    $week = 5; //echo 'week 8';
                }elseif ($daydiff > 5 && $daydiff <= 6) {
                    $week = 6; //echo 'week 9';
                }elseif ($daydiff > 6 && $daydiff <= 7) {
                    $week = 7; //echo 'week 10';
                }elseif ($daydiff > 7 && $daydiff <= 8) {
                    $week = 8; //echo 'week 11';
                }elseif ($daydiff > 8 && $daydiff <= 9) {
                    $week = 9; //echo 'week 12';
                }elseif ($daydiff > 9 && $daydiff <= 10) {
                    $week = 10; //echo 'week 13';
                }elseif ($daydiff > 10 && $daydiff <= 11) {
                    $week = 11; //echo 'week 14';
                }elseif ($daydiff > 11 && $daydiff <= 12) {
                    $week = 12; //echo 'week 15';
                }elseif ($daydiff > 12 && $daydiff <= 13) {
                    $week = 13; //echo 'week 16';
                }elseif ($daydiff > 13 && $daydiff <= 14) {
                    $week = 14; //echo 'week 17';
                }elseif ($daydiff > 14 && $daydiff <= 15) {
                    $week = 15; //echo 'week 18';
                }elseif ($daydiff > 15 && $daydiff <= 16) {
                    $week = 16; //echo 'week 19';
                }elseif ($daydiff > 16 && $daydiff <= 17) {
                    $week = 17; //echo 'week 20';
                }elseif ($daydiff > 17 && $daydiff <= 18) {
                    $week = 18; //echo 'week 21';
                }elseif ($daydiff > 18 && $daydiff <= 19) {
                    $week = 19; //echo 'week 22';
                }elseif ($daydiff > 19 && $daydiff <= 20) {
                    $week = 20; //echo 'week 23';
                }elseif ($daydiff > 20 && $daydiff <= 21) {
                    $week = 21; //echo 'week 24';
                }elseif ($daydiff > 21 && $daydiff <= 22) {
                    $week = 22; //echo 'week 25';
                }elseif ($daydiff > 22 && $daydiff <= 23) {
                    $week = 23; //echo 'week 26';
                }elseif ($daydiff > 23 && $daydiff <= 24) {
                    $week = 24; //echo 'week 27';
                }elseif ($daydiff > 24 && $daydiff <= 25) {
                    $week = 25; //echo 'week 28';
                }elseif ($daydiff > 25 && $daydiff <= 26) {
                    $week = 26; //echo 'week 29';
                }elseif ($daydiff > 26 && $daydiff <= 27) {
                    $week = 27; //echo 'week 30';
                }elseif ($daydiff > 27 && $daydiff <= 28) {
                    $week = 28; //echo 'week 31';
                }elseif ($daydiff > 28 && $daydiff <= 29) {
                    $week = 29; //echo 'week 32';
                }elseif ($daydiff > 29 && $daydiff <= 30) {
                    $week = 30; //echo 'week 33';
                }elseif ($daydiff > 30 && $daydiff <= 31) {
                    $week = 31; //echo 'week 34';
                }elseif ($daydiff > 31 && $daydiff <= 32) {
                    $week = 32; //echo 'week 35';
                }elseif ($daydiff > 32 && $daydiff <= 33) {
                    $week = 33; //echo 'week 36';
                }elseif ($daydiff > 33 && $daydiff <= 34) {
                    $week = 34; //echo 'week 37';
                }elseif ($daydiff > 34 && $daydiff <= 35) {
                    $week = 35; //echo 'week 38';
                }elseif ($daydiff > 35 && $daydiff <= 36) {
                    $week = 36; //echo 'week 39';
                }elseif ($daydiff > 36 && $daydiff <= 37) {
                    $week = 37; //echo 'week 40';
                }

                $preg_milestone = Pregnancy_Milestone::find($week);
                echo json_encode($preg_milestone);
        }
    }
    public function pregnancy_milestone_status(Request $request){
        $user_id = $request->user_id;
        $type = $request->type;
        $milestone_id = 5;

        $user_status = Pregnancy_Milestone_Status::where('user_id',$user_id)->where('type',$type)->get();
        $status = $user_status[0]['status'];
        $id = $user_status[0]['id'];
      
        if($status == 'inactive'){
            if($type == 'parent'){
                $milestone = MilestoneUser::where('parent_id',$user_id)->where('milestone_id',$milestone_id)->get();
                $milestone_date = $milestone[0]['date'];
                if($milestone_date != ''){
                    $milestatus = Pregnancy_Milestone_Status::find($id);
                    $milestatus->status = 'active';
                    $milestatus->save();

                    return Response::json([
                        'error_code' => '1004',
                        'status' => '201',
                        'message' => 'Status change successfully'
                    ], 201);

                }else{
                    return Response::json([
                        'error_code' => '1001',
                        'status' => '401',
                        'message' => 'Please add the date of embryo milestone'
                    ], 401);
                }
            }else{
                $milestone = MilestoneUser::where('surrogate_id',$user_id)->where('milestone_id',$milestone_id)->get();
                $milestone_date = $milestone[0]['date'];
                if($milestone_date != ''){
                    $milestatus = Pregnancy_Milestone_Status::find($id);
                    $milestatus->status = 'active';
                    $milestatus->save();

                    return Response::json([
                        'error_code' => '1004',
                        'status' => '201',
                        'message' => 'Status change successfully'
                    ], 201);

                }else{
                    return Response::json([
                        'error_code' => '1001',
                        'status' => '401',
                        'message' => 'Please add the date of embryo milestone'
                    ], 401);
                }
            }
        }else{
            $milestatus = Pregnancy_Milestone_Status::find($id);
            $milestatus->status = 'inactive';
            $milestatus->save();

            return Response::json([
                'error_code' => '1004',
                'status' => '201',
                'message' => 'Status change successfully'
            ], 201);
        }

    }
}