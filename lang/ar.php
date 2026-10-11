<?php

declare(strict_types=1);

/**
 * النصوص العربية (اللغة الافتراضية).
 * المفاتيح مسطحة: 'nav.home' => 'الرئيسية'
 */
return [
    // عام
    'app.name' => 'المتجر الليبي',

    // القائمة
    'nav.home' => 'الرئيسية',
    'nav.products' => 'المنتجات',
    'nav.categories' => 'الأقسام',
    'nav.cart' => 'السلة',
    'nav.account' => 'حسابي',
    'nav.login' => 'تسجيل الدخول',
    'nav.register' => 'إنشاء حساب',
    'nav.logout' => 'تسجيل الخروج',
    'nav.admin' => 'لوحة الإدارة',
    'nav.search_placeholder' => 'ابحثي عن منتج...',

    // أزرار
    'buttons.search' => 'بحث',
    'buttons.browse_products' => 'تصفّح المنتجات',
    'buttons.back_home' => 'العودة للرئيسية',
    'buttons.add_to_cart' => 'أضف إلى السلة',
    'buttons.view_details' => 'عرض التفاصيل',
    'buttons.submit' => 'إرسال',
    'buttons.save' => 'حفظ',
    'buttons.cancel' => 'إلغاء',
    'buttons.delete' => 'حذف',
    'buttons.edit' => 'تعديل',
    'buttons.continue_shopping' => 'متابعة التسوق',
    'buttons.checkout' => 'إتمام الطلب',
    'buttons.confirm_order' => 'تأكيد الطلب',
    'buttons.back_to_cart' => 'العودة للسلة',

    // التذييل
    'footer.tagline' => 'تسوّق ليبي بسيط وآمن بالدينار الليبي.',
    'footer.currency' => 'العملة',
    'footer.rights' => 'كل الحقوق محفوظة',

    // الأخطاء
    'errors.404_title' => 'الصفحة غير موجودة',
    'errors.404_message' => 'عذرًا، الصفحة التي تبحثين عنها غير موجودة.',
    'errors.500_title' => 'حدث خطأ غير متوقع',
    'errors.500_message' => 'نعتذر، حدث مشكلة من جانبنا. تم تسجيل التفاصيل وسيتم إصلاحها قريبًا.',
    'errors.forbidden' => 'ليس لديك صلاحية للوصول إلى هذه الصفحة.',
    'errors.unauthorized' => 'يجب تسجيل الدخول أولًا.',

    // التحقق من المدخلات
    'validation.required' => 'الحقل :field مطلوب.',
    'validation.email' => 'الحقل :field يجب أن يكون بريدًا إلكترونيًا صحيحًا.',
    'validation.url' => 'الحقل :field يجب أن يكون رابطًا صحيحًا.',
    'validation.min' => 'الحقل :field يجب ألا يقل عن :min حرفًا.',
    'validation.max' => 'الحقل :field يجب ألا يتجاوز :max حرفًا.',
    'validation.numeric' => 'الحقل :field يجب أن يكون رقمًا.',
    'validation.integer' => 'الحقل :field يجب أن يكون رقمًا صحيحًا.',
    'validation.min_value' => 'الحقل :field يجب أن يكون :min أو أكبر.',
    'validation.max_value' => 'الحقل :field يجب أن يكون :max أو أقل.',
    'validation.in' => 'قيمة الحقل :field غير مسموح بها.',
    'validation.confirmed' => 'تأكيد الحقل :field غير مطابق.',
    'validation.slug' => 'الحقل :field يجب أن يحتوي على حروف لاتينية وأرقام وشرطات فقط.',
    'validation.date' => 'الحقل :field يجب أن يكون تاريخًا صحيحًا (YYYY-MM-DD).',
    'validation.boolean' => 'الحقل :field يجب أن يكون قيمة صحيحة.',

    // الصفحة الرئيسية
    'home.hero_title' => 'كل ما تحتاجينه في مكان واحد',
    'home.hero_text' => 'تسوّقي منتجات مختارة بعناية بأسعار بالدينار الليبي، مع خيارات دفع وتوصيل داخل ليبيا.',
    'home.hero_cta' => 'تصفّحي المنتجات',
    'home.sections_title' => 'تسوّقي حسب القسم',
    'home.featured_title' => 'منتجات مميزة',
    'home.latest_title' => 'وصل حديثًا',
    'home.view_all' => 'عرض الكل',
    'home.trust_shipping_title' => 'توصيل سريع',
    'home.trust_shipping_text' => 'شحن لجميع المدن الليبية خلال أيام قليلة.',
    'home.trust_pay_title' => 'دفع مرن',
    'home.trust_pay_text' => 'الدفع عند الاستلام أو تحويل بنكي (يُفعَّل لاحقًا).',
    'home.trust_returns_title' => 'استبدال سهل',
    'home.trust_returns_text' => 'إمكانية الاستبدال خلال 3 أيام من الاستلام.',

    // المنتجات
    'products.title' => 'كل المنتجات',
    'products.search_results' => 'نتائج البحث عن: :q',
    'products.search_placeholder' => 'ابحثي عن منتج...',
    'products.search_button' => 'بحث',
    'products.all_categories' => 'كل الأقسام',
    'products.sort_label' => 'الترتيب',
    'products.sort_newest' => 'الأحدث',
    'products.sort_price_asc' => 'السعر: من الأقل',
    'products.sort_price_desc' => 'السعر: من الأعلى',
    'products.count' => ':count منتج',
    'products.no_results' => 'لا توجد منتجات مطابقة لبحثك. جرّبي كلمة أخرى أو تصفّحي كل الأقسام.',
    'products.in_stock' => 'متوفر (:count قطعة)',
    'products.out_of_stock' => 'نفد المخزون',
    'products.description' => 'الوصف',
    'products.features' => 'المميزات',
    'products.related' => 'منتجات مشابهة',
    'products.more_from_category' => 'منتجات القسم نفسه',
    'products.previous' => 'السابق',
    'products.next' => 'التالي',

    // الأقسام
    'categories.title' => 'أقسام المتجر',
    'categories.intro' => 'اختر القسم الذي تريدين وتصفّحي منتجاته.',
    'categories.count' => ':count منتج',

    // مسار التنقل
    'breadcrumbs.home' => 'الرئيسية',

    // السلة (مؤقت)
    'cart.title' => 'سلة المشتريات',
    'cart.coming_soon' => 'ميزة إضافة المنتجات إلى السلة ستفعَّل في المرحلة الرابعة. يمكنكِ الآن تصفح المنتجات والاطلاع على تفاصيلها.',

    // الحساب والدخول
    'auth.login_title' => 'تسجيل الدخول',
    'auth.login_intro' => 'أدخلي بريدك وكلمة المرور للوصول إلى حسابك.',
    'auth.register_title' => 'إنشاء حساب جديد',
    'auth.register_intro' => 'أنشئي حسابًا مجانًا لتتتبّعي طلباتك بسهولة.',
    'auth.name' => 'الاسم الكامل',
    'auth.email' => 'البريد الإلكتروني',
    'auth.phone' => 'رقم الهاتف',
    'auth.password' => 'كلمة المرور',
    'auth.password_confirm' => 'تأكيد كلمة المرور',
    'auth.password_hint' => '8 أحرف على الأقل.',
    'auth.no_account' => 'ليس لديك حساب؟',
    'auth.have_account' => 'لديك حساب بالفعل؟',
    'auth.invalid_credentials' => 'بيانات الدخول غير صحيحة.',
    'auth.too_many_attempts' => 'عدد محاولات الدخول كبير. انتظري دقائق ثم حاولي مجددًا.',
    'auth.email_taken' => 'هذا البريد مسجّل مسبقًا.',
    'auth.registered' => 'تم إنشاء حسابك بنجاح.',
    'auth.welcome_back' => 'أهلًا بعودتكِ، :name',
    'auth.logged_out' => 'تم تسجيل الخروج.',

    // صفحة الحساب
    'account.title' => 'حسابي — :name',
    'account.my_orders' => 'طلباتي',
    'account.no_orders' => 'لم تطلبي أي شيء بعد. ابدئي التسوق الآن!',
    'account.order_no' => 'رقم الطلب',
    'account.order_date' => 'التاريخ',
    'account.order_total' => 'الإجمالي',
    'account.order_status' => 'الحالة',

    // لوحة الإدارة
    'admin.dashboard' => 'لوحة التحكم',
    'admin.role_you' => 'دورك: :role',
    'admin.stat_products' => 'المنتجات',
    'admin.stat_orders' => 'الطلبات',
    'admin.stat_pending' => 'طلبات قيد المراجعة',
    'admin.stat_low_stock' => 'مخزون منخفض (5 أو أقل)',
    'admin.stat_users' => 'المستخدمون',
    'admin.recent_orders' => 'أحدث الطلبات',
    'admin.no_orders_yet' => 'لا توجد طلبات حتى الآن.',
    'admin.view_all_orders' => 'عرض كل الطلبات',
    'admin.products' => 'المنتجات',
    'admin.add_product' => 'إضافة منتج',
    'admin.orders' => 'الطلبات',
    'admin.users' => 'المستخدمون',
    'admin.orders_count' => 'عدد الطلبات: :count',
    'admin.users_count' => 'عدد المستخدمين: :count',
    'admin.col_product' => 'المنتج',
    'admin.col_category' => 'القسم',
    'admin.col_price' => 'السعر',
    'admin.col_stock' => 'المخزون',
    'admin.col_status' => 'الحالة',
    'admin.col_actions' => 'إجراءات',
    'admin.col_user' => 'المستخدم',
    'admin.col_joined' => 'تاريخ التسجيل',
    'admin.col_role' => 'الدور والحالة',
    'admin.published' => 'منشور',
    'admin.draft' => 'مسودة',
    'admin.customer' => 'العميلة',
    'admin.contact' => 'التواصل',
    'admin.address' => 'العنوان',
    'admin.items' => 'صنف',
    'admin.active' => 'مفعّل',
    'admin.disabled' => 'موقوف',
    'admin.you' => '(أنتِ)',
    'admin.f_name' => 'اسم المنتج',
    'admin.f_slug' => 'المعرّف في الرابط',
    'admin.f_slug_hint' => 'اتركيه فارغًا ليُولَّد تلقائيًا.',
    'admin.f_category' => 'القسم',
    'admin.f_stock' => 'الكمية المتوفرة',
    'admin.f_price' => 'السعر',
    'admin.f_old_price' => 'السعر قبل الخصم',
    'admin.f_old_price_hint' => 'اتركيه فارغًا إذا لا يوجد خصم.',
    'admin.f_emoji' => 'أيقونة (إيموجي)',
    'admin.f_color' => 'لون الخلفية',
    'admin.f_tagline' => 'وصف مختصر',
    'admin.f_description' => 'الوصف الكامل',
    'admin.f_features' => 'المميزات',
    'admin.f_features_hint' => 'ميزة واحدة في كل سطر.',
    'admin.f_image' => 'صورة المنتج',
    'admin.f_image_hint' => 'JPG أو PNG أو WEBP بحد أقصى :max ميجابايت.',
    'admin.f_active' => 'منشور للزوار',
    'admin.f_featured' => 'منتج مميز في الرئيسية',

    // السلة
    'cart.items_count' => 'عدد المنتجات: :count',
    'cart.empty' => 'سلتك فارغة حاليًا.',
    'cart.col_product' => 'المنتج',
    'cart.col_price' => 'السعر',
    'cart.col_qty' => 'الكمية',
    'cart.col_total' => 'الإجمالي',
    'cart.in_stock' => 'متوفر: :count',
    'cart.summary' => 'ملخص الطلب',
    'cart.subtotal' => 'المجموع الفرعي',
    'cart.shipping' => 'التوصيل',
    'cart.shipping_note' => 'يُحدد حسب المدينة عند التأكيد',
    'cart.total' => 'الإجمالي',
    'cart.secure_note' => 'لن تُخصم أي مبلغ الآن — نتصل بك لتأكيد الطلب.',
    'cart.updated' => 'تم تحديث السلة.',
    'cart.removed' => 'تم حذف المنتج من السلة.',
    'cart.added' => 'تمت إضافة المنتج إلى السلة.',

    // إتمام الطلب
    'checkout.title' => 'إتمام الطلب',
    'checkout.intro' => 'أدخلي بياناتك وسنتصل بك لتأكيد الطلب والتوصيل.',
    'checkout.customer_data' => 'بيانات العميلة',
    'checkout.city' => 'المدينة',
    'checkout.choose_city' => 'اختاري المدينة',
    'checkout.address' => 'العنوان بالتفصيل',
    'checkout.address_hint' => 'الحي، الشارع، أقرب معلم...',
    'checkout.notes' => 'ملاحظات إضافية',
    'checkout.notes_hint' => 'مثال: الوقت المناسب للاتصال.',
    'checkout.your_order' => 'طلبك',
    'checkout.payment_note' => 'طريقة الدفع: نقدًا عند الاستلام أو تحويل بنكي بعد تأكيد الطلب.',

    // تأكيد الطلب
    'order.success_title' => 'تم استلام طلبك بنجاح — #:id',
    'order.success_text' => 'شكرًا لثقتكِ! سيتواصل معك فريقنا خلال ساعات لتأكيد الطلب وترتيب التوصيل.',
    'order.contact_note' => 'احتفظي برقم طلبك للاستفسار عنه في أي وقت.',

    // الأقسام (إدارة)
    'admin.categories' => 'الأقسام',
    'admin.add_category' => 'إضافة قسم',
    'admin.col_slug' => 'الرابط',
    'admin.col_products_count' => 'عدد المنتجات',

    // لوحة التحكم (إضافات)
    'admin.stat_revenue' => 'إجمالي المبيعات',
    'admin.low_stock_banner' => 'تنبيه مخزون منخفض (:count منتج)! يُنصح بإعادة الطلب.',
    'admin.stock_left' => 'المتبقي: :count',
    'admin.order_details' => 'تفاصيل الطلب',
    'admin.order_summary' => 'ملخص الطلب',
    'admin.linked_account' => 'مرتبط بحساب',
    'admin.guest_order' => 'طلب من زائرة بدون حساب',

    // تغيير كلمة المرور
    'password.title' => 'تغيير كلمة المرور',
    'password.intro' => 'تأكدي من كلمة المرور الحالية قبل التغيير. سيُطلب منك الدخول مجددًا بعد التغيير.',
    'password.current' => 'كلمة المرور الحالية',
    'password.new' => 'كلمة المرور الجديدة',
    'password.confirm' => 'تأكيد كلمة المرور الجديدة',

    // الإعدادات (إدارة)
    'admin.settings' => 'إعدادات المتجر',
    'admin.settings_intro' => 'رسوم التوصيل لكل مدينة، ملاحظة التوصيل، وهاتف المتجر الذي يظهر للعميلات.',
    'admin.f_delivery_cities' => 'مدن التوصيل ورسومها',
    'admin.delivery_hint' => 'كل مدينة مع رسومها بالدينار الليبي. تظهر هذه القائمة للعميلة عند اختيار المدينة.',
    'admin.f_city_name' => 'اسم المدينة',
    'admin.f_delivery_fee' => 'رسم التوصيل (د.ل)',
    'admin.add_city' => 'إضافة مدينة',
    'admin.f_delivery_note' => 'ملاحظة التوصيل',
    'admin.f_store_phone' => 'هاتف المتجر',
    'checkout.fee_by_city' => 'يُحسب حسب المدينة',

    // المنتج
    'admin.f_prep' => 'مدة التحضير بالدقائق',
    'admin.f_prep_hint' => 'اتركيها فارغة أو صفراً للملابس والأغراض غير الغذائية.',
    'product.prep_time' => '⏱️ التحضير: :min دقيقة',
    'product.prep_time_short' => '⏱️ :min دقيقة',

    // اللغة
    'lang.switch' => 'English',
];