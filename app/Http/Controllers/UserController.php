<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Section;
use App\Models\Admin_Question;
use App\Models\App_User_surrogate;
use App\Models\App_User;
use Auth;
use DB;

class UserController extends Controller
{
    // ******************************************** Update profile data *********************************************
    public function profile(){
        $userid = 5;

        $user = User::find($userid);
        
        echo json_encode($user);
    }
    public function profile_update(Request $request){
       
        $userid = 5;
        
        if($request->hasFile('image')){

         $image = $request->file('image');
 
         $name = time().'.'.$image->getClientOriginalExtension();
 
         $destinationPath = public_path('/images/profile');
 
         $image->move($destinationPath,$name); 
 
        }else{
            $name = '1650516105.jpg';
        }

        $user = User::find($userid);
        $user->name = $request->name;
        $user->email = $request->email;
        // $user->password = Hash::make($request->password);
        $user->bio = $request->bio;
        $user->image = $name;
        $user->insta = $request->insta;
        $user->forum = $request->forum;
        $user->save();
        
        $data['Status']     = '1';
		$data['message']  	= 'Data updated successfully';

        echo json_encode($data);
    }
    //********************************************  view login page **********************************************
    public function logi_n(){
        return view('login');
    }
    // ******************************************* Check user login or not **************************************
    public function check_login(Request $request){

        $user_data = array(
            'email' => $request->email,
            'password' => $request->password
        );

        if(Auth::attempt($user_data))
        {
            $username = Auth()->user()->name;
            $data['Status']     = '1';
            $data['name']       = $username;
		    $data['message']  	= 'Login successfully';

        }else{
            $data['Status']     = '0';
		    $data['message']  	= 'Login faild';

        }
        
        echo json_encode($data);
    }
    // ************************************************* Logout admin ****************************************************
    public function logout(){
        Auth::logout();
        
        $data['Status']     = '1';
		$data['message']  	= 'Logout successfully';

        echo json_encode($data);
    }
    // *********************************************** Change admin password ********************************************
    public function change_password(Request $request){
        
        $userid = 5;
        $user = User::find($userid);
        $password = $request->old_password;
        
        if(Hash::check($request->old_password, $user->password)){
            $user = User::find($userid);
            $user->password = Hash::make($request->new_password);
            $user->save();

            $data['Status']     = '1';  
		    $data['message']  	= 'Password updated successfully';

        }else{
            $data['Status']     = '0';
		    $data['message']  	= 'Old password has been not match';
        }
        echo json_encode($data);
    }
    // ************************************************* For category part **********************************************
    public function category(){
        $category = DB::table('categories')
                ->join('admin__questions','categories.id','=', 'admin__questions.category_id')
                ->get();

            echo json_encode($category);
    }
    public function category_create(Request $request){
        
        $category = new Category;
        $category->category = $request->category;
        $category->save();

        if($category){
            $data['Status']     = '1';
		    $data['message']  	= 'Category created successfully';
        }else{
            $data['Status']     = '0';
		    $data['message']  	= 'Category has been not created';
        }
        echo json_encode($data);
    }
    public function category_edit($id){
        $category = Category::find($id);
        
        if($category){
            $data['status']     = '1';
		    $data['data']  	= $category;
        }else{
            $data['status']     = '0';
		    $data['message']  	= 'No record found';
        }
        echo json_encode($data);
    }
    public function category_update(Request $request,$id){
        $category = Category::find($id);
        $category->category = $request->category;
        $category->save();

        if($category){
            $data['status']     = '1';
		    $data['message']  	= 'category updated successfully';
        }else{
            $data['status']     = '0';
		    $data['message']  	= 'No record found';   
        }
        echo json_encode($data);
    }
    // *********************************************** For section part ********************************************
    public function section_get(){
        $section = Section::get();
        echo json_encode($section);
    }
    public function section_create(Request $request){
        $section = new Section;
        $section->category_id = $request->category_id;
        $section->section = $request->section;
        $section->save();

        if($section){
            $data['status']     = '1';
		    $data['message']  	= 'Section created successfully';
        }else{
            $data['status']     = '0';
		    $data['message']  	= 'Section has been not created';
        }
        echo json_encode($data);
    }
    public function section_edit($id){
        $section = Section::find($id);
        
        if($section){
            $data['status']     = '1';
		    $data['data']  	= $section;
        }else{
            $data['status']     = '0';
		    $data['message']  	= 'No record found';
        }
        echo json_encode($data);
    }
    public function section_update(Request $request, $id){
        $section = Section::find($id);
        $section->category_id = $request->category_id;
        $section->section = $request->section;
        $section->save();

        if($section){
            $data['status']     = '1';
		    $data['message']  	= 'section updated successfully';
        }else{
            $data['status']     = '0';
		    $data['message']  	= 'section has been not updated';
        }
        echo json_encode($data);
    }
    public function section_destroy($id){
        $section = Section::find($id);
        $section->delete();

        $data['status']     = '1';
        $data['message']  	= 'section deleted successfully';

        echo json_encode($data);
    }
    // ************************************************* For Question part *********************************************
    public function question_get($id){
        $question = Admin_Question::where('section_id',$id)->get();
        echo json_encode($question);
    }
    public function question_create(Request $request){
        $question = new Admin_Question;
        $question->category_id = $request->category_id;
        $question->question = $request->question;
        $question->save();

        if($question){
            $data['status']     = '1';
		    $data['message']  	= 'Question created successfully';
        }else{
            $data['status']     = '0';
		    $data['message']  	= 'Question has been not created';
        }
        echo json_encode($data);
    }
    public function question_edit($id){
        $question = Admin_Question::find($id);
        echo json_encode($question);
    }
    public function question_update(Request $request,$id){
        $question = Admin_Question::find($id);
        $question->category_id = $request->category_id;
        $question->question = $request->question;
        $question->save();

        if($question){
            $data['status']     = '1';
		    $data['message']  	= 'Question updated successfully';
        }else{
            $data['status']     = '0';
		    $data['message']  	= 'Question has been not updated';
        }
        echo json_encode($data);
    }
    public function question_destroy($id){
        $question = Admin_Question::find($id);
        $question->delete();
        
        $data['status']     = '1';
        $data['message']  	= 'question deleted successfully';

        echo json_encode($data);
    }
    // ************************************************* For our journy ******************************************
    public function get_journy(){
            $journy = DB::table('app__users')
            ->join('app__user_surrogates', 'app__users.id', '=', 'app__user_surrogates.partner_id')
            ->get();

            echo json_encode($journy);
    }
    public function journy_milestone($id){
            $journy = DB::table('app__users')
            ->join('app__user_surrogates', 'app__users.id', '=', 'app__user_surrogates.partner_id')
            ->where('app__user_surrogates.id','=',$id)
            ->get();

            $milestone = DB::table('milestones')
            ->join('milestone_users', 'milestones.id', '=', 'milestone_users.milestone_id')
            ->where('milestone_users.surrogate_id','=',$id)
            ->get();

            $data['journy']     = $journy;
            $data['milestone']  = $milestone;

            echo json_encode($data);
    }
    public function journy_user_detail($id,$slug){
        if($slug == 'surrogate'){
            $user = App_User_surrogate::find($id);
        }else{
            $user = App_User::where('parent_partner_id',$id)->get();
        }
        echo json_encode($user);
    }
}