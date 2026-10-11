<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Csrf;
use App\Core\Validate;

/**
 * سلة المشتريات.
 */
final class CartController extends Controller
{
    public function index(): void
    {
        $items = Cart::items();

        $this->view('cart/index', [
            'items' => $items,
            'subtotal' => Cart::subtotal(),
            'page' => [
                'title' => 'سلة المشتريات - ' . env('APP_NAME', 'المتجر الليبي'),
                'description' => 'سلة المشتريات في المتجر الليبي.',
                'canonical' => '/cart',
                'noindex' => true,
            ],
        ]);
    }

    /** إضافة منتج إلى السلة: POST /cart/add */
    public function add(): void
    {
        Csrf::verify();

        $errors = Validate::check($_POST, [
            'product_id' => ['required', 'integer', 'min_value:1'],
            'qty' => ['required', 'integer', 'min_value:1', 'max_value:99'],
        ], ['product_id' => 'المنتج', 'qty' => 'الكمية']);

        if ($errors !== []) {
            flash('errors', implode(' — ', $errors), 'error');
            $this->back('/products');
        }

        $productId = (int) $_POST['product_id'];
        $qty = (int) $_POST['qty'];

        if (!Cart::add($productId, $qty)) {
            flash('errors', 'تعذّر الإضافة: إما أن المنتج نفد مخزونه أو بلغت الحد الأقصى.', 'error');
            $this->back('/cart');
        }

        flash('cart', 'تمت إضافة المنتج إلى السلة.', 'success');

        $back = (string) ($_POST['return_to'] ?? '');
        if ($back !== '' && str_starts_with($back, '/') && !str_starts_with($back, '//')) {
            redirect($back);
        }

        redirect('/cart');
    }

    /** تحديث الكميات: POST /cart/update (qty[id]) */
    public function update(): void
    {
        Csrf::verify();

        $quantities = is_array($_POST['qty'] ?? null) ? $_POST['qty'] : [];
        $items = Cart::items();

        foreach ($items as $item) {
            $value = $quantities[$item['id']] ?? null;
            if ($value === null) {
                continue;
            }

            $qty = filter_var((string) $value, FILTER_VALIDATE_INT);
            Cart::setQty($item['id'], $qty === false ? $item['qty'] : (int) $qty);
        }

        flash('cart', 'تم تحديث السلة.', 'success');
        redirect('/cart');
    }

    /** حذف منتج: POST /cart/remove */
    public function remove(): void
    {
        Csrf::verify();

        $productId = filter_var((string) ($_POST['product_id'] ?? ''), FILTER_VALIDATE_INT);
        if ($productId !== false) {
            Cart::remove((int) $productId);
            flash('cart', 'تم حذف المنتج من السلة.', 'success');
        }

        redirect('/cart');
    }
}