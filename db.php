<?php
$db = new SQLite3('database.db');

// Initialisiere die Datenbank
$db->exec("CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_name TEXT NOT NULL,
    supplier_number TEXT,
    article_number TEXT NOT NULL,
    image_path TEXT,
    barcode_url TEXT
)");

function addProduct($product_name, $supplier_number, $article_number, $image_path, $barcode_url) {
    global $db;
    $stmt = $db->prepare("INSERT INTO products (product_name, supplier_number, article_number, image_path, barcode_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $product_name, SQLITE3_TEXT);
    $stmt->bindValue(2, $supplier_number, SQLITE3_TEXT);
    $stmt->bindValue(3, $article_number, SQLITE3_TEXT);
    $stmt->bindValue(4, $image_path, SQLITE3_TEXT);
    $stmt->bindValue(5, $barcode_url, SQLITE3_TEXT);
    $stmt->execute();
}

function getProducts() {
    global $db;
    $result = $db->query("SELECT * FROM products");
    $products = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $products[] = $row;
    }
    return $products;
}
?>
