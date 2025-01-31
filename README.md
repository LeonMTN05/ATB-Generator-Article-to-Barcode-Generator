# ATB-Generator (Article to Barcode Generator)

ATB-Generator is a web-based application designed for **inventory management, barcode generation, and data export**. It allows users to add articles, generate barcodes, manage stored items, and export data to **Excel or PDF**.

## 🚀 Features
- **Add Articles** – Input new articles with details.
- **Generate Barcodes** – Automatically create barcodes for articles.
- **Manage Inventory** – View, edit, and delete stored items.
- **Export Data** – Download your inventory list in **Excel (.xlsx) or PDF**.
- **Simple & Responsive UI** – Easy-to-use interface.

## 📌 Requirements
- **Web Server**: Apache2 or Nginx
- **PHP**: Version 7.4 or higher
- **Composer**: For dependency management

## 📂 Installation & Setup
### **1️⃣ Upload to a Web Server**
Copy all files to your **Apache2** or **Nginx** web directory.

### **2️⃣ Install Dependencies**
Run the following command in the project folder:
```bash
composer install
```

### **3️⃣ Set Permissions** (if necessary)
```bash
chmod -R 775 /path/to/ATB-Generator
```

### **4️⃣ Open in Browser**
Navigate to:
```bash
http://your-server-ip-or-domain/
```

## 🔗 APIs & Libraries Used
- **[Picqer PHP Barcode Generator](https://github.com/picqer/php-barcode-generator)** – For generating barcodes.
- **[BarcodeAPI.org](https://barcodeapi.org/index.html#auto)** – For displaying and generating barcodes via API.
- **[PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)** – For exporting data to Excel.
- **[TCPDF](https://github.com/tecnickcom/TCPDF)** – For generating PDF exports.

## 📜 License
This project is licensed under the **MIT License**.

---

**Developed with ❤️ for efficient inventory management.**

🔗 **GitHub Repository:** [ATB-Generator-Article-to-Barcode-Generator](https://github.com/LeonMTN05/ATB-Generator-Article-to-Barcode-Generator.git) 

📧 **Discord:** leonmtn05 (No e-mail address is displayed here due to spam).
