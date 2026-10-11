<?php

declare(strict_types=1);

/**
 * بيانات تجريبية للمنتجات — مطاعم ومقاهي وملابس (للاختبار فقط).
 * الحقول: name, slug, category, price, old_price, stock, emoji, color, tagline, description, features, featured, created_at
 */
return [
    // ===== ملابس نسائية =====
    [
        'id' => 1, 'name' => 'عباية كريب مطرّزة', 'slug' => 'embroidered-abaya', 'category' => 'fashion-women',
        'price' => 185.0, 'old_price' => 220.0, 'stock' => 12, 'emoji' => '🥻', 'color' => '#fbcfe8',
        'tagline' => 'تطريز يدوي فاخر مناسب للمناسبات',
        'description' => 'عباية كريب أصلي بتطريز يدوي على الأكمام والصدر، خامة ثقيلة لا تتجعد وتنسدل بشكل أنيق. متوفرة بقياسات من S إلى XXL بلونين.',
        'features' => ['قماش كريب أصلي', 'تطريز يدوي فاخر', 'قياسات S حتى XXL'],
        'featured' => true, 'created_at' => '2026-10-08',
    ],
    [
        'id' => 2, 'name' => 'فستان صيفي مزهر', 'slug' => 'floral-summer-dress', 'category' => 'fashion-women',
        'price' => 95.0, 'old_price' => null, 'stock' => 20, 'emoji' => '👗', 'color' => '#fbcfe8',
        'tagline' => 'خفيف ومريح للجوي الحار',
        'description' => 'فستان صيفي بنقوشة مزهرة من قماش قطني خفيف، مبطّن بالكامل وسهل الغسل. مناسب للخروج اليومي والنزهات.',
        'features' => ['قطن 100%', 'خفيف ومسامي', 'قياسات M حتى XXL'],
        'featured' => false, 'created_at' => '2026-10-05',
    ],
    [
        'id' => 3, 'name' => 'بلوزة حرير', 'slug' => 'silk-blouse', 'category' => 'fashion-women',
        'price' => 70.0, 'old_price' => 85.0, 'stock' => 25, 'emoji' => '👚', 'color' => '#fbcfe8',
        'tagline' => 'نعومة الحرير بلمسة رسمية',
        'description' => 'بلوزة من الحرير الصناعي المريح بأكمام طويلة وأزرار مخفية، تناسب العمل والمناسبات الرسمية.',
        'features' => ['حرير ناعم', 'أزرار مخفية', 'سهلة الكي'],
        'featured' => false, 'created_at' => '2026-10-02',
    ],
    [
        'id' => 4, 'name' => 'جينز نسائي سترتش', 'slug' => 'stretch-jeans-women', 'category' => 'fashion-women',
        'price' => 85.0, 'old_price' => null, 'stock' => 15, 'emoji' => '👖', 'color' => '#fbcfe8',
        'tagline' => 'مريح ويطابق جميع الأجسام',
        'description' => 'جينز نسائي بخامة سترتش عالية الجودة، خصر عالي وقصّة مستقيمة تمنح الراحة طوال اليوم.',
        'features' => ['خامة سترتش', 'خصر عالي', 'قياسات 28 حتى 36'],
        'featured' => false, 'created_at' => '2026-09-29',
    ],

    // ===== ملابس رجالية =====
    [
        'id' => 5, 'name' => 'قميص كتان صيفي', 'slug' => 'linen-shirt-men', 'category' => 'fashion-men',
        'price' => 75.0, 'old_price' => null, 'stock' => 18, 'emoji' => '👔', 'color' => '#bfdbfe',
        'tagline' => 'يمنع الحرارة ويسمح بمرور الهواء',
        'description' => 'قميص رجالي من الكتان الخالص، أكمام قابلة للطي وألوان هادئة. مثالي للأجواء الحارة في طرابلس.',
        'features' => ['كتان خالص', 'أكمام قابلة للطي', 'قياسات M حتى XXL'],
        'featured' => false, 'created_at' => '2026-10-06',
    ],
    [
        'id' => 6, 'name' => 'ثوب رجالي كلاسيك', 'slug' => 'classic-thobe-men', 'category' => 'fashion-men',
        'price' => 140.0, 'old_price' => 165.0, 'stock' => 10, 'emoji' => '🥼', 'color' => '#bfdbfe',
        'tagline' => 'خياطة متقنة وقماش فاخر',
        'description' => 'ثوب رجالي بخياطة مزدوجة وقماش لا يتأثر بالعصر، جيوب داخلية وتصميم كلاسيكي أنيق.',
        'features' => ['خياطة مزدوجة', 'جيوب داخلية', 'قياسات 50 حتى 60'],
        'featured' => true, 'created_at' => '2026-10-01',
    ],
    [
        'id' => 7, 'name' => 'جاكيت جينز رجالي', 'slug' => 'denim-jacket-men', 'category' => 'fashion-men',
        'price' => 120.0, 'old_price' => null, 'stock' => 12, 'emoji' => '🧥', 'color' => '#bfdbfe',
        'tagline' => 'كلاسيكي يناسب كل الإطلالات',
        'description' => 'جاكيت جينز بقصّة عصرية وأزرار معدنية، مبطّن بطبقة خفيفة تحمي من رياح الشتاء.',
        'features' => ['جينز ثقيل', 'بطانة خفيفة', 'جيوب أمامية'],
        'featured' => false, 'created_at' => '2026-09-27',
    ],
    [
        'id' => 8, 'name' => 'حذاء رياضي رجالي', 'slug' => 'men-sneakers', 'category' => 'fashion-men',
        'price' => 110.0, 'old_price' => 130.0, 'stock' => 14, 'emoji' => '👟', 'color' => '#bfdbfe',
        'tagline' => 'نعل مضاد للانزلاق',
        'description' => 'حذاء رياضي خفيف بنعل مطّاطي مضاد للانزلاق وجزء علوي شبكي يسمح بتهوية القدم.',
        'features' => ['نعل مطّاطي', 'جزء شبكي مهوّى', 'مقاسات 39 حتى 45'],
        'featured' => false, 'created_at' => '2026-09-24',
    ],

    // ===== مقاهي - مشروبات =====
    [
        'id' => 9, 'name' => 'قهوة عربية بالهيل', 'slug' => 'arabic-coffee-hail', 'category' => 'cafes',
        'price' => 12.0, 'old_price' => null, 'stock' => 60, 'prep_minutes' => 5, 'emoji' => '☕', 'color' => '#fed7aa',
        'tagline' => 'تحميص أسبوعي وطحنة فاخرة',
        'description' => 'قهوة عربية محمّصة أسبوعيًا مع الهيل والزعفران، تُحضَّر على الطريقة التقليدية وتُقدَّم مع تمر.',
        'features' => ['تحميص طازج', 'هيل وزعفران', 'كوب 150 مل'],
        'featured' => true, 'created_at' => '2026-10-09',
    ],
    [
        'id' => 10, 'name' => 'كابتشينو كبير', 'slug' => 'large-cappuccino', 'category' => 'cafes',
        'price' => 15.0, 'old_price' => null, 'stock' => 50, 'prep_minutes' => 7, 'emoji' => '☕', 'color' => '#fed7aa',
        'tagline' => 'رغوة كثيفة وحبوب عربية',
        'description' => 'كابتشينو بحبوب محمصة فاخرة ورغوة حليب كثيفة، يمكن تخصيصه بحليب لوز أو شوفان.',
        'features' => ['حبوب مطحونة طازجًا', 'حجم 350 مل', 'حليب مخفوق ناعم'],
        'featured' => false, 'created_at' => '2026-10-07',
    ],
    [
        'id' => 11, 'name' => 'آيس لاتيه', 'slug' => 'iced-latte', 'category' => 'cafes',
        'price' => 16.0, 'old_price' => 18.0, 'stock' => 45, 'prep_minutes' => 5, 'emoji' => '🥤', 'color' => '#fed7aa',
        'tagline' => 'منعش لصباحات طرابلس الحارة',
        'description' => 'آيس لاتيه بجرعتين من الإسبريسو وحليب بارد ومكعبات ثلج، يُقدَّم بحجم كبير مناسب للطريق.',
        'features' => ['جرعتان إسبريسو', 'حجم 450 مل', 'يُقدَّم بارداً'],
        'featured' => true, 'created_at' => '2026-10-04',
    ],
    [
        'id' => 12, 'name' => 'شاي أخضر بالنعناع', 'slug' => 'green-mint-tea', 'category' => 'cafes',
        'price' => 10.0, 'old_price' => null, 'stock' => 40, 'prep_minutes' => 4, 'emoji' => '🍵', 'color' => '#fed7aa',
        'tagline' => 'نعناع طازج من السوق',
        'description' => 'شاي أخضر صيني مع نعناع طازج يُقطف يوميًا، يُقدَّم ساخنًا أو باردًا حسب الطلب.',
        'features' => ['نعناع طازج', 'أكياس شاي فاخرة', 'كوب 250 مل'],
        'featured' => false, 'created_at' => '2026-09-30',
    ],

    // ===== حلويات =====
    [
        'id' => 13, 'name' => 'كنافة بالقشطة', 'slug' => 'kunafa-cream', 'category' => 'desserts',
        'price' => 25.0, 'old_price' => null, 'stock' => 20, 'prep_minutes' => 15, 'emoji' => '🍰', 'color' => '#fde68a',
        'tagline' => 'تُخبز عند الطلب وتُقدَّم ساخنة',
        'description' => 'كنافة خشنة بجبن قشطة طازج وقطر خفيف بالمقادير المعتادة، تُخبز عند الطلب لتصل إليك ساخنة.',
        'features' => ['قشطة طازجة', 'تُخبز عند الطلب', 'حجم يكفي شخصين'],
        'featured' => true, 'created_at' => '2026-10-08',
    ],
    [
        'id' => 14, 'name' => 'تشيز كيك توت', 'slug' => 'berry-cheesecake', 'category' => 'desserts',
        'price' => 22.0, 'old_price' => 26.0, 'stock' => 18, 'prep_minutes' => 5, 'emoji' => '🧁', 'color' => '#fde68a',
        'tagline' => 'طبقة توت طبيعية',
        'description' => 'تشيز كيك كريمي على قاعدة بسكويت زبدية مع صلصة توت طبيعية، يُحفظ مبرّدًا ويُقدَّم بارداً.',
        'features' => ['توت طبيعي', 'بدون مواد حافظة', 'قطعة واحدة'],
        'featured' => false, 'created_at' => '2026-10-03',
    ],

    // ===== مطاعم - وجبات =====
    [
        'id' => 15, 'name' => 'منسف لحم', 'slug' => 'lamb-mansaf', 'category' => 'restaurants',
        'price' => 45.0, 'old_price' => null, 'stock' => 30, 'prep_minutes' => 20, 'emoji' => '🍚', 'color' => '#bbf7d0',
        'tagline' => 'لحم طري على أرز بالشعيرية',
        'description' => 'منسف لحم بلدي مطبوخ على نار هادئة فوق أرز بالشعيرية مع الجميد، يكفي شخصين مع المقبلات.',
        'features' => ['لحم بلدي', 'يكفي شخصين', 'يشمل المقبلات'],
        'featured' => true, 'created_at' => '2026-10-09',
    ],
    [
        'id' => 16, 'name' => 'مندي دجاج', 'slug' => 'chicken-mandi', 'category' => 'restaurants',
        'price' => 38.0, 'old_price' => 42.0, 'stock' => 35, 'prep_minutes' => 25, 'emoji' => '🍗', 'color' => '#bbf7d0',
        'tagline' => 'مدخّن بطعم التوابل اليمنية',
        'description' => 'دجاج كامل مدخّن على أرز مندي بتوابل خاصة، يُقدَّم مع صلصة الدقوس والسلطة.',
        'features' => ['دخان طبيعي', 'أرز مندي', 'يشمل صلصة وسلطة'],
        'featured' => false, 'created_at' => '2026-10-06',
    ],
    [
        'id' => 17, 'name' => 'برياني دجاج', 'slug' => 'chicken-biryani', 'category' => 'restaurants',
        'price' => 35.0, 'old_price' => null, 'stock' => 30, 'prep_minutes' => 20, 'emoji' => '🍛', 'color' => '#bbf7d0',
        'tagline' => 'توابل هندية دافئة',
        'description' => 'برياني بحبّات أرز بسمتي ودجاج متبّل بالتوابل الهندية مع اللوز والزبيب، حارق مناسب لعشاق البهارات.',
        'features' => ['أرز بسمتي', 'دجاج طازج', 'حارق قليلًا'],
        'featured' => false, 'created_at' => '2026-10-02',
    ],

    // ===== وجبات سريعة =====
    [
        'id' => 18, 'name' => 'بيتزا مارغريتا', 'slug' => 'margherita-pizza', 'category' => 'fastfood',
        'price' => 28.0, 'old_price' => 32.0, 'stock' => 25, 'prep_minutes' => 15, 'emoji' => '🍕', 'color' => '#fecaca',
        'tagline' => 'عجين رقيق وجبنة موزاريلا',
        'description' => 'بيتزا عجين رقيق بصلصة طماطم طازجة وموزاريلا أصلي وريحان، تُخبز في فرن حجري ساخن.',
        'features' => ['فرن حجري', 'موزاريلا أصلي', 'حجم 30 سم'],
        'featured' => true, 'created_at' => '2026-10-10',
    ],
    [
        'id' => 19, 'name' => 'برجر لحم مشوي', 'slug' => 'grilled-beef-burger', 'category' => 'fastfood',
        'price' => 22.0, 'old_price' => null, 'stock' => 40, 'prep_minutes' => 10, 'emoji' => '🍔', 'color' => '#fecaca',
        'tagline' => 'لحم طازج بدون مجمّدات',
        'description' => 'برجر لحم مشوي على الفحم مع جبن شيدر وخلطة خضار، يُقدَّم مع بطاطس مقلية كبيرة.',
        'features' => ['لحم طازج', 'مشوي على الفحم', 'يشمل بطاطس'],
        'featured' => false, 'created_at' => '2026-10-07',
    ],
    [
        'id' => 20, 'name' => 'شاورما دجاج', 'slug' => 'chicken-shawarma', 'category' => 'fastfood',
        'price' => 15.0, 'old_price' => null, 'stock' => 50, 'prep_minutes' => 8, 'emoji' => '🌯', 'color' => '#fecaca',
        'tagline' => 'تُلفّ أمامك مباشرة',
        'description' => 'شاورما دجاج بتتبيلة الثوم والليمون، ملفوفة بخبز صاج مع ثوم وطرشي وتُقدَّم مع البطاطس.',
        'features' => ['دجاج طازج', 'ثوم وطرشي', 'خبز صاج رقيق'],
        'featured' => false, 'created_at' => '2026-10-05',
    ],
];
