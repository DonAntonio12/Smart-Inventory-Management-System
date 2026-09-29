<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $category = $request->query('category');
        $brand = $request->query('brand');
        $stock = $request->query('stock');

        $products = Product::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%')
                        ->orWhere('brand', 'like', '%'.$search.'%');
                });
            })
            ->when($category !== null, function (Builder $query) use ($category): void {
                if ($category === '__uncategorized') {
                    $query->where(function (Builder $query): void {
                        $query->whereNull('category')->orWhere('category', '');
                    });

                    return;
                }

                $query->where('category', $category);
            })
            ->when($brand !== null, function (Builder $query) use ($brand): void {
                if ($brand === '__unbranded') {
                    $query->where(function (Builder $query): void {
                        $query->whereNull('brand')->orWhere('brand', '');
                    });

                    return;
                }

                $query->where('brand', $brand);
            })
            ->when($stock === 'low', fn (Builder $query) => $query
                ->where('quantity', '>', 0)
                ->whereColumn('quantity', '<=', 'low_stock_threshold'))
            ->when($stock === 'out', fn (Builder $query) => $query->where('quantity', 0))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'search' => $search,
            'selectedCategory' => $category,
            'selectedBrand' => $brand,
            'selectedStock' => $stock,
            'categories' => Product::query()->whereNotNull('category')->where('category', '<>', '')->distinct()->orderBy('category')->pluck('category'),
            'brands' => Product::query()->whereNotNull('brand')->where('brand', '<>', '')->distinct()->orderBy('brand')->pluck('brand'),
            'productCount' => Product::query()->count(),
            'unitCount' => Product::query()->sum('quantity'),
            'lowStockCount' => Product::query()->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'low_stock_threshold')->count(),
        ]);
    }

    public function categories(): View
    {
        return view('products.groups', [
            'title' => 'Categories',
            'description' => 'Browse products by category.',
            'groups' => $this->groupedProducts('category'),
            'filterKey' => 'category',
            'uncategorizedValue' => '__uncategorized',
        ]);
    }

    public function brands(): View
    {
        return view('products.groups', [
            'title' => 'Brands',
            'description' => 'Browse products by brand.',
            'groups' => $this->groupedProducts('brand'),
            'filterKey' => 'brand',
            'uncategorizedValue' => '__unbranded',
        ]);
    }

    public function create(): View
    {
        return view('products.form', [
            'product' => new Product,
            'categories' => Product::query()->whereNotNull('category')->where('category', '<>', '')->distinct()->orderBy('category')->pluck('category'),
            'brands' => Product::query()->whereNotNull('brand')->where('brand', '<>', '')->distinct()->orderBy('brand')->pluck('brand'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Product::create($request->validate($this->rules()));

        return redirect()->route('products.index')->with('status', 'Product added to your catalog.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', [
            'product' => $product,
            'categories' => Product::query()->whereNotNull('category')->where('category', '<>', '')->distinct()->orderBy('category')->pluck('category'),
            'brands' => Product::query()->whereNotNull('brand')->where('brand', '<>', '')->distinct()->orderBy('brand')->pluck('brand'),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($request->validate($this->rules($product)));

        return redirect()->route('products.index')->with('status', 'Product details updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('status', 'Product removed from your catalog.');
    }

    private function rules(?Product $product = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product)],
            'category' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:120'],
            'quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'expiration_date' => ['nullable', 'date'],
        ];
    }

    private function groupedProducts(string $column): Collection
    {
        return Product::query()
            ->select($column)
            ->selectRaw('COUNT(*) as product_count, COALESCE(SUM(quantity), 0) as unit_count')
            ->groupBy($column)
            ->get()
            ->groupBy(fn (Product $product): string => filled($product->{$column}) ? $product->{$column} : 'Uncategorized')
            ->map(fn (Collection $products, string $name): object => (object) [
                'name' => $name,
                'product_count' => $products->sum('product_count'),
                'unit_count' => $products->sum('unit_count'),
                'filter_value' => $name === 'Uncategorized' ? ($column === 'category' ? '__uncategorized' : '__unbranded') : $name,
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }
}
