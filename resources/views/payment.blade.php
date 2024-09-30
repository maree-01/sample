<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
</head>
<body>
    <h1>Make Payment</h1>
    <form action="{{ route('payment.checkout') }}" method="POST">
    @csrf
    <button type="submit">Proceed to Checkout</button>
</form>
</body>
</html>
