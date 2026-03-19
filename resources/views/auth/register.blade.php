<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - {{ __('messages.register') }}</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <div class="lang-switcher">
        <a href="{{ route('locale.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active-lang' : '' }}">EN</a>
        <span class="lang-switcher-divider">|</span>
        <a href="{{ route('locale.switch', 'ko') }}" class="{{ app()->getLocale() === 'ko' ? 'active-lang' : '' }}">KO</a>
    </div>
    <div class="auth-container">
        <div class="brand">
            <div class="brand-name"><span class="daily">Daily</span><span class="pulse">Pulse</span></div>
            <div class="brand-tagline">{{ __('messages.daily_report_system') }}</div>
        </div>

        <div class="auth-card">
            <div class="tab-bar">
                <a href="{{ route('login') }}">{{ __('messages.login') }}</a>
                <a href="{{ route('register') }}" class="active">{{ __('messages.register') }}</a>
            </div>

            @if($errors->any())
                <div class="alert">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label for="name">{{ __('messages.full_name') }}</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="John Doe" required autofocus>
                </div>
                <div class="form-group">
                    <label for="email">{{ __('messages.email') }}</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="you@example.com" required>
                </div>
                <div class="form-group">
                    <label for="password">{{ __('messages.password') }}</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">{{ __('messages.confirm_password') }}</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;&#9679;" required>
                </div>
                <button type="submit" class="btn-submit">{{ __('messages.create_account') }}</button>
            </form>
        </div>
    </div>
</body>
</html>
