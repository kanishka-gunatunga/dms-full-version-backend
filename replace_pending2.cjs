const fs = require('fs');
const file = 'd:/KODE TECH/DMS LATEST WITH LICENCE/backend/app/Http/Controllers/DocumentAPIController.php';
let content = fs.readFileSync(file, 'utf8');

const regex = /\$roleIds = \[\];[\s\S]*?->get\(\);/;

const replacement = `            // NEW LOGIC: Check levels
            $categories = \\App\\Models\\Categories::whereNotNull('signing_users')->get(['id', 'signing_users']);
            $assignedCategoriesData = [];
            
            foreach ($categories as $category) {
                $signingLevels = json_decode($category->signing_users, true);
                if (!is_array($signingLevels)) continue;
                
                $userLevel = null;
                $previousLevelUsers = [];
                
                foreach ($signingLevels as $levelObj) {
                    if (isset($levelObj['users']) && is_array($levelObj['users'])) {
                        if (in_array((string)$userId, $levelObj['users'])) {
                            $userLevel = $levelObj['level'];
                            break;
                        } else {
                            $previousLevelUsers = array_merge($previousLevelUsers, $levelObj['users']);
                        }
                    }
                }
                
                if ($userLevel !== null) {
                    $assignedCategoriesData[$category->id] = array_unique($previousLevelUsers);
                }
            }
            
            $assignedCategoryIds = array_keys($assignedCategoriesData);

            $documentsQuery = \\App\\Models\\Documents::whereIn('category', $assignedCategoryIds)
                ->where(function ($query) {
                    $query->where('is_archived', 0)
                          ->orWhereNull('is_archived');
                })
                ->where('indexed_or_encrypted', 'yes')
                ->whereDoesntHave('signatures', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->select('id', 'name', 'type', 'storage', 'category', 'uploaded_method', 'document_preview')
                ->orderBy('id', 'DESC')
                ->with(['category' => function ($query) {
                    $query->select('id', 'category_name');
                }])
                ->get();
                
            $filteredDocuments = [];
            
            if ($documentsQuery->count() > 0) {
                $documentIds = $documentsQuery->pluck('id')->toArray();
                
                $allSignatures = \\App\\Models\\DocumentSignature::whereIn('document_id', $documentIds)
                    ->get(['document_id', 'user_id'])
                    ->groupBy('document_id');
                    
                foreach ($documentsQuery as $document) {
                    $previousUsersRequired = $assignedCategoriesData[$document->category] ?? [];
                    
                    if (empty($previousUsersRequired)) {
                        $filteredDocuments[] = $document;
                        continue;
                    }
                    
                    $documentSignatures = isset($allSignatures[$document->id]) 
                        ? $allSignatures[$document->id]->pluck('user_id')->map(function($id) { return (string)$id; })->toArray() 
                        : [];
                        
                    $allPreviousSigned = true;
                    foreach ($previousUsersRequired as $reqUserId) {
                        if (!in_array((string)$reqUserId, $documentSignatures)) {
                            $allPreviousSigned = false;
                            break;
                        }
                    }
                    
                    if ($allPreviousSigned) {
                        $filteredDocuments[] = $document;
                    }
                }
            }
            
            $documents = collect($filteredDocuments);`;

if (regex.test(content)) {
    content = content.replace(regex, replacement);
    fs.writeFileSync(file, content);
    console.log('Replaced successfully');
} else {
    console.log('Regex not found');
}
