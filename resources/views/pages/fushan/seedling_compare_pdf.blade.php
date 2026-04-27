<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        @font-face {
            font-family: 'msjh';
            font-style: normal;
            font-weight: normal;
            src: url('{{ storage_path('fonts/msjh.ttf') }}') format('truetype');
        }

        html,
        body {
            font-family: 'msjh';
            margin: 24px 20px;
            font-size: 14px;
            line-height: 1.75;
            color: #111827;
        }

        h1 {
            margin: 0 0 16px 0;
            font-size: 20px;
        }

        .content {
            white-space: normal;
            word-break: break-word;
        }
    </style>
</head>

<body>
    <h1>{{ $title }}</h1>
    <div class="content">{!! $comnote !!}</div>
</body>

</html>
