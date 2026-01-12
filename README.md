Dokumentasi & Panduan Pengguna: Plugin Letter of Acceptance (LoA) OJS

1. Pengantar

Letter of Acceptance (LoA) adalah plugin untuk Open Journal Systems (OJS) 3.4+ yang mengotomatiskan seluruh proses pembuatan dan manajemen Letter of Acceptance (LoA). Ketika editor accept naskah, plugin otomatis akan mengenerate file PDF LoA berisi logo jurnal, QR code (bisa diverifikasi), dan tombol unduh untuk editor maupun penulis.

2. Fitur Utama

- Pembuatan PDF Otomatis: LoA dalam format PDF langsung digenerate saat editor mengklik "Accept Submission".
- Template mudah disesuaikan: Manajer Jurnal dapat dengan mudah mengubah isi header, body, dan footer surat melalui halaman Pengaturan plugin tanpa menyentuh kode.
- Logo Jurnal Dinamis: Plugin secara otomatis mengambil dan menampilkan logo header jurnal yang sudah diunggah di pengaturan OJS, membuat setiap LoA terlihat resmi.
- Verifikasi QR Code: Setiap LoA dilengkapi dengan QR Code unik yang mengarah ke halaman verifikasi di situs jurnal, meningkatkan keamanan dan keaslian dokumen.
- URL Verifikasi Aman: URL verifikasi diamankan dengan sebuah _token_ rahasia untuk mencegah akses tidak sah ke detail naskah lain.
- Akses Mudah: Tombol "Unduh Letter of Acceptance" secara otomatis muncul di dasbor editor dan penulis setelah naskah diterima.
- Logika Cerdas: Tombol unduh akan otomatis hilang jika status penerimaan naskah dibatalkan, memastikan hanya naskah yang valid yang memiliki akses ke LoA.

3. Instalasi

Ikuti langkah-langkah berikut untuk menginstal plugin:

1.  Unduh Folder Plugin:
    Unduh folder `loa` yang berisi file (`LoaPlugin.php`, `LoaHandler.php`, `form/`, `pages/`, `templates/`, `locale/`).

