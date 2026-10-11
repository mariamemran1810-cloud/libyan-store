<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Csrf;
use App\Core\Validate;
use App\Models\SettingRepository;

/**
 * إعدادات المتجر — للمسؤولة فقط (role:admin في routes).
 * رسوم التوصيل لكل مدينة، ملاحظة التوصيل، وهاتف المتجر.
 */
final class SettingsAdminController extends Controller
{
    public function index(): void
    {
        $this->view('admin/settings/index', [
            'cities' => SettingRepository::deliveryCities(),
            'deliveryNote' => SettingRepository::deliveryNote(),
            'storePhone' => SettingRepository::storePhone(),
            'page' => [
                'title' => 'إعدادات المتجر - ' . env('APP_NAME', 'المتجر الليبي'),
                'canonical' => '/admin/settings',
                'noindex' => true,
            ],
        ]);
    }

    public function update(): void
    {
        Csrf::verify();

        $errors = Validate::check($_POST, [
            'delivery_note' => ['required', 'max:300'],
            'store_phone' => ['required', 'max:30'],
        ], [
            'delivery_note' => 'ملاحظة التوصيل',
            'store_phone' => 'هاتف المتجر',
        ]);

        if ($errors !== []) {
            flash('errors', implode(' — ', $errors), 'error');
            redirect('/admin/settings');
        }

        // المدن والرسوم من صفوف النموذج (city_name[i] / city_fee[i])
        $names = is_array($_POST['city_name'] ?? null) ? $_POST['city_name'] : [];
        $fees = is_array($_POST['city_fee'] ?? null) ? $_POST['city_fee'] : [];

        $cities = [];
        foreach (array_values($names) as $index => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            if (mb_strlen($name) > 60) {
                flash('errors', 'اسم المدينة أطول من 60 حرفًا.', 'error');
                redirect('/admin/settings');
            }

            $feeRaw = (string) ($fees[$index] ?? '0');
            if (!is_numeric($feeRaw) || (float) $feeRaw < 0) {
                flash('errors', 'رسوم التوصيل يجب أن يكون رقمًا موجبًا (المدينة: ' . $name . ').', 'error');
                redirect('/admin/settings');
            }

            $cities[$name] = (float) $feeRaw;
        }

        if ($cities === []) {
            flash('errors', 'أضيفي مدينة واحدة على الأقل مع رسومها.', 'error');
            redirect('/admin/settings');
        }

        SettingRepository::setDeliveryCities($cities);
        SettingRepository::put('delivery_note', trim((string) $_POST['delivery_note']));
        SettingRepository::put('store_phone', trim((string) $_POST['store_phone']));

        flash('admin', 'تم حفظ إعدادات المتجر (' . count($cities) . ' مدينة).', 'success');
        redirect('/admin/settings');
    }
}
