<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment</title>
    <script src="https://js.stripe.com/v3/"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body>

<h2>Stripe Payment</h2>

<form id="payment-form" action="{{ url('stripe/payment') }}" method="POST">
    @csrf

    <!-- This div will hold the Stripe Card Element -->
    <div id="card-element">
        <!-- A Stripe Element will be inserted here. -->
    </div>

    <!-- Used to display form errors -->
    <div id="card-errors" role="alert"></div>

    <!-- Submit button -->
    <button id="submit">Pay Now</button>
</form>

<script type="text/javascript">
    // Your Stripe public key
    var stripe = Stripe("{{ config('stripe.key') }}");

    // Create an instance of Elements
    var elements = stripe.elements();

    // Create an instance of the card Element
    var card = elements.create("card");

    // Mount the card Element into the DOM
    card.mount("#card-element");

    // Handle form submission
    var form = document.getElementById("payment-form");
    var cardErrors = document.getElementById("card-errors");

    form.addEventListener("submit", async function (event) {
        event.preventDefault();

        // Create a token using the card Element
        const {token, error} = await stripe.createToken(card);

        // If there's an error, display it
        if (error) {
            cardErrors.textContent = error.message;
        } else {
            // If successful, send the token to your server for processing the payment
            var response = await fetch("{{ url('stripe/payment') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    payment_method_id: token.id,
                })
            });

            var paymentResult = await response.json();

            // Handle the response from the server
            if (paymentResult.client_secret) {
                alert("Payment successful! Client secret: " + paymentResult.client_secret);
                var successUrl = paymentResult.success_url + "?payment_intent=" + paymentResult.payment_intent;
                window.location.href = successUrl;
            } else {
                alert("Payment failed! Error: " + paymentResult.error);
            }
        }
    });
</script>

</body>
</html>
