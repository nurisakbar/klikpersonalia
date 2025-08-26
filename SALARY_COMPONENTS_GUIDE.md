# Panduan Modul Komponen Gaji

## Deskripsi
Modul Komponen Gaji adalah fitur yang memungkinkan administrator dan HR untuk mengelola berbagai komponen gaji yang digunakan dalam sistem penggajian. Modul ini mendukung komponen pendapatan (earning) dan potongan (deduction) dengan berbagai pengaturan seperti status aktif, perhitungan pajak, dan perhitungan BPJS.

## Fitur Utama

### 1. Manajemen Komponen Gaji
- **Tambah Komponen Baru**: Membuat komponen gaji dengan nama, tipe, nilai default, dan pengaturan lainnya
- **Edit Komponen**: Mengubah informasi komponen yang sudah ada
- **Hapus Komponen**: Menghapus komponen yang tidak digunakan (dengan validasi)
- **Lihat Detail**: Melihat informasi lengkap komponen gaji

### 2. Pengaturan Komponen
- **Status Aktif/Tidak Aktif**: Mengontrol apakah komponen tersedia untuk digunakan
- **Tipe Komponen**: 
  - **Pendapatan (Earning)**: Komponen yang menambah gaji
  - **Potongan (Deduction)**: Komponen yang mengurangi gaji
- **Nilai Default**: Nilai standar komponen gaji
- **Urutan Tampilan**: Mengatur urutan komponen dalam daftar
- **Dikenakan Pajak**: Apakah komponen dihitung dalam perhitungan pajak
- **Dihitung BPJS**: Apakah komponen dihitung dalam perhitungan BPJS

### 3. Fitur Tambahan
- **Drag & Drop Sorting**: Mengatur urutan komponen dengan mudah
- **Bulk Actions**: Aksi massal untuk komponen yang dipilih
- **Export Data**: Export ke Excel, PDF, dan Print
- **Search & Filter**: Pencarian dan filter komponen
- **Responsive Design**: Tampilan yang responsif untuk berbagai perangkat

## Struktur Database

### Tabel: `salary_components`
```sql
CREATE TABLE salary_components (
    id UUID PRIMARY KEY,
    company_id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    default_value DECIMAL(15,2) DEFAULT 0,
    type ENUM('earning', 'deduction') DEFAULT 'earning',
    is_active BOOLEAN DEFAULT TRUE,
    is_taxable BOOLEAN DEFAULT FALSE,
    is_bpjs_calculated BOOLEAN DEFAULT FALSE,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_company_component (company_id, name)
);
```

## Penggunaan

### 1. Menambah Komponen Gaji Baru
1. Buka menu "Komponen Gaji"
2. Klik tombol "Tambah Komponen"
3. Isi form dengan informasi:
   - **Nama Komponen**: Nama yang akan ditampilkan
   - **Tipe**: Pilih Pendapatan atau Potongan
   - **Nilai Default**: Nilai standar komponen
   - **Deskripsi**: Penjelasan detail (opsional)
   - **Pengaturan**: Status aktif, pajak, BPJS
4. Klik "Simpan Komponen"

### 2. Mengedit Komponen
1. Dari daftar komponen, klik tombol edit (ikon pensil)
2. Ubah informasi yang diperlukan
3. Klik "Update Komponen"

### 3. Mengatur Urutan Komponen
1. Klik tombol "Atur Urutan" di halaman index
2. Drag & drop komponen untuk mengatur urutan
3. Klik "Simpan Urutan"

### 4. Mengubah Status Komponen
1. Dari daftar komponen, klik tombol toggle status
2. Konfirmasi perubahan status
3. Komponen akan berubah dari aktif ke tidak aktif atau sebaliknya

## Komponen Default

### Komponen Pendapatan
1. **Gaji Pokok**: Gaji pokok bulanan karyawan
2. **Tunjangan Makan**: Tunjangan makan harian
3. **Tunjangan Transport**: Tunjangan transportasi bulanan
4. **Tunjangan Jabatan**: Tunjangan berdasarkan posisi
5. **Tunjangan Kinerja**: Tunjangan berdasarkan kinerja
6. **Tunjangan Lembur**: Tunjangan jam kerja lembur
7. **Bonus Tahunan**: THR dan bonus tahunan
8. **Tunjangan Kesehatan**: Tunjangan kesehatan tambahan