2.  Unduh Library Pendukung:
    Plugin ini memerlukan dua library eksternal yang dapat diunduh pada link:

    - Dompdf (untuk membuat PDF): [Unduh di sini](https://github.com/dompdf/dompdf/releases). Pastikan mengunduh file ZIP rilisnya (misalnya, `dompdf_2-0-7.zip`).
    - phpqrcode (untuk membuat QR Code): [Unduh di sini](https://sourceforge.net/projects/phpqrcode/files/).

3.  Struktur Folder:

    - Salin folder `loa` ke dalam direktori `plugins/generic/` di instalasi OJS.
    - Ekstrak Dompdf, ubah nama foldernya menjadi `dompdf`, lalu letakkan di dalam folder `loa`.
    - Ekstrak phpqrcode, pastikan nama foldernya adalah `phpqrcode`, lalu letakkan di dalam folder `loa`.

    Struktur akhir di dalam folder `loa` akan terlihat seperti ini:

    /plugins/generic/loa/
    ├── dompdf/
    ├── form/
    ├── locale/
    ├── pages/
    ├── phpqrcode/
    ├── templates/
    ├── LoaPlugin.php
    └── version.xml

4.  Buat Folder Penyimpanan Publik:

    - Di dalam direktori root OJS Anda, buka folder `public/`.
    - Buat folder baru bernama `loa`. Path-nya akan menjadi `public/loa/`. Folder ini digunakan untuk menyimpan file PDF yang dihasilkan.

5.  Aktivasi Plugin:

    - Masuk ke OJS sebagai Administrator.
    - Buka Settings \> Website \> Plugins \> Installed Plugins.
    - Cari "Letter of Acceptance Plugin" di bawah kategori "Generic Plugins" dan centang kotak untuk mengaktifkannya.

4. Konfigurasi (Wajib Dilakukan)

Setelah plugin aktif, atur template surat.

1.  Di halaman Installed Plugins, klik link "Settings" yang ada di samping "Letter of Acceptance Plugin".

2.  Halaman form akan muncul. Isi kolom Header, Body, dan Footer dengan teks yang Anda inginkan.

3.  Variabel-variabel berikut dapat digunakan untuk menampilkan teks secara otomatis:

    - `{$journalLogo}`: Menampilkan logo header jurnal Anda.
    - `{$journalTitle}`: Nama jurnal.
    - `{$articleTitle}`: Judul naskah yang diterima.
    - `{$submissionId}`: ID naskah.
    - `{$acceptanceDate}`: Tanggal naskah diterima.
    - `{$authorNamesString}`: Daftar nama penulis, dipisahkan koma.
    - `{$qrCodeDataUri}`: (Disarankan) Gunakan di dalam tag `<img>` untuk menampilkan QR Code. Contoh: `<img src="{$qrCodeDataUri}" width="90">`.

4.  Klik Save.

5. Panduan Pengguna


## Untuk Editor/Manajer Jurnal:

- Proses: Lakukan alur kerja editorial seperti biasa. Saat Anda yakin sebuah naskah layak diterima, klik tombol "Accept Submission".
- Otomatisasi: Saat itu juga, plugin akan membuat dan menyimpan file PDF LoA di server.
- Unduh: Buka halaman workflow naskah tersebut. Sebuah tombol "Unduh Letter of Acceptance" akan tersedia di samping judul artikel.

## Untuk Penulis (Author):

- Notifikasi: Penulis akan melihat status naskah mereka berubah menjadi "Accepted" atau masuk ke tahap selanjutnya.
- Unduh: Penulis dapat masuk ke dasbor mereka dan membuka halaman submisi yang telah diterima. Di bagian atas, akan ada tombol "Unduh Letter of Acceptance" yang bisa mereka gunakan untuk mengunduh surat tersebut.

# Letter of Acceptance (LoA) Plugin

    Author: Ramli
    Email: ramli.baharuddin@gmail.com
    Contributions: Gemini, Grok & ChatGpt

## About

    This plugin for OJS 3.4+ automates the entire process of creating and managing a Letter of Acceptance (LoA). Upon an editor's decision to accept a submission, this plugin automatically generates a professional PDF LoA.

## Key features include:

    - Automatic PDF Generation: Creates a PDF LoA the moment a submission is accepted.
    - Customizable Template: Journal Managers can easily edit the LoA's header, body, and footer content directly from the plugin's settings page without touching any code.
    - Dynamic Journal Logo: Automatically fetches and embeds the journal's header logo for official branding.
    - Secure QR Code Verification: Each LoA is embedded with a unique QR Code that links to a secure verification page on the journal's site, ensuring the document's authenticity. The verification URL is protected by a secret token.
    - Easy Access for Editors and Authors: A "Download Letter of Acceptance" button automatically appears on the submission's workflow page for editors and on the author's submission dashboard.
    - Smart Logic: The download button is context-aware and will disappear if an acceptance decision is reversed, ensuring LoAs are only available for currently accepted submissions.

## License

    This plugin and its source code are licensed under the Creative Commons Attribution-ShareAlike 4.0 International License (CC BY-SA 4.0).

## System Requirements

    - OJS 3.4 (tested on 3.4.0.9)
    - PHP with GD2 extension enabled (for QR Code generation).
    - This plugin bundles the necessary versions of Dompdf and phpqrcode.

## Installation

    - Copy the loa plugin folder into your OJS installation at plugins/generic/loa.
    - The plugin requires two libraries. Please download them and place them inside the loa folder:
        - Dompdf: Download the release ZIP from GitHub. Extract it and rename the folder to dompdf.
        - phpqrcode: Download the library from SourceForge. Extract it and ensure the folder is named phpqrcode.
    - Create a writable directory for the generated PDF files at public/loa/.
    - Log in to your OJS administration panel, navigate to Settings > Website > Plugins > Installed Plugins, find the "Letter of Acceptance Plugin" under "Generic Plugins", and enable it.

## Configuration

    This plugin's template is fully configurable via the OJS dashboard.
        - Navigate to Settings > Website > Plugins > Installed Plugins.
        - Find the "Letter of Acceptance Plugin" and click the blue Settings link.
        - In the modal window that appears, you can customize the Header, Body, and Footer of the LoA.
        - You can use the following variables in your template, which will be replaced with the submission's data:
            {$journalLogo}: Displays the journal's header logo image.
            {$journalTitle}: The name of the journal.
            {$articleTitle}: The title of the accepted submission.
            {$submissionId}: The unique ID of the submission.
            {$acceptanceDate}: The date the submission was accepted.
            {$authorNamesString}: A comma-separated list of the authors' full names.
            {$qrCodeDataUri}: The data string for the verification QR Code. Use it inside an <img> tag like this: <img src="{$qrCodeDataUri}" width="90">.
        - Click Save.

## Known Issues

    - The verification page (/loa/verify/...) uses a custom page handler. This could potentially conflict with other plugins that aggressively manage URL routing.
    - The plugin relies on the journal having a pageHeaderLogoImage configured in Settings > Website > Appearance > Logo for the {$journalLogo} variable to work.
    - QR code generation temporarily writes a file to the server's temp directory (sys_get_temp_dir()). The web server must have write permissions for this directory.

## Contact/Support

    Github: @mabere
