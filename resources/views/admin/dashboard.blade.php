@extends('admin.layouts.master_layout')
@section('title', 'Admin Dashboard')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="mb-3">{{ __('common.welcome') }} - Language Switcher Test</h3>
                    <p class="text-muted mb-4">{{ __('common.dashboard') }} - Current Locale: <strong>{{ app()->getLocale() }}</strong></p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <h5>Common Translations:</h5>
                            <ul class="list-unstyled">
                                <li><strong>{{ __('common.edit') }}</strong></li>
                                <li><strong>{{ __('common.delete') }}</strong></li>
                                <li><strong>{{ __('common.create') }}</strong></li>
                                <li><strong>{{ __('common.save') }}</strong></li>
                                <li><strong>{{ __('common.cancel') }}</strong></li>
                                <li><strong>{{ __('common.status') }}</strong>: {{ __('common.active') }} / {{ __('common.inactive') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Admin Translations:</h5>
                            <ul class="list-unstyled">
                                <li><strong>{{ __('admin.menu.users') }}</strong></li>
                                <li><strong>{{ __('admin.menu.roles') }}</strong></li>
                                <li><strong>{{ __('admin.menu.properties') }}</strong></li>
                                <li><strong>{{ __('admin.menu.projects') }}</strong></li>
                                <li><strong>{{ __('admin.properties.listing_type') }}</strong></li>
                                <li><strong>{{ __('admin.properties.bedrooms') }}</strong></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>All Available Language Switcher Themes:</h5>
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <div>
                                <p class="mb-2 small text-muted">Admin Theme (Current in Header):</p>
                                <x-language-switcher theme="admin" />
                            </div>
                            <div>
                                <p class="mb-2 small text-muted">Compact Theme:</p>
                                <x-language-switcher theme="compact" />
                            </div>
                            <div>
                                <p class="mb-2 small text-muted">Tabs Theme:</p>
                                <x-language-switcher theme="tabs" />
                            </div>
                            <div>
                                <p class="mb-2 small text-muted">Default Theme:</p>
                                <x-language-switcher theme="default" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

