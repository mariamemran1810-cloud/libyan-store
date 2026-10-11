-- =====================================================
-- مخطط قاعدة البيانات للمتجر الليبي (SQLite)
-- يُنفَّذ بأمر:  php scripts/migrate.php
-- =====================================================

-- الأقسام
CREATE TABLE IF NOT EXISTS categories (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL,
    slug        TEXT    NOT NULL UNIQUE,
    emoji       TEXT    NOT NULL DEFAULT '',
    color       TEXT    NOT NULL DEFAULT '#e5e7eb',
    description TEXT    NOT NULL DEFAULT '',
    position    INTEGER NOT NULL DEFAULT 0
);

-- المنتجات (الأسعار بالدينار الليبي)
CREATE TABLE IF NOT EXISTS products (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
    name        TEXT    NOT NULL,
    slug        TEXT    NOT NULL UNIQUE,
    price       REAL    NOT NULL DEFAULT 0,
    old_price   REAL    NULL,
    stock       INTEGER NOT NULL DEFAULT 0,
    prep_minutes INTEGER NOT NULL DEFAULT 0,
    emoji       TEXT    NOT NULL DEFAULT '',
    color       TEXT    NOT NULL DEFAULT '#e5e7eb',
    tagline     TEXT    NOT NULL DEFAULT '',
    description TEXT    NOT NULL DEFAULT '',
    features    TEXT    NOT NULL DEFAULT '[]',
    image       TEXT    NULL,
    featured    INTEGER NOT NULL DEFAULT 0,
    is_active   INTEGER NOT NULL DEFAULT 1,
    created_at  TEXT    NOT NULL DEFAULT (datetime('now')),
    updated_at  TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_active   ON products(is_active);
CREATE INDEX IF NOT EXISTS idx_products_price    ON products(price);

-- المستخدمون (roles: admin = مسؤول، seller = بائعة، customer = عميلة)
CREATE TABLE IF NOT EXISTS users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT    NOT NULL,
    email         TEXT    NOT NULL UNIQUE,
    phone         TEXT    NULL,
    password_hash TEXT    NOT NULL,
    role          TEXT    NOT NULL DEFAULT 'customer',
    is_active     INTEGER NOT NULL DEFAULT 1,
    created_at    TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

-- الطلبات
CREATE TABLE IF NOT EXISTS orders (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
    customer_name   TEXT    NOT NULL,
    customer_phone  TEXT    NOT NULL,
    customer_city   TEXT    NOT NULL,
    customer_address TEXT   NOT NULL,
    notes           TEXT    NULL,
    delivery_fee    REAL    NOT NULL DEFAULT 0,
    status          TEXT    NOT NULL DEFAULT 'pending',
    total           REAL    NOT NULL DEFAULT 0,
    created_at      TEXT    NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_user   ON orders(user_id);

-- بنود الطلب
CREATE TABLE IF NOT EXISTS order_items (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id     INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id   INTEGER NULL REFERENCES products(id) ON DELETE SET NULL,
    product_name TEXT    NOT NULL,
    price        REAL    NOT NULL,
    qty          INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id);

-- إعدادات المتجر (مفتاح/قيمة): رسوم التوصيل، ملاحظات، هاتف...
CREATE TABLE IF NOT EXISTS settings (
    key        TEXT NOT NULL PRIMARY KEY,
    value      TEXT NOT NULL DEFAULT '',
    updated_at TEXT NOT NULL DEFAULT (datetime('now'))
);

-- القيم الافتراضية (تُعدَّل من لوحة الإدارة)
INSERT OR IGNORE INTO settings (key, value) VALUES
    ('delivery_cities', 'طرابلس:5, بنغازي:8, مصراتة:6, زليتن:5, سرت:7, البيضاء:7, درنة:8, الخمس:6, مزدة:7'),
    ('delivery_note',   'التوصيل داخل المدينة خلال ساعة تقريبًا، والمدن الأخرى خلال 24 ساعة.'),
    ('store_phone',     '0912345678');