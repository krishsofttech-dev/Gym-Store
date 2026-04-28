<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function __construct(private ImageService $imageService) {}

    public function index(Request $request)
    {
        $query = Product::withTrashed()->with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::active()->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data         = $this->validateProduct($request);
        $data['slug'] = Str::slug($request->name);

        $data['is_active']      = $request->boolean('is_active');
        $data['is_featured']    = $request->boolean('is_featured');
        $data['is_new']         = $request->boolean('is_new');
        $data['track_quantity'] = $request->boolean('track_quantity');

        $allFiles = $request->allFiles();

        if (!empty($allFiles['thumbnail'])) {
            $data['thumbnail'] = $this->imageService->storeThumbnail($allFiles['thumbnail']);
        }

        if (!empty($allFiles['images'])) {
            $files = is_array($allFiles['images']) ? $allFiles['images'] : [$allFiles['images']];
            $data['images'] = $this->imageService->storeMultiple($files);
        }

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        return redirect()->route('admin.products.edit', $product);
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data         = $this->validateProduct($request, $product->id);
        $data['slug'] = Str::slug($request->name);

        $data['is_active']      = $request->boolean('is_active');
        $data['is_featured']    = $request->boolean('is_featured');
        $data['is_new']         = $request->boolean('is_new');
        $data['track_quantity'] = $request->boolean('track_quantity');

        $allFiles = $request->allFiles();

        if (!empty($allFiles['thumbnail'])) {
            if ($product->thumbnail) {
                $this->imageService->delete($product->thumbnail);
            }
            $data['thumbnail'] = $this->imageService->storeThumbnail($allFiles['thumbnail']);
        }

        if (!empty($allFiles['images'])) {
            if (!empty($product->images)) {
                $this->imageService->deleteMultiple($product->images);
            }
            $files = is_array($allFiles['images']) ? $allFiles['images'] : [$allFiles['images']];
            $data['images'] = $this->imageService->storeMultiple($files);
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product removed.');
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['required', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'price'             => ['required', 'numeric', 'min:0'],
            'compare_price'     => ['nullable', 'numeric', 'min:0'],
            'cost_price'        => ['nullable', 'numeric', 'min:0'],
            'sku'               => ['nullable', 'string', 'unique:products,sku,' . $ignoreId],
            'stock_quantity'    => ['required', 'integer', 'min:0'],
            'category_id'       => ['required', 'exists:categories,id'],
            'brand'             => ['nullable', 'string', 'max:100'],
            'weight'            => ['nullable', 'numeric', 'min:0'],
            'dimensions'        => ['nullable', 'string', 'max:100'],
            'is_active'         => ['nullable', 'boolean'],
            'is_featured'       => ['nullable', 'boolean'],
            'is_new'            => ['nullable', 'boolean'],
            'track_quantity'    => ['nullable', 'boolean'],
            'thumbnail'         => ['nullable', 'image', 'max:5048'],
            'images'            => ['nullable', 'array'],
            'images.*'          => ['nullable', 'image', 'max:5048'],
        ]);
    }
}