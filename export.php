<?php
require 'db.php';
require 'vendor/autoload.php'; // Für PhpSpreadsheet und FPDF

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Funktion zur Konvertierung von Bildern in PNG
function convertToPng($sourcePath, $destinationPath) {
    $info = getimagesize($sourcePath);
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($sourcePath);
    } elseif ($info['mime'] == 'image/gif') {
        $image = imagecreatefromgif($sourcePath);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($sourcePath);
    } else {
        return false; // Unsupported format
    }

    imagepng($image, $destinationPath);
    imagedestroy($image);
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start(); // Output-Buffering aktivieren
    $format = $_POST['format'];

    // Daten aus der Datenbank abrufen
    $products = getProducts();

    if ($format === 'excel') {
        // Excel-Export
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Produkte');

        // Kopfzeile
        $sheet->setCellValue('A1', 'Produktname');
        $sheet->setCellValue('B1', 'Lieferant-Nr.');
        $sheet->setCellValue('C1', 'Artikel-Nr.');
        $sheet->setCellValue('D1', 'Bild');
        $sheet->setCellValue('E1', 'Barcode');

        // Formatierung der Kopfzeile
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        $row = 2; // Erste Datenzeile
        $tempDir = __DIR__ . '/temp_images';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        foreach ($products as $product) {
            $sheet->setCellValue("A{$row}", $product['product_name']);
            $sheet->setCellValue("B{$row}", $product['supplier_number']);
            $sheet->setCellValue("C{$row}", $product['article_number']);

            // Produktbild einfügen
            if (!empty($product['image_path'])) {
                $imageFile = $tempDir . "/product_{$product['id']}.png";
                if (!convertToPng($product['image_path'], $imageFile)) {
                    $imageFile = $product['image_path']; // Fallback auf Originalbild
                }

                $drawing = new Drawing();
                $drawing->setName('Produktbild');
                $drawing->setDescription('Produktbild');
                $drawing->setPath($imageFile);
                $drawing->setHeight(50);
                $drawing->setCoordinates("D{$row}");
                $drawing->setWorksheet($sheet);
            }

            // Barcode einfügen
            $barcodeFile = $tempDir . "/barcode_{$product['id']}.png";
            file_put_contents($barcodeFile, file_get_contents($product['barcode_url']));

            $drawing = new Drawing();
            $drawing->setName('Barcode');
            $drawing->setDescription('Barcode');
            $drawing->setPath($barcodeFile);
            $drawing->setHeight(50);
            $drawing->setCoordinates("E{$row}");
            $drawing->setWorksheet($sheet);

            $row++;
        }

        // Excel-Datei erstellen und herunterladen
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Produkte.xlsx';
        $filePath = $tempDir . '/' . $fileName;
        $writer->save($filePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        readfile($filePath);

        // Temporäre Dateien löschen
        array_map('unlink', glob("$tempDir/*"));
        rmdir($tempDir);
        exit;
    } elseif ($format === 'pdf') {
        // PDF-Export
        require('vendor/fpdf/fpdf.php');

        class PDF extends FPDF {
            function Header() {
                $this->SetFont('Arial', 'B', 14);
                $this->Cell(0, 10, 'Produktverwaltung - Exportierte Produkte', 0, 1, 'C');
                $this->Ln(10);
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(0, 10, 'Seite ' . $this->PageNo(), 0, 0, 'C');
            }
        }

        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        // Tabellenkopf
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255, 255, 255);
        $columnWidth = 30; // Angepasste Breite für Textspalten
        $imageColumnWidth = 50; // Breite für Bilder und Barcodes
        $pdf->Cell($columnWidth, 10, 'Produktname', 1, 0, 'C', true);
        $pdf->Cell($columnWidth, 10, 'Lieferant-Nr.', 1, 0, 'C', true);
        $pdf->Cell($columnWidth, 10, 'Artikel-Nr.', 1, 0, 'C', true);
        $pdf->Cell($imageColumnWidth, 10, 'Bild', 1, 0, 'C', true);
        $pdf->Cell($imageColumnWidth, 10, 'Barcode', 1, 1, 'C', true);

        $tempDir = __DIR__ . '/temp_images';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        // Tabelleninhalt
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0);
        foreach ($products as $product) {
            $pdf->Cell($columnWidth, 30, $product['product_name'], 1);
            $pdf->Cell($columnWidth, 30, $product['supplier_number'], 1);
            $pdf->Cell($columnWidth, 30, $product['article_number'], 1);

            // Produktbild einfügen
            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Cell($imageColumnWidth, 30, '', 1);
            if (!empty($product['image_path'])) {
                $imageFile = $tempDir . "/product_{$product['id']}.png";
                if (!convertToPng($product['image_path'], $imageFile)) {
                    $imageFile = $product['image_path']; // Fallback auf Originalbild
                }
                $pdf->Image($imageFile, $x + 5, $y + 5, 40, 20, '', '', true);
            }

            // Barcode hinzufügen
            $barcodeUrl = $product['barcode_url'];
            $barcodeFile = $tempDir . "/barcode_{$product['id']}.png";
            file_put_contents($barcodeFile, file_get_contents($barcodeUrl));

            $x = $pdf->GetX();
            $y = $pdf->GetY();
            $pdf->Cell($imageColumnWidth, 30, '', 1);
            $pdf->Image($barcodeFile, $x + 5, $y + 5, 40, 20, '', '', true);
            $pdf->Ln();
        }

        // Temporäre Dateien löschen
        array_map('unlink', glob("$tempDir/*"));
        rmdir($tempDir);

        // PDF-Datei ausgeben
        ob_end_clean(); // Output-Buffering beenden
        $fileName = 'Produkte.pdf';
        $pdf->Output('I', $fileName);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datenexport</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #e0e0e0;
        }
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            max-height: 50px;
            margin-right: 10px;
        }
        .navbar-brand .divider {
            height: 50px;
            width: 1px;
            background-color: #6c757d;
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
        .container {
            max-width: 800px;
            margin-top: 50px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        #export-status {
            display: none;
        }
        .alert-status {
            font-size: 16px;
            padding: 15px;
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
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/logo.png" alt="Logo">
                <div class="divider"></div>
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

    <div class="container" id="export-container">
        <div id="export-form">
            <h1 class="text-center mb-4">Data export</h1>
            <form id="exportForm" action="export.php" method="post">
                <div class="mb-3">
                    <label for="format" class="form-label">Export format</label>
                    <select id="format" name="format" class="form-select" required>
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-gear"></i> Export
                </button>
            </form>
        </div>
    </div>
</body>
    <footer>
        &copy; Open-Source-Project from <a href="https://github.com/LeonMTN05">LeonMTN05</a>
    </footer>
</html>
