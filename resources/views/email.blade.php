<!DOCTYPE html>
<html lang="en">

<head>

    <style>
        /* Definição da cor base */
        :root {
            --primary-color: #e9540d;
            --primary-color-light: #fcd5c0;
        }

        /* Estilos gerais */
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        /* Container */
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        /* Cabeçalho */
        .header {
            background-color: #e9540d;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        /* Corpo */
        .body {
            padding: 20px;
            text-align: justify;
            line-height: 1.5;
        }

        /* Rodapé */
        .footer {
            background-color: #fcd5c0;
            padding: 20px;
            text-align: center;
            font-size: 0.8em;
            color: #555;
        }

        /* Botão */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e9540d;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #c93f0a;
        }

        /* Link */
        a {
            color: #e9540d;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #c93f0a;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>{{ $subject }}</h1>
        </div>

        <div class="body">
            <p>{!! $body !!}</p>


            {{ __('Regards') }},<br>
            {{ config('app.name') }},<br><br>
            @if($signature != null)
            {!!$signature !!}
            @endif
            @if($hasAttachment)
            <hr>
            {{ __('Please note that this email contains an attached file.') }}
            @endif

            <hr>

            {{ __('This is an automated email. Please do not reply to this message as this inbox is not monitored. If you have any questions or require assistance, please contact us through ower team') }}

        </div>
    </div>
</body>