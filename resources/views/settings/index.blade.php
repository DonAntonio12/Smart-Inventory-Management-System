@extends('products.layout')

@section('page_title', 'System Settings')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">System Configuration</p>
            <h1>Settings &amp; Preferences</h1>
            <p>Manage workspace defaults, stock alert thresholds, receipt layout, and security credentials.</p>
        </div>
    </div>

    <nav class="product-tabs" aria-label="Settings sections">
        <a class="product-tab {{ $activeTab === 'general' ? 'active' : '' }}" href="{{ route('settings.index', ['tab' => 'general']) }}">General Store</a>
        <a class="product-tab {{ $activeTab === 'inventory' ? 'active' : '' }}" href="{{ route('settings.index', ['tab' => 'inventory']) }}">Inventory &amp; Thresholds</a>
        <a class="product-tab {{ $activeTab === 'receipt' ? 'active' : '' }}" href="{{ route('settings.index', ['tab' => 'receipt']) }}">Receipt Customization</a>
        <a class="product-tab {{ $activeTab === 'security' ? 'active' : '' }}" href="{{ route('settings.index', ['tab' => 'security']) }}">Account &amp; Security</a>
    </nav>

    @if ($activeTab === 'general')
        <section class="panel form-panel">
            <form method="POST" action="{{ route('settings.general.update') }}">
                @csrf
                <div class="form-grid">
                    <div class="field full">
                        <label for="store_name">Store / Business Name</label>
                        <input id="store_name" name="store_name" type="text" value="{{ old('store_name', $settings['store_name']) }}" required placeholder="e.g. Stockroom Store">
                        @error('store_name')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="store_email">Business Email</label>
                        <input id="store_email" name="store_email" type="email" value="{{ old('store_email', $settings['store_email']) }}" required placeholder="info@example.com">
                        @error('store_email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="store_phone">Phone Number</label>
                        <input id="store_phone" name="store_phone" type="text" value="{{ old('store_phone', $settings['store_phone']) }}" placeholder="+63 (02) 8123-4567">
                        @error('store_phone')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field full">
                        <label for="store_address">Business Address</label>
                        <input id="store_address" name="store_address" type="text" value="{{ old('store_address', $settings['store_address']) }}" placeholder="Street address, City, Country">
                        @error('store_address')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="currency_symbol">Currency Symbol</label>
                        <select id="currency_symbol" name="currency_symbol">
                            <option value="₱" @selected(old('currency_symbol', $settings['currency_symbol']) === '₱')>PHP (₱)</option>
                            <option value="$" @selected(old('currency_symbol', $settings['currency_symbol']) === '$')>USD ($)</option>
                            <option value="€" @selected(old('currency_symbol', $settings['currency_symbol']) === '€')>EUR (€)</option>
                            <option value="£" @selected(old('currency_symbol', $settings['currency_symbol']) === '£')>GBP (£)</option>
                            <option value="¥" @selected(old('currency_symbol', $settings['currency_symbol']) === '¥')>JPY / CNY (¥)</option>
                        </select>
                        @error('currency_symbol')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="timezone">System Timezone</label>
                        <select id="timezone" name="timezone">
                            <option value="Asia/Manila" @selected(old('timezone', $settings['timezone']) === 'Asia/Manila')>Asia/Manila (PHT)</option>
                            <option value="UTC" @selected(old('timezone', $settings['timezone']) === 'UTC')>UTC (Coordinated Universal Time)</option>
                            <option value="America/New_York" @selected(old('timezone', $settings['timezone']) === 'America/New_York')>America/New York (EST/EDT)</option>
                            <option value="Europe/London" @selected(old('timezone', $settings['timezone']) === 'Europe/London')>Europe/London (GMT/BST)</option>
                            <option value="Asia/Singapore" @selected(old('timezone', $settings['timezone']) === 'Asia/Singapore')>Asia/Singapore (SGT)</option>
                        </select>
                        @error('timezone')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button class="button button-primary" type="submit">Save Store Settings</button>
                </div>
            </form>
        </section>
    @elseif ($activeTab === 'inventory')
        <section class="panel form-panel">
            <form method="POST" action="{{ route('settings.inventory.update') }}">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="default_low_stock_threshold">Default Low-Stock Threshold (Units)</label>
                        <input id="default_low_stock_threshold" name="default_low_stock_threshold" type="number" min="0" value="{{ old('default_low_stock_threshold', $settings['default_low_stock_threshold']) }}" required>
                        <p class="field-hint">Default threshold applied when adding new catalog items.</p>
                        @error('default_low_stock_threshold')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="expiry_warning_days">Expiry Warning Window (Days)</label>
                        <input id="expiry_warning_days" name="expiry_warning_days" type="number" min="1" max="365" value="{{ old('expiry_warning_days', $settings['expiry_warning_days']) }}" required>
                        <p class="field-hint">Number of days prior to expiration date to trigger warning alerts.</p>
                        @error('expiry_warning_days')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button class="button button-primary" type="submit">Save Threshold Settings</button>
                </div>
            </form>
        </section>
    @elseif ($activeTab === 'receipt')
        <section class="panel form-panel">
            <form method="POST" action="{{ route('settings.receipt.update') }}">
                @csrf
                <div class="form-grid">
                    <div class="field full">
                        <label for="tax_number">Tax / TIN Registration Number</label>
                        <input id="tax_number" name="tax_number" type="text" value="{{ old('tax_number', $settings['tax_number']) }}" placeholder="e.g. TIN: 123-456-789-000">
                        @error('tax_number')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field full">
                        <label for="receipt_header">Receipt Header Message</label>
                        <textarea id="receipt_header" name="receipt_header" rows="2" class="text-area-field" placeholder="Welcome message printed at the top of receipts...">{{ old('receipt_header', $settings['receipt_header']) }}</textarea>
                        @error('receipt_header')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field full">
                        <label for="receipt_footer">Receipt Footer / Terms &amp; Conditions</label>
                        <textarea id="receipt_footer" name="receipt_footer" rows="3" class="text-area-field" placeholder="Terms, return policy, or thank you note printed at bottom...">{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
                        @error('receipt_footer')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button class="button button-primary" type="submit">Save Receipt Settings</button>
                </div>
            </form>
        </section>
    @elseif ($activeTab === 'security')
        <section class="panel form-panel">
            <form method="POST" action="{{ route('settings.security.update') }}">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="name">Your Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required placeholder="Your full name">
                        @error('name')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="email">Account Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required placeholder="user@example.com">
                        @error('email')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field full" style="margin-top: 10px; border-top: 1px dashed #e8eae3; padding-top: 14px;">
                        <span class="section-subheading">Change Password (Optional)</span>
                    </div>

                    <div class="field full">
                        <label for="current_password">Current Password</label>
                        <input id="current_password" name="current_password" type="password" placeholder="Required only if changing password">
                        @error('current_password')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="password">New Password</label>
                        <input id="password" name="password" type="password" minlength="8" placeholder="Minimum 8 characters">
                        @error('password')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Repeat new password">
                    </div>
                </div>

                <div class="form-actions">
                    <button class="button button-primary" type="submit">Update Account &amp; Security</button>
                </div>
            </form>
        </section>
    @endif
@endsection

@push('styles')
    <style>
        .field-hint { margin: 4px 0 0; color: #78847c; font-size: 9px; }
        .section-subheading { display: block; font: 700 13px 'Manrope', sans-serif; color: #29372e; }
        .text-area-field { display: block; width: 100%; padding: 10px; border: 1px solid #dfe4da; border-radius: 4px; color: var(--ink); background: #fff; font-size: 11px; font-family: inherit; resize: vertical; }
        .text-area-field:focus { border-color: #70926e; outline: none; box-shadow: 0 0 0 3px rgba(112,146,110,.12); }
    </style>
@endpush
