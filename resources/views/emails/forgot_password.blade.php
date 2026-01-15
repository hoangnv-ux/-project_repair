<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('email_template.email_subject', ['project' => config('app.name')]) }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f7;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        h1 {
            font-size: 20px;
            color: #0a4386;
            text-align: center;
        }
        p {
            line-height: 1.6;
            font-size: 16px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 20px 0;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            color: #0a4386;
        }
        .footer p {
            font-size: 12px;
        }
        .footer hr {
            margin: 20px 0;
            border: 0;
            border-top: 1px solid #ddd;
        }
        .copyright {
            width: 100%;
            padding: 14px 0;
            background-color: #007bff;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ __('email_template.email_subject_reset_password', ['project' => config('app.name')]) }}</h1>

        <p>{{ __('email_template.greeting_reset_password', ['project' => config('app.name')]) }}</p>

        <p>{{ __('email_template.instruction_reset_password') }}</p>

        <p style="text-align: center;">
            <a href="{{ $url }}" class="button">{{ __('email_template.button_reset_password') }}</a>
        </p>

        <p>
            {{ __('email_template.reset_password_expiration_notice', ['hours' => $expiration_time]) }}
        </p>

        <p>{{ __('email_template.no_action_required') }}</p>

        <p>{{ __('email_template.thanks', ['project' => config('app.name')]) }}</p>

        <div class="footer">
            <hr />
            <p>{{ __('email_template.no_reply') }}</p>
            <p>{{ __('email_template.no_reply_notice') }}</p>
            <p>{{ __('email_template.contact_info') }}</p>
            <p>{{ __('email_template.support') }}</p>
            --
            <p>{{ __('email_template.company_name') }}</p>
            <p>{{ __('email_template.company_address') }}</p>
            <div class="copyright">
                {{ __('email_template.copyright') }}
            </div>
        </div>
    </div>
</body>
</html>
