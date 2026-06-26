<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Http;

use App\Models\LoginAudits;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Categories;
use App\Models\Sectors;
use App\Models\Attribute;
use App\Models\ADCredential;
class ADController extends Controller
{

  public function import_users(Request $request)
{
    try {

        $getUsers = new CommonFunctionsController();
        $usersResponse = $getUsers->getUsers();

        if (!isset($usersResponse['value'])) {
            return response()->json([
                'status' => "fail",
                'message' => 'No users found in the response'
            ], 400);
        }

        $users = $usersResponse['value'];

        foreach ($users as $adUser) {

            try {

                $upn  = $adUser['userPrincipalName'] ?? null;
                $mail = $adUser['mail'] ?? null;
                $name = $adUser['displayName'] ?? '';

                if (!$upn) {
                    continue;
                }

                /**
                 * 🔍 STEP 1: find existing user by mail OR email
                 */
                $existingUser = User::where('email', $mail)
                    ->orWhere('email', $upn)
                    ->first();

                if ($existingUser) {

                    /**
                     * 🔁 STEP 2: update email to latest UPN
                     */
                    if ($existingUser->email !== $upn) {
                        $existingUser->email = $upn;
                        $existingUser->save();
                    }

                } else {

                    /**
                     * ➕ STEP 3: create new user
                     */
                    $nameParts = explode(' ', $name, 2);
                    $firstName = $nameParts[0] ?? null;
                    $lastName  = $nameParts[1] ?? null;

                    $user = User::create([
                        "email" => $upn,
                        "user_type" => 'ad',
                    ]);

                    $userDetails = new UserDetails();
                    $userDetails->user_id = $user->id;
                    $userDetails->first_name = $firstName;
                    $userDetails->last_name = $lastName;
                    $userDetails->mobile_no = $adUser['mobilePhone'] ?? null;
                    $userDetails->save();
                }

            } catch (\Exception $e) {
                continue;
            }
        }

        return response()->json([
            'status' => "success",
            'message' => 'Users synced successfully (mail → UPN update enabled)'
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'status' => "fail",
            'message' => 'Request failed',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function ad_credentials(Request $request)
{
    try {
        if ($request->isMethod('get')) {
            $credential_details = ADCredential::first() ?? [];
            return response()->json($credential_details);
        }

        if ($request->isMethod('post')) {

            $validator = Validator::make($request->all(), [
                'tenant_id' => 'required',
                'client_id' => 'required',
                'client_secret' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422); 
            } 
            $credential_details = ADCredential::first();
            if($credential_details){
                $credential = ADCredential::where('id', '=', $credential_details->id)->first();
                $credential->tenant_id = $request->tenant_id;
                $credential->client_id = $request->client_id;
                $credential->client_secret = $request->client_secret;
                $credential->update();
            }
            else{
                $credential = new ADCredential();
                $credential->tenant_id = $request->tenant_id;
                $credential->client_id = $request->client_id;
                $credential->client_secret = $request->client_secret;
                $credential->save();
            }

            return response()->json([
                'status' => "success",
                'message' => 'Credentials added successfully'
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
}
