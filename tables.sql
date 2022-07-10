CREATE TABLE IF NOT EXISTS bid (
    id uuid DEFAULT gen_random_uuid() PRIMARY KEY,
    buyer_phone VARCHAR(8) NULL,
    basket TEXT NOT NULL,
    tax INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT NOW() NOT NULL,
    sold_at TIMESTAMP NULL
)