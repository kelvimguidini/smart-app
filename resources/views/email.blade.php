<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        /* Base Styles */
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
            background-color: #f9fafb;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            color: #334155;
        }

        /* Container */
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f9fafb;
            padding-bottom: 40px;
        }

        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        .header {
            background: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.025em;
        }

        /* Content Body */
        .content {
            padding: 40px 30px;
            line-height: 1.6;
            font-size: 16px;
        }

        .content p {
            margin-bottom: 20px;
        }

        /* Signature Area */
        .signature {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 40px;
            font-size: 14px;
            color: #64748b;
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
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            margin: 20px 0;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }

        .hr {
            border: 0;
            border-top: 1px solid #e2e8f0;
            margin: 30px 0;
        }

        .badge {
            background-color: #e0e7ff;
            color: #4338ca;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
        }

        @media screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
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
                    <!-- Logo would go here -->
                    <div style="background: #ffffff; width: 60px; height: 60px; border-radius: 15px; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img src="{{ config('app.url') }}/logo.png" alt="SmartApp" style="width: 40px; height: 40px; object-contain: fill;">
                    </div>
                    <h1>{{ $subject }}</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <div style="color: #1e293b; font-size: 18px; font-weight: 700; margin-bottom: 20px;">
                        Olá,
                    </div>
                    
                    <div>
                        {!! $body !!}
                    </div>

                    @if($hasAttachment)
                    <div style="margin-top: 30px; padding: 15px; background-color: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1; text-align: center;">
                        <span class="badge">ANEXO INCLUSO</span>
                        <p style="margin: 10px 0 0; font-size: 13px; color: #64748b;">
                            {{ __('Este e-mail contém arquivos importantes em anexo para sua conferência.') }}
                        </p>
                    </div>
                    @endif

                    <div class="signature">
                        {{ __('Atenciosamente') }},<br>
                        <strong style="color: #4f46e5;">{{ config('app.name') }}</strong><br>
                        @if($signature != null)
                            <div style="margin-top: 10px;">
                                {!! $signature !!}
                            </div>
                        @endif
                    </div>

                    <div class="hr"></div>

                    <div style="font-size: 12px; color: #94a3b8; text-align: center;">
                        {{ __('Este é um e-mail automático. Por favor, não responda a esta mensagem. Caso precise de suporte, entre em contato com nossa equipe oficial.') }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.<br>
            <span style="font-weight: 600;">CODE WEY Desenvolvimento</span>
        </div>
    </div>
</body>
</html>