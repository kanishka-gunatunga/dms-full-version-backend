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
            'email' => 'required|email',
            'password' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = trim($request->email);

        $user = User::where('email', $email)->first();

        /*
        |--------------------------------------------------------------------------
        | ACCOUNT LOCK CHECK
        |--------------------------------------------------------------------------
        */

        if ($user) {

            if ($user->lockout_until && Carbon::parse($user->lockout_until)->isFuture()) {

                $remainingSeconds = now()->diffInSeconds(Carbon::parse($user->lockout_until));

                LoginAudits::create([
                    'email' => $email,
                    'date_time' => now(),
                    'ip_address' => $request->ip(),
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'status' => 'locked_attempt',
                ]);

                return response()->json([
                    'status' => 'locked',
                    'message' => 'Account locked. Try again later.',
                    'remaining_seconds' => $remainingSeconds
                ], 423);
            }

            // Reset expired lock
            if ($user->lockout_until && Carbon::parse($user->lockout_until)->isPast()) {
                $user->failed_attempts = 0;
                $user->lockout_until = null;
                $user->save();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN ATTEMPT
        |--------------------------------------------------------------------------
        */

        $userType = $request->type == 'normal' ? 'normal' : 'super_admin';

        $credentials = [
            'email' => $email,
            'password' => $request->password,
            'user_type' => $userType
        ];

        if (!Auth::attempt($credentials)) {

            if ($user) {

                $user->failed_attempts += 1;

                if ($user->failed_attempts >= 5) {
                    $user->lockout_until = now()->addMinutes(15);
                }

                $user->save();
            }

            LoginAudits::create([
                'email' => $email,
                'date_time' => now(),
                'ip_address' => $request->ip(),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => 'fail',
            ]);

            $attemptsLeft = max(0, 5 - ($user ? $user->failed_attempts : 0));

            return response()->json([
                'status' => 'fail',
                'message' => $attemptsLeft > 0
                    ? "Invalid credentials. {$attemptsLeft} attempts remaining."
                    : "Account locked for 15 minutes.",
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | AUTH SUCCESS
        |--------------------------------------------------------------------------
        */

        $authenticatedUser = Auth::user();

        $authenticatedUser->failed_attempts = 0;
        $authenticatedUser->lockout_until = null;
        $authenticatedUser->save();

        /*
        |--------------------------------------------------------------------------
        | PASSWORD RULES
        |--------------------------------------------------------------------------
        */

        if ($authenticatedUser->must_change_password) {

            return response()->json([
                'status' => 'change_password_required',
                'message' => 'Password change required.',
                'email' => $authenticatedUser->email,
                'id' => $authenticatedUser->id,
                'temp_token' => Crypt::encryptString($authenticatedUser->id)
            ]);
        }

        $passwordChangedAt = $authenticatedUser->password_changed_at
            ? Carbon::parse($authenticatedUser->password_changed_at)
            : Carbon::parse($authenticatedUser->created_at);

        if ($passwordChangedAt->copy()->addDays(90)->isPast()) {

            return response()->json([
                'status' => 'password_expired',
                'message' => 'Password expired.',
                'email' => $authenticatedUser->email,
                'id' => $authenticatedUser->id,
                'temp_token' => Crypt::encryptString($authenticatedUser->id)
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MFA CHECK (ONLY IF ENABLED)
        |--------------------------------------------------------------------------
        */

        if ($authenticatedUser->mfa_enabled == 1) {

            return response()->json([
                'status' => 'mfa_required',
                'message' => 'MFA verification required.',
                'temp_token' => Crypt::encryptString($authenticatedUser->id)
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | NORMAL LOGIN SUCCESS (NO MFA)
        |--------------------------------------------------------------------------
        */

        LoginAudits::create([
            'email' => $authenticatedUser->email,
            'date_time' => now(),
            'ip_address' => $request->ip(),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'status' => 'success',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'token' => $authenticatedUser->createToken('Web Token')->accessToken,
                'email' => $authenticatedUser->email,
                'id' => $authenticatedUser->id,
                'name' => $authenticatedUser->userDetails
                    ? ($authenticatedUser->userDetails->first_name . ' ' . $authenticatedUser->userDetails->last_name)
                    : $authenticatedUser->name,
                'type' => $authenticatedUser->user_type
            ]
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'fail',
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
                    $this->getPasswordRule($request->user_type ?? 'normal')
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
                "user_type" => 'normal',
                "must_change_password" => 1,
                "password_changed_at" => now(),
            ]);

            $this->addPasswordToHistory($user, $hashedPassword);

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
             $user = User::where('email', $request->email)->first();
             
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'current_password' => 'required',
                'password' => [
                    'required',
                    $this->getPasswordRule($request->user_type ?? 'normal')
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

            if ($user && Hash::check($request->input('current_password'), $user->password)) {
                // 1. Password Reuse Prevention Check
                if ($this->isPasswordInHistory($user, $request->input('password'))) {
                    return response()->json([
                        'status' => "fail",
                        'message' => 'You cannot reuse any of your last 5 passwords.'
                    ], 422);
                }

                $hashedPassword = Hash::make($request->input('password'));
                $user->password = $hashedPassword;
                $user->must_change_password = 0;
                $user->password_changed_at = now();
                $user->update();

                // Log to history
                $this->addPasswordToHistory($user, $hashedPassword);

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
public function users_by_sectors(Request $request)
{
    try {
        $sectorIds = $request->sector_ids ?? [];
        if (!is_array($sectorIds)) {
            $sectorIds = [$sectorIds];
        }

        $users = User::select('id', 'email', 'role')->where('user_type', '!=', 'super_admin') 
            ->with(['userDetails' => function ($query) {
                $query->select('user_id', 'first_name', 'last_name', 'mobile_no', 'sector');
            }])
            ->get();

        if (empty($sectorIds)) {
            return response()->json([]);
        }

        $filteredUsers = $users->filter(function($user) use ($sectorIds) {
            if (!$user->userDetails || !$user->userDetails->sector) {
                return false;
            }
            $userSectors = json_decode($user->userDetails->sector, true);
            if (!is_array($userSectors)) {
                $userSectors = [$user->userDetails->sector];
            }
            return count(array_intersect($userSectors, $sectorIds)) > 0;
        });

        return response()->json($filteredUsers->values());
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
                $user = User::find($user_id);
                if (!$user) {
                    return response()->json([
                        'status' => "fail",
                        'message' => 'User not found.'
                    ], 404);
                }

                $roleIds = is_string($user->role) ? json_decode($user->role, true) : $user->role;
                $userType = $user->user_type;

                $validator = Validator::make($request->all(), [
                    "password" => [
                        'required',
                        'confirmed',
                        $this->getPasswordRule($userType)
                    ],
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

                // 1. Password History Check (No reuse of last 5)
                if ($this->isPasswordInHistory($user, $request->input('password'))) {
                    return response()->json([
                        'status' => "fail",
                        'message' => 'You cannot reuse any of your last 5 passwords.'
                    ], 422);
                }

                $hashedPassword = Hash::make($request->input('password'));
                $user->password = $hashedPassword;
                $user->must_change_password = 0;
                $user->password_changed_at = now();
                $user->update();

                // Log to history
                $this->addPasswordToHistory($user, $hashedPassword);

                return response()->json([
                    'status' => "success",
                    'message' => 'Password reset successful'
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => "fail",
                    'message' => 'Request failed',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    }

     /**
     * ISO PASSWORD SECURITY UTILITIES & HELPER METHODS
     */

    private function getPasswordRule($userType = 'normal')
    {
        $minLength = 12; // Uniform ISO standard 12 characters for all users

        return Password::min($minLength)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
    }

    private function isPasswordInHistory($user, $newPassword)
    {
        if (!$user) {
            return false;
        }

        // Check the last 5 passwords saved in the database histories table
        $histories = DB::table('password_histories')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        foreach ($histories as $history) {
            if (Hash::check($newPassword, $history->password)) {
                return true;
            }
        }

        // Also block the current active password
        if (Hash::check($newPassword, $user->password)) {
            return true;
        }

        return false;
    }

    private function addPasswordToHistory($user, $hashedPassword)
{
    if (!$user) {
        return;
    }

    DB::table('password_histories')->insert([
        'user_id' => $user->id,
        'password' => $hashedPassword,
        'created_at' => now()
    ]);

    // Keep only latest 5 records
    $oldIds = DB::table('password_histories')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->skip(5)
        ->take(1000) // 🔥 IMPORTANT FIX
        ->pluck('id');

    if ($oldIds->isNotEmpty()) {
        DB::table('password_histories')
            ->whereIn('id', $oldIds)
            ->delete();
    }
}

    /**
     * MULTI-FACTOR AUTHENTICATION (MFA) CONTROLLERS
     */

    public function mfa_generate(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['status' => 'fail', 'message' => 'Unauthorized'], 401);
            }

            $secret = \App\Helpers\Google2FA::generateSecretKey();
            $qrCode = \App\Helpers\Google2FA::generateQRCode(
    $user->email,
    $secret,
    'DMS-CMG'
);

            $recoveryCodes = [];
            for ($i = 0; $i < 8; $i++) {
                $recoveryCodes[] = bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(4));
            }

            return response()->json([
                'status' => 'success',
                'secret' => $secret,
                'qrCodeUrl' => $qrCode,
                'recovery_codes' => $recoveryCodes
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()], 500);
        }
    }

    public function mfa_enable(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['status' => 'fail', 'message' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'secret' => 'required|string|size:16',
                'code' => 'required|string|size:6',
                'recovery_codes' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'fail', 'errors' => $validator->errors()], 422);
            }

            $secret = $request->input('secret');
            $code = $request->input('code');

            if (\App\Helpers\Google2FA::verifyKey($secret, $code)) {
                $userModel = User::find($user->id);
                $userModel->mfa_enabled = 1;
                $userModel->mfa_secret = Crypt::encryptString($secret);
                $userModel->mfa_recovery_codes = json_encode($request->input('recovery_codes'));
                $userModel->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'MFA successfully enabled.'
                ], 200);
            }

            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid OTP verification code. Please try again.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()], 500);
        }
    }

    public function mfa_disable(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json(['status' => 'fail', 'message' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
                'code' => 'required|string|size:6'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'fail', 'errors' => $validator->errors()], 422);
            }

            $userModel = User::find($user->id);

            if (!Hash::check($request->input('password'), $userModel->password)) {
                return response()->json(['status' => 'fail', 'message' => 'Incorrect password.'], 400);
            }

            $secret = Crypt::decryptString($userModel->mfa_secret);
            $code = $request->input('code');

            if (\App\Helpers\Google2FA::verifyKey($secret, $code)) {
                $userModel->mfa_enabled = 0;
                $userModel->mfa_secret = null;
                $userModel->mfa_recovery_codes = null;
                $userModel->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'MFA successfully disabled.'
                ], 200);
            }

            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid OTP verification code.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()], 500);
        }
    }

    public function mfa_verify_login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'temp_token' => 'required|string',
                'code' => 'required|string|size:6'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'fail', 'errors' => $validator->errors()], 422);
            }

            try {
                $userId = Crypt::decryptString($request->input('temp_token'));
            } catch (\Exception $e) {
                return response()->json(['status' => 'fail', 'message' => 'Session expired or invalid token.'], 400);
            }

            $user = User::with('userDetails')->find($userId);
            if (!$user) {
                return response()->json(['status' => 'fail', 'message' => 'User not found.'], 404);
            }

            if ($user->lockout_until && Carbon::parse($user->lockout_until)->isFuture()) {
                $remainingSeconds = Carbon::parse($user->lockout_until)->diffInSeconds(now());
                $remainingMinutes = ceil($remainingSeconds / 60);
                return response()->json([
                    'status' => "locked",
                    'message' => "Account is locked. Try again in {$remainingMinutes} minutes.",
                    'remaining_seconds' => $remainingSeconds
                ], 423);
            }

            $secret = Crypt::decryptString($user->mfa_secret);
            $code = $request->input('code');

            if (\App\Helpers\Google2FA::verifyKey($secret, $code)) {
                $user->failed_attempts = 0;
                $user->lockout_until = null;
                $user->save();

                LoginAudits::create([
                    'email' => $user->email,
                    'date_time' => now(),
                    'ip_address' => $request->ip(),
                    'latitude' => $request->get('latitude'),
                    'longitude' => $request->get('longitude'),
                    'status' => "success",
                ]);

                $response = [
                    'token' => $user->createToken('Web Token')->accessToken,
                    'email' => $user->email,
                    'id' => $user->id,
                    'name' => $user->userDetails ? ($user->userDetails->first_name . ' ' . $user->userDetails->last_name) : $user->name,
                    'type' => $user->user_type
                ];

                return response()->json([
                    'status' => 'success',
                    'message' => 'User login successful',
                    'data' => $response
                ], 200);
            }

            // Failure handles lockout
            $user->failed_attempts += 1;
            if ($user->failed_attempts >= 5) {
                $user->lockout_until = now()->addMinutes(15);
            }
            $user->save();

            $attemptsLeft = max(0, 5 - $user->failed_attempts);
            return response()->json([
                'status' => 'fail',
                'message' => $attemptsLeft > 0 
                    ? "Invalid OTP verification code. You have {$attemptsLeft} attempts remaining." 
                    : "Account locked due to consecutive failures. Try again in 15 minutes."
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()], 500);
        }
    }

    public function mfa_setup_generate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'temp_token' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'fail', 'errors' => $validator->errors()], 422);
            }

            try {
                $userId = Crypt::decryptString($request->input('temp_token'));
            } catch (\Exception $e) {
                return response()->json(['status' => 'fail', 'message' => 'Session expired.'], 400);
            }

            $user = User::find($userId);
            if (!$user) {
                return response()->json(['status' => 'fail', 'message' => 'User not found.'], 404);
            }

            $secret = \App\Helpers\Google2FA::generateSecretKey();
            $qrCode= \App\Helpers\Google2FA::generateQRCode($user->email, $secret, 'DMS-CMG');

            $recoveryCodes = [];
            for ($i = 0; $i < 8; $i++) {
                $recoveryCodes[] = bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(4));
            }

            return response()->json([
                'status' => 'success',
                'secret' => $secret,
                'qrCodeUrl' => $qrCode,
                'recovery_codes' => $recoveryCodes
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()], 500);
        }
    }

    public function mfa_setup_enable(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'temp_token' => 'required|string',
                'secret' => 'required|string|size:16',
                'code' => 'required|string|size:6',
                'recovery_codes' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'fail', 'errors' => $validator->errors()], 422);
            }

            try {
                $userId = Crypt::decryptString($request->input('temp_token'));
            } catch (\Exception $e) {
                return response()->json(['status' => 'fail', 'message' => 'Session expired.'], 400);
            }

            $user = User::with('userDetails')->find($userId);
            if (!$user) {
                return response()->json(['status' => 'fail', 'message' => 'User not found.'], 404);
            }

            $secret = $request->input('secret');
            $code = $request->input('code');

            if (\App\Helpers\Google2FA::verifyKey($secret, $code)) {
                $user->mfa_enabled = 1;
                $user->mfa_secret = Crypt::encryptString($secret);
                $user->mfa_recovery_codes = json_encode($request->input('recovery_codes'));
                $user->save();

                LoginAudits::create([
                    'email' => $user->email,
                    'date_time' => now(),
                    'ip_address' => $request->ip(),
                    'latitude' => $request->get('latitude'),
                    'longitude' => $request->get('longitude'),
                    'status' => "success",
                ]);

                $response = [
                    'token' => $user->createToken('Web Token')->accessToken,
                    'email' => $user->email,
                    'id' => $user->id,
                    'name' => $user->userDetails ? ($user->userDetails->first_name . ' ' . $user->userDetails->last_name) : $user->name,
                    'type' => $user->user_type
                ];

                return response()->json([
                    'status' => 'success',
                    'message' => 'MFA successfully enabled and logged in.',
                    'data' => $response
                ], 200);
            }

            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid OTP verification code.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()], 500);
        }
    }
     public function update_signature(Request $request)
    {
        try {
            $user_id = auth('api')->id();
            if (!$user_id) {
                return response()->json(['status' => 'fail', 'message' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'signature' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user_details = UserDetails::where('user_id', $user_id)->first();
            if (!$user_details) {
                return response()->json(['status' => 'fail', 'message' => 'User details not found'], 404);
            }

            if ($request->hasFile('signature')) {
                $file = $request->file('signature');
                $filename = time() . '_' . $user_id . '_' . $file->getClientOriginalName();
                $file->move(public_path('signatures'), $filename); 
                
                $user_details->signature = 'signatures/' . $filename;
                $user_details->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Signature updated successfully',
                    'signature_url' => asset('signatures/' . $filename)
                ], 200);
            }

            return response()->json(['status' => 'fail', 'message' => 'File not found'], 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => 'Request failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function get_signature(Request $request)
    {
        try {
            $user_id = auth('api')->id();
            if (!$user_id) {
                return response()->json(['status' => 'fail', 'message' => 'Unauthorized'], 401);
            }

            $user_details = UserDetails::where('user_id', $user_id)->first();
            if (!$user_details || !$user_details->signature) {
                return response()->json(['status' => 'fail', 'message' => 'Signature not found'], 404);
            }

            return response()->json([
                'status' => 'success',
                'signature_url' => asset($user_details->signature)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => 'Request failed', 'error' => $e->getMessage()], 500);
        }
    }
}
