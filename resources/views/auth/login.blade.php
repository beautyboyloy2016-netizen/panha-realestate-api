<x-guest-layout>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 3rem 4rem;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
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
            margin-bottom: 2rem;
        }

        .logo-section h1 {
            font-size: 2.25rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .logo-section p {
            color: #6b7280;
            font-size: 1.05rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1.125rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            color: #6b7280;
            cursor: pointer;
        }

        .checkbox-label input {
            margin-right: 0.5rem;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }        .forgot-link {
            font-size: 0.95rem;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .forgot-link:hover {
            color: #764ba2;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e5e7eb;
        }

        .divider span {
            padding: 0 1rem;
            color: #9ca3af;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .social-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.875rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .social-btn svg {
            width: 18px;
            height: 18px;
            margin-right: 0.5rem;
        }

        .social-btn.google {
            color: #ea4335;
            border-color: #ea4335;
        }

        .social-btn.google:hover {
            background: #ea4335;
            color: white;
        }

        .social-btn.telegram {
            color: #0088cc;
            border-color: #0088cc;
        }

        .social-btn.telegram:hover {
            background: #0088cc;
            color: white;
        }

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: #6b7280;
        }

        .signup-link a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }

        .signup-link a:hover {
            color: #764ba2;
        }

        .alert {
            padding: 0.875rem;
            border-radius: 8px;
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

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9998;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .telegram-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 90%;
            animation: slideUp 0.3s ease-out;
        }

        .modal-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .modal-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .modal-header p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .modal-close {
            width: 100%;
            margin-top: 1rem;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: white;
            color: #6b7280;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }
    </style>

    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <h1>🏠 Panha Estate</h1>
                <p>Welcome back! Please login to your account</p>
            </div>

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

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input id="email" type="email" name="email" class="form-control"
                           value="{{ old('email') }}" required autofocus
                           autocomplete="username" placeholder="you@example.com">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" name="password" class="form-control"
                           required autocomplete="current-password" placeholder="••••••••">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember & Forgot -->
                <div class="remember-forgot">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary">
                    Sign In
                </button>
            </form>

            @php
                $socialAuthEnabled = is_social_auth_enabled();
                $googleEnabled = is_google_login_enabled();
                $telegramEnabled = is_telegram_login_enabled();
                $anySocialEnabled = $googleEnabled || $telegramEnabled;
            @endphp

            @if($anySocialEnabled)
                <!-- Divider -->
                <div class="divider">
                    <span>Or continue with</span>
                </div>

                <!-- Social Buttons -->
                <div class="social-buttons">
                    @if($googleEnabled)
                        <a href="{{ route('auth.google') }}" class="social-btn google">
                            <svg viewBox="0 0 24 24">
                                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Google
                        </a>
                    @endif

                    @if($telegramEnabled)
                        <button type="button" id="telegramLoginBtn" class="social-btn telegram">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 1.897-.962 6.502-1.359 8.627-.168.9-.5 1.201-.82 1.23-.697.064-1.226-.461-1.901-.903-1.056-.692-1.653-1.123-2.678-1.799-1.185-.781-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.139-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.241-1.865-.44-.752-.244-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635.099-.002.321.023.465.14.121.098.155.231.171.363.016.132.036.431.02.666z"/>
                            </svg>
                            Telegram
                        </button>
                    @endif
                </div>
            @endif

            <!-- Sign Up Link -->
            @if (Route::has('register'))
                <div class="signup-link">
                    Don't have an account? <a href="{{ route('register') }}">Sign up now</a>
                </div>
            @endif
        </div>
    </div>

    @if($telegramEnabled ?? false)
        <!-- Telegram Modal -->
        <div class="modal-overlay" id="modalOverlay"></div>
        <div class="telegram-modal" id="telegramModal">
            <div class="modal-header">
                <h3>Telegram Login</h3>
                <p>Authenticate with your Telegram account</p>
            </div>
            <div style="text-align: center;">
                <script async src="https://telegram.org/js/telegram-widget.js?22"
                    data-telegram-login="{{ config('services.telegram.bot_username') }}"
                    data-size="large"
                    data-onauth="onTelegramAuth(user)"
                    data-request-access="write">
                </script>
            </div>
            <button class="modal-close" onclick="closeTelegramModal()">Cancel</button>
        </div>
    @endif

    @push('scripts')
      <script>
          // Telegram Login Handler
          document.getElementById('telegramLoginBtn')?.addEventListener('click', function() {
              document.getElementById('modalOverlay').style.display = 'block';
              document.getElementById('telegramModal').style.display = 'block';
          });

          function closeTelegramModal() {
              document.getElementById('modalOverlay').style.display = 'none';
              document.getElementById('telegramModal').style.display = 'none';
          }

          // Handle Telegram callback
          window.onTelegramAuth = function(user) {
              const modal = document.getElementById('telegramModal');
              modal.innerHTML = '<div style="text-align: center; padding: 2rem;"><div style="border: 4px solid #f3f4f6; border-top: 4px solid #0088cc; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto;"></div><p style="margin-top: 1.5rem; color: #6b7280; font-weight: 600;">Authenticating...</p></div>';

              fetch('{{ route('auth.telegram') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}'
                  },
                  body: JSON.stringify(user)
              })
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      modal.innerHTML = '<div style="text-align: center; padding: 2rem;"><div style="color: #10b981; font-size: 4rem;">✓</div><p style="margin-top: 1rem; color: #059669; font-weight: 700; font-size: 1.25rem;">Success!</p><p style="color: #6b7280; margin-top: 0.5rem;">Redirecting to dashboard...</p></div>';
                      setTimeout(() => {
                          window.location.href = data.redirect;
                      }, 1500);
                  } else {
                      modal.innerHTML = '<div style="text-align: center; padding: 2rem;"><div style="color: #ef4444; font-size: 4rem;">✕</div><p style="margin-top: 1rem; color: #dc2626; font-weight: 600;">' + data.message + '</p><button onclick="location.reload()" style="margin-top: 1.5rem; padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600;">Try Again</button></div>';
                  }
              })
              .catch(error => {
                  console.error('Error:', error);
                  modal.innerHTML = '<div style="text-align: center; padding: 2rem;"><div style="color: #ef4444; font-size: 4rem;">✕</div><p style="margin-top: 1rem; color: #dc2626; font-weight: 600;">Authentication failed</p><button onclick="location.reload()" style="margin-top: 1.5rem; padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600;">Try Again</button></div>';
              });
          };
      </script>
    @endpush
</x-guest-layout>
