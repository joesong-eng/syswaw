--- a/www/wwwroot/syswaw/README.md
+++ b/www/wwwroot/syswaw/README.md
@@ -1,48 +1,81 @@
-<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>
+# 遊樂場機台管理系統 (Arcade Machine Management System)
 
-<p align="center">
-<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
-<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
-<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
-<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
-</p>
+這是一個基於 Laravel 開發的後台管理系統，旨在提供一個全面性的解決方案，用於管理遊樂場的場地、機台、權限以及營運數據。
 
-## About Laravel
+---
 
-Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:
+## 核心功能 (V1)
 
-- [Simple, fast routing engine](https://laravel.com/docs/routing).
-- [Powerful dependency injection container](https://laravel.com/docs/container).
-- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-- [Robust background job processing](https://laravel.com/docs/queues).
-- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).
+- **權限管理:** 基於角色的權限控制 (Admin, Arcade Owner, Machine Owner)，確保不同使用者只能存取授權的資源。
+- **場地管理 (Arcades):** 新增、編輯、管理多個遊樂場地及其基本資訊。
+- **機台管理 (Machines):**
+    - 建立、編輯及刪除機台資料。
+    - 綁定唯一的硬體通訊卡 ID (`chip_hardware_id`) 與認證金鑰 (`auth_key`)。
+    - 設定機台詳細參數，如費率、分潤比例、退獎模式等。
+    - 遠端啟用或停用特定機台。
+- **安全認證:**
+    - 為每台機台生成唯一的認證金鑰，用於與後端系統通訊。
+    - 提供 QR Code 打印功能，方便現場人員快速設定機台。
+- **數據記錄:** (基礎建設) 建立 `machine_data_records` 資料表，用於接收並儲存來自機台的營運數據。
 
-Laravel is accessible, powerful, and provides tools required for large, robust applications.
+## 技術棧 (Tech Stack)
 
-## Learning Laravel
+- **後端:** PHP 8.x / Laravel 10.x
+- **前端:** Blade / (可能是 Vue.js 或 Livewire，由 Jetstream 提供)
+- **認證與授權:** Laravel Jetstream, Spatie/laravel-permission
+- **資料庫:** MySQL / MariaDB
+- **開發環境:** Laravel Sail (建議) 或其他 LEMP/LAMP 環境
 
-Laravel has the most extensive and thorough documentation and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.
+---
 
-You may also try the Laravel Bootcamp, where you will be guided through building a modern Laravel application from scratch.
+## 安裝與設定 (Installation & Setup)
 
-If you don't feel like reading, Laracasts can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.
+請依照以下步驟來設定開發環境。
 
-## Laravel Sponsors
+### 1. 取得專案程式碼
+```bash
+git clone <your-repository-url>
+cd syswaw
+```
 
-We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel Partners program.
+### 2. 安裝依賴套件
+```bash
+composer install
+npm install
+```
 
-### Premium Partners
+### 3. 設定環境變數
+複製範例環境設定檔，並根據您的本機環境進行修改。
+```bash
+cp .env.example .env
+```
+接著，生成應用程式金鑰：
+```bash
+php artisan key:generate
+```
+**重要:** 請務必在 `.env` 檔案中設定正確的資料庫連線資訊 (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)。
 
-- **Vehikl**
-- **Tighten Co.**
-- **WebReinvent**
-- **Kirschbaum Development Group**
-- **64 Robots**
-- **Curotec**
-- **Cyber-Duck**
-- **DevSquad**
-- **Jump24**
-- **Redberry**
-- **Active Logic**
-- **byte5**
-- **OP.GG**
+### 4. 執行資料庫遷移與填充
+這將會建立所有必要的資料表，並填充預設的角色資料。
+```bash
+php artisan migrate --seed
+```
 
-## Contributing
+### 5. 編譯前端資源
+```bash
+npm run dev
+```
 
-Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the Laravel documentation.
+### 6. 啟動伺服器
+您可以使用 Artisan 內建的伺服器來運行專案：
+```bash
+php artisan serve
+```
+現在，您可以透過瀏覽器訪問 `http://127.0.0.1:8000` 來查看應用程式。
 
-## Code of Conduct
+## 預設登入帳號
 
-In order to ensure that the Laravel community is welcoming to all, please review and abide by the Code of Conduct.
-
-## Security Vulnerabilities
-
-If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via taylor@laravel.com. All security vulnerabilities will be promptly addressed.
-
-## License
-
-The Laravel framework is open-sourced software licensed under the MIT license.
+*(請在此處填寫由 Seeder 建立的預設管理員帳號和密碼，如果有的話)*
+- **帳號:** `admin@tg25.win`
+- **密碼:** `we123123`

