<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductApiController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['required', 'image', 'max:10240'], // 10 megabytes
            'description' => ['required', 'string', 'max:65535'],
            'price' => ['required', 'integer'],
            'category_id' => ['required', 'exists:categories,id']
        ]);

        // store photo
        $data['photo'] = $this->storeImage($data['photo']);

        $product = auth()->user()->products()->create($data);

        // this code to fix bug:
        // after creating model, fields are converted to string
        $product->refresh();

        return response($product, Response::HTTP_CREATED);
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        abort_if($product->user_id !== auth()->id(), Response::HTTP_FORBIDDEN,
            'You are not permitted to edit other user products');

        // cannot receive file with PUT method
        // if it is required you can change method to POST
        // or create another route to update photo

        $data = $request->validate([
            'name' => ['string', 'max:255'],
            'description' => ['string', 'max:65535'],
            'price' => ['integer'],
            'category_id' => ['integer', 'exists:categories,id']
        ]);

        $product->update($data);

        // this code to fix bug:
        // after updating model, changed fields are converted to string
        $product->refresh();

        return response($product, Response::HTTP_ACCEPTED);
    }

    public function destroy(Product $product)
    {
        abort_if($product->user_id !== auth()->id(), Response::HTTP_FORBIDDEN,
            'You are not permitted to delete other user products');

        $product->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'max:255']
        ]);
        return Product::query()
            ->where('name', 'like', '%' . $data['query'] . '%')
            ->orWhere('description', 'like', '%' . $data['query'] . '%')
            ->orWhere(function ($query) use ($data) {
                $query->whereHas('category', function ($q) use ($data) {
                    $q->where('name', 'LIKE', '%' . $data['query'] . '%');
                });
            })
            ->get();
    }

    private function storeImage($image)
    {
        $imageName = time() . '.' . $image->extension();
        $image->storeAs('public/images', $imageName);
        return 'storage/images/' . $imageName;
    }
}
