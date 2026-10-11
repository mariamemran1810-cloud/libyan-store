<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validate;
use App\Exceptions\OutOfStockException;
use App\Models\OrderRepository;

/**
 * إتمام الطلب: مراجعة السلة + بيانات العميلة + إنشاء الطلب.
 */
final class CheckoutController extends Controller
{
    public function show(): void
    {
        $items = Cart::items();

        if ($items === []) {
            flash('cart', 'سلتك فارغة. أضيفي منتجات أولًا.', 'info');
            redirect('/cart');
        }

        $user = Auth::user();

        $this->view('checkout/show', [
            'items' => $items,
            'subtotal' => Cart::subtotal(),
            'cities' => \App\Models\SettingRepository::deliveryCities(),
            'deliveryNote' => \App\Models\SettingRepository::deliveryNote(),
            'customer' => [
                'name' => $user['name'] ?? '',
                'phone' => $user['phone'] ?? '',
                'city' => '',
                'address' => '',
                'notes' => '',
            ],
            'page' => [
                'title' => 'إتمام الطلب - ' . env('APP_NAME', 'المتجر الليبي'),
                'description' => 'أكملي بياناتك لإتمام الطلب في المتجر الليبي.',
                'canonical' => '/checkout',
                'noindex' => true,
            ],
        ]);
    }

    public function store(): void
    {
        Csrf::verify();

        // منع الإرسال المتكرر: 5 محاولات كحد أقصى كل دقيقة
        if (!\App\Core\Throttle::attempt('checkout', 5, 60)) {
            flash('errors', 'تم إرسال طلبات كثيرة في وقت قصير. انتظري لحظات ثم حاولي مجددًا.', 'error');
            redirect('/cart');
        }

        $items = Cart::items();
        if ($items === []) {
            flash('cart', 'سلتك فارغة.', 'info');
            redirect('/cart');
        }

        $errors = Validate::check($_POST, [
            'name' => ['required', 'min:2', 'max:100'],
            'phone' => ['required', 'min:8', 'max:30'],
            'city' => ['required', 'min:2', 'max:60'],
            'address' => ['required', 'min:5', 'max:250'],
            'notes' => ['max:500'],
        ], [
            'name' => 'الاسم',
            'phone' => 'رقم الهاتف',
            'city' => 'المدينة',
            'address' => 'العنوان',
            'notes' => 'ملاحظات',
        ]);

        if ($errors !== []) {
            \App\Core\Session::flash('old', $_POST);
            flash('errors', implode(' — ', $errors), 'error');
            redirect('/checkout');
        }

        // رسم التوصيل حسب المدينة (من إعدادات لوحة الإدارة)
        $deliveryFee = \App\Models\SettingRepository::deliveryFeeFor((string) $_POST['city']);
        if ($deliveryFee === null) {
            \App\Core\Session::flash('old', $_POST);
            flash('errors', 'المدينة المختارة غير مدرجة حاليًا. اختري مدينة من القائمة أو تواصلي معنا.', 'error');
            redirect('/checkout');
        }

        $orderItems = [];
        foreach ($items as $item) {
            $orderItems[] = [
                'product_id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['price'],
                'qty' => $item['qty'],
            ];
        }

        try {
            $orderId = OrderRepository::create([
                'name' => trim((string) $_POST['name']),
                'phone' => trim((string) $_POST['phone']),
                'city' => trim((string) $_POST['city']),
                'address' => trim((string) $_POST['address']),
                'notes' => trim((string) ($_POST['notes'] ?? '')) ?: null,
            ], $orderItems, Auth::id(), $deliveryFee);
        } catch (OutOfStockException $e) {
            flash('errors', $e->getMessage(), 'error');
            redirect('/cart');
        }

        Cart::clear();
        \App\Core\Throttle::reset('checkout');
        Session::set('last_order_id', $orderId);
        Session::regenerate(); // تحديث معرّف الجلسة بعد عملية حساسة

        flash('order', 'تم استلام طلبك بنجاح! رقم الطلب #' . $orderId, 'success');
        redirect('/order/' . $orderId);
    }

    /** صفحة تأكيد الطلب: /order/{id} */
    public function order(string $id): void
    {
        $orderId = (int) $id;
        $order = OrderRepository::find($orderId);

        $isOwner = $order !== null && (
            (int) Session::get('last_order_id') === $orderId
            || (Auth::id() !== null && (int) $order['user_id'] === Auth::id())
            || Auth::roleIs(['admin', 'seller'])
        );

        if (!$isOwner) {
            \App\Core\Errors::notFoundPage();
            return;
        }

        $this->view('orders/success', [
            'order' => $order,
            'items' => OrderRepository::items($orderId),
            'page' => [
                'title' => 'تأكيد الطلب #' . $orderId . ' - ' . env('APP_NAME', 'المتجر الليبي'),
                'description' => 'تم استلام طلبك في المتجر الليبي.',
                'canonical' => '/order/' . $orderId,
                'noindex' => true,
            ],
        ]);
    }
}