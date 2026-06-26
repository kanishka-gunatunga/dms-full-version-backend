<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Crypt;
use Laravel\Passport\Token;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\LoginAudits;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Roles;
use App\Models\Sectors;

use App\Http\Controllers\CommonFunctionsController;
use App\Models\DocumentAuditTrial;

use Mail;

class UserAPIController extends Controller
{

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'email|required',
                'password' => 'required',
                'type' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }
            if($request->get('type') == "normal"){
                $user_data = [
                    'email' => $request->get('email'),
                    'password' => $request->get('password'),
                    'user_type' => 'normal'
                ];
                if (Auth::attempt($user_data)) {
                    LoginAudits::create([
                        'email' => $request->get('email'),
                        'date_time' => now(),
                        'ip_address' => $request->ip(),
                        'latitude' => $request->get('latitude'),
                        'longitude' => $request->get('longitude'),
                        'status' => "success",
                    ]);
    
                    $user = Auth::user();
                    $user_details = User::where('id', $user->id)->with('userDetails')->first();
                    $response = [
                        'token' => $user->createToken('Web Token')->accessToken,
                        'email' => $user->email,
                        'id' => $user->id,
                        'name' => $user_details->userDetails->first_name. ' '  .$user_details->userDetails->last_name,
                        'type' => "normal"
                    ];
    
                    return response()->json([
                        'status' => "success",
                        'message' => 'User login successful',
                        'data' => $response,
                    ], 201);
                } else {
                    LoginAudits::create([
                        'email' => $request->get('email'),
                        'date_time' => now(),
                        'ip_address' => $request->ip(),
                        'latitude' => $request->get('latitude'),
                        'longitude' => $request->get('longitude'),
                        'status' => "fail",
                    ]);
    
                    return response()->json([
                        'status' => "fail",
                        'message' => 'User login failed',
                        'data' => null,
                    ], 500);
                }
            }
           else{
                $user_data = [
                    'email' => $request->get('email'),
                    'password' => $request->get('password'),
                    'user_type' => 'super_admin'
                ];
                if (Auth::attempt($user_data)) {
                    $user = Auth::user();
                    $user_details = User::where('id', $user->id)->with('userDetails')->first();
                    $response = [
                        'token' => $user->createToken('Web Token')->accessToken,
                        'email' => $user->email,
                        'id' => $user->id,
                        'name' => $user_details->userDetails->first_name. ' '  .$user_details->userDetails->last_name,
                        'type' => "super_admin"
                    ];
    
                    return response()->json([
                        'status' => "success",
                        'message' => 'User login successful',
                        'data' => $response,
                    ], 201);
                }
                else{
                    return response()->json([
                        'status' => "fail",
                        'message' => 'User login failed',
                        'data' => null,
                    ], 500);
                }
           }
            
        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function auto_login(Request $request)
    {
        try {
            $userId = Crypt::decryptString($request->encrypted_user);
            $documentId = Crypt::decryptString($request->encrypted_doc);
    
            // Fetch user with details
            $user = User::with('userDetails')->find($userId);
    
            if (!$user) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Invalid link or user not found'
                ], 400);
            }
    
            $token = $user->createToken('Web Token')->accessToken;
    
            return response()->json([
                'status' => 'success',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->userDetails->first_name . ' ' . $user->userDetails->last_name,
                        'email' => $user->email,
                        'type' => "normal"
                    ],
                    'document_id' => $documentId,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid or expired link',
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
public function login_with_ad(Request $request)
{
    try {

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'token' => 'required', // Now expecting a token from MSAL
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => "fail",
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->input('email');
        $token = $request->input('token');

        $loginUser = new CommonFunctionsController();
        
        // Fetch user details from Azure AD using the provided token
        $userInfo = $loginUser->getAzureADUserInfo($token);
        
        if (!$userInfo['success']) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Failed to fetch user details from Azure AD. User may need to login to AD first.',
                'error' => $userInfo['error'],
            ], 401);
        }

        $adUser = $userInfo['data'];

        if (!$adUser['accountEnabled']) {
            return response()->json([
                'status' => 'fail',
                'message' => 'User is disabled in Azure AD.',
            ], 403);
        }

        $upn = $adUser['userPrincipalName'] ?? null;

        // Check if the UPN matches the email provided by frontend (security check)
        if (strtolower($upn) !== strtolower($email)) {
             return response()->json([
                'status' => 'fail',
                'message' => 'Identity mismatch. Token does not belong to the provided email.',
            ], 403);
        }

        $user = User::where('email', $upn)->first();

        if (!$user) {
            return response()->json([
                'status' => "fail",
                'message' => 'User not found in system',
            ], 404);
        }

        Auth::login($user);

        $user_details = User::where('id', $user->id)
            ->with('userDetails')
            ->first();

        return response()->json([
            'status' => "success",
            'message' => 'User login successful',
            'data' => [
                'token' => $user->createToken('Web Token')->accessToken,
                'email' => $user->email,
                'id' => $user->id,
                'name' => $user_details->userDetails->first_name . ' ' . $user_details->userDetails->last_name,
                'type' => "ad"
            ],
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => "fail",
            'message' => 'Request failed',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function get_ad_config()
{
    try {
        $credential_details = \App\Models\ADCredential::first();

        if (!$credential_details) {
            return response()->json([
                'status' => "fail",
                'message' => 'Azure AD credentials not configured.',
            ], 404);
        }

        return response()->json([
            'status' => "success",
            'data' => [
                'client_id' => $credential_details->client_id,
                'tenant_id' => $credential_details->tenant_id,
            ],
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => "fail",
            'message' => 'Request failed',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function add_user(Request $request)
    {
        try {
          
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email',
                'sector' => 'required',
                'password' => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                ],
                'password_confirmation' => 'required|same:password'
            ]);
        
        
            if ($validator->fails()) {
                return response()->json([
                     'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422); 
            }

            $user = User::create([
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "role" => $request->role,
                "user_type" => 'normal'
            ]);

            $userDetails = new UserDetails();
            $userDetails->user_id = $user->id;
            $userDetails->first_name = $request->first_name;
            $userDetails->last_name = $request->last_name;
            $userDetails->mobile_no = $request->mobile_no;
            $userDetails->sector = $request->sector;
            $userDetails->save();

            $userId = auth('api')->id();

            $date_time = Carbon::now()->format('Y-m-d H:i:s');
            $auditFunction = new CommonFunctionsController();
            $auditFunction->document_audit_trail('new user added','user', $userId, $user->id, $date_time, null, null);
    
            return response()->json([
                'status' => "success",
                'message' => 'User added'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }
    
    public function user_details($id,Request $request)
    {
         
        try {
            if($request->isMethod('get')){
                $user = User::where('id', $id)->with('userDetails')->first();
                if($user->userDetails->sector == null || $user->userDetails->sector == ''){
                    $user->userDetails->sector_name = 'none';
                }
                else{
                    $sectorVal = $user->userDetails->sector;
                    $sectorIds = json_decode($sectorVal, true);
                    if (!is_array($sectorIds)) {
                        $sectorIds = $sectorVal ? [$sectorVal] : [];
                    }
                    if (empty($sectorIds)) {
                        $user->userDetails->sector_name = 'none';
                    } else {
                        $sectorNames = Sectors::whereIn('id', $sectorIds)->pluck('sector_name')->toArray();
                        $user->userDetails->sector_name = !empty($sectorNames) ? implode(', ', $sectorNames) : 'none';
                    }
                }
                return response()->json($user);
            }
            if($request->isMethod('post')){

           
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
                'sector' => 'required'
            ]);
        
        
            if ($validator->fails()) {
                return response()->json([
                     'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422); 
            }

            
            if(User::where("id", "=", $id)->where("email", "=", $request->email)->exists()){
                $email = $request->email;
            }
            elseif(User::where("email", "=", $request->email)->exists()){
             return response()->json([
                'status' => "fail",
                'message' => 'This email is already in use',
            ], 500);

            }
            else{
             $email = $request->email;
            }
            
           
            $roleData = is_string($request->role)
            ? json_decode($request->role, true)
            : $request->role;

         

            $userDetails =  UserDetails::where('user_id', '=', $id)->first();;
            $userDetails->first_name = $request->first_name;
            $userDetails->last_name = $request->last_name;
            $userDetails->mobile_no = $request->mobile_no;
            $userDetails->sector = $request->sector;
            $userDetails->update();
            $user = User::find($id);
            $user->email = $email;
            $user->role = $roleData;
            $user->update();

            $userId = auth('api')->id();

            $date_time = Carbon::now()->format('Y-m-d H:i:s');
            $auditFunction = new CommonFunctionsController();
            $auditFunction->document_audit_trail('user details updated','user', $userId, $id, $date_time, null, null);

            return response()->json([
                'status' => "success",
                'message' => 'User updated'
            ], 201);
        }
        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }

    public function user_sectors($id)
    {
        try {
            $user = User::with('userDetails')->find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User not found'
                ], 404);
            }

            if ($user->user_type === 'super_admin') {
                $sectors = Sectors::select('id', 'parent_sector', 'sector_name')->get();
                return response()->json($sectors);
            }

            if (!$user->userDetails || $user->userDetails->sector === null || $user->userDetails->sector === '') {
                return response()->json([]);
            }

            $sectorVal = $user->userDetails->sector;
            $sectorIds = json_decode($sectorVal, true);
            if (!is_array($sectorIds)) {
                $sectorIds = $sectorVal ? [$sectorVal] : [];
            }

            $sectors = Sectors::whereIn('id', $sectorIds)->select('id', 'parent_sector', 'sector_name')->get();
            return response()->json($sectors);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update_password(Request $request)
    {
         
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'current_password' => 'required',
                'password' => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                ],
                'password_confirmation' => 'required|same:password'
            ]);
        
        
            if ($validator->fails()) {
                return response()->json([
                     'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422); 
            }

            if (Hash::check($request->input('current_password'), User::where('email', $request->email)->value('password'))) {

                $user = User::where('email', $request->email)->first();
                $user->password = Hash::make($request->input('password'));
                $user->update();

                $userId = auth('api')->id();

                $date_time = Carbon::now()->format('Y-m-d H:i:s');
                $auditFunction = new CommonFunctionsController();
                $auditFunction->document_audit_trail('user password updated','user', $userId, 'other', $date_time, null, null);

                return response()->json([
                    'status' => "success",
                    'message' => 'Password updated'
                ], 201);

               }
               else{
                return response()->json([
                    'status' => "fail",
                    'message' => 'Current password is incorrect.',
                ], 500);
            }

        } catch (\Exception $e) {

           

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);

        }    
    }

    public function delete_user($id,Request $request)
    {
         
        try {
            UserDetails::where('user_id', '=', $id)->delete();
            User::where('id', $id)->delete(); 

            $userId = auth('api')->id();

            $date_time = Carbon::now()->format('Y-m-d H:i:s');
            $auditFunction = new CommonFunctionsController();
            $auditFunction->document_audit_trail('user deleted','user', $userId, $id, $date_time, null, null);

            return response()->json([
                'status' => "success",
                'message' => 'User Deleted'
            ], 201);
        
        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }


    public function users(Request $request)
    {
         
        try {
            if($request->isMethod('get')){
                $users = User::select('id', 'email', 'role')->where('user_type', '!=', 'super_admin') 
                ->with(['userDetails' => function ($query) {
                    $query->select('user_id', 'first_name', 'last_name', 'mobile_no');
                }])
                ->get();
                
                return response()->json($users);
            }
         
        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }
    public function login_audits(Request $request)
    {
         
        try {
            if($request->isMethod('get')){
                $audits = LoginAudits::select('id', 'email', 'date_time', 'ip_address', 'status', 'latitude', 'longitude')->get();
                return response()->json($audits);
            }
         
        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }
    public function user_permissions($id, Request $request)
{
    try {
        if ($request->isMethod('get')) {
            $user_role = User::where('id', $id)->value('role');

            $user_roles_array = json_decode($user_role, true) ?? [];

            if (empty($user_roles_array)) {
                return response()->json([
                    'status' => 'fail',
                    'user_id' => $id,
                    'permissions' => []
                ]);
            }

            $all_permissions = [];

            foreach ($user_roles_array as $user_roles_arra) {
                $role = Roles::where('id', $user_roles_arra)->first();
                $permissions = $role->permissions;
                if ($permissions !== null) {
                    $decoded_permissions = json_decode($permissions, true);
                    if (is_array($decoded_permissions)) {
                        $all_permissions = array_merge($all_permissions, $decoded_permissions);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'is_admin' => $role->is_admin ?? 0,
                'permissions' => $all_permissions
            ]);
        }

    } catch (\Exception $e) {
        return response()->json([
            'status' => "fail",
            'user_id' => $id,
            'message' => 'Request failed',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function role_user(Request $request)
    {
         
        try {
           
            if($request->isMethod('post')){

           
            $validator = Validator::make($request->all(), [
                'user' => 'required',
                'role' => 'required'
            ]);
        
        
            if ($validator->fails()) {
                return response()->json([
                     'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422); 
            }

            $existingRoles = User::where('id',$request->user)->first();
            $existingRolesArray = json_decode($existingRoles->role, true) ?? [];
            $roleData = is_string($request->role)
            ? json_decode($request->role, true)
            : $request->role;

            $newRolesArray = array_unique(array_merge($existingRolesArray, $roleData));

            $user = User::find($request->user);
            $user->role =$newRolesArray;
            $user->update();


            $userId = auth('api')->id();

            $date_time = Carbon::now()->format('Y-m-d H:i:s');
            $auditFunction = new CommonFunctionsController();
            $auditFunction->document_audit_trail('user role updated','user', $userId, $request->user, $date_time, null, null);

            return response()->json([
                'status' => "success",
                'message' => 'Role Changed'
            ], 201);
        }
        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }
    public function users_by_role($id, Request $request)
{
    try {
        if ($request->isMethod('get')) {

            $usersWithRole = User::select('id', 'email', 'role')
                ->whereJsonContains('role', $id)
                ->with(['userDetails' => function ($query) {
                    $query->select('user_id', 'first_name', 'last_name', 'mobile_no');
                }])
                ->get();

            $usersWithoutRole = User::select('id', 'email', 'role')
                ->whereNot(function ($query) use ($id) {
                    $query->whereJsonContains('role', $id);
                })
                ->with(['userDetails' => function ($query) {
                    $query->select('user_id', 'first_name', 'last_name', 'mobile_no');
                }])
                ->get();

            return response()->json([
                'users_with_role' => $usersWithRole,
                'users_without_role' => $usersWithoutRole,
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => "fail",
            'message' => 'Request failed',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function remove_role_user(Request $request)
{
    try {
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'user' => 'required',
                'role' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::find($request->user);

            if (!$user) {
                return response()->json([
                    'status' => "fail",
                    'message' => 'User not found'
                ], 404);
            }

            $existingRolesArray = json_decode($user->role, true) ?? [];

            $roleData = is_string($request->role)
                ? json_decode($request->role, true)
                : $request->role;

            if (!is_array($roleData)) {
                $roleData = [$roleData];
            }

            $newRolesArray = array_diff($existingRolesArray, $roleData);

            $user->role = json_encode(array_values($newRolesArray));
            $user->update();
            $userId = auth('api')->id();

            $date_time = Carbon::now()->format('Y-m-d H:i:s');
            $auditFunction = new CommonFunctionsController();
            $auditFunction->document_audit_trail('user role removed','user', $userId, $request->user, $date_time, null, null);

            return response()->json([
                'status' => "success",
                'message' => 'Role removed successfully'
            ], 200);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => "fail",
            'message' => 'Request failed',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function forgot_password(Request $request)
    {

        if ($request->isMethod('post')) {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (User::where("email", $request->email)->exists()) {
                $login_details = User::where('email', $request->email)->first();
            } else {
                return response()->json([
                    'status' => "fail",
                    'message' => 'Please insert a valid email',
                ], 500);
            }

            $details  = [
                'body' => "Please Click On The Link Below To Rest Your Password",
                'link1' => Crypt::encryptString($login_details->id),
                'link2' => "reset",


            ];
            Mail::to($request->email)->send(new \App\Mail\ForgotPassword($details));

            return response()->json([
                'status' => "success",
                'message' => 'Password reset link has been sent to your email'
            ], 200);
        }
        catch (\Exception $e) {
            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }
        }
    }
    public function reset_password(Request $request)
    {

        if ($request->isMethod('post')) {
            try {
            $user_id = Crypt::decryptString($request->id);

            $validator = Validator::make($request->all(), [
               "password" => "required | min:6 | confirmed",
               "password_confirmation" => "required",
               "id" => "required",
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }
            $password = User::find($user_id);
            $password->password = Hash::make($request->input('password'));
            $password->update();
            return response()->json([
                'status' => "success",
                'message' => 'Password reset successful'
            ], 200);
        }
            catch (\Exception $e) {
                return response()->json([
                    'status' => "fail",
                    'message' => 'Request failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

    }
}
