<?php
require 'db.php';

// Artikel löschen, wenn der Lösch-Button geklickt wird
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bindValue(1, $id, SQLITE3_INTEGER);
    $stmt->execute();

    // Weiterleitung nach dem Löschen, um den aktuellen Zustand der Tabelle zu sehen
    header("Location: products.php");
    exit;
}

$products = getProducts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article overview</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
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
        .btn-danger:hover {
            background-color: #d9534f;
        }
        .nav-buttons {
            margin-left: auto;
        }
        .nav-buttons a {
            margin-left: 10px;
        }
        .container {
            max-width: 900px;
            margin-top: 50px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .table img {
            max-height: 50px;
            cursor: pointer; /* Zeigt an, dass das Bild klickbar ist */
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

    <!-- Artikelübersicht -->
    <div class="container">
        <h1 class="text-center mb-4">Article overview</h1>
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Product name</th>
                    <th>Supplier number</th>
                    <th>Item number</th>
                    <th>Picture</th>
                    <th>Barcode</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><?= $product['product_name'] ?></td>
                    <td><?= $product['supplier_number'] ?></td>
                    <td><?= $product['article_number'] ?></td>
                    <td>
                        <?php if ($product['image_path']): ?>
                            <img src="<?= $product['image_path'] ?>" alt="Bild" class="img-thumbnail" data-bs-toggle="modal" data-bs-target="#imageModal" data-src="<?= $product['image_path'] ?>">
                        <?php else: ?>
                            <span>No picture</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm view-barcode" data-barcode="<?= $product['supplier_number'] . $product['article_number'] ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="barcode-container mt-2" style="display: none;"></div>
                    </td>
                    <td>
                        <a href="products.php?delete=<?= $product['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this article?')">
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal für größere Bildansicht -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Show picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Bild" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('img[data-bs-target="#imageModal"]').forEach(img => {
            img.addEventListener('click', function() {
                const modalImage = document.getElementById('modalImage');
                modalImage.src = this.getAttribute('data-src');
            });
        });

        // Zeigt den Barcode, wenn der Button gedrückt wird
        document.querySelectorAll('.view-barcode').forEach(button => {
            button.addEventListener('click', function() {
                const barcodeData = this.getAttribute('data-barcode');
                const container = this.nextElementSibling;

                if (container.style.display === 'none') {
                    // Barcode von der API abrufen
                    fetch(`https://barcodeapi.org/api/auto/${barcodeData}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Barcode could not be loaded.');
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            const imageUrl = URL.createObjectURL(blob);
                            container.innerHTML = `<img src="${imageUrl}" alt="Barcode" class="img-fluid">`;
                            container.style.display = 'block';
                        })
                        .catch(error => {
                            container.innerHTML = `<span class="text-danger">${error.message}</span>`;
                            container.style.display = 'block';
                        });
                } else {
                    container.style.display = 'none'; // Barcode ausblenden
                }
            });
        });
    </script>
</body>
    <footer>
        &copy; Open-Source-Project from <a href="https://github.com/LeonMTN05">LeonMTN05</a>
    </footer>
</html>
