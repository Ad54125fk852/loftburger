@php
    // 🔹 Calcular total por si es PREVIEW (cuando aún no existe grand_total)
    $calculatedTotal = 0;
    foreach ($orderDetails as $details) {
        $calculatedTotal += $details['price'] * $details['quantity'];
    }

    // 🔹 Usar el total correcto
    $total = ($billDetails['grand_total'] ?? 0) > 0
        ? $billDetails['grand_total']
        : $calculatedTotal;
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo</title>

    <style>
        /* 🔴 CLAVE PARA QUE NO SALGA GRIS */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        html, body {
            width: 80mm;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            padding: 8px;
            color: #000;
        }

        .center { text-align: center; }
        .left { text-align: left; }
        .right { text-align: right; }

        .divider {
            border-top: 2px dashed #000;
            margin: 10px 0;
        }

        h2 {
            margin: 6px 0;
            font-size: 15px;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th {
            background: #000;
            color: #fff;
            font-weight: normal;
            padding: 4px;
            font-size: 11px;
        }

        td {
            padding: 4px;
            border-bottom: 1px solid #ccc;
            font-size: 11px;
        }

        .product {
            text-align: left;
            word-break: break-word;
        }

        .total {
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
            text-align: right;
        }

        .payment {
            margin-top: 8px;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <!-- INFO RESTAURANTE -->
    <div class="center">
        <strong>{{ $restaurant['name'] }}</strong><br>
        {{ $restaurant['address'] }}<br>
        {{ $restaurant['phone'] }}<br>
        Instagram: @LoftBurger
    </div>

    <div class="divider"></div>

    <!-- TITULO -->
    <h2 class="center">RECIBO DE VENTA</h2>

    <!-- INFO VENTA -->
    <table>
        <tr>
            <td class="left">Venta:</td>
            <td class="right">
    {{ $billDetails['KOT'] ?? $orderDetails['KOT'] ?? 'KOT' }}
</td>
        </tr>
        <tr>
            <td class="left">Fecha:</td>
            <td class="right">{{ $billDetails['date'] }}</td>
        </tr>
        <tr>
            <td class="left">Vendedor:</td>
            <td class="right">Caja 1</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- PRODUCTOS -->
    <table>
        <thead>
            <tr>
                <th>Cant</th>
                <th>Producto</th>
                <th>V/U</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orderDetails as $name => $details)
                <tr>
                    <td class="center">{{ $details['quantity'] }}</td>
                    <td class="product">{{ $name }}</td>
                    <td class="right">$ {{ number_format($details['price'], 0) }}</td>
                    <td class="right">
                        $ {{ number_format($details['price'] * $details['quantity'], 0) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TOTAL -->
    <div class="total">
        Total: $ {{ number_format($total, 0) }}
    </div>

    <div class="divider"></div>

</body>
</html>
