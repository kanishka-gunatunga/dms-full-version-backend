<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;
use App\Http\Controllers\CommonFunctionsController;
use App\Models\LoginAudits;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Categories;
use App\Models\Sectors;

class SectorAPIController extends Controller
{

   
    public function add_sector(Request $request)
    {
        try {
            $input = $request->all();
            if (isset($input['category_ids']) && is_string($input['category_ids'])) {
                $decoded = json_decode($input['category_ids'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $input['category_ids'] = $decoded;
                }
            }

            $validator = Validator::make($input, [
                'parent_sector' => 'required',
                'sector_name' => 'required',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'integer|exists:categories,id'
            ]);
        
        
            if ($validator->fails()) {
                return response()->json([
                     'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422); 
            }


            $sector = new Sectors();
            $sector->parent_sector = $request->parent_sector;
            $sector->sector_name = $request->sector_name;
            $sector->save();

            if (isset($input['category_ids']) && is_array($input['category_ids'])) {
                $sector->categories()->sync($input['category_ids']);
            }

            $sector = Sectors::where('id', $sector->id)->first();
            
            $userId = auth('api')->id();

            $date_time = Carbon::now()->format('Y-m-d H:i:s');
            $auditFunction = new CommonFunctionsController();
            $auditFunction->document_audit_trail('new sector added','sector', $userId, $sector->id, $date_time, null, null);

            return response()->json([
                'status' => "success",
                'message' => 'Sector added.',
                'parent' => $sector->parent_sector,
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }
    public function sector_details($id,Request $request)
    {
         
        try {
            if($request->isMethod('get')){
                $sector = Sectors::with('categories')->where('id', $id)->first();
                return response()->json($sector);
            }
            if($request->isMethod('post')){

                $input = $request->all();
                if (isset($input['category_ids']) && is_string($input['category_ids'])) {
                    $decoded = json_decode($input['category_ids'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $input['category_ids'] = $decoded;
                    }
                }

                $validator = Validator::make($input, [
                    'parent_sector' => 'required',
                    'sector_name' => 'required',
                    'category_ids' => 'nullable|array',
                    'category_ids.*' => 'integer|exists:categories,id'
                ]);
        
        
            if ($validator->fails()) {
                return response()->json([
                     'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422); 
            }
            
            $sector =  Sectors::where('id',  $id)->first();;
            $sector->parent_sector = $request->parent_sector;
            $sector->sector_name = $request->sector_name;
            $sector->update();

            if (isset($input['category_ids']) && is_array($input['category_ids'])) {
                $sector->categories()->sync($input['category_ids']);
            } else {
                $sector->categories()->sync([]);
            }

            $sector_details = Sectors::where('id', $id)->first();
    
            $userId = auth('api')->id();

            $date_time = Carbon::now()->format('Y-m-d H:i:s');
            $auditFunction = new CommonFunctionsController();
            $auditFunction->document_audit_trail('sector removed','sector', $userId, $id, $date_time, null, null);

            return response()->json([
                'status' => "success",
                'message' => 'Sector updated.',
                'parent' => $sector_details->parent_sector,
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

    public function delete_sector($id,Request $request)
    {
         
        try {

            $sector_details = Sectors::where('id', $id)->first();

            Sectors::where('id', $id)->delete(); 
            Sectors::where('parent_sector', $id)->delete(); 

            $userId = auth('api')->id();

            $date_time = Carbon::now()->format('Y-m-d H:i:s');
            $auditFunction = new CommonFunctionsController();
            $auditFunction->document_audit_trail('sector details updated','sector', $userId, $id, $date_time, null, null);

            return response()->json([
                'status' => "success",
                'message' => 'Sector Deleted.',
                'parent' => $sector_details->parent_sector,
            ], 201);
        
        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }

    public function sectors(Request $request)
    {
         
        try {
            if($request->isMethod('get')){
                $sectors = Sectors::where('parent_sector', 'none')->select('id', 'parent_sector', 'sector_name')->get();
                return response()->json($sectors);
            }
         
        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }
    public function sectors_by_id($id,Request $request)
    {
         
        try {
            if($request->isMethod('get')){
                $sectors = Sectors::where('parent_sector', $id)->select('id', 'parent_sector', 'sector_name')->get();
                return response()->json($sectors);
            }
         
        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }
    public function all_sectors(Request $request)
    {
         
        try {
            if($request->isMethod('get')){
                $sectors = Sectors::select('id', 'parent_sector', 'sector_name')->get();
                return response()->json($sectors);
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
