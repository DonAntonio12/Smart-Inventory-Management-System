<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    private array $defaultSettings = [
        'store_name' => 'Stockroom Store',
        'store_email' => 'info@stockroom.local',
        'store_phone' => '+63 (02) 8123-4567',
        'store_address' => 'Metro Manila, Philippines',
        'currency_symbol' => '₱',
        'timezone' => 'Asia/Manila',
        'default_low_stock_threshold' => '10',
        'expiry_warning_days' => '30',
        'receipt_header' => 'Thank you for shopping with us!',
        'receipt_footer' => 'Items sold are non-refundable. Present receipt for exchange within 7 days.',
        'tax_number' => 'TIN: 123-456-789-000',
    ];

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'general');
        $tab = in_array($tab, ['general', 'inventory', 'receipt', 'security'], true) ? $tab : 'general';

        $settings = Setting::getMany($this->defaultSettings);

        return view('settings.index', [
            'activeTab' => $tab,
            'settings' => $settings,
            'user' => auth()->user(),
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_email' => ['required', 'email', 'max:255'],
            'store_phone' => ['nullable', 'string', 'max:100'],
            'store_address' => ['nullable', 'string', 'max:500'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:100'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index', ['tab' => 'general'])->with('status', 'General store settings updated.');
    }

    public function updateInventory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_low_stock_threshold' => ['required', 'integer', 'min:0', 'max:100000'],
            'expiry_warning_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index', ['tab' => 'inventory'])->with('status', 'Inventory & alert threshold settings updated.');
    }

    public function updateReceipt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receipt_header' => ['nullable', 'string', 'max:500'],
            'receipt_footer' => ['nullable', 'string', 'max:1000'],
            'tax_number' => ['nullable', 'string', 'max:100'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index', ['tab' => 'receipt'])->with('status', 'Receipt customization settings updated.');
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['nullable', 'string', 'required_with:password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));

        if (filled($validated['password'] ?? null)) {
            if (! Hash::check($validated['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'The provided current password does not match our records.',
                ]);
            }
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('settings.index', ['tab' => 'security'])->with('status', 'Profile and account security settings updated.');
    }
}
