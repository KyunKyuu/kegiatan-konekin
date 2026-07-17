<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Person;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1. Ensure user is admin
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect()->route('calendar')->withErrors(['error' => 'Akses ditolak. Halaman ini hanya untuk Administrator.']);
        }

        // 1.5. Leaderboard filter parameters
        $lbMonth = $request->input('lb_month');
        $lbYear = $request->input('lb_year');

        // Subquery select statements with dynamic date filtering
        $picCountSql = 'SELECT COUNT(*) FROM activity_pic as apic JOIN activities as a ON apic.activity_id = a.id WHERE apic.person_id = p.id';
        $partCountSql = 'SELECT COUNT(*) FROM activity_participant as ap JOIN activities as a ON ap.activity_id = a.id WHERE ap.person_id = p.id';

        if ($lbMonth && $lbYear) {
            $formattedMonth = str_pad($lbMonth, 2, '0', STR_PAD_LEFT);
            $picCountSql .= " AND strftime('%m', a.activity_date) = '{$formattedMonth}' AND strftime('%Y', a.activity_date) = '{$lbYear}'";
            $partCountSql .= " AND strftime('%m', a.activity_date) = '{$formattedMonth}' AND strftime('%Y', a.activity_date) = '{$lbYear}'";
        } elseif ($lbYear) {
            $picCountSql .= " AND strftime('%Y', a.activity_date) = '{$lbYear}'";
            $partCountSql .= " AND strftime('%Y', a.activity_date) = '{$lbYear}'";
        }

        // 2. Metrics
        $totalActivities = Activity::count();
        $totalPeople = Person::count();
        $totalUsers = User::count();

        // 3. Who has the most activities (Combined PIC and Participant)
        // Using SQL Subqueries for high performance
        $topPeople = DB::table('people as p')
            ->select('p.id', 'p.name')
            ->selectRaw("({$picCountSql}) as pic_count")
            ->selectRaw("({$partCountSql}) as participant_count")
            ->selectRaw("({$picCountSql}) + ({$partCountSql}) as total_activities")
            ->orderBy('total_activities', 'desc')
            ->limit(10)
            ->get();

        $mostActivePerson = $topPeople->first();

        // 4. Activities by Category
        $categoriesShare = Activity::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();

        // 5. Monthly Trend of Activities (SQLite-compatible strftime)
        $monthlyTrendRaw = Activity::select(
                DB::raw("strftime('%Y-%m', activity_date) as month_key"),
                DB::raw('count(*) as count')
            )
            ->groupBy('month_key')
            ->orderBy('month_key', 'asc')
            ->get();

        // Map trend keys to readable format e.g. "Jan 2026"
        $monthlyTrend = $monthlyTrendRaw->map(function ($item) {
            if (empty($item->month_key)) {
                return ['label' => 'Unknown', 'count' => $item->count];
            }
            try {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $item->month_key);
                return [
                    'label' => $date->translatedFormat('M Y'),
                    'count' => $item->count
                ];
            } catch (\Exception $e) {
                return ['label' => $item->month_key, 'count' => $item->count];
            }
        });

        // 6. Recent Activities for log/management table
        $recentActivities = Activity::with(['pics', 'participants', 'creator'])
            ->orderBy('activity_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // 7. All People Table (with detail counts)
        $allPeople = DB::table('people as p')
            ->select('p.id', 'p.name', 'p.created_at')
            ->selectRaw("({$picCountSql}) as pic_count")
            ->selectRaw("({$partCountSql}) as participant_count")
            ->selectRaw("({$picCountSql}) + ({$partCountSql}) as total_count")
            ->orderBy('total_count', 'desc')
            ->paginate(5, ['*'], 'members_page');

        // 8. Categories List
        $categories = Category::all();
        $categoryColors = $categories->pluck('color', 'name')->toArray();

        // 9. All Users List (for CRUD)
        $users = User::orderBy('name', 'asc')->paginate(10, ['*'], 'users_page');

        return view('admin.dashboard', compact(
            'totalActivities',
            'totalPeople',
            'totalUsers',
            'topPeople',
            'mostActivePerson',
            'categoriesShare',
            'monthlyTrend',
            'recentActivities',
            'allPeople',
            'categories',
            'categoryColors',
            'users',
            'lbMonth',
            'lbYear'
        ));
    }

    public function addCategory(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:50|unique:categories,name',
            'color' => 'required|string|in:purple,blue,green,orange,red,cyan,grey',
        ], [
            'name.unique' => 'Kategori dengan nama tersebut sudah terdaftar!',
        ]);

        Category::create([
            'name' => trim($request->name),
            'color' => $request->color,
        ]);

        return redirect()->back()->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function deleteCategory($id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $category = Category::findOrFail($id);
        
        // Prevent deleting category if it is in use by activities
        $inUse = Activity::where('category', $category->name)->exists();
        if ($inUse) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus! Kategori "' . $category->name . '" sedang digunakan oleh beberapa kegiatan.']);
        }

        $category->delete();

        return redirect()->back()->with('success', 'Kategori berhasil dihapus!');
    }

    public function exportExcel(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $query = Activity::with(['pics', 'participants', 'creator']);

        // Apply custom date range or monthly filters
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date && $request->end_date) {
            $query->whereBetween('activity_date', [$request->start_date, $request->end_date]);
            $filename = "kegiatan-periode-{$request->start_date}-sd-{$request->end_date}.csv";
        } elseif ($request->has('month') && $request->has('year') && $request->month && $request->year) {
            $query->whereYear('activity_date', $request->year)
                  ->whereMonth('activity_date', $request->month);
            $monthName = \Carbon\Carbon::create($request->year, $request->month, 1)->translatedFormat('F');
            $filename = "kegiatan-bulan-{$monthName}-{$request->year}.csv";
        } else {
            $filename = "semua-kegiatan.csv";
        }

        $activities = $query->orderBy('activity_date', 'asc')->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['No', 'Tanggal', 'Jam Mulai', 'Jam Selesai', 'Kategori', 'Isi Kegiatan', 'PIC', 'Peserta Terlibat', 'Pembuat'];

        $callback = function() use($activities, $columns) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Microsoft Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns, ';');

            foreach ($activities as $idx => $act) {
                $pics = $act->pics->pluck('name')->implode(', ');
                $participants = $act->participants->pluck('name')->implode(', ');
                
                fputcsv($file, [
                    $idx + 1,
                    $act->activity_date->format('d/m/Y'),
                    $act->start_time ? substr($act->start_time, 0, 5) : 'Sepanjang Hari',
                    $act->end_time ? substr($act->end_time, 0, 5) : '-',
                    $act->category,
                    $act->description,
                    $pics ?: '-',
                    $participants ?: '-',
                    $act->creator->name
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function addUser(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'is_admin' => 'nullable|boolean',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'is_admin' => (bool)$request->is_admin,
        ]);

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUser(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'is_admin' => 'nullable|boolean',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'is_admin' => (bool)$request->is_admin,
        ];

        if ($request->filled('password')) {
            $userData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->back()->with('success', 'User berhasil diubah.');
    }

    public function deleteUser($id)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        if (Auth::id() == $id) {
            return redirect()->back()->withErrors(['error' => 'Anda tidak bisa menghapus akun Anda sendiri!']);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }
}
