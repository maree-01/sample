<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
</head>
<body>
    <h1>Checkout</h1>
    <p>Order ID: {{ $order->id }}</p>
    <p>Amount: {{ $order->amount }}</p>
    <p>Currency: {{ $order->currency }}</p>
    <p>Status: {{ $order->status }}</p>
    <form action="{{ route('payment.callback') }}" method="POST">
        @csrf
        <input type="hidden" name="razorpay_order_id" value="{{ $order->id }}">
        <script src="https://checkout.razorpay.com/v1/checkout.js"
            data-key="{{ env('RAZORPAY_KEY_ID') }}"
            data-amount="{{ $order->amount }}"
            data-currency="{{ $order->currency }}"
            data-order_id="{{ $order->id }}"
            data-buttontext="Pay Now"
            data-name="My Store"
            data-description="Payment"
            data-image="https://your-company-logo.png"
            data-prefill.name="Customer Name"
            data-prefill.email="customer@example.com"
            data-prefill.contact="6385792887"
            data-theme.color="#F37254">
        </script>
    </form>
</body>
</html>
