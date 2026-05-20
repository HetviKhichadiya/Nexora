<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $mail_data['subject'] ?? 'Nexora Notification' }}</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f7fb;
            font-family: Arial, Helvetica, sans-serif;
            color: #333333;
        }

        .mail-wrapper {
            width: 100%;
            padding: 40px 0;
            background-color: #f4f7fb;
        }

        .mail-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .mail-header {
            background-color: #111827;
            padding: 25px;
            text-align: center;
        }

        .mail-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
        }

        .mail-body {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #111827;
        }

        .message {
            font-size: 15px;
            line-height: 1.8;
            color: #4b5563;
            margin-bottom: 30px;
        }

        .button-wrapper {
            text-align: center;
            margin: 35px 0;
        }

        .mail-button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
            margin-top: 40px;
            font-size: 13px;
            color: #6b7280;
            text-align: center;
        }

        .product-name {
            margin-top: 15px;
            font-size: 13px;
            color: #9ca3af;
        }

        @media only screen and (max-width: 600px) {
            .mail-body {
                padding: 30px 20px;
            }

            .mail-header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="mail-wrapper">

    <div class="mail-container">

        {{-- Header --}}
        <div class="mail-header">
            <h1>
                {{ $mail_data['from_name'] ?? 'Nexora' }}
            </h1>
        </div>

        {{-- Body --}}
        <div class="mail-body">

            {{-- Greeting --}}
            @if(!empty($mail_data['greeting']))
                <div class="greeting">
                    {{ $mail_data['greeting'] }}
                </div>
            @endif

            {{-- Main Content --}}
            <div class="message">
                {!! nl2br(e($mail_data['email_data']['body_text'] ?? '')) !!}
            </div>

            {{-- Action Button --}}
            @if(!empty($mail_data['email_data']['button_url']))
                <div class="button-wrapper">
                    <a
                        href="{{ $mail_data['email_data']['button_url'] }}"
                        class="mail-button"
                    >
                        {{ $mail_data['email_data']['button_text'] ?? 'View Details' }}
                    </a>
                </div>
            @endif

            {{-- Footer --}}
            <div class="footer">

                <div>
                    {{ $mail_data['email_data']['footer_txt'] ?? 'Thank you for using Nexora.' }}
                </div>

                @if(!empty($mail_data['email_data']['product_name']))
                    <div class="product-name">
                        {{ $mail_data['email_data']['product_name'] }}
                    </div>
                @endif

            </div>

        </div>

    </div>

</div>

</body>
</html>