<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\User;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Auth;
use Intervention\Image\ImageManagerStatic as Image;
use Helper;
use Storage;
use Mail;
use URL;

class UserController extends Controller
{
    

    public function addUser(Request $request){
        $response['message'] = "";
        $response['status'] = false;
        $errorCode = 400;

        $validator = Validator::make($request->all(),[
            'email' => ['required', 'string', 'email', 'unique:users'],
            'added_by' => ['required', 'numeric'],
        ]);
        if ($validator->fails()) {  
            $response['status'] = false;
            $response['message'] =  $validator->errors();
            $errorCode = 422;
        } else{ 
            try {
                $getUser = User::find($request->input('added_by'));
                    if($getUser && $getUser->user_role == 1){
                        $requestData = $request->only('email');
                        $getData = User::create($requestData);
                        $email_body = 'Hi, you are invited to setup your accunt at '.URL::to('/');
                        $email_title = 'Set Up Account';
                        $_email = $request->input('email');
                        Mail::send('emails.index', ['data' => $email_body], function ($m) use ($email_title,$_email,$email_body) {
                            $m->from('testondev@gmail.com', $email_title);
                            $m->to($_email)->subject($email_title);
                        });
                        if (!$getData){
                            $response['message'] = "invalid Record";
                            $errorCode = 400;
                        } else{ 
                            $response['message'] = "User added successful";
                            $response['status'] = true;
                            $errorCode = 200;
                        }
                    }else{
                        $response['message'] = "Only Admins are allowed to add user";
                         $errorCode = 401;
                    }
            } catch (Exception $e) {
                $response['message'] = $e->message;
                $response['status'] = false;
                $errorCode = 400;
            }
        }
        return response()->json($response, $errorCode);
    }

    public function register(Request $request){
        $response['message'] = "";
        $response['status'] = false;
        $errorCode = 400;

        $validator = Validator::make($request->all(),[
            'user_name' => ['required', 'string', 'min:4','max:20', 'unique:users'],
            'password' => ['required','min:4','max:20'],
            'email' => ['required', 'string', 'email'],
        ]);
        if ($validator->fails()) {  
            $response['status'] = false;
            $response['message'] =  $validator->errors();
            $errorCode = 422;
        } else{ 
            try {
                $getUser = User::where(['email' => $request->input('email'),'status' => 0])->first();
                    if($getUser && $getUser->user_role == 2){
                        $password = Hash::make($request->password);
                        //$requestData = $request->only('email');
                        $activation_pin = random_int(100000, 999999);
                        $email_body = 'Hi, your activation pin is '.$activation_pin;
                        $email_title = 'Activation Pin';
                        $_email = $request->input('email');
                        Mail::send('emails.index', ['data' => $email_body], function ($m) use ($email_title,$_email,$email_body) {
                            $m->from('testondev@gmail.com', $email_title);
                            $m->to($_email)->subject($email_title);
                        });
                        $getData = User::updateOrCreate(
                            ['email' =>  $request->input('email')],
                            [
                                'user_name' => $request->input('user_name'),
                                'password' => $password,
                                'activation_pin' => $activation_pin,
                            ]
                        );
                        
                        if (!$getData){
                            $response['message'] = "An error occured";
                            $errorCode = 400;
                        } else{ 
                            $response['message'] = "User register successfully";
                            $response['status'] = true;
                            $response['activation_pin'] = $activation_pin;
                            $errorCode = 200;
                        }
                    }else{
                        $response['message'] = "No user found";
                         $errorCode = 401;
                    }
            } catch (Exception $e) {
                $response['message'] = $e->message;
                $response['status'] = false;
                $errorCode = 400;
            }
        }
        return response()->json($response, $errorCode);
    }

