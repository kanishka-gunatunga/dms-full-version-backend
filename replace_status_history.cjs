const fs = require('fs');
const file = 'd:/KODE TECH/DMS LATEST WITH LICENCE/backend/app/Http/Controllers/DocumentAPIController.php';
let content = fs.readFileSync(file, 'utf8');

// 1. document_sign_status logic
const statusRegex = /\$category = Categories::find\(\$document->category\);[\s\S]*?\$result\[\] = \[\s*'user_id' => \$userId,\s*'name' => trim\(\$userDetails->first_name \. ' ' \. \$userDetails->last_name\),\s*'status' => \$status,\s*'sign_option' => \$signOption,\s*'date' => \$date\s*\];\s*\}/;

const statusReplacement = `$category = \\App\\Models\\Categories::find($document->category);
            $signingLevels = [];
            
            if ($category) {
                $signingLevels = is_string($category->signing_users) ? json_decode($category->signing_users, true) : (array)$category->signing_users;
            }

            $userLevelsMap = [];
            if (is_array($signingLevels)) {
                foreach ($signingLevels as $levelObj) {
                    if (isset($levelObj['users']) && is_array($levelObj['users']) && isset($levelObj['level'])) {
                        foreach ($levelObj['users'] as $uId) {
                            $userLevelsMap[(string)$uId] = $levelObj['level'];
                        }
                    }
                }
            }

            $assignedUserIds = array_keys($userLevelsMap);
            $assignedUsers = \\App\\Models\\UserDetails::with('user')->whereIn('user_id', $assignedUserIds)->get();

            $signatures = \\App\\Models\\DocumentSignature::where('document_id', $id)->get()->keyBy('user_id');

            $result = [];
            foreach ($assignedUsers as $userDetails) {
                $userId = $userDetails->user_id;
                $sig = $signatures->get($userId);
                
                if ($sig) {
                    $status = 'Signed';
                    $signOption = $sig->is_marked_as_signed ? 'Marked' : 'Signature';
                    $date = $sig->created_at ? $sig->created_at->format('Y-m-d H:i:s') : null;
                } else {
                    $status = 'Not Signed';
                    $signOption = '-';
                    $date = null;
                }

                $result[] = [
                    'user_id' => $userId,
                    'name' => trim($userDetails->first_name . ' ' . $userDetails->last_name),
                    'status' => $status,
                    'sign_option' => $signOption,
                    'date' => $date,
                    'level' => $userLevelsMap[(string)$userId] ?? null
                ];
            }

            usort($result, function($a, $b) {
                return ($a['level'] ?? 999) <=> ($b['level'] ?? 999);
            })`;

if (statusRegex.test(content)) {
    content = content.replace(statusRegex, statusReplacement);
    console.log('Status updated');
} else {
    console.log('Status target not found!');
}


// 2. document_sign_history logic
const historyRegex = /\$pdf = new \\FPDF\(\);[\s\S]*?\$pdf->Cell\(40, 10, \$status, 1\);\s*\$pdf->Ln\(\);\s*\}/;

const historyReplacement = `$pdf = new \\FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, 'Sign History: ' . $document->name, 0, 1, 'C');
            $pdf->Ln(5);

            $category = \\App\\Models\\Categories::find($document->category);
            $userLevelsMap = [];
            if ($category) {
                $signingLevels = is_string($category->signing_users) ? json_decode($category->signing_users, true) : (array)$category->signing_users;
                if (is_array($signingLevels)) {
                    foreach ($signingLevels as $levelObj) {
                        if (isset($levelObj['users']) && is_array($levelObj['users']) && isset($levelObj['level'])) {
                            foreach ($levelObj['users'] as $uId) {
                                $userLevelsMap[(string)$uId] = $levelObj['level'];
                            }
                        }
                    }
                }
            }

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(45, 10, 'Name', 1);
            $pdf->Cell(15, 10, 'Level', 1);
            $pdf->Cell(25, 10, 'Date', 1);
            $pdf->Cell(25, 10, 'Time', 1);
            $pdf->Cell(40, 10, 'IP Address', 1);
            $pdf->Cell(40, 10, 'Status', 1);
            $pdf->Ln();

            $pdf->SetFont('Arial', '', 12);
            foreach ($signatures as $sig) {
                $name = $sig->user ? $sig->user->first_name . ' ' . $sig->user->last_name : 'Unknown';
                $level = $userLevelsMap[(string)$sig->user_id] ?? '-';
                $date = $sig->created_at->format('Y-m-d');
                $time = $sig->created_at->format('H:i:s');
                $ip = $sig->ip_address ?? 'N/A';
                $status = $sig->is_marked_as_signed ? 'Marked as Signed' : 'Signed';

                $pdf->Cell(45, 10, substr($name, 0, 20), 1);
                $pdf->Cell(15, 10, $level, 1);
                $pdf->Cell(25, 10, $date, 1);
                $pdf->Cell(25, 10, $time, 1);
                $pdf->Cell(40, 10, $ip, 1);
                $pdf->Cell(40, 10, $status, 1);
                $pdf->Ln();
            }`;

if (historyRegex.test(content)) {
    content = content.replace(historyRegex, historyReplacement);
    console.log('History updated');
} else {
    console.log('History target not found!');
}

fs.writeFileSync(file, content);
console.log('Done');
