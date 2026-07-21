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
use App\Models\Documents;
use App\Models\Sectors;
use App\Models\DocumentSharedUsers;
class DashboardController extends Controller
{

  
    public function admin_dashboard_data(Request $request)
    {
        try {
            if ($request->isMethod('get')) {
    
                // --- TOTAL COUNTS ---
                $total_user_count = User::where('user_type', 'normal')->count();
                $total_document_count = Documents::where('indexed_or_encrypted', 'yes')->count();
                $total_categories_count = Categories::where('status', 'active')->count();
                $total_sectors_count = Sectors::count();
    
                // --- DOCUMENTS BY CATEGORY ---
                $documents_by_category = Documents::select('category', DB::raw('COUNT(*) as count'))
                    ->where('indexed_or_encrypted', 'yes')
                    ->groupBy('category')
                    ->get();
    
                $categories = Categories::where('status', 'active')
                    ->select('id', 'category_name')
                    ->get();
    
                $category_distribution = $categories->map(function ($category) use ($documents_by_category, $total_document_count) {
                    $docCount = $documents_by_category->where('category', $category->id)->first()->count ?? 0;
                    $percentage = $total_document_count > 0 ? round(($docCount / $total_document_count) * 100, 2) : 0;
    
                    return [
                        'category' => $category->id,
                        'category_name' => $category->category_name,
                        'documents_count' => $docCount,
                        'percentage' => $percentage
                    ];
                });
    
                // --- DOCUMENTS BY SECTOR ---
                $documents_by_sector = Documents::select('sector_category', DB::raw('COUNT(*) as count'))
                    ->where('indexed_or_encrypted', 'yes')
                    ->groupBy('sector_category')
                    ->get();
    
                $sectors = Sectors::select('id', 'sector_name')->get();
    
                $sector_distribution = $sectors->map(function ($sector) use ($documents_by_sector, $total_document_count) {
                    $docCount = $documents_by_sector->where('sector_category', $sector->id)->first()->count ?? 0;
                    $percentage = $total_document_count > 0 ? round(($docCount / $total_document_count) * 100, 2) : 0;
    
                    return [
                        'sector' => $sector->id,
                        'sector_name' => $sector->sector_name,
                        'documents_count' => $docCount,
                        'percentage' => $percentage
                    ];
                });
    
                // --- NEAR EXPIRED DOCUMENTS (within 30 days) ---
                $nearExpiryDocuments = Documents::with(['category_data', 'sector'])
                    ->where('indexed_or_encrypted', 'yes')
                    ->whereNotNull('expiration_date')
                    ->where('expiration_date', '>=', now())
                    ->where('expiration_date', '<=', now()->addDays(30))
                    ->get()
                    ->map(function ($doc) {
                        return [
                            'id' => $doc->id,
                            'name' => $doc->name,
                            'category_id' => $doc->category_data->id ?? null,
                            'category_name' => $doc->category_data->category_name ?? 'Uncategorized',
                            'sector_id' => $doc->sector->id ?? null,
                            'sector_name' => $doc->sector->sector_name ?? 'N/A',
                            'expiration_date' => $doc->expiration_date,
                          'days_to_expire' => $doc->expiration_date
    ? (int) \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($doc->expiration_date))
    : null,
                        ];
                    });
    
                // --- PENDING ARCHIVE DOCUMENTS ---
                $pendingArchiveDocuments = Documents::with(['category_data', 'sector'])
                    ->where('indexed_or_encrypted', 'yes')
                    ->whereNotNull('expiration_date')
                    ->where('expiration_date', '<', now())
                    ->where('force_archive', 0)
                    ->where(function ($q) {
                        $q->where('is_archived', 0)->orWhereNull('is_archived');
                    })
                    ->get()
                    ->map(function ($doc) {
                        return [
                            'id' => $doc->id,
                            'name' => $doc->name,
                            'category_id' => $doc->category_data->id ?? null,
                            'category_name' => $doc->category_data->category_name ?? 'Uncategorized',
                            'sector_id' => $doc->sector->id ?? null,
                            'sector_name' => $doc->sector->sector_name ?? 'N/A',
                            'expiration_date' => $doc->expiration_date,
                            'days_expired' => (int) \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($doc->expiration_date), false),
                        ];
                    });

                // --- RESPONSE ---
                return response()->json([
                    'status' => 'success',
                    'total_users' => $total_user_count,
                    'total_documents' => $total_document_count,
                    'total_categories' => $total_categories_count,
                    'total_sectors' => $total_sectors_count,
                    'documents_by_category' => $category_distribution,
                    'documents_by_sector' => $sector_distribution,
                    'near_expiry_documents' => $nearExpiryDocuments,
                    'pending_archive_documents' => $pendingArchiveDocuments,
                ]);
            }
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Request failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
public function user_dashboard_data(Request $request)
{
    try {
        if ($request->isMethod('get')) {
            $userId = auth('api')->id();

            // 🔹 Get user's sector (supports multiple sectors)
            $sector = UserDetails::where('user_id', $userId)->value('sector');
            $sectorIds = json_decode($sector, true);
            if (!is_array($sectorIds)) {
                $sectorIds = $sector ? [$sector] : [];
            }

            $sector_details = Sectors::whereIn('id', $sectorIds)->get();
            $sectorNames = $sector_details->pluck('sector_name')->toArray();
            $sectorNameStr = !empty($sectorNames) ? implode(', ', $sectorNames) : 'N/A';

            // 🔹 Get shared documents for this user
            $sharedDocumentIds = DocumentSharedUsers::where('user', $userId)->pluck('document_id');

            // 🔹 Count documents belonging to these sectors
            $sector_document_count = Documents::where(function ($query) {
                    $query->where('is_archived', 0)->orWhereNull('is_archived');
                })
                ->where('indexed_or_encrypted', 'yes')
                ->whereIn('sector_category', $sectorIds)
                ->count();

            // 🔹 Count users in the same sectors
            $sector_user_count = UserDetails::where(function ($query) use ($sectorIds) {
                foreach ($sectorIds as $secId) {
                    $query->orWhereJsonContains('sector', $secId)
                          ->orWhere('sector', $secId);
                }
            })->count();

            // 🔹 Get all assigned documents (no trashed)
            $assigned_documents = Documents::where(function ($query) {
                    $query->where('is_archived', 0)->orWhereNull('is_archived');
                })
                ->where('indexed_or_encrypted', 'yes')
                ->where(function ($query) use ($sectorIds, $sharedDocumentIds) {
                    $query->whereIn('sector_category', $sectorIds)
                          ->orWhereIn('id', $sharedDocumentIds);
                })
                ->with(['category_data' => function ($query) {
                    $query->select('id', 'category_name');
                }])
                ->get();

            $assigned_documents_count = $assigned_documents->count();

            // 🔹 Recently assigned (within 2 weeks)
            $recently_assigned_count = $assigned_documents->filter(function ($doc) {
                return $doc->created_at >= now()->subWeeks(2);
            })->count();

            // 🔹 Due this week (expiration_date within current week)
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();

            $due_this_week_count = $assigned_documents->filter(function ($doc) use ($startOfWeek, $endOfWeek) {
                return $doc->expiration_date &&
                       $doc->expiration_date >= $startOfWeek &&
                       $doc->expiration_date <= $endOfWeek;
            })->count();

            // 🔹 Overdue documents (expired — even if soft deleted)
            $overdue_count = Documents::withTrashed()
                ->where(function ($query) {
                    $query->where('is_archived', 0)->orWhereNull('is_archived');
                })
                ->where('indexed_or_encrypted', 'yes')
                ->where(function ($query) use ($sectorIds, $sharedDocumentIds) {
                    $query->whereIn('sector_category', $sectorIds)
                          ->orWhereIn('id', $sharedDocumentIds);
                })
                ->where('expiration_date', '<', now())
                ->count();

            // 🔹 Prepare formatted assigned documents
            $formatted_assigned_documents = $assigned_documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'document_name' => $doc->name,
                    'category_id' => $doc->category_data->id ?? null,
                    'category_name' => $doc->category_data->category_name ?? 'Uncategorized',
                    'expiration_date' => $doc->expiration_date
                        ? \Carbon\Carbon::parse($doc->expiration_date)->format('Y-m-d')
                        : null,
                    'is_new' => $doc->created_at >= now()->subWeeks(2) ? 1 : 0,
                    'days_since_added' => $doc->created_at
                        ? (int) $doc->created_at->diffInDays(now())
                        : 0,
                ];
            });

