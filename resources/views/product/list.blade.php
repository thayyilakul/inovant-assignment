@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Product List</h1>

    <!-- Display success message if exists -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Add New Product Button -->
    <a href="{{ route('product-create') }}" class="btn btn-success mb-2">Add New Product</a>

    <!-- Products Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Price</th>
                <th>Images</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $product->name }}</td>
                    <td>Rs. {{ number_format($product->price, 2) }}</td>
                    <td>
                        @foreach ($product->images as $image)
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $product->name }}" width="50" height="50">
                        @endforeach
                    </td>
                    <td>
                        <!-- Edit and Delete buttons (you can add functionality later) -->
                        <a href="{{ route('product-edit', $product->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection