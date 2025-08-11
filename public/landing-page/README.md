# KlikMedis Payroll Landing Page

Landing page profesional untuk aplikasi payroll KlikMedis dengan desain modern dan responsif.

## 🎨 Fitur Desain

- **Warna Biru Profesional**: Menggunakan palet warna biru yang memberikan kesan profesional dan teduh
- **Responsif**: Didesain untuk semua ukuran layar (desktop, tablet, mobile)
- **Animasi Smooth**: Menggunakan AOS (Animate On Scroll) untuk animasi yang halus
- **Modern UI/UX**: Interface yang modern dengan komponen Bootstrap 5
- **Performance Optimized**: Lazy loading dan optimasi performa

## 📁 Struktur File

```
landing-page/
├── index.html                 # Landing page utama (semua dalam satu file)
├── index-separated.html       # Versi dengan file terpisah
├── assets/
│   ├── css/
│   │   └── style.css         # File CSS terpisah
│   └── js/
│       └── main.js           # File JavaScript terpisah
└── README.md                 # Dokumentasi ini
```

## 🚀 Cara Menggunakan

### Versi 1: Semua dalam Satu File
```bash
# Buka file index.html di browser
open public/landing-page/index.html
```

### Versi 2: File Terpisah (Recommended)
```bash
# Buka file index-separated.html di browser
open public/landing-page/index-separated.html
```

## 🎯 Komponen Landing Page

### 1. Navigation Bar
- Logo KlikMedis Payroll
- Menu navigasi (Fitur, Statistik, Kontak)
- Tombol "Masuk" untuk login

### 2. Hero Section
- Judul utama yang menarik
- Deskripsi singkat tentang produk
- Call-to-action buttons
- Floating elements untuk visual appeal

### 3. Features Section
- 6 fitur utama aplikasi payroll:
  - Manajemen Karyawan
  - Proses Payroll Otomatis
  - Perhitungan PPh 21
  - Integrasi BPJS
  - Sistem Absensi
  - Laporan & Analitik

### 4. Statistics Section
- Statistik perusahaan (500+ Perusahaan, 50K+ Karyawan, dll)
- Animasi counter saat scroll

### 5. Call-to-Action Section
- Ajakan untuk trial gratis
- Konsultasi gratis

### 6. Footer
- Informasi kontak
- Social media links
- Menu footer

## 🎨 Palet Warna

```css
:root {
    --primary-blue: #1e40af;    /* Biru utama */
    --secondary-blue: #3b82f6;  /* Biru sekunder */
    --light-blue: #dbeafe;      /* Biru muda */
    --dark-blue: #1e3a8a;       /* Biru gelap */
    --accent-blue: #60a5fa;     /* Biru aksen */
    --text-dark: #1f2937;       /* Teks gelap */
    --text-light: #6b7280;      /* Teks terang */
    --white: #ffffff;           /* Putih */
    --gray-50: #f9fafb;         /* Abu-abu sangat muda */
    --gray-100: #f3f4f6;        /* Abu-abu muda */
}
```

## 🔧 Teknologi yang Digunakan

- **HTML5**: Struktur markup
- **CSS3**: Styling dengan custom properties
- **Bootstrap 5**: Framework CSS untuk responsivitas
- **JavaScript (ES6+)**: Interaktivitas dan animasi
- **Font Awesome**: Icons
- **Google Fonts (Inter)**: Typography
- **AOS Library**: Animate On Scroll

## 📱 Responsivitas

Landing page responsif untuk:
- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: < 768px

## ⚡ Fitur JavaScript

- Smooth scrolling untuk anchor links
- Navbar background change on scroll
- Counter animation untuk statistik
- Feature card hover effects
- Mobile menu toggle
- Parallax effect untuk hero section
- Form validation
- Notification system
- Lazy loading untuk images
- Back to top button
- Performance optimization dengan debouncing

## 🎭 Animasi

- **AOS Animations**: Fade, slide, zoom effects
- **Hover Effects**: Transform dan shadow pada cards
- **Floating Elements**: Background elements di hero section
- **Counter Animation**: Statistik yang beranimasi
- **Loading Animation**: Elements yang muncul bertahap

## 🔗 Integrasi

Landing page dapat diintegrasikan dengan:
- Laravel application (`/login`, `/register`)
- External forms
- Analytics tools
- Social media platforms

## 📈 SEO Optimized

- Meta tags yang lengkap
- Semantic HTML structure
- Alt text untuk images
- Proper heading hierarchy
- Fast loading times

## 🛠️ Customization

### Mengubah Warna
Edit file `assets/css/style.css` dan ubah nilai di `:root`:

```css
:root {
    --primary-blue: #your-color;
    --secondary-blue: #your-color;
    /* ... */
}
```

### Menambah Fitur Baru
1. Tambahkan section di HTML
2. Style di CSS
3. Add JavaScript functionality jika diperlukan

### Mengubah Konten
Edit langsung di file HTML sesuai kebutuhan:
- Teks dan deskripsi
- Statistik
- Informasi kontak
- Social media links

## 🚀 Deployment

### Local Development
```bash
# Buka di browser lokal
file:///path/to/landing-page/index.html
```

### Web Server
```bash
# Upload ke web server
# Pastikan semua file assets terupload
```

### CDN (Optional)
- Upload assets ke CDN untuk performa lebih baik
- Update path di HTML

## 📞 Support

Untuk pertanyaan atau bantuan:
- Email: info@klikmedis.com
- Phone: +62 21 1234 5678

## 📄 License

© 2025 KlikMedis Payroll. All rights reserved.
