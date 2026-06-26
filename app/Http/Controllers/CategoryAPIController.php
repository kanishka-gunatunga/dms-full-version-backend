<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Validation\Rules\Password; 
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Http\Controllers\CommonFunctionsController;

use App\Models\LoginAudits;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Categories;
use App\Models\Attribute;


class CategoryAPIController extends Controller
{

   
public function add_category(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'parent_category' => 'required',
            'category_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => "fail",
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422); 
        }

        $category = new Categories();
        $category->parent_category = $request->parent_category;
        $category->category_name = $request->category_name;
        $category->description = $request->description;
        if ($request->filled('ftp_account')) {
            $category->ftp_account = $request->ftp_account;
        }
         if ($request->has('signing_roles')) {
            $category->signing_roles = $request->signing_roles;
        }
        if ($request->has('signing_users')) {
            $category->signing_users = $request->signing_users;
        }
        $category->status = 'active';
        $category->save();

        $headers = [['name', 'description', 'meta_tags']];

        if ($request->attribute_data != '') {
            $attribute = new Attribute();
            $attribute->category = $category->id;

            $attributeData = is_string($request->attribute_data)
                ? json_decode($request->attribute_data, true)
                : $request->attribute_data;

            $attribute->attributes = json_encode($attributeData);
            $attribute->save();
            foreach ($attributeData as $key => $value) {
                if (is_string($value)) {
                    $headers[0][] = $value;
                }
            }

            $fileName = 'category_template_' . $category->id . '_' . time() . '.xlsx';
            $filePath = 'excel_templates/' . $fileName;

            Excel::store(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
                private $data;
                public function __construct(array $data)
                {
                    $this->data = $data;
                }

                public function array(): array
                {
                    return $this->data;
                }
            }, $filePath, 'public');

            $storagePath = storage_path('app/public/' . $filePath);
            $destinationPath = public_path('uploads/excel_templates');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $destinationFilePath = $destinationPath . '/' . $fileName;
            
            rename($storagePath, $destinationFilePath);

            $downloadUrl = asset('uploads/excel_templates/'.$fileName.'');
           
        }
        else{
            $fileName = 'category_template_' . $category->id . '_' . time() . '.xlsx';
            $filePath = 'excel_templates/' . $fileName;
            
            Excel::store(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
                private $data;
                public function __construct(array $data)
                {
                    $this->data = $data;
                }

                public function array(): array
                {
                    return $this->data;
                }
            }, $filePath, 'public');

            $storagePath = storage_path('app/public/' . $filePath);
            $destinationPath = public_path('uploads/excel_templates');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $destinationFilePath = $destinationPath . '/' . $fileName;
            
            rename($storagePath, $destinationFilePath);

            $downloadUrl = asset('uploads/excel_templates/'.$fileName.'');

        }

        $category =  Categories::where('id', '=', $category->id)->first();;
        $category->template = $fileName;
        $category->update();

        $userId = auth('api')->id();

        $date_time = Carbon::now()->format('Y-m-d H:i:s');
        $auditFunction = new CommonFunctionsController();
        $auditFunction->document_audit_trail('new category added','category', $userId, $category->id, $date_time, null, null);

        return response()->json([
            'status' => 'success',
            'message' => 'Category added.',
            'template_url' => $downloadUrl ?? null,
        ], 201);

    } catch (\Exception $e) {

        return response()->json([
            'status' => "fail",
            'message' => 'Request failed',
            'error' => $e->getMessage()
        ], 500);
    }    
}