    public function verifyPin(Request $request){
        $response['message'] = "";
        $response['status'] = false;
        $errorCode = 400;

        $validator = Validator::make($request->all(),[
           
            'email' => ['required', 'string', 'email'],
            'activation_pin' => ['required','numeric'],
        ]);
        if ($validator->fails()) {  
            $response['status'] = false;
            $response['message'] =  $validator->errors();
            $errorCode = 422;
        } else{ 
            try {
                $getUser = User::where(['email' => $request->email,'status' => 0,'activation_pin' => $request->activation_pin])->first();
                    if($getUser && $getUser->user_role == 2){
                        $getData = User::updateOrCreate(
                            ['email' =>  $request->input('email')],
                            [
                                'status' => 1,
                                'activation_pin' => NULL,
                            ]
                        );
                        if (!$getData){
                            $response['message'] = "An error occured";
                            $errorCode = 400;
                        } else{ 
                            $response['message'] = "User activated successfully";
                            $response['status'] = true;
                            $response['user_id'] = $getUser->id;
                            $errorCode = 200;
                        }
                    }else{
                        $response['message'] = "No user found";
                         $errorCode = 401;
                    }
            } catch (Exception $e) {
                $response['message'] = $e->message;
                $response['status'] = false;
                $errorCode = 400;
            }
        }
        return response()->json($response, $errorCode);
    }

    public function login(Request $request){
        $response['message'] = "";
        $response['status'] = false;
        $errorCode = 400;

        $validator = Validator::make($request->all(),[
           
            'user_name' => ['required'],
            'password' => ['required'],
        ]);
        if ($validator->fails()) {  
            $response['status'] = false;
            $response['message'] =  $validator->errors();
            $errorCode = 422;
        } else{ 
            try {
                $credentials = $request->only('user_name', 'password');
                $token = JWTAuth::attempt($credentials);
                    if(!$token = JWTAuth::attempt($credentials)) {
                        $response['message'] = "Invalid credentials";
                        $errorCode = 200;
                       
                    }else{ 
                        $response['message'] = "login successful";
                        $response['status'] = true;
                       // $response['user_id'] = $getUser->id;
                        $response['token'] = $token;
                        $errorCode = 200;
                    }
            } catch (Exception $e) {
                $response['message'] = $e->message;
                $response['status'] = false;
                $errorCode = 400;
            }
        }
        return response()->json($response, $errorCode);
    }

    public function editProfile(Request $request){
        $response['message'] = "";
        $response['status'] = false;
        $errorCode = 400;

        $validator = Validator::make($request->all(),[ 
            'name' => ['required','min:4','max:50'],
           // 'avatar' => ['required'],
        ]);
        if ($validator->fails()) {  
            $response['status'] = false;
            $response['message'] =  $validator->errors();
            $errorCode = 422;
        } else{ 
            try {
                $getUserId = Auth::user()->id;
                $requestData = $request->only('name');
                // upload image
                $file = $request->file('avatar');
                $path = 'public/images/user_images';
                $name = self::uploadImage($file,$path); 
                $storeData = User::where('id', '=', $getUserId)->update($requestData);
                    if(!$storeData) {
                        $response['message'] = "An error occured";
                        $errorCode = 400;
                       
                    }else{ 
                        $response['message'] = "user updated";
                        $response['status'] = true;
                        $errorCode = 200;
                    }
            } catch (Exception $e) {
                $response['message'] = $e->message;
                $response['status'] = false;
                $errorCode = 400;
            }
        }
        return response()->json($response, $errorCode);
    }


    public static function uploadImage($file,$path){
      $name = time() . '.' . $file->getClientOriginalName();
      //save original
      $img = Image::make($file->getRealPath());
      $img->stream();
      Storage::disk('local')->put($path.'/'.$name, $img, 'public');
      //savethumb
      $img = Image::make($file->getRealPath());
      $img->resize(256, 256, function ($constraint) {
          $constraint->aspectRatio();
      });
      $img->stream();
      Storage::disk('local')->put($path.'/thumb/'.$name, $img, 'public');
      return $name;
  }  

   

}
