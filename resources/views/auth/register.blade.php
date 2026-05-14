<x-guest-layout>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
        }

        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-section {
            text-align: center;
            padding: 2.5rem 4rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .logo-section h1 {
            font-size: 2.25rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
        }

        .logo-section p {
            margin: 0;
            opacity: 0.95;
            font-size: 0.95rem;
        }

        .form-section {
            padding: 2.5rem 4rem 3rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-label .optional {
            font-weight: 400;
            color: #9ca3af;
            font-size: 0.85rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #fafafa;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-control.error {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .error-message {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .password-strength-bar.weak {
            width: 33.33%;
            background: #ef4444;
        }

        .password-strength-bar.medium {
            width: 66.66%;
            background: #f59e0b;
        }

        .password-strength-bar.strong {
            width: 100%;
            background: #10b981;
        }

        .password-hint {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.5rem;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            margin-top: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #6b7280;
        }

        .login-link a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }

        .login-link a:hover {
            color: #764ba2;
        }

        .alert {
            padding: 0.875rem;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        @media (max-width: 768px) {
            .register-container {
                padding: 1rem;
            }

            .form-section {
                padding: 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .logo-section h1 {
                font-size: 1.5rem;
            }
        }
    </style>

    <div class="register-container">
        <div class="register-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <h1>🏠 Create Account</h1>
                <p>Join Panha Estate and find your dream property</p>
            </div>

            <!-- Form Section -->
            <div class="form-section">
                <!-- Alerts -->
                @if (session('error'))
                    <div class="alert alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name Row -->
                    <div class="form-row">
                        <!-- First Name -->
                        <div class="form-group">
                            <label class="form-label" for="first_name">First Name</label>
                            <input id="first_name" type="text" name="first_name"
                                   class="form-control {{ $errors->has('first_name') ? 'error' : '' }}"
                                   value="{{ old('first_name') }}"
                                   required autofocus
                                   autocomplete="given-name"
                                   placeholder="John">
                            @error('first_name')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Last Name -->
                        <div class="form-group">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input id="last_name" type="text" name="last_name"
                                   class="form-control {{ $errors->has('last_name') ? 'error' : '' }}"
                                   value="{{ old('last_name') }}"
                                   required
                                   autocomplete="family-name"
                                   placeholder="Doe">
                            @error('last_name')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Row -->
                    <div class="form-row">
                        <!-- Username -->
                        <div class="form-group">
                            <label class="form-label" for="username">
                                Username <span class="optional">(Optional)</span>
                            </label>
                            <input id="username" type="text" name="username"
                                   class="form-control {{ $errors->has('username') ? 'error' : '' }}"
                                   value="{{ old('username') }}"
                                   autocomplete="username"
                                   placeholder="johndoe">
                            @error('username')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="form-group">
                            <label class="form-label" for="phone_no">
                                Phone Number <span class="optional">(Optional)</span>
                            </label>
                            <input id="phone_no" type="tel" name="phone_no"
                                   class="form-control {{ $errors->has('phone_no') ? 'error' : '' }}"
                                   value="{{ old('phone_no') }}"
                                   autocomplete="tel"
                                   placeholder="+855 12 345 678">
                            @error('phone_no')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group full-width">
                        <label class="form-label" for="email">Email Address</label>
                        <input id="email" type="email" name="email"
                               class="form-control {{ $errors->has('email') ? 'error' : '' }}"
                               value="{{ old('email') }}"
                               required
                               autocomplete="email"
                               placeholder="john.doe@example.com">
                        @error('email')
                            <div class="error-message">⚠️ {{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Row -->
                    <div class="form-row">
                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <input id="password" type="password" name="password"
                                   class="form-control {{ $errors->has('password') ? 'error' : '' }}"
                                   required
                                   autocomplete="new-password"
                                   placeholder="••••••••">
                            <div class="password-strength">
                                <div class="password-strength-bar" id="strengthBar"></div>
                            </div>
                            <div class="password-hint" id="strengthText">At least 8 characters</div>
                            @error('password')
                                <div class="error-message">⚠️ {{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirm Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                   class="form-control"
                                   required
                                   autocomplete="new-password"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary">
                        Create Account
                    </button>

                    <!-- Login Link -->
                    <div class="login-link">
                        Already have an account? <a href="{{ route('login') }}">Sign in here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        if (passwordInput && strengthBar && strengthText) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;

                if (password.length >= 8) strength++;
                if (password.match(/[a-z]+/)) strength++;
                if (password.match(/[A-Z]+/)) strength++;
                if (password.match(/[0-9]+/)) strength++;
                if (password.match(/[$@#&!]+/)) strength++;

                // Update bar and text
                strengthBar.className = 'password-strength-bar';

                if (strength <= 2) {
                    strengthBar.classList.add('weak');
                    strengthText.textContent = 'Weak password';
                    strengthText.style.color = '#ef4444';
                } else if (strength <= 4) {
                    strengthBar.classList.add('medium');
                    strengthText.textContent = 'Medium password';
                    strengthText.style.color = '#f59e0b';
                } else {
                    strengthBar.classList.add('strong');
                    strengthText.textContent = 'Strong password';
                    strengthText.style.color = '#10b981';
                }

                if (password.length === 0) {
                    strengthBar.className = 'password-strength-bar';
                    strengthText.textContent = 'At least 8 characters';
                    strengthText.style.color = '#6b7280';
                }
            });
        }

        // Match password validation
        const confirmPassword = document.getElementById('password_confirmation');

        if (confirmPassword && passwordInput) {
            confirmPassword.addEventListener('input', function() {
                if (this.value !== passwordInput.value && this.value.length > 0) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
        }
    </script>
    @endpush
</x-guest-layout>
