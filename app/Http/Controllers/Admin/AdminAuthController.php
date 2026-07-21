<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Exception;
use App\Models\Customer;
use App\Models\VehicleInventory;
use App\Models\VehiclePurchaseOrder;
use App\Models\SparePartStock;
use App\Models\VehicleMaster;

class AdminAuthController extends Controller
{
    
    public function index()
    {
        try{
            if(Auth::user()) {
                $user = Auth::user();
                if($user->role == "admin") {
                    return redirect()->route('admin.dashboard');
                }else{
                    return back()->with("error","Opps! You do not have access this");
                }
            }else{
                return redirect()->route('admin.login');
            }

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    

    public function login()
    {
        return view("admin.auth.login");
    }

    public function registration()
    {
        return redirect()->route('admin.login')->with('error', 'Registration is disabled.');
    }

    public function postLogin(Request $request)
    {
        try{
            $request->validate([
                "email" => "required",
                "password" => "required",
            ]);
            $user = User::where('role','admin')->where('email',$request->email)->first();
            if($user && Auth::attempt($request->only("email", "password"))){
                return redirect()->route("admin.dashboard")->with("success", "Welcome to your dashboard.");
            }
            return back()->with("error","Invalid credentials");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function postRegistration(Request $request)
    {
        return redirect()->route('admin.login')->with('error', 'Registration is disabled.');
    }

    public function create(array $data)
    {
        return User::create([
            "first_name" => $data["first_name"] ?? $data["name"] ?? '',
            "last_name" => $data["last_name"] ?? '',
            "full_name" => ($data["first_name"] ?? $data["name"] ?? '') . ' ' . ($data["last_name"] ?? ''),
            "email" => $data["email"],
            "phone" => $data["phone"] ?? '',
            "password" => Hash::make($data["password"]),
            "role" => 'admin',
            "status" => 'active',
            "country" => $data["country"] ?? 'India',
        ]);
    }

    public function showForgetPasswordForm()
    {
        return view("admin.auth.forgot-password");
    }

    public function submitForgetPasswordForm(Request $request)
    {
        try{
            $request->validate([
                "email" => "required|email|exists:users",
            ]);

            $token = Str::random(64);

            DB::table("password_reset_tokens")->insert([
                "email" => $request->email,
                "token" => $token,
                "created_at" => Carbon::now(),
            ]);

            $new_link_token = url("admin/reset-password/" . $token);
            Mail::send("admin.email.forgot-password",["token" => $new_link_token, "email" => $request->email],
                function ($message) use ($request) {
                    $message->to($request->email);
                    $message->subject("Reset Password");
                }
            );
            return redirect()->route("admin.login")->with("success","We have e-mailed your password reset link!");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    
    }

    public function showResetPasswordForm($token)
    {
        try{    
            $user = DB::table("password_reset_tokens")->where("token", $token)->first();
            if (!$user) {
                return redirect()->route("admin.login")->with("error", "Invalid or expired reset link.");
            }
            $email = $user->email;
            return view("admin.auth.reset-password", ["token" => $token,"email" => $email,]);
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function submitResetPasswordForm(Request $request)
    {
        try{
            $request->validate([
                "email" => "required|email|exists:users",
                "password" => "required|string|min:6|confirmed",
                "password_confirmation" => "required",
            ]);

            $updatePassword = DB::table("password_reset_tokens")->where(["email" => $request->email,"token" => $request->token])->first();

            if (!$updatePassword) {
                return back()->withInput()->with("error", "Invalid token!");
            }

            $tokenCreatedAt = \Carbon\Carbon::parse($updatePassword->created_at);
            if ($tokenCreatedAt->diffInMinutes(now()) > 60) {
                DB::table("password_reset_tokens")->where(["email" => $request->email])->delete();
                return back()->withInput()->with("error", "Reset link has expired. Please request a new one.");
            }

            $user = User::where("email", $request->email)->update(["password" => Hash::make($request->password)]);

            DB::table("password_reset_tokens")->where(["email" => $request->email])->delete();

            return redirect()->route("admin.login")->with("success","Your password has been changed successfully!");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function changePassword()
    {
        return view("admin.auth.change-password");
    }

    public function updatePassword(Request $request)
    {
        try{
            $request->validate([
                "old_password" => "required",
                "new_password" => "required|confirmed",
            ]);
            #Match The Old Password
            if (!Hash::check($request->old_password, auth()->user()->password)) {
                return back()->with("error", "Old Password Doesn't match!");
            }
            #Update the new Password
            User::whereId(auth()->user()->id)->update([
                "password" => Hash::make($request->new_password),
            ]);
            return back()->with("success", "Password changed successfully!");
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    

    public function logout(Request $request)
    {
        try{
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route("admin.login")->withSuccess('Logout Successful!');
        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function adminProfile()
    {
        try{
            $user = Auth::user();
            return view("admin.auth.profile", compact("user"));

        }
        catch(Exception $e){
            return back()->with("error",$e->getMessage());
        }
    }

    public function updateAdminProfile(Request $request)
    {
        try
        {
            $user = Auth::user();
            $data = $request->all();
            $validator = Validator::make($data,[
                "first_name" => "required",
                "last_name" => "required",
                "phone" => "required|digits:10|unique:users,phone," .$user->id,
                "email" => "required|email|unique:users,email," . $user->id,
                "avatar" => "sometimes|image|mimes:jpeg,jpg,png|max:5000"
            ]);
            
            if($validator->fails()) {
                return redirect()->back()->withInput($request->all())->withErrors($validator->errors());
            }
            
            if($request->file("avatar")) {
                $file = $request->file("avatar");
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $folder = "uploads/user/";
                $path = public_path($folder);
                if (!File::exists($path)) {
                    File::makeDirectory($path, $mode = 0755, true, true);
                }
                $file->move($path, $filename);
                $user->avatar = $folder . $filename;
            }
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->full_name = $request->first_name . " " . $request->last_name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->save();
            return redirect()->back()->with("success", "Profile update successfully!");
        }
        catch (Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }
    }

    public function adminDashboard()
    {
        $totalCustomers = Customer::count();

        $vehicleInventoryCount = VehicleInventory::where('status', 'available')->count();
        $pendingVehiclePOs = VehiclePurchaseOrder::whereIn('status', ['pending', 'partial'])->count();
        
        // Parts low stock count
        $lowStockCount = SparePartStock::where('spare_part_stocks.is_active', true)
            ->join('spare_parts', 'spare_parts.id', '=', 'spare_part_stocks.spare_part_id')
            ->where(function($q) {
                $q->whereColumn('spare_part_stocks.quantity', '<=', 'spare_parts.min_stock')
                  ->where('spare_parts.min_stock', '>', 0);
            })->count();

        // Vehicle low stock variants count
        $vehicleMasters = VehicleMaster::where('is_active', true)->where('min_stock', '>', 0)->get();
        $variantCounts = VehicleInventory::where('status', 'available')
            ->select('vehicle_description', DB::raw('COUNT(*) as total'))
            ->groupBy('vehicle_description')
            ->pluck('total', 'vehicle_description')
            ->toArray();

        $lowStockVehicleCount = 0;
        foreach ($vehicleMasters as $vm) {
            $name = $vm->variant_name . ($vm->color_name ? ' (' . $vm->color_name . ')' : '');
            $available = $variantCounts[$name] ?? ($variantCounts[$vm->variant_name] ?? 0);
            if ($available <= $vm->min_stock) {
                $lowStockVehicleCount++;
            }
        }

        // Calculate "To Collect" (outstanding balance from sales)
        $toCollectPart = \App\Models\PartSalesInvoice::where('is_active', true)->sum('balance');
        $toCollectVehicle = \App\Models\VehicleSalesInvoice::where('is_active', true)->sum('balance');
        $toCollect = $toCollectPart + $toCollectVehicle;

        // Calculate "To Pay" (outstanding balance from purchases)
        $toPayPart = \App\Models\PurchaseOrder::where('is_active', true)->sum('balance');
        $toPayVehicle = \App\Models\VehiclePurchaseOrder::where('is_active', true)->sum('balance');
        $toPay = $toPayPart + $toPayVehicle;

        // Calculate "Stock Value" (purchase value of available stock)
        $stockValueParts = \App\Models\SparePartStock::where('is_active', true)->sum(DB::raw('quantity * purchase_price'));
        $stockValueVehicles = VehicleInventory::where('status', 'available')->sum(DB::raw('quantity * purchase_price'));
        $stockValue = $stockValueParts + $stockValueVehicles;

        // Calculate "Stock Count" (total quantity of items in stock)
        $stockCountParts = \App\Models\SparePartStock::where('is_active', true)->sum('quantity');
        $stockCountVehicles = VehicleInventory::where('status', 'available')->count();

        // Calculate "This week's sale"
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $partSalesThisWeek = \App\Models\PartSalesInvoice::where('is_active', true)
            ->whereBetween('invoice_date', [$startOfWeek, $endOfWeek])
            ->sum('total_amount');
        $vehicleSalesThisWeek = \App\Models\VehicleSalesInvoice::where('is_active', true)
            ->whereBetween('invoice_date', [$startOfWeek, $endOfWeek])
            ->sum('grand_total');
        $thisWeeksSale = $partSalesThisWeek + $vehicleSalesThisWeek;

        // Latest Transactions
        $partInvoices = \App\Models\PartSalesInvoice::where('is_active', true)
            ->latest('invoice_date')
            ->latest('id')
            ->take(10)
            ->get()
            ->map(function ($inv) {
                return [
                    'date' => $inv->invoice_date ? $inv->invoice_date->format('d M Y') : '-',
                    'raw_date' => $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : '',
                    'id' => $inv->id,
                    'type' => 'Sales Invoices',
                    'txn_no' => $inv->invoice_number,
                    'party_name' => $inv->customer_name ?: ($inv->customer ? $inv->customer->name : 'N/A'),
                    'amount' => $inv->total_amount,
                    'url' => route('admin.part-sales-invoices.show', $inv->id),
                ];
            });

        $vehicleInvoices = \App\Models\VehicleSalesInvoice::where('is_active', true)
            ->latest('invoice_date')
            ->latest('id')
            ->take(10)
            ->get()
            ->map(function ($inv) {
                return [
                    'date' => $inv->invoice_date ? $inv->invoice_date->format('d M Y') : '-',
                    'raw_date' => $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : '',
                    'id' => $inv->id,
                    'type' => 'Sales Invoices',
                    'txn_no' => $inv->invoice_number,
                    'party_name' => $inv->customer_name ?: ($inv->customer ? $inv->customer->name : 'N/A'),
                    'amount' => $inv->grand_total,
                    'url' => route('admin.vehicle-sales-invoices.show', $inv->id),
                ];
            });

        $latestTransactions = $partInvoices->concat($vehicleInvoices)
            ->sortByDesc(function ($item) {
                return $item['raw_date'] . '_' . sprintf('%08d', $item['id']);
            })
            ->take(6)
            ->values();

        // Monthly Sales Report Graph Data (Last 6 Months)
        $months = [];
        $partSalesData = [];
        $vehicleSalesData = [];
        $totalSalesData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $pSales = \App\Models\PartSalesInvoice::where('is_active', true)
                ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                ->sum('total_amount');

            $vSales = \App\Models\VehicleSalesInvoice::where('is_active', true)
                ->whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                ->sum('grand_total');

            $months[] = $monthName;
            $partSalesData[] = (float) $pSales;
            $vehicleSalesData[] = (float) $vSales;
            $totalSalesData[] = (float) ($pSales + $vSales);
        }

        $salesChartData = [
            'categories' => $months,
            'vehicle_sales' => $vehicleSalesData,
            'part_sales' => $partSalesData,
            'total_sales' => $totalSalesData,
        ];

        return view("admin.dashboard.index", compact(
            'totalCustomers',
            'vehicleInventoryCount', 'pendingVehiclePOs', 'lowStockCount', 'lowStockVehicleCount',
            'toCollect', 'toPay', 'stockValue', 'stockCountParts', 'stockCountVehicles', 'thisWeeksSale',
            'latestTransactions', 'salesChartData'
        ));
    }


}
