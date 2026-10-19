-- InternTrack - Sistem Monitoring PKL
-- Import file ini lewat phpMyAdmin (tab Import) atau: mysql -u root < interntrack.sql
DROP DATABASE IF EXISTS interntrack;
CREATE DATABASE interntrack CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE interntrack;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  role ENUM('admin','pembimbing','siswa') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE siswa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  nis VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  kelas VARCHAR(20) NOT NULL,
  jurusan VARCHAR(50) NOT NULL,
  no_hp VARCHAR(20),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE perusahaan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_perusahaan VARCHAR(100) NOT NULL,
  alamat VARCHAR(255) NOT NULL,
  bidang VARCHAR(100) NOT NULL,
  kontak VARCHAR(50)
) ENGINE=InnoDB;

CREATE TABLE pembimbing (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  nip VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  no_hp VARCHAR(20),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE penempatan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  siswa_id INT NOT NULL,
  perusahaan_id INT NOT NULL,
  pembimbing_id INT NOT NULL,
  tanggal_mulai DATE NOT NULL,
  tanggal_selesai DATE NOT NULL,
  status ENUM('Belum Mulai','Aktif','Selesai') NOT NULL DEFAULT 'Belum Mulai',
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
  FOREIGN KEY (perusahaan_id) REFERENCES perusahaan(id),
  FOREIGN KEY (pembimbing_id) REFERENCES pembimbing(id)
) ENGINE=InnoDB;

