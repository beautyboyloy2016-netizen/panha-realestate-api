@extends('admin.layouts.master_layout')

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">System Settings</h1>
            <p class="text-muted mb-0">Manage your application configuration and preferences</p>
        </div>
        <form method="POST" action="{{ route('admin.settings.clear-cache') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt me-1"></i> Clear Cache
            </button>
        </form>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Settings Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    @foreach($settings as $group => $items)
                        @php
                            $icon = match($group) {
                                'general' => 'cog',
                                'localization' => 'globe',
                                'languages' => 'language',
                                'property' => 'home',
                                'seo' => 'search',
                                'media' => 'image',
                                'map' => 'map-marker-alt',
                                'mail' => 'envelope',
                                'security' => 'shield-alt',
                                'authentication' => 'key',
                                default => 'cog'
                            };
                        @endphp
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="{{ $group }}-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#{{ $group }}"
                                    type="button"
                                    role="tab">
                                <i class="fas fa-{{ $icon }} me-1"></i>
                                {{ ucfirst($group) }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="settingsTabsContent">
                    @foreach($settings as $group => $items)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                             id="{{ $group }}"
                             role="tabpanel">

                            @if($group === 'languages')
                                <!-- Languages Special Layout -->
                                <div class="mb-3">
                                    <p class="text-muted">Enable or disable languages for your website. The default language cannot be disabled.</p>
                                </div>
                                <div class="row">
                                    @php
                                        $languages = [
                                            'en' => ['name' => 'English', 'flag' => '🇺🇸', 'native' => 'English'],
                                            'km' => ['name' => 'Khmer', 'flag' => '🇰🇭', 'native' => 'ភាសាខ្មែរ'],
                                            'zh' => ['name' => 'Chinese', 'flag' => '🇨🇳', 'native' => '中文'],
                                            'fr' => ['name' => 'French', 'flag' => '🇫🇷', 'native' => 'Français'],
                                        ];
                                        $defaultLang = $settings['localization']->firstWhere('key', 'language.default')?->value ?? 'en';
                                    @endphp
                                    @foreach($languages as $locale => $langInfo)
                                        @php
                                            $enabledSetting = $items->firstWhere('key', "language.{$locale}.enabled");
                                            $nameSetting = $items->firstWhere('key', "language.{$locale}.name");
                                            $isEnabled = $enabledSetting?->value ?? true;
                                            $isDefault = $locale === $defaultLang;
                                        @endphp
                                        <div class="col-md-6 col-lg-3 mb-4">
                                            <div class="card h-100 border {{ $isEnabled ? 'border-success' : 'border-secondary' }} language-card" data-locale="{{ $locale }}">
                                                <div class="card-body text-center p-4">
                                                    <div class="mb-3" style="font-size: 3rem;">
                                                        {{ $langInfo['flag'] }}
                                                    </div>
                                                    <h5 class="card-title mb-1">{{ $langInfo['name'] }}</h5>
                                                    <p class="text-muted small mb-3">{{ $langInfo['native'] }}</p>

                                                    @if($isDefault)
                                                        <span class="badge bg-primary mb-3">
                                                            <i class="fas fa-star me-1"></i> Default
                                                        </span>
                                                    @endif

                                                    <div class="form-check form-switch d-flex justify-content-center">
                                                        @if($enabledSetting)
                                                            @if($isDefault)
                                                                {{-- Default language always enabled - use hidden input with value 1 --}}
                                                                <input type="hidden" name="settings[{{ $enabledSetting->key }}]" value="1">
                                                                <input type="checkbox"
                                                                       class="form-check-input language-toggle"
                                                                       id="setting_{{ $enabledSetting->id }}"
                                                                       data-locale="{{ $locale }}"
                                                                       value="1"
                                                                       checked
                                                                       disabled
                                                                       style="transform: scale(1.3);">
                                                            @else
                                                                <input type="hidden" name="settings[{{ $enabledSetting->key }}]" value="0">
                                                                <input type="checkbox"
                                                                       class="form-check-input language-toggle"
                                                                       id="setting_{{ $enabledSetting->id }}"
                                                                       name="settings[{{ $enabledSetting->key }}]"
                                                                       data-locale="{{ $locale }}"
                                                                       value="1"
                                                                       {{ $isEnabled ? 'checked' : '' }}
                                                                       style="transform: scale(1.3);">
                                                            @endif
                                                        @endif
                                                    </div>
                                                    <small class="text-muted mt-2 d-block status-text">
                                                        {{ $isEnabled ? 'Enabled' : 'Disabled' }}
                                                        @if($isDefault)
                                                            <br><span class="text-primary">(Default language cannot be disabled)</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Hidden inputs for name settings -->
                                @foreach($items as $setting)
                                    @if(str_contains($setting->key, '.name'))
                                        <input type="hidden" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}">
                                    @endif
                                @endforeach
                            @else
                                <!-- Default Layout for other groups -->
                                <div class="row">
                                @foreach($items as $setting)
                                    <div class="col-md-6 mb-4">
                                        <div class="setting-item">
                                            <label class="form-label fw-semibold">
                                                {{ ucwords(str_replace(['_', '.'], ' ', str_replace($group.'.', '', $setting->key))) }}
                                                @if($setting->description)
                                                    <i class="fas fa-info-circle text-muted ms-1"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ $setting->description }}"></i>
                                                @endif
                                            </label>

                                            @if($setting->type === 'boolean')
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                                    <input type="checkbox"
                                                           class="form-check-input"
                                                           id="setting_{{ $setting->id }}"
                                                           name="settings[{{ $setting->key }}]"
                                                           value="1"
                                                           {{ $setting->value ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="setting_{{ $setting->id }}">
                                                        {{ $setting->value ? 'Enabled' : 'Disabled' }}
                                                    </label>
                                                </div>

                                            @elseif($setting->type === 'json')
                                                <textarea name="settings[{{ $setting->key }}]"
                                                          class="form-control font-monospace"
                                                          rows="3"
                                                          placeholder="Enter JSON data">{{ json_encode($setting->value, JSON_PRETTY_PRINT) }}</textarea>

                                            @elseif($setting->type === 'integer' || $setting->type === 'float')
                                                <input type="number"
                                                       class="form-control"
                                                       name="settings[{{ $setting->key }}]"
                                                       value="{{ $setting->value }}"
                                                       step="{{ $setting->type === 'float' ? '0.01' : '1' }}">

                                            @elseif($setting->type === 'file')
                                                <div class="input-group">
                                                    <input type="text"
                                                           class="form-control"
                                                           name="settings[{{ $setting->key }}]"
                                                           value="{{ $setting->value }}"
                                                           placeholder="/storage/path/to/file">
                                                    <button class="btn btn-outline-secondary" type="button">
                                                        <i class="fas fa-upload"></i> Browse
                                                    </button>
                                                </div>
                                                @if($setting->value && file_exists(public_path($setting->value)))
                                                    <div class="mt-2">
                                                        <img src="{{ asset($setting->value) }}"
                                                             alt="Preview"
                                                             class="img-thumbnail"
                                                             style="max-height: 100px;">
                                                    </div>
                                                @endif

                                            @else
                                                <input type="text"
                                                       class="form-control"
                                                       name="settings[{{ $setting->key }}]"
                                                       value="{{ $setting->value }}"
                                                       placeholder="Enter value">
                                            @endif

                                            @if($setting->description)
                                                <small class="text-muted d-block mt-1">
                                                    {{ $setting->description }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Save Button -->
                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Toggle switch label update with live save for authentication settings
        document.querySelectorAll('.form-check-input[type="checkbox"]').forEach(function(checkbox) {
            const settingId = checkbox.id;
            const settingKey = checkbox.name.replace('settings[', '').replace(']', '');

            checkbox.addEventListener('change', function() {
                const label = this.nextElementSibling;
                if (label) {
                    label.textContent = this.checked ? 'Enabled' : 'Disabled';
                }

                // Auto-save authentication and language settings
                if (settingKey.startsWith('auth.') || settingKey.startsWith('language.')) {
                    const value = this.checked ? 1 : 0;
                    const isLanguageToggle = this.classList.contains('language-toggle');

                    // Show saving indicator
                    let originalBg, card;
                    if (isLanguageToggle) {
                        card = this.closest('.language-card');
                        originalBg = card.style.borderColor;
                        card.style.borderColor = '#ffc107';
                    } else {
                        originalBg = this.parentElement.style.backgroundColor;
                        this.parentElement.style.backgroundColor = '#fff3cd';
                    }

                    fetch('{{ route("admin.settings.toggle") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            key: settingKey,
                            value: value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success
                            if (isLanguageToggle) {
                                card.style.borderColor = this.checked ? '#198754' : '#6c757d';
                                card.classList.toggle('border-success', this.checked);
                                card.classList.toggle('border-secondary', !this.checked);
                                const statusText = card.querySelector('.status-text');
                                if (statusText) {
                                    statusText.innerHTML = this.checked ? 'Enabled' : 'Disabled';
                                }
                            } else {
                                this.parentElement.style.backgroundColor = '#d4edda';
                                setTimeout(() => {
                                    this.parentElement.style.backgroundColor = originalBg;
                                }, 1000);
                            }

                            // Show toast notification
                            showToast('success', data.message || 'Setting updated successfully!');
                        } else {
                            // Revert on error
                            this.checked = !this.checked;
                            if (label) label.textContent = this.checked ? 'Enabled' : 'Disabled';
                            showToast('error', data.message || 'Failed to update setting');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Revert on error
                        this.checked = !this.checked;
                        if (label) label.textContent = this.checked ? 'Enabled' : 'Disabled';
                        showToast('error', 'Failed to update setting');
                    });
                }
            });
        });

        // Toast notification helper
        function showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 end-0 m-3`;
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
    @endpush
@endsection
