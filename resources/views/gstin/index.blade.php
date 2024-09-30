<!DOCTYPE html>
<html>
<head>
    <title>GSTIN Form</title>
</head>
<body>
    <form action="{{ route('gstin.fetch') }}" method="POST">
        @csrf
        <label for="gstin">Enter GSTIN:</label><br>
        <input type="text" id="gstin" name="gstin" required><br><br>
        <button type="submit">Fetch and Store GSTIN Data</button>
    </form>
</body>
</html>
