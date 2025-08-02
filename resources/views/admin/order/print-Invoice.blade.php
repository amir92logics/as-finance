<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <style>
        * {
            font-size: 12px;
            font-family: 'Times New Roman';
        }
        td,
        th,
        tr,
        table {
            padding-top: 4px;
            padding-bottom: 4px;
            border-collapse: collapse;
        }

        .font-700{
            font-weight: 700;
        }
        .border-top-hash {
            border-top-style: dashed;
        }

        .border-bottom-hash {
            border-bottom-style: dashed;
        }

        .border-bottom-groove {
            border-bottom: 1px solid #dcdada;
            border-bottom-style: groove;
        }

        td.description,
        th.description {
            width: 75px;
            max-width: 75px;
        }

        td.quantity,
        th.quantity {
            width: 20px;
            max-width: 20px;
            word-break: break-all;
        }

        td.price,
        th.price {
            width: 60px;
            max-width: 60px;
            word-break: break-all;
        }

        .centered {
            text-align: center;
            align-content: center;
        }

        .ticket {
            width: 255px;
            max-width: 255px;
        }

        img {
            max-width: inherit;
            width: inherit;
        }

        .text-center {
            text-align: center;
        }

        .pt-2 {
            padding-top: 20px;
        }

        .mt-2 {
            margin-top: 20px;
        }
        .mb-3{
            margin-bottom: 36px;
        }


        .fw-semibold {
            font-weight: 700;
        }

        @media print {
            .hidden-print,
            .hidden-print * {
                display: none !important;
            }
        }

        @page {
            size: auto;
            margin: 0mm;
        }
        .text-end {
            text-align: end;
        }
    </style>
</head>
<body>
<div class="ticket">
    <h1 class="text-center">{{basicControl()->site_title}}</h1>
    <p class="centered fw-semibold">
        Order No. - {{ $order->order_number }}
    </p>
    <p class="centered">
        {{dateTime($order->created_at) }}
    </p>

    @if(count($order->orderItem) > 0)
        <table>
            <thead class="border-top-hash border-bottom-groove">
            <tr>
                <th class="description">Des.</th>
                <th class="quantity">Qty</th>
                <th class="mrp">MRP</th>
                <th class="price">Amount</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->orderItem as $item)
                <tr class="border-bottom-groove">
                    <td class="description">{{ optional(optional($item->product)->details)->title }}</td>
                    <td class="quantity text-center">{{ $item->quantity }}</td>
                    <td class="mrp ">{{ currencyPosition($item->price) }}</td>
                    <td class="price text-end">{{ currencyPosition($item->price * $item->quantity ) }}</td>
                </tr>
            @endforeach


            <tr class="border-top-hash">
                <td class="description font-700">Sub Total</td>
                <td class="quantity text-center"></td>
                <td class="mrp "></td>
                <td class="price font-700">{{ currencyPosition($order->subtotal) }}</td>
            </tr>

            <tr class="border-top-hash">
                <td class="quantity"></td>
                <td class="description">Discount</td>
                <td></td>
                <td class="price">{{currencyPosition($order->discount) }}</td>
            </tr>

            @if(basicControl()->vat_status)
                <tr class="">
                    <td class="quantity"></td>
                    <td class="description">Including VAT({{basicControl()->vat}}%)VAT</td>
                    <td></td>
                    <td class="price">{{ currencyPosition($order->vat) }}</td>
                </tr>
            @endif


            <tr>
                <td class="quantity"></td>
                <td class="description">Delivery Charge</td>
                <td></td>
                <td class="price">{{ currencyPosition($order->delivery_charge) }}</td>
            </tr>


            <tr>
                <td class="quantity"></td>
                <td class="description">Net Amount</td>
                <td></td>
                <td class="price">{{ currencyPosition(($order->total)) }}</td>
            </tr>

            <tr>
                <td class="quantity"></td>
                <td class="description">Paid Amount</td>
                <td></td>
                <td class="price">{{currencyPosition(($order->total - $order->due))}}</td>
            </tr>
            <tr>
                <td class="quantity"></td>
                <td class="description">Due Amount</td>
                <td></td>
                <td class="price">{{currencyPosition(($order->due))}}</td>
            </tr>

            <tr class="border-top-hash border-bottom-hash ">
                <td class="quantity "><span class="font-700 mb-3"> Payment Info:</span>   {{$order->payment_by}}</td>

            </tr>
            </tbody>
        </table>
    @endif
    <br>
    <span><strong>Instructions :</strong></span>
    <p>{{basicControl()->instructions}}</p>
    <p class="centered">Thank you for your order.</p>
</div>
<button id="btnPrint" class="hidden-print">Print</button>
<a href="{{ url()->previous() }}" class="hidden-print">Back</a>
<script>
    const $btnPrint = document.querySelector("#btnPrint");
    $btnPrint.addEventListener("click", () => {
        window.print();
    });
</script>
</body>
</html>
