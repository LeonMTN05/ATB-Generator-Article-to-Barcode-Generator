<?php
require 'db.php'; // Verbindet sich mit der Datenbank und enthält die addProduct()-Funktion

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formulardaten abrufen
    $product_name = $_POST['product_name'];
    $supplier_number = $_POST['supplier_number'] ?? ''; // Optional
    $article_number = $_POST['article_number'];

    // Prüfen, ob Pflichtfelder ausgefüllt sind
    if (!$product_name || !$article_number) {
        die("Produktname und Artikelnummer sind Pflichtfelder!");
    }

    // Bild hochladen
    $image_path = '';
    if (!empty($_FILES['product_image']['name'])) {
        $upload_dir = 'uploads/';
        $image_path = $upload_dir . basename($_FILES['product_image']['name']);
        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $image_path)) {
            die("Fehler beim Hochladen des Bildes.");
        }
    }

    // Barcode generieren (immer basierend auf Lieferantennummer und Artikelnummer)
    $barcode_data = $supplier_number . $article_number;
    $barcode_url = "https://barcodeapi.org/api/auto/$barcode_data";

    // Produkt in die Datenbank einfügen
    addProduct($product_name, $supplier_number, $article_number, $image_path, $barcode_url);

    // Weiterleitung zur Artikelübersicht
    header('Location: products.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar {
            background-color: #e0e0e0; /* Grau für den Header */
        }
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            max-height: 50px; /* Logo-Größe */
            margin-right: 10px;
        }
        .navbar-brand .divider {
            height: 50px;
            width: 1px;
            background-color: #6c757d; /* Grau für die Linie */
            margin: 0 10px;
        }
        .btn-primary {
            background-color: #99c01b;
            border-color: #99c01b;
        }
        .btn-primary:hover {
            background-color: #88a819;
            border-color: #88a819;
        }
        .nav-buttons {
            margin-left: auto;
        }
        .nav-buttons a {
            margin-left: 10px;
        }
		
        footer {
            background-color: #99c01b; /* Grüner Farbton */
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }	
    </style>
</head>
<body>
    <!-- Navigation mit angepasstem Header und Divider -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/logo.png" alt="Logo">
                <div class="divider"></div> <!-- Horizontale Linie -->
                Product management
            </a>
            <div class="nav-buttons">
                <a href="index.php" class="btn btn-primary">Add article</a>
                <a href="products.php" class="btn btn-primary">Article overview</a>
                <a href="export.php" class="btn btn-primary">
                    <i class="bi bi-download"></i> Data export
                </a>
            </div>
        </div>
    </nav>

    <!-- Formular -->
    <div class="container">
        <h1 class="text-center mb-4">Add product</h1>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="product_name" class="form-label">Product name*</label>
                <input type="text" class="form-control" id="product_name" name="product_name" required>
            </div>
            <div class="mb-3">
                <label for="supplier_number" class="form-label">Supplier number</label>
                <input type="text" class="form-control" id="supplier_number" name="supplier_number">
            </div>
            <div class="mb-3">
                <label for="article_number" class="form-label">Item number*</label>
                <input type="text" class="form-control" id="article_number" name="article_number" required>
            </div>
            <div class="mb-3">
                <label for="product_image" class="form-label">Product image</label>
                <input type="file" class="form-control" id="product_image" name="product_image">
            </div>
            <button type="submit" class="btn btn-primary w-100">Add article</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
    <footer>
        &copy; Open-Source-Project from <a href="https://github.com/LeonMTN05">LeonMTN05</a>
    </footer>
</html>
