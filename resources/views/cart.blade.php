@extends('layouts.app')

@section('content')

<div class="container my-5">
    <h1 class="text-center mb-4">All Shopping Cart</h1>

    <!-- Cart Table -->
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th scope="col">Image</th>
                <th scope="col">User</th>
                <th scope="col">Product</th>
                <th scope="col">Price</th>
                <th scope="col">Quantity</th>
                <th scope="col">Total</th>
                <th scope="col">Added Date</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($carts as $cart)
            <tr class="cart-item">
                <td><img src="{{ asset('storage/' . $cart->product->images[0]->image_path) }}" alt="{{ $cart->product->name }}" height="100" width="100"></td>
                <td>{{ $cart->user->name }}</td>
                <td>{{ $cart->product->name }}</td>
                <td>Rs. {{ $cart->product->price }}</td>
                <td>{{ $cart->quantity }}</td>
                <td>Rs. {{ $cart->product->price }}</td>
                <td>{{ date('d/m/Y h:i:s A', strtotime($cart->created_at)) }}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="deleteFromCart({{ $cart->id }})">Remove</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

<script>
    // Function to delete a cart item
    function deleteFromCart(cartId) {
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            // Make the DELETE request to the API
            fetch(`/api/cart/${cartId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    // Optionally, remove the item from the DOM
                    // document.querySelector(`.cart-item[data-cart-id="${cartId}"]`).remove();
                    window.location.replace("http://localhost:8000/products");
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    }
</script>
@endsection