            // 🔹 Category-based count and percentage (from assigned documents)
            $category_stats = $formatted_assigned_documents
                ->groupBy('category_name')
                ->map(function ($group) use ($assigned_documents_count) {
                    $count = $group->count();
                    $percentage = $assigned_documents_count > 0
                        ? round(($count / $assigned_documents_count) * 100, 2)
                        : 0;
                    return [
                        'category_name' => $group->first()['category_name'],
                        'documents_count' => $count,
                        'percentage' => $percentage,
                    ];
                })
                ->values(); // reset keys

                $nearExpiryDocuments = $assigned_documents
                ->load('sector') // make sure sector relation is loaded
                ->filter(function ($doc) {
                    return $doc->expiration_date && 
                           \Carbon\Carbon::parse($doc->expiration_date)->between(now(), now()->addDays(30));
                })
                ->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'document_name' => $doc->name,
                        'category_id' => $doc->category_data->id ?? null,
                        'category_name' => $doc->category_data->category_name ?? 'Uncategorized',
                        'sector_id' => $doc->sector->id ?? null,            // <-- added sector
                        'sector_name' => $doc->sector->sector_name ?? 'N/A',// <-- added sector
                        'expiration_date' => $doc->expiration_date,
                        'days_to_expire' => (int) \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($doc->expiration_date)),
                    ];
                })->values();

                $pendingArchiveDocuments = $assigned_documents
                ->load('sector')
                ->filter(function ($doc) {
                    return $doc->expiration_date && 
                           \Carbon\Carbon::parse($doc->expiration_date)->isPast() &&
                           $doc->force_archive == 0;
                })
                ->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'document_name' => $doc->name,
                        'category_id' => $doc->category_data->id ?? null,
                        'category_name' => $doc->category_data->category_name ?? 'Uncategorized',
                        'sector_id' => $doc->sector->id ?? null,
                        'sector_name' => $doc->sector->sector_name ?? 'N/A',
                        'expiration_date' => $doc->expiration_date,
                        'days_expired' => (int) \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($doc->expiration_date), false),
                    ];
                });

            // ✅ Return JSON response
            return response()->json([
                'status' => 'success',
                'sector_name' => $sectorNameStr,
                'sector_user_count' => $sector_user_count,
                'sector_document_count' => $sector_document_count,
                'assigned_documents_count' => $assigned_documents_count,
                'recently_assigned_count' => $recently_assigned_count,
                'due_this_week_count' => $due_this_week_count,
                'overdue_count' => $overdue_count,
                'assigned_documents' => $formatted_assigned_documents,
                'category_distribution' => $category_stats,
                'near_expiry_documents' => $nearExpiryDocuments,
                'pending_archive_documents' => $pendingArchiveDocuments,
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'fail',
            'message' => 'Request failed',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
