<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('报表') }}</title>
    <style>
        body {
            font-family: sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        @page {
            size: A4;
            margin: 20mm;
        }
        @media print {
            body * {
                visibility: visible;
            }
            #printButton {
                display: none;
            }
        }
    </style>
</head>
<body>
    <h1>{{ __('报表') }}</h1>

    <table>
        <thead>
            <tr>
                <th>{{ __('机器') }}</th>
                <th>{{ __('信用额') }}</th>
                <th>{{ __('进球数') }}</th>
                <th>{{ __('出球数') }}</th>
                <th>{{ __('收益') }}</th>
                <th>{{ __('时间戳') }}</th>
            </tr>
        </thead>
        <tbody>
            {{-- TODO: Populate table with report data --}}
        </tbody>
    </table>

    <button id="printButton" onclick="window.print()">{{ __('打印') }}</button>
</body>
</html>