public function category_details($id, Request $request)
{
    try {
        if ($request->isMethod('get')) {
            $category = Categories::with('sectors')->where('id', $id)->first();
            $category->attributes = Attribute::where('category', $id)->first() ?? null;
            $category->template = asset('uploads/excel_templates/'.$category->template.'') ?? null;
            return response()->json($category);
        }

        if ($request->isMethod('post')) {

            $validator = Validator::make($request->all(), [
                'parent_category' => 'required',
                'category_name' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => "fail",
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422); 
            } 

            if ($request->parent_category !== 'none') {
                if ($request->parent_category == $id) {
                    return response()->json([
                        'status' => "fail",
                        'message' => 'Category cannot be its own parent.'
                    ], 422);
                }
                if ($this->isDescendantOf($request->parent_category, $id)) {
                    return response()->json([
                        'status' => "fail",
                        'message' => 'Category cannot have its own descendant as a parent.'
                    ], 422);
                }
            }

            $headers = [['name', 'description', 'meta_tags']];

            $existingAttribute = Attribute::where('category', $id)->first();

            if ($existingAttribute) {
                $attributeData = is_string($request->attribute_data)
                    ? json_decode($request->attribute_data, true)
                    : $request->attribute_data;
            
                $existingAttribute->attributes = json_encode($attributeData ?? []);
                $existingAttribute->save();
            } else {
                $attribute = new Attribute();
                $attribute->category = $id;
                $attributeData = is_string($request->attribute_data)
                    ? json_decode($request->attribute_data, true)
                    : $request->attribute_data;
                $attribute->attributes = json_encode($attributeData ?? []);
                $attribute->save();
            }

            $updatedAttribute = Attribute::where('category', $id)->first();
            if ($updatedAttribute) {
                $attributeData = json_decode($updatedAttribute->attributes, true);

                foreach ($attributeData as $key => $value) {
                    if (is_string($value)) {
                        $headers[0][] = $value; 
                    }
                }
            }

            $fileName = 'category_template_' . $id . '_' . time() . '.xlsx';
            $filePath = 'excel_templates/' . $fileName;

            Excel::store(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
                private $data;
                public function __construct(array $data)
                {
                    $this->data = $data;
                }

                public function array(): array
                {
                    return $this->data;
                }
            }, $filePath, 'public');

            $storagePath = storage_path('app/public/' . $filePath);
            $destinationPath = public_path('uploads/excel_templates');
            
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $destinationFilePath = $destinationPath . '/' . $fileName;
            
            rename($storagePath, $destinationFilePath);

            $downloadUrl = asset('uploads/excel_templates/'.$fileName.'');

            $category = Categories::where('id', '=', $id)->first();
            $category->parent_category = $request->parent_category;
            $category->category_name = $request->category_name;
            $category->description = $request->description;
            $category->template = $fileName;  
            if ($request->filled('ftp_account')) {
                $category->ftp_account = $request->ftp_account;
            }
              if ($request->has('signing_roles')) {
                $category->signing_roles = $request->signing_roles;
            }
            if ($request->has('signing_users')) {
                $category->signing_users = $request->signing_users;
            }
            $category->update();

            $userId = auth('api')->id();

            $date_time = Carbon::now()->format('Y-m-d H:i:s');
            $auditFunction = new CommonFunctionsController();
            $auditFunction->document_audit_trail('category details updated','category', $userId,  $id, $date_time, null, null);

            return response()->json([
                'status' => "success",
                'message' => 'Category updated successfully',
                'template_url' => $downloadUrl
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


public function delete_category($id,Request $request)
{
     
    try {

        $category =  Categories::where('id', '=', $id)->first();
        if ($category) {
            $category->status = 'inactive';
            $category->update();
            
            $this->disableDescendants($id);
        }

        return response()->json([
            'status' => "success",
            'message' => 'Category Disabled'
        ], 201);
    
    } catch (\Exception $e) {

        return response()->json([
            'status' => "fail",
            'message' => 'Request failed',
            'error' => $e->getMessage()
        ], 500);
    }    
}

    public function categories(Request $request)
    {
         
        try {
            if($request->isMethod('get')){
                $categories = Categories::where('status','active')->select('id', 'parent_category', 'category_name', 'template', 'status', 'signing_roles', 'signing_users')->get();
                foreach ($categories as $category) {
                    $category->template = asset('uploads/excel_templates/'.$category->template.'') ?? null;
                }
                
                return response()->json($categories);
            }
         
        } catch (\Exception $e) {

            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }
    public function categories_with_childs(Request $request)
    {
        try {
            if($request->isMethod('get')){
                $categories = Categories::where('status','active')->select('id', 'parent_category', 'category_name', 'template', 'status', 'signing_roles', 'signing_users')->get();

                $categoryMap = [];
                foreach ($categories as $category) {
                    $categoryMap[$category->id] = [
                        'id' => $category->id,
                        'category_name' => $category->category_name,
                        'parent_category' => $category->parent_category,
                        'children' => [],
                        'template' => $category->template ? asset('uploads/excel_templates/'.$category->template) : null,
                        'status' => $category->status,
                        'signing_roles' => $category->signing_roles,
                        'signing_users' => $category->signing_users,
                    ];
                }

                $tree = [];
                foreach ($categoryMap as $id => &$categoryNode) {
                    $parent = $categoryNode['parent_category'];
                    if ($parent === 'none' || !isset($categoryMap[$parent])) {
                        $tree[] = &$categoryNode;
                    } else {
                        $categoryMap[$parent]['children'][] = &$categoryNode;
                    }
                }

                return response()->json(array_values($tree));
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }
    public function categories_with_doc_count(Request $request)
    {
        try {
            if ($request->isMethod('get')) {
    
                // Fetch all active categories with documents count
                $categories = Categories::where('status', 'active')
                    ->select('id', 'parent_category', 'category_name')
                    ->withCount('documents')
                    ->get();
    
                $childrenMap = [];
                foreach ($categories as $cat) {
                    $childrenMap[$cat->parent_category][] = $cat;
                }

                $getDescendantsDocCount = null;
                $getDescendantsDocCount = function($catId) use (&$childrenMap, &$getDescendantsDocCount) {
                    $total = 0;
                    if (isset($childrenMap[$catId])) {
                        foreach ($childrenMap[$catId] as $child) {
                            $total += $child->documents_count + $getDescendantsDocCount($child->id);
                        }
                    }
                    return $total;
                };

                $parentCategories = [];
                foreach ($categories as $category) {
                    if ($category->parent_category === 'none') {
                        $parentCategories[] = [
                            'id' => $category->id,
                            'category_name' => $category->category_name,
                            'documents_count' => $category->documents_count + $getDescendantsDocCount($category->id),
                        ];
                    }
                }
    
                return response()->json($parentCategories);
            }
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => "fail",
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }    
    }

    private function disableDescendants($parentId)
    {
        $subCategories = Categories::where('parent_category', '=', $parentId)->get();
        foreach ($subCategories as $subCategory) {
            $subCategory->status = 'inactive';
            $subCategory->update();
            $this->disableDescendants($subCategory->id);
        }
    }

    private function isDescendantOf($parentCandidateId, $childId)
    {
        $parent = Categories::where('id', '=', $parentCandidateId)->first();
        while ($parent && $parent->parent_category !== 'none') {
            if ($parent->parent_category == $childId) {
                return true;
            }
            $parent = Categories::where('id', '=', $parent->parent_category)->first();
        }
        return false;
    }
    
}