### Komponen Potongan
1. **Potongan BPJS Kesehatan**: 2% dari gaji pokok
2. **Potongan BPJS Ketenagakerjaan**: 2% dari gaji pokok
3. **Potongan Pajak**: PPh 21
4. **Potongan Keterlambatan**: Untuk keterlambatan datang
5. **Potongan Cuti**: Untuk cuti melebihi hak
6. **Potongan Pinjaman**: Pinjaman karyawan
7. **Potongan Lainnya**: Potongan tidak terduga

## Integrasi dengan Modul Lain

### 1. Modul Penggajian
- Komponen gaji digunakan dalam perhitungan gaji karyawan
- Nilai default dapat di-override per karyawan
- Status aktif menentukan ketersediaan komponen

### 2. Modul Pajak
- Komponen dengan `is_taxable = true` dihitung dalam PPh 21
- Nilai komponen mempengaruhi penghasilan kena pajak

### 3. Modul BPJS
- Komponen dengan `is_bpjs_calculated = true` dihitung dalam BPJS
- Biasanya berupa persentase dari gaji pokok

## Keamanan dan Izin

### Role yang Diperlukan
- **Admin**: Akses penuh ke semua fitur
- **HR Manager**: Dapat mengelola komponen gaji
- **Payroll Manager**: Dapat melihat komponen gaji

### Permission yang Diperlukan
- `view salary components`: Melihat komponen gaji
- `create salary components`: Membuat komponen baru
- `update salary components`: Mengubah komponen
- `delete salary components`: Menghapus komponen

## Validasi dan Aturan Bisnis

### 1. Validasi Input
- Nama komponen wajib diisi dan unik per perusahaan
- Nilai default harus berupa angka positif
- Tipe komponen harus valid (earning/deduction)

### 2. Aturan Bisnis
- Komponen yang sudah digunakan dalam penggajian tidak dapat dihapus
- Hanya komponen aktif yang tersedia untuk penggajian
- Urutan komponen mempengaruhi tampilan dalam sistem

### 3. Pembatasan
- Satu perusahaan tidak boleh memiliki nama komponen yang sama
- Komponen tidak dapat dipindahkan antar perusahaan
- Soft delete untuk menjaga integritas data

## Troubleshooting

### 1. Komponen Tidak Muncul dalam Penggajian
- Periksa status aktif komponen
- Pastikan komponen sudah di-assign ke karyawan
- Cek pengaturan komponen dalam kebijakan penggajian

### 2. Error Saat Menghapus Komponen
- Komponen masih digunakan dalam penggajian
- Periksa relasi dengan tabel lain
- Gunakan fitur nonaktifkan sebagai alternatif

### 3. Urutan Komponen Tidak Tersimpan
- Pastikan semua komponen memiliki sort_order yang valid
- Cek permission untuk mengubah urutan
- Refresh halaman setelah menyimpan urutan

## Pengembangan Selanjutnya

### 1. Fitur yang Direncanakan
- **Template Komponen**: Template komponen gaji yang dapat digunakan ulang
- **Import/Export**: Import komponen dari file Excel
- **Versioning**: Riwayat perubahan komponen
- **Audit Trail**: Log semua perubahan komponen

### 2. Integrasi Lanjutan
- **API Integration**: Integrasi dengan sistem eksternal
- **Workflow Approval**: Persetujuan perubahan komponen
- **Notification**: Notifikasi perubahan komponen ke stakeholder

### 3. Analitik dan Reporting
- **Usage Analytics**: Analisis penggunaan komponen
- **Cost Analysis**: Analisis biaya komponen gaji
- **Trend Analysis**: Analisis tren komponen gaji

## Kesimpulan

Modul Komponen Gaji memberikan fleksibilitas dan kontrol penuh dalam mengelola struktur gaji perusahaan. Dengan fitur yang komprehensif dan antarmuka yang user-friendly, modul ini memudahkan HR dan administrator dalam mengatur komponen gaji sesuai dengan kebijakan perusahaan.

Modul ini dirancang dengan mempertimbangkan skalabilitas dan integrasi dengan modul lain dalam sistem, sehingga dapat mendukung pertumbuhan bisnis dan kebutuhan penggajian yang kompleks.
