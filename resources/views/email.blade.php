<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Base Styles */
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            color: #334155;
        }

        /* Container */
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding-bottom: 40px;
        }

        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border-top: 4px solid #4f46e5;
        }

        /* Header */
        .header {
            background: #ffffff;
            padding: 40px 20px 24px 20px;
            text-align: center;
        }

        .header h1 {
            color: #0f172a;
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.025em;
            line-height: 1.3;
        }

        /* Content Body */
        .content {
            padding: 20px 30px 40px 30px;
            line-height: 1.6;
            font-size: 15px;
            color: #475569;
        }

        .content p {
            margin-bottom: 20px;
            margin-top: 0;
        }

        /* Signature Area */
        .signature {
            border-left: 3px solid #4f46e5;
            padding-left: 16px;
            margin-top: 32px;
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #94a3b8;
        }

        /* Components */
        .btn {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            margin: 20px 0;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
            font-size: 15px;
        }

        .hr {
            border: 0;
            border-top: 1px solid #f1f5f9;
            margin: 30px 0;
        }

        @media screen and (max-width: 600px) {
            .content {
                padding: 20px 20px 30px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div style="height: 40px;"></div>

        <table class="main" role="presentation">
            <tr>
                <td class="header">
                    <!-- Logo -->
                    <div style="margin-bottom: 20px; text-align: center;">
                        @if(file_exists(public_path('logo.png')))
                        <img src="{{ $message->embed(public_path('logo.png')) }}" alt="{{ config('app.name') }}" style="height: 44px; display: inline-block; vertical-align: middle; border: 0; outline: none;">
                        @else
                        <div style="font-size: 24px; font-weight: 800; color: #4f46e5; display: inline-block; letter-spacing: -0.03em;">
                            Smart<span style="color: #6366f1;">App</span>
                        </div>
                        @endif
                    </div>
                    <h1>{{ $subject }}</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <div style="color: #0f172a; font-size: 18px; font-weight: 700; margin-bottom: 16px;">
                        Olá,
                    </div>

                    <div style="margin-bottom: 24px;">
                        {!! $body !!}
                    </div>

                    <div class="signature">
                        @if(!empty($signature) && trim(strip_tags($signature)) !== '')
                        {!! $signature !!}
                        @else
                        <span style="color: #64748b; font-size: 13px; display: block; margin-bottom: 4px;">{{ __('Atenciosamente') }},</span>
                        <strong style="color: #4f46e5; font-size: 15px;">{{ config('app.name') }}</strong>
                        @endif
                    </div>

                    @if($hasAttachment)
                    <div style="margin-top: 30px; margin-bottom: 20px;">
                        <table style="width: 100%; border-collapse: collapse; background-color: #f0fdf4; border-radius: 12px; border: 1px solid #bbf7d0;" role="presentation">
                            <tr>
                                <td style="padding: 16px; width: 44px; vertical-align: middle; text-align: center;">
                                    <div style="background-color: #dcfce7; color: #16a34a; border-radius: 8px; width: 36px; height: 36px; line-height: 36px; text-align: center; font-size: 18px; font-weight: bold;">
                                        📎
                                    </div>
                                </td>
                                <td style="padding: 16px 16px 16px 0; vertical-align: middle; text-align: left;">
                                    <span style="font-size: 11px; font-weight: 700; color: #15803d; letter-spacing: 0.05em; text-transform: uppercase; display: block; line-height: 1;">Anexo Incluso</span>
                                    <span style="font-size: 13px; color: #166534; display: block; margin-top: 4px; line-height: 1.4;">{{ __('Este e-mail contém arquivos importantes em anexo para sua conferência.') }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    @endif


                    <div class="hr"></div>

                    <div style="font-size: 12px; color: #94a3b8; text-align: center; line-height: 1.6;">
                        {{ __('Este é um e-mail automático. Por favor, não responda diretamente a esta mensagem.') }}
                        @if(!empty($senderEmail))
                        <br>{{ __('Para dúvidas ou suporte, entre em contato com o responsável pelo e-mail:') }} <a href="mailto:{{ $senderEmail }}" style="color: #4f46e5; text-decoration: none; font-weight: 600;">{{ $senderEmail }}</a>.
                        @else
                        <br>{{ __('Caso precise de suporte, entre em contato com nossa equipe oficial.') }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.<br>
            <span style="font-weight: 600; color: #64748b; margin-top: 4px; display: inline-block;">CODE WEY Desenvolvimento</span>
        </div>
    </div>
</body>

</html>