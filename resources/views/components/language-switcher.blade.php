@props(['theme' => 'default'])

@php
    $currentLocale = app()->getLocale();
    // Get only enabled languages from settings instead of config
    $availableLocales = get_enabled_languages();

    $localeNames = [
        'en' => ['name' => 'English', 'flag' => '🇺🇸', 'short' => 'EN'],
        'km' => ['name' => 'ខ្មែរ', 'flag' => '🇰🇭', 'short' => 'KM'],
        'zh' => ['name' => '中文', 'flag' => '🇨🇳', 'short' => 'ZH'],
        'fr' => ['name' => 'Français', 'flag' => '🇫🇷', 'short' => 'FR'],
    ];

    $currentLocaleName = $localeNames[$currentLocale]['name'] ?? 'English';
    $currentLocaleFlag = $localeNames[$currentLocale]['flag'] ?? '🇺🇸';
    $currentLocaleShort = $localeNames[$currentLocale]['short'] ?? 'EN';
@endphp

@if($theme === 'admin')
<!-- Admin Theme Dropdown -->
<div class="dropdown">
    <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2"
            type="button"
            id="languageDropdown"
            data-bs-toggle="dropdown"
            aria-expanded="false">
        <span class="fs-5">{{ $currentLocaleFlag }}</span>
        <span class="d-none d-md-inline">{{ $currentLocaleShort }}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
        @foreach($availableLocales as $locale)
            @php
                $localeName = $localeNames[$locale]['name'] ?? $locale;
                $localeFlag = $localeNames[$locale]['flag'] ?? '';
            @endphp
            <li>
                <a class="dropdown-item {{ $locale === $currentLocale ? 'active' : '' }}"
                   href="{{ route('language.switch', $locale) }}">
                    <span class="me-2">{{ $localeFlag }}</span>
                    {{ $localeName }}
                    @if($locale === $currentLocale)
                        <i class="fas fa-check ms-2 text-success"></i>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</div>

@elseif($theme === 'compact')
<!-- Compact Theme (Icons Only) -->
<div class="btn-group" role="group" aria-label="Language Switcher">
    @foreach($availableLocales as $locale)
        @php
            $localeFlag = $localeNames[$locale]['flag'] ?? '';
        @endphp
        <a href="{{ route('language.switch', $locale) }}"
           class="btn btn-sm {{ $locale === $currentLocale ? 'btn-primary' : 'btn-outline-secondary' }}"
           title="{{ $localeNames[$locale]['name'] ?? $locale }}">
            {{ $localeFlag }}
        </a>
    @endforeach
</div>

@elseif($theme === 'tabs')
<!-- Tab Theme -->
<ul class="nav nav-pills" role="tablist">
    @foreach($availableLocales as $locale)
        @php
            $localeName = $localeNames[$locale]['name'] ?? $locale;
            $localeShort = $localeNames[$locale]['short'] ?? strtoupper($locale);
        @endphp
        <li class="nav-item" role="presentation">
            <a href="{{ route('language.switch', $locale) }}"
               class="nav-link {{ $locale === $currentLocale ? 'active' : '' }}">
                {{ $localeShort }}
            </a>
        </li>
    @endforeach
</ul>

@else
<!-- Default Theme Dropdown -->
<div class="dropdown">
    <button class="btn btn-light dropdown-toggle"
            type="button"
            id="languageDropdown"
            data-bs-toggle="dropdown"
            aria-expanded="false">
        <i class="fas fa-globe me-2"></i>
        {{ $currentLocaleName }}
    </button>
    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
        @foreach($availableLocales as $locale)
            @php
                $localeName = $localeNames[$locale]['name'] ?? $locale;
                $localeFlag = $localeNames[$locale]['flag'] ?? '';
            @endphp
            <li>
                <a class="dropdown-item {{ $locale === $currentLocale ? 'active' : '' }}"
                   href="{{ route('language.switch', $locale) }}">
                    <span class="me-2">{{ $localeFlag }}</span>
                    {{ $localeName }}
                    @if($locale === $currentLocale)
                        <i class="fas fa-check ms-2 text-success"></i>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</div>
@endif

<style>
.dropdown-item.active {
    background-color: #0d6efd;
    color: white;
}
</style>
