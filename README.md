# 🎓 Polytechnic ERP: Production-Grade Result Management

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-5.x-EBB308?style=for-the-badge&logo=filament)](https://filamentphp.com)
[![BTEB Compliant](https://img.shields.io/badge/BTEB-Compliant-blue?style=for-the-badge)](https://bteb.gov.bd)

A high-performance, secure, and auditable Academic Result ERP designed specifically for the **Bangladesh Technical Education Board (BTEB)** standards. This system automates complex grading logic, handles bulk mark entries, and provides secure, QR-verified transcripts.

---

## 🚀 Key Highlights

- **🎯 Precision Grading Engine**: Automated BTEB separate-pass logic for Theory Final (TF) and Practical Final (PF).
- **📊 Tabulation Intelligence**: Real-time generation of semester tabulation sheets with GPA/Status insights.
- **🛡️ Data Integrity**: SHA-256 snapshot hashing for transcripts and immutable result locking.
- **⚡ Bulk Operations**: Excel imports and high-speed bulk mark entry with auto-attendance derivation.
- **🔍 Full Audit Trail**: Every mark change and publication event is logged for administrative transparency.

---

## 🛠️ Technology Stack

- **Backend**: Laravel 12 (PHP 8.2+)
- **Admin Panel**: Filament v3 (TALL Stack)
- **Database**: MySQL (Production) / SQLite (Testing)
- **Reporting**: DomPDF, Excel Export, QR Code Generator
- **Security**: Signed URLs, CSRF Protection, Locked Snapshots

---

## 🧠 Core Business Logic: BTEB Grading

The `BtebGradingService` implements the rigorous BTEB pass criteria:

1. **Dual-Final Pass**: Students must score at least **40%** in *both* Theory Final (TF) and Practical Final (PF).
2. **Component Pass**: Theory (TC+TF) and Practical (PC+PF) aggregates must meet the **40%** threshold.
3. **Overall Pass**: The total percentage across all components must be **≥ 40%**.
4. **Referred Status**: Failure in any single component results in a `REFERRED` status and `F` grade for the subject.

---

## 🔒 Security & Verification

Each transcript features a **unique QR code** linked to a **Temporary Signed URL**.
- **Integrity Check**: The system verifies the current result data against a stored **SHA-256 hash** before rendering.
- **Locking Mechanism**: Once a result is "Locked", no marks can be modified without administrative unlock authority.

---

## 📋 Installation & Demo

1. **Clone & Install**:
   ```bash
   git clone https://github.com/emonmirth/polytechnic-erp.git
   composer install
   npm install && npm run build
   ```

2. **Setup Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database & Seed**:
   ```bash
   php artisan migrate --seed
   ```

4. **Run Server**:
   ```bash
   php artisan serve
   ```

---

## 📄 License

The Polytechnic ERP is open-source software licensed under the [MIT license](LICENSE).

---
*Developed with ❤️ for Academic Excellence.*
