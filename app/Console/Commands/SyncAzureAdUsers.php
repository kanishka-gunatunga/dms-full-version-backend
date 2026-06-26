<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CommonFunctionsController;
use App\Models\User;
use App\Models\UserDetails;

class SyncAzureAdUsers extends Command
{
    protected $signature = 'ad:sync-users';
    protected $description = 'Sync users from Azure AD';

    public function handle()
    {
        try {

            $getUsers = new CommonFunctionsController();
            $usersResponse = $getUsers->getUsers();

            if (!isset($usersResponse['value'])) {
                $this->error("No users found");
                return 0;
            }

            foreach ($usersResponse['value'] as $adUser) {

                try {

                    $upn  = $adUser['userPrincipalName'] ?? null;
                    $mail = $adUser['mail'] ?? null;
                    $name = $adUser['displayName'] ?? '';

                    if (!$upn) continue;

                    $user = User::where('email', $mail)
                        ->orWhere('email', $upn)
                        ->first();

                    if ($user) {

                        $user->email = $upn;
                        $user->save();

                    } else {

                        $nameParts = explode(' ', $name, 2);

                        $user = User::create([
                            "email" => $upn,
                            "user_type" => "ad",
                        ]);

                        UserDetails::create([
                            "user_id" => $user->id,
                            "first_name" => $nameParts[0] ?? null,
                            "last_name" => $nameParts[1] ?? null,
                            "mobile_no" => $adUser['mobilePhone'] ?? null,
                        ]);
                    }

                } catch (\Exception $e) {
                    continue;
                }
            }

            $this->info("Azure AD sync completed successfully");

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}