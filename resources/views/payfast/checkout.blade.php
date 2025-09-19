<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to PayFast...</title>
</head>
<body onload="document.forms['payfast_form'].submit();">
    <p>Redirecting to PayFast...</p>

    <form name="payfast_form" method="post" action="{{ $payfastUrl }}">
        @foreach($data as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
    </form>
</body>
</html>
