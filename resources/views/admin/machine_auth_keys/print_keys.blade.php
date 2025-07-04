{{-- filepath: /Users/ilawusong/Documents/v2waw/resources/views/admin/machine_auth_keys/print_keys.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>列印 QR Code</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .a4-container {
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
            background: white;
        }

        .qr-row {
            display: block;
            align-items: flex-end;
            /* 讓 QR Code 底部對齊 */
            justify-content: space-between;
        }

        .qr-row div {
            border: 1px dashed #000000;
            margin: auto;
        }

        .qr-row p {
            padding: 0 2px;
        }

        .cut-line {
            border-left: 1px dashed #999;
            height: 100%;
        }

        /* 新增水平切線 */
        .cut-line-horizontal {
            border-top: 1px dashed #999;
            width: 100%;
            margin: -1px 0;
        }

        svg {
            width: 100%;
            height: auto;
            margin: 0 auto;
            border: #FFFFFF solid 12px;
        }

        @media print {
            .qr-row {
                page-break-inside: avoid;
            }

            button {
                display: none;
            }
        }
    </style>
</head>

<body class="p-4 mx-auto flex justify-center">
    <div class="a4-container border border-dashed border-gray-400">
        @foreach ($chipKeys as $chipKey)
            <div class="flex items-center w-full">
                <div class="w-[4/9] qr-row ">
                    <p style="font-size: 0.7rem">{{ $chipKey->key }}</p>
                    <div>{!! QrCode::size(200)->generate(md5($chipKey->key)) !!}</div>
                </div>
                <div class="w-[3/9] qr-row ">
                    <p style="font-size: 0.6rem">{{ $chipKey->key }}</p>
                    <div>{!! QrCode::size(130)->generate(md5($chipKey->key)) !!}</div>
                </div>
                <div class="w-[2/9] qr-row ">
                    <p style="font-size: 0.5rem">{{ $chipKey->key }}</p>
                    <div>{!! QrCode::size(80)->generate(md5($chipKey->key)) !!}</div>
                </div>
            </div>
            {{-- 新增水平切線，將橫列與下一列隔開 --}}
            <div class="cut-line-horizontal"></div>
            <hr class="mt-3">
        @endforeach
    </div>
    {{-- 添加一個列印按鈕 在列印頁面是隱藏不會列印出來 --}}
    <div class="w-[1/4] fixed right-0 top-0 p-3">
        <a onclick="window.print()" style="cursor: pointer;"
            class="rounded-lg bg-pink-500 px-3 py-2 text-sm/6 font-bold text-white @min-[theme(--breakpoint-sm)]:w-auto pointer">{{ __('msg.print') }}</a>
        <a href="{{ URL::previous() }}"
            class="rounded-lg bg-pink-500 px-3 py-2 text-sm/6 font-bold text-white @min-[theme(--breakpoint-sm)]:w-auto pointer">{{ __('msg.previous') }}</a>
    </div>
</body>

</html>