CREATE TABLE jurnal (
  id INT AUTO_INCREMENT PRIMARY KEY,
  siswa_id INT NOT NULL,
  tanggal DATE NOT NULL,
  kegiatan TEXT NOT NULL,
  hasil TEXT,
  kendala TEXT,
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE absensi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  siswa_id INT NOT NULL,
  tanggal DATE NOT NULL,
  status ENUM('Hadir','Izin','Sakit','Alpa') NOT NULL,
  UNIQUE KEY uq_absen (siswa_id, tanggal),
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE monitoring (
  id INT AUTO_INCREMENT PRIMARY KEY,
  siswa_id INT NOT NULL,
  pembimbing_id INT NULL,
  tanggal DATE NOT NULL,
  catatan TEXT NOT NULL,
  nilai INT NOT NULL,
  rekomendasi TEXT,
  FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
  FOREIGN KEY (pembimbing_id) REFERENCES pembimbing(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (id,username,password,nama,role) VALUES
(1,'admin','$2b$12$jQUxaylvZEbG4MkHsrT3J.IMMHLvohjwkrjueiw5.gk77AoAmJwQG','Administrator','admin'),
(2,'pembimbing1','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Budi Santoso','pembimbing'),
(3,'pembimbing2','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Siti Rahma','pembimbing'),
(4,'pembimbing3','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Agus Prasetyo','pembimbing'),
(5,'pembimbing4','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Dewi Lestari','pembimbing'),
(6,'pembimbing5','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Eko Wahyudi','pembimbing'),
(7,'pembimbing6','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Rina Marlina','pembimbing'),
(8,'pembimbing7','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Hendra Gunawan','pembimbing'),
(9,'pembimbing8','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Yuni Astuti','pembimbing'),
(10,'pembimbing9','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Joko Susilo','pembimbing'),
(11,'pembimbing10','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Maya Anggraini','pembimbing'),
(12,'2026001','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Ahmad Fauzi','siswa'),
(13,'2026002','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Aisyah Putri','siswa'),
(14,'2026003','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Bagas Saputra','siswa'),
(15,'2026004','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Citra Dewi','siswa'),
(16,'2026005','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Dimas Anggara','siswa'),
(17,'2026006','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Eka Nurhaliza','siswa'),
(18,'2026007','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Fajar Ramadhan','siswa'),
(19,'2026008','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Gita Savitri','siswa'),
(20,'2026009','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Hafiz Maulana','siswa'),
(21,'2026010','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Indah Permata','siswa'),
(22,'2026011','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Joko Widodo Putra','siswa'),
(23,'2026012','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Kirana Lestari','siswa'),
(24,'2026013','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Lukman Hakim','siswa'),
(25,'2026014','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Mega Safitri','siswa'),
(26,'2026015','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Naufal Rizki','siswa'),
(27,'2026016','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Olivia Rahma','siswa'),
(28,'2026017','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Putra Mahendra','siswa'),
(29,'2026018','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Qori Amalia','siswa'),
(30,'2026019','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Rizal Firmansyah','siswa'),
(31,'2026020','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Salsabila Nur','siswa'),
(32,'2026021','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Tegar Pratama','siswa'),
(33,'2026022','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Ulfa Maharani','siswa'),
(34,'2026023','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Vino Aditya','siswa'),
(35,'2026024','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Winda Sari','siswa'),
(36,'2026025','$2b$12$UDp.OrnAQoJ72cFVSWzAI.xgm/Oe1NccfkgnzQ0aCF1gBHA6.uVpi','Yoga Pratama','siswa');

INSERT INTO siswa (user_id,nis,nama,kelas,jurusan,no_hp) VALUES
(12,'2026001','Ahmad Fauzi','XII RPL 1','Rekayasa Perangkat Lunak','081234567801'),
(13,'2026002','Aisyah Putri','XII RPL 2','Rekayasa Perangkat Lunak','081234567802'),
(14,'2026003','Bagas Saputra','XII TKJ 1','Teknik Komputer dan Jaringan','081234567803'),
(15,'2026004','Citra Dewi','XII RPL 1','Rekayasa Perangkat Lunak','081234567804'),
(16,'2026005','Dimas Anggara','XII RPL 2','Rekayasa Perangkat Lunak','081234567805'),
(17,'2026006','Eka Nurhaliza','XII TKJ 1','Teknik Komputer dan Jaringan','081234567806'),
(18,'2026007','Fajar Ramadhan','XII RPL 1','Rekayasa Perangkat Lunak','081234567807'),
(19,'2026008','Gita Savitri','XII RPL 2','Rekayasa Perangkat Lunak','081234567808'),
(20,'2026009','Hafiz Maulana','XII TKJ 1','Teknik Komputer dan Jaringan','081234567809'),
(21,'2026010','Indah Permata','XII RPL 1','Rekayasa Perangkat Lunak','081234567810'),
(22,'2026011','Joko Widodo Putra','XII RPL 2','Rekayasa Perangkat Lunak','081234567811'),
(23,'2026012','Kirana Lestari','XII TKJ 1','Teknik Komputer dan Jaringan','081234567812'),
(24,'2026013','Lukman Hakim','XII RPL 1','Rekayasa Perangkat Lunak','081234567813'),
(25,'2026014','Mega Safitri','XII RPL 2','Rekayasa Perangkat Lunak','081234567814'),
(26,'2026015','Naufal Rizki','XII TKJ 1','Teknik Komputer dan Jaringan','081234567815'),
(27,'2026016','Olivia Rahma','XII RPL 1','Rekayasa Perangkat Lunak','081234567816'),
(28,'2026017','Putra Mahendra','XII RPL 2','Rekayasa Perangkat Lunak','081234567817'),
(29,'2026018','Qori Amalia','XII TKJ 1','Teknik Komputer dan Jaringan','081234567818'),
(30,'2026019','Rizal Firmansyah','XII RPL 1','Rekayasa Perangkat Lunak','081234567819'),
(31,'2026020','Salsabila Nur','XII RPL 2','Rekayasa Perangkat Lunak','081234567820'),
(32,'2026021','Tegar Pratama','XII TKJ 1','Teknik Komputer dan Jaringan','081234567821'),
(33,'2026022','Ulfa Maharani','XII RPL 1','Rekayasa Perangkat Lunak','081234567822'),
(34,'2026023','Vino Aditya','XII RPL 2','Rekayasa Perangkat Lunak','081234567823'),
(35,'2026024','Winda Sari','XII TKJ 1','Teknik Komputer dan Jaringan','081234567824'),
(36,'2026025','Yoga Pratama','XII RPL 1','Rekayasa Perangkat Lunak','081234567825');

INSERT INTO pembimbing (user_id,nip,nama,no_hp) VALUES
(2,'19800001','Budi Santoso','085700000001'),
(3,'19800002','Siti Rahma','085700000002'),
(4,'19800003','Agus Prasetyo','085700000003'),
(5,'19800004','Dewi Lestari','085700000004'),
(6,'19800005','Eko Wahyudi','085700000005'),
(7,'19800006','Rina Marlina','085700000006'),
(8,'19800007','Hendra Gunawan','085700000007'),
(9,'19800008','Yuni Astuti','085700000008'),
(10,'19800009','Joko Susilo','085700000009'),
(11,'19800010','Maya Anggraini','085700000010');

INSERT INTO perusahaan (nama_perusahaan,alamat,bidang,kontak) VALUES
('PT Telkom Indonesia','Jl. Gatot Subroto No. 1 Semarang','Telekomunikasi','0248000001'),
('CV Digital Kreasi','Jl. Pandanaran No. 20 Semarang','Software House','0248000002'),
('PT Nusantara Net','Jl. Pemuda No. 45 Semarang','Jaringan & ISP','0248000003'),
('Dinas Kominfo Kota Semarang','Jl. Pemuda No. 148 Semarang','Pemerintahan - TI','0248000004'),
('PT Solusi Web Indonesia','Jl. Setiabudi No. 12 Semarang','Pengembangan Web','0248000005'),
('Bank Jateng','Jl. Pemuda No. 142 Semarang','Perbankan','0248000006'),
('PT Kreasi Aplikasi','Jl. Majapahit No. 88 Semarang','Aplikasi Mobile','0248000007'),
('RS Kariadi','Jl. Dr. Sutomo No. 16 Semarang','Sistem Informasi Rumah Sakit','0248000008'),
('CV Mitra Komputer','Jl. Sriwijaya No. 7 Semarang','Service & Jaringan','0248000009'),
('PT Inovasi Data','Jl. Simpang Lima No. 3 Semarang','Data & Analitik','0248000010');

INSERT INTO penempatan (siswa_id,perusahaan_id,pembimbing_id,tanggal_mulai,tanggal_selesai,status) VALUES
(1,1,1,'2026-09-14','2026-12-14','Aktif'),
(2,2,2,'2026-09-14','2026-12-14','Aktif'),
(3,3,3,'2026-09-14','2026-12-14','Aktif'),
(4,4,4,'2026-09-14','2026-12-14','Aktif'),
(5,5,5,'2026-09-14','2026-12-14','Aktif'),
(6,6,6,'2026-09-14','2026-12-14','Aktif'),
(7,7,7,'2026-06-01','2026-09-01','Selesai'),
(8,8,8,'2026-06-01','2026-09-01','Selesai'),
(9,9,9,'2026-11-01','2027-02-01','Belum Mulai'),
(10,10,10,'2026-11-01','2027-02-01','Belum Mulai');

INSERT INTO jurnal (siswa_id,tanggal,kegiatan,hasil,kendala) VALUES
(1,'2026-09-21','Mengenal lingkungan kerja dan prosedur perusahaan','Orientasi selesai','Belum hafal alur kerja'),
(1,'2026-09-22','Membuat desain database','ERD selesai dibuat','Masih kesulitan menentukan relasi tabel'),
(1,'2026-09-23','Membuat halaman login','Halaman login berfungsi','Validasi belum sempurna'),
(1,'2026-09-24','Membuat modul CRUD','Modul CRUD selesai','Tidak ada kendala'),
(1,'2026-09-25','Pengujian aplikasi','Bug berhasil diperbaiki','Beberapa bug ditemukan'),
(1,CURDATE(),'Menyusun dokumentasi','Dokumen selesai','Tidak ada kendala'),
(2,'2026-09-21','Mengenal lingkungan kerja dan prosedur perusahaan','Orientasi selesai','Belum hafal alur kerja'),
(2,'2026-09-22','Membuat desain database','ERD selesai dibuat','Masih kesulitan menentukan relasi tabel'),
(2,'2026-09-23','Membuat halaman login','Halaman login berfungsi','Validasi belum sempurna'),
(2,'2026-09-24','Membuat modul CRUD','Modul CRUD selesai','Tidak ada kendala'),
(2,'2026-09-25','Pengujian aplikasi','Bug berhasil diperbaiki','Beberapa bug ditemukan'),
(2,CURDATE(),'Menyusun dokumentasi','Dokumen selesai','Tidak ada kendala'),
(3,'2026-09-21','Mengenal lingkungan kerja dan prosedur perusahaan','Orientasi selesai','Belum hafal alur kerja'),
(3,'2026-09-22','Membuat desain database','ERD selesai dibuat','Masih kesulitan menentukan relasi tabel'),
(3,'2026-09-23','Membuat halaman login','Halaman login berfungsi','Validasi belum sempurna'),
(3,'2026-09-24','Membuat modul CRUD','Modul CRUD selesai','Tidak ada kendala'),
(3,'2026-09-25','Pengujian aplikasi','Bug berhasil diperbaiki','Beberapa bug ditemukan'),
(3,CURDATE(),'Menyusun dokumentasi','Dokumen selesai','Tidak ada kendala'),
(4,'2026-09-21','Mengenal lingkungan kerja dan prosedur perusahaan','Orientasi selesai','Belum hafal alur kerja'),
(4,'2026-09-22','Membuat desain database','ERD selesai dibuat','Masih kesulitan menentukan relasi tabel'),
(4,'2026-09-23','Membuat halaman login','Halaman login berfungsi','Validasi belum sempurna'),
(4,'2026-09-24','Membuat modul CRUD','Modul CRUD selesai','Tidak ada kendala'),
(4,'2026-09-25','Pengujian aplikasi','Bug berhasil diperbaiki','Beberapa bug ditemukan'),
(4,'2026-09-28','Menyusun dokumentasi','Dokumen selesai','Tidak ada kendala'),
(5,'2026-09-21','Mengenal lingkungan kerja dan prosedur perusahaan','Orientasi selesai','Belum hafal alur kerja'),
(5,'2026-09-22','Membuat desain database','ERD selesai dibuat','Masih kesulitan menentukan relasi tabel'),
(5,'2026-09-23','Membuat halaman login','Halaman login berfungsi','Validasi belum sempurna'),
(5,'2026-09-24','Membuat modul CRUD','Modul CRUD selesai','Tidak ada kendala'),
(5,'2026-09-25','Pengujian aplikasi','Bug berhasil diperbaiki','Beberapa bug ditemukan'),
(5,'2026-09-28','Menyusun dokumentasi','Dokumen selesai','Tidak ada kendala');

INSERT INTO absensi (siswa_id,tanggal,status) VALUES
(1,'2026-09-21','Hadir'),
(1,'2026-09-22','Hadir'),
(1,'2026-09-23','Hadir'),
(1,'2026-09-24','Hadir'),
(1,'2026-09-25','Hadir'),
(1,'2026-09-28','Hadir'),
(2,'2026-09-21','Hadir'),
(2,'2026-09-22','Hadir'),
(2,'2026-09-23','Hadir'),
(2,'2026-09-24','Hadir'),
(2,'2026-09-25','Hadir'),
(2,'2026-09-28','Izin'),
(3,'2026-09-21','Hadir'),
(3,'2026-09-22','Hadir'),
(3,'2026-09-23','Hadir'),
(3,'2026-09-24','Hadir'),
(3,'2026-09-25','Sakit'),
(3,'2026-09-28','Alpa'),
(4,'2026-09-21','Hadir'),
(4,'2026-09-22','Hadir'),
(4,'2026-09-23','Hadir'),
(4,'2026-09-24','Izin'),
(4,'2026-09-25','Alpa'),
(4,'2026-09-28','Alpa'),
(5,'2026-09-21','Hadir'),
(5,'2026-09-22','Hadir'),
(5,'2026-09-23','Hadir'),
(5,'2026-09-24','Hadir'),
(5,'2026-09-25','Hadir'),
(5,'2026-09-28','Hadir');

INSERT INTO monitoring (siswa_id,pembimbing_id,tanggal,catatan,nilai,rekomendasi) VALUES
(1,1,'2026-09-21','Siswa mampu berkomunikasi dengan baik dan dapat menyelesaikan tugas yang diberikan industri.',85,'Pertahankan kinerja'),
(2,2,'2026-09-22','Siswa disiplin dan aktif bertanya.',80,'Tingkatkan inisiatif'),
(3,3,'2026-09-23','Perlu meningkatkan ketelitian dalam pengerjaan.',70,'Latihan lebih banyak'),
(4,4,'2026-09-24','Kehadiran perlu diperbaiki.',60,'Bimbingan kedisiplinan'),
(5,5,'2026-09-25','Hasil kerja baik dan tepat waktu.',88,'Layak diteruskan'),
(6,6,'2026-09-26','Siswa mampu berkomunikasi dengan baik dan dapat menyelesaikan tugas yang diberikan industri.',90,'Pertahankan kinerja'),
(7,7,'2026-09-27','Siswa disiplin dan aktif bertanya.',78,'Tingkatkan inisiatif'),
(8,8,'2026-09-28','Perlu meningkatkan ketelitian dalam pengerjaan.',82,'Latihan lebih banyak'),
(9,9,'2026-09-29','Kehadiran perlu diperbaiki.',75,'Bimbingan kedisiplinan'),
(10,10,'2026-09-30','Hasil kerja baik dan tepat waktu.',86,'Layak diteruskan');
