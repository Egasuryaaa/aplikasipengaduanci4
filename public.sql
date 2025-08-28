/*
 Navicat Premium Dump SQL

 Source Server         : ega
 Source Server Type    : PostgreSQL
 Source Server Version : 160002 (160002)
 Source Host           : localhost:5432
 Source Catalog        : pengaduan_kominfo
 Source Schema         : public

 Target Server Type    : PostgreSQL
 Target Server Version : 160002 (160002)
 File Encoding         : 65001

 Date: 28/08/2025 19:33:06
*/


-- ----------------------------
-- Sequence structure for instansi_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."instansi_id_seq";
CREATE SEQUENCE "public"."instansi_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for kategori_pengaduan_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."kategori_pengaduan_id_seq";
CREATE SEQUENCE "public"."kategori_pengaduan_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for komentar_pengaduan_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."komentar_pengaduan_id_seq";
CREATE SEQUENCE "public"."komentar_pengaduan_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for migrations_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."migrations_id_seq";
CREATE SEQUENCE "public"."migrations_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for notifications_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."notifications_id_seq";
CREATE SEQUENCE "public"."notifications_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for pengaduan_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."pengaduan_id_seq";
CREATE SEQUENCE "public"."pengaduan_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for status_history_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."status_history_id_seq";
CREATE SEQUENCE "public"."status_history_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for users_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."users_id_seq";
CREATE SEQUENCE "public"."users_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Table structure for instansi
-- ----------------------------
DROP TABLE IF EXISTS "public"."instansi";
CREATE TABLE "public"."instansi" (
  "id" int4 NOT NULL DEFAULT nextval('instansi_id_seq'::regclass),
  "nama" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "alamat" text COLLATE "pg_catalog"."default",
  "telepon" varchar(20) COLLATE "pg_catalog"."default",
  "email" varchar(255) COLLATE "pg_catalog"."default",
  "is_active" bool NOT NULL DEFAULT true,
  "created_at" timestamp(6),
  "updated_at" timestamp(6)
)
;

-- ----------------------------
-- Records of instansi
-- ----------------------------
INSERT INTO "public"."instansi" VALUES (2, 'Dinas Pendidikan, Pemuda dan Olahraga', 'Jl. Pemuda No. 32, Wonosari, Gunung Kidul', '0274-391038', 'dikpora@gunungkidulkab.go.id', 't', '2025-08-12 01:45:48', '2025-08-12 01:45:48');
INSERT INTO "public"."instansi" VALUES (3, 'Dinas Kesehatan', 'Jl. Dr. Sutomo No. 15, Wonosari, Gunung Kidul', '0274-391039', 'dinkes@gunungkidulkab.go.id', 't', '2025-08-12 01:45:48', '2025-08-12 01:45:48');
INSERT INTO "public"."instansi" VALUES (4, 'Dinas Pekerjaan Umum dan Perumahan Rakyat', 'Jl. Veteran No. 28, Wonosari, Gunung Kidul', '0274-391040', 'pupr@gunungkidulkab.go.id', 't', '2025-08-12 01:45:48', '2025-08-12 01:45:48');
INSERT INTO "public"."instansi" VALUES (5, 'Dinas Sosial', 'Jl. Raya Yogya-Wonosari Km. 31, Wonosari', '0274-391041', 'dinsos@gunungkidulkab.go.id', 't', '2025-08-12 01:45:48', '2025-08-12 01:45:48');
INSERT INTO "public"."instansi" VALUES (6, 'Dinas Pariwisata', 'Jl. Baron No. 5, Wonosari, Gunung Kidul', '0274-391042', 'dispar@gunungkidulkab.go.id', 't', '2025-08-12 01:45:48', '2025-08-12 01:45:48');
INSERT INTO "public"."instansi" VALUES (7, 'Dinas Perhubungan', 'Jl. Nasional III No. 12, Wonosari, Gunung Kidul', '0274-391043', 'dishub@gunungkidulkab.go.id', 't', '2025-08-12 01:45:48', '2025-08-12 01:45:48');
INSERT INTO "public"."instansi" VALUES (8, 'Dinas Lingkungan Hidup', 'Jl. Tentara Pelajar No. 8, Wonosari, Gunung Kidul', '0274-391044', 'dlh@gunungkidulkab.go.id', 't', '2025-08-12 01:45:48', '2025-08-12 01:45:48');
INSERT INTO "public"."instansi" VALUES (9, 'Dinas Pertanian dan Pangan', 'Jl. Ngalau No. 20, Wonosari, Gunung Kidul', '0274-391045', 'distanpang@gunungkidulkab.go.id', 't', '2025-08-12 01:45:48', '2025-08-12 01:45:48');
INSERT INTO "public"."instansi" VALUES (10, 'Dinas Perdagangan dan Perindustrian', 'Jl. Pahlawan No. 14, Wonosari, Gunung Kidul', '0274-391046', 'disperindag@gunungkidulkab.go.id', 't', '2025-08-12 01:45:48', '2025-08-12 01:45:48');
INSERT INTO "public"."instansi" VALUES (1, 'Dinas Komunikasi dan Informatikaa', 'Jl. Brigjen Katamso No. 1, Wonosari, Gunung Kidul', '02743910370', 'kominfo@gunungkidulkab.go.id', 't', '2025-08-12 01:45:48', '2025-08-12 02:11:27');
INSERT INTO "public"."instansi" VALUES (11, 'Dinas Komunikasi dan Informatika', 'Jl. Brigjen Katamso No. 1, Wonosari, Gunung Kidul', '0274-391037', 'kominfo@gunungkidulkab.go.id', 't', '2025-08-12 07:49:51', '2025-08-12 07:49:51');
INSERT INTO "public"."instansi" VALUES (12, 'Dinas Pendidikan, Pemuda dan Olahraga', 'Jl. Pemuda No. 32, Wonosari, Gunung Kidul', '0274-391038', 'dikpora@gunungkidulkab.go.id', 't', '2025-08-12 07:49:51', '2025-08-12 07:49:51');
INSERT INTO "public"."instansi" VALUES (13, 'Dinas Kesehatan', 'Jl. Dr. Sutomo No. 15, Wonosari, Gunung Kidul', '0274-391039', 'dinkes@gunungkidulkab.go.id', 't', '2025-08-12 07:49:51', '2025-08-12 07:49:51');
INSERT INTO "public"."instansi" VALUES (14, 'Dinas Pekerjaan Umum dan Perumahan Rakyat', 'Jl. Veteran No. 28, Wonosari, Gunung Kidul', '0274-391040', 'pupr@gunungkidulkab.go.id', 't', '2025-08-12 07:49:51', '2025-08-12 07:49:51');
INSERT INTO "public"."instansi" VALUES (15, 'Dinas Sosial', 'Jl. Raya Yogya-Wonosari Km. 31, Wonosari', '0274-391041', 'dinsos@gunungkidulkab.go.id', 't', '2025-08-12 07:49:51', '2025-08-12 07:49:51');
INSERT INTO "public"."instansi" VALUES (16, 'Dinas Pariwisata', 'Jl. Baron No. 5, Wonosari, Gunung Kidul', '0274-391042', 'dispar@gunungkidulkab.go.id', 't', '2025-08-12 07:49:51', '2025-08-12 07:49:51');
INSERT INTO "public"."instansi" VALUES (17, 'Dinas Perhubungan', 'Jl. Nasional III No. 12, Wonosari, Gunung Kidul', '0274-391043', 'dishub@gunungkidulkab.go.id', 't', '2025-08-12 07:49:51', '2025-08-12 07:49:51');
INSERT INTO "public"."instansi" VALUES (18, 'Dinas Lingkungan Hidup', 'Jl. Tentara Pelajar No. 8, Wonosari, Gunung Kidul', '0274-391044', 'dlh@gunungkidulkab.go.id', 't', '2025-08-12 07:49:51', '2025-08-12 07:49:51');
INSERT INTO "public"."instansi" VALUES (19, 'Dinas Pertanian dan Pangan', 'Jl. Ngalau No. 20, Wonosari, Gunung Kidul', '0274-391045', 'distanpang@gunungkidulkab.go.id', 't', '2025-08-12 07:49:51', '2025-08-12 07:49:51');
INSERT INTO "public"."instansi" VALUES (20, 'Dinas Perdagangan dan Perindustrian', 'Jl. Pahlawan No. 14, Wonosari, Gunung Kidul', '0274-391046', 'disperindag@gunungkidulkab.go.id', 't', '2025-08-12 07:49:51', '2025-08-12 07:49:51');

-- ----------------------------
-- Table structure for kategori_pengaduan
-- ----------------------------
DROP TABLE IF EXISTS "public"."kategori_pengaduan";
CREATE TABLE "public"."kategori_pengaduan" (
  "id" int4 NOT NULL DEFAULT nextval('kategori_pengaduan_id_seq'::regclass),
  "nama" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "deskripsi" text COLLATE "pg_catalog"."default",
  "is_active" bool NOT NULL DEFAULT true,
  "created_at" timestamp(6),
  "updated_at" timestamp(6)
)
;

-- ----------------------------
-- Records of kategori_pengaduan
-- ----------------------------
INSERT INTO "public"."kategori_pengaduan" VALUES (1, 'Infrastruktur TI', 'Pengaduan terkait infrastruktur teknologi informasi, jaringan, server, dan perangkat keras', 't', '2025-08-12 01:46:02', '2025-08-12 01:46:02');
INSERT INTO "public"."kategori_pengaduan" VALUES (2, 'Aplikasi dan Sistem', 'Pengaduan terkait aplikasi, sistem informasi, dan software', 't', '2025-08-12 01:46:02', '2025-08-12 01:46:02');
INSERT INTO "public"."kategori_pengaduan" VALUES (3, 'Website dan Portal', 'Pengaduan terkait website resmi, portal, dan layanan online', 't', '2025-08-12 01:46:02', '2025-08-12 01:46:02');
INSERT INTO "public"."kategori_pengaduan" VALUES (4, 'Keamanan Informasi', 'Pengaduan terkait keamanan data, privasi, dan cyber security', 't', '2025-08-12 01:46:02', '2025-08-12 01:46:02');
INSERT INTO "public"."kategori_pengaduan" VALUES (5, 'Layanan Publik Digital', 'Pengaduan terkait layanan publik berbasis digital dan e-government', 't', '2025-08-12 01:46:02', '2025-08-12 01:46:02');
INSERT INTO "public"."kategori_pengaduan" VALUES (6, 'Komunikasi dan Media', 'Pengaduan terkait komunikasi publik, media sosial, dan informasi', 't', '2025-08-12 01:46:02', '2025-08-12 01:46:02');
INSERT INTO "public"."kategori_pengaduan" VALUES (7, 'Pelatihan dan Literasi Digital', 'Pengaduan terkait pelatihan TI dan program literasi digital', 't', '2025-08-12 01:46:02', '2025-08-12 01:46:02');
INSERT INTO "public"."kategori_pengaduan" VALUES (8, 'Data dan Statistik', 'Pengaduan terkait pengelolaan data, statistik, dan basis data', 't', '2025-08-12 01:46:02', '2025-08-12 01:46:02');
INSERT INTO "public"."kategori_pengaduan" VALUES (9, 'Smart City', 'Pengaduan terkait program smart city dan inovasi kota cerdas', 't', '2025-08-12 01:46:02', '2025-08-12 01:46:02');

-- ----------------------------
-- Table structure for komentar_pengaduan
-- ----------------------------
DROP TABLE IF EXISTS "public"."komentar_pengaduan";
CREATE TABLE "public"."komentar_pengaduan" (
  "id" int4 NOT NULL DEFAULT nextval('komentar_pengaduan_id_seq'::regclass),
  "pengaduan_id" int4 NOT NULL,
  "user_id" int4 NOT NULL,
  "komentar" text COLLATE "pg_catalog"."default" NOT NULL,
  "is_internal" bool NOT NULL DEFAULT false,
  "created_at" timestamp(6),
  "updated_at" timestamp(6)
)
;

-- ----------------------------
-- Records of komentar_pengaduan
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS "public"."migrations";
CREATE TABLE "public"."migrations" (
  "id" int8 NOT NULL DEFAULT nextval('migrations_id_seq'::regclass),
  "version" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "class" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "group" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "namespace" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "time" int4 NOT NULL,
  "batch" int4 NOT NULL
)
;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO "public"."migrations" VALUES (8, '2025-08-12-000001', 'App\Database\Migrations\CreateUsersTable', 'default', 'App', 1754963143, 1);
INSERT INTO "public"."migrations" VALUES (9, '2025-08-12-000002', 'App\Database\Migrations\CreateInstansiTable', 'default', 'App', 1754963143, 1);
INSERT INTO "public"."migrations" VALUES (10, '2025-08-12-000003', 'App\Database\Migrations\CreateKategoriPengaduanTable', 'default', 'App', 1754963143, 1);
INSERT INTO "public"."migrations" VALUES (11, '2025-08-12-000004', 'App\Database\Migrations\CreatePengaduanTable', 'default', 'App', 1754963143, 1);
INSERT INTO "public"."migrations" VALUES (12, '2025-08-12-000005', 'App\Database\Migrations\CreateStatusHistoryTable', 'default', 'App', 1754963143, 1);
INSERT INTO "public"."migrations" VALUES (13, '2025-08-12-000006', 'App\Database\Migrations\CreateKomentarPengaduanTable', 'default', 'App', 1754963143, 1);
INSERT INTO "public"."migrations" VALUES (14, '2025-08-12-000007', 'App\Database\Migrations\CreateNotificationsTable', 'default', 'App', 1754963143, 1);
INSERT INTO "public"."migrations" VALUES (15, '2025-08-12-000008', 'App\Database\Migrations\ChangeFotoBuktiColumnType', 'default', 'App', 1754974460, 2);
INSERT INTO "public"."migrations" VALUES (16, '2025-08-13-040000', 'App\Database\Migrations\AddApiTokenToUsers', 'default', 'App', 1755056512, 3);
INSERT INTO "public"."migrations" VALUES (17, '2025-08-13-050000', 'App\Database\Migrations\AddApiTokenToUsers', 'default', 'App', 1755573013, 4);
INSERT INTO "public"."migrations" VALUES (18, '2025-08-19-095000', 'App\Database\Migrations\CreateLoginAttemptsTable', 'default', 'App', 1755573040, 5);

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS "public"."notifications";
CREATE TABLE "public"."notifications" (
  "id" int4 NOT NULL DEFAULT nextval('notifications_id_seq'::regclass),
  "user_id" int4 NOT NULL,
  "pengaduan_id" int4,
  "title" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "message" text COLLATE "pg_catalog"."default" NOT NULL,
  "type" varchar(50) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'info'::character varying,
  "is_read" bool NOT NULL DEFAULT false,
  "created_at" timestamp(6)
)
;

-- ----------------------------
-- Records of notifications
-- ----------------------------
INSERT INTO "public"."notifications" VALUES (5, 4, 26, 'Status Pengaduan Diperbarui', 'Status pengaduan ADU2025080002 telah diubah menjadi: Selesai', 'success', 'f', '2025-08-18 15:17:55');
INSERT INTO "public"."notifications" VALUES (6, 4, 29, 'Status Pengaduan Diperbarui', 'Status pengaduan ADU2025080003 telah diubah menjadi: Selesai', 'success', 'f', '2025-08-18 15:32:43');
INSERT INTO "public"."notifications" VALUES (7, 4, 29, 'Status Pengaduan Diperbarui', 'Status pengaduan ADU2025080003 telah diubah menjadi: Sedang Diproses', 'info', 'f', '2025-08-18 15:33:56');
INSERT INTO "public"."notifications" VALUES (9, 12, 36, 'Status Pengaduan Diperbarui', 'Status pengaduan ADU2025080009 telah diubah menjadi: Selesai', 'success', 'f', '2025-08-28 02:42:14');
INSERT INTO "public"."notifications" VALUES (10, 12, 36, 'Status Pengaduan Diperbarui', 'Status pengaduan ADU2025080009 telah diubah menjadi: Selesai', 'success', 'f', '2025-08-28 11:44:08');

-- ----------------------------
-- Table structure for pengaduan
-- ----------------------------
DROP TABLE IF EXISTS "public"."pengaduan";
CREATE TABLE "public"."pengaduan" (
  "id" int4 NOT NULL DEFAULT nextval('pengaduan_id_seq'::regclass),
  "uuid" varchar(36) COLLATE "pg_catalog"."default" NOT NULL,
  "nomor_pengaduan" varchar(50) COLLATE "pg_catalog"."default" NOT NULL,
  "user_id" int4 NOT NULL,
  "instansi_id" int4 NOT NULL,
  "kategori_id" int4 NOT NULL,
  "deskripsi" text COLLATE "pg_catalog"."default" NOT NULL,
  "foto_bukti" varchar(255) COLLATE "pg_catalog"."default",
  "status" varchar(20) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'pending'::character varying,
  "tanggal_selesai" timestamp(6),
  "keterangan_admin" text COLLATE "pg_catalog"."default",
  "created_at" timestamp(6),
  "updated_at" timestamp(6)
)
;

-- ----------------------------
-- Records of pengaduan
-- ----------------------------
INSERT INTO "public"."pengaduan" VALUES (26, '6c96fd4e-fe3b-4984-8831-41c56d638048', 'ADU2025080002', 4, 1, 4, 'bismillah kenek', '["1755530245_b0933bd085fb2fc27726.jpg"]', 'selesai', '2025-08-18 15:17:55', 'wes dibenakno', '2025-08-18 15:17:25', '2025-08-18 15:17:55');
INSERT INTO "public"."pengaduan" VALUES (29, 'd3c14e19-5dd9-4205-be84-75f888777640', 'ADU2025080003', 4, 1, 4, 'fix kabehhh', '["1755531129_ab09dd2948a69957068a.png"]', 'diproses', '2025-08-18 15:32:43', NULL, '2025-08-18 15:32:09', '2025-08-18 15:33:56');
INSERT INTO "public"."pengaduan" VALUES (36, 'c17c60ca-1c25-4f73-87c6-c30c03c8991d', 'ADU2025080009', 12, 3, 7, 'ada error di dinas kesehatan', '["1756348852_e0cea56c5cb98ec774fe.png"]', 'selesai', '2025-08-28 11:44:08', 'sudah diperbaiki ya', '2025-08-28 02:40:52', '2025-08-28 11:44:08');

-- ----------------------------
-- Table structure for status_history
-- ----------------------------
DROP TABLE IF EXISTS "public"."status_history";
CREATE TABLE "public"."status_history" (
  "id" int4 NOT NULL DEFAULT nextval('status_history_id_seq'::regclass),
  "pengaduan_id" int4 NOT NULL,
  "status_old" varchar(20) COLLATE "pg_catalog"."default",
  "status_new" varchar(20) COLLATE "pg_catalog"."default" NOT NULL,
  "keterangan" text COLLATE "pg_catalog"."default",
  "updated_by" int4 NOT NULL,
  "created_at" timestamp(6)
)
;

-- ----------------------------
-- Records of status_history
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS "public"."users";
CREATE TABLE "public"."users" (
  "id" int4 NOT NULL DEFAULT nextval('users_id_seq'::regclass),
  "uuid" varchar(36) COLLATE "pg_catalog"."default" NOT NULL,
  "name" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "email" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "phone" varchar(20) COLLATE "pg_catalog"."default",
  "password" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "instansi_id" int4,
  "role" varchar(20) COLLATE "pg_catalog"."default" NOT NULL DEFAULT 'user'::character varying,
  "is_active" bool NOT NULL DEFAULT true,
  "email_verified_at" timestamp(6),
  "last_login" timestamp(6),
  "created_at" timestamp(6),
  "updated_at" timestamp(6),
  "api_token" varchar(255) COLLATE "pg_catalog"."default"
)
;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO "public"."users" VALUES (3, 'e2d68faf-3816-49c9-b269-e89b409552a2', 'Admin 2', 'admin2@kominfo-gunungkidul.go.id', '081234567892', '$2y$10$8m9JCxRE8fPWRkB0.IaB4.DbFbYKtZEQ0xuOkxVcEOme4Yn8U54C.', NULL, 'admin', 't', '2025-08-12 01:46:07', NULL, '2025-08-12 01:46:07', '2025-08-12 01:46:07', NULL);
INSERT INTO "public"."users" VALUES (2, 'eda831cb-1e81-4633-8a16-a3c11e0dc18f', 'Admin 1', 'admin1@kominfo-gunungkidul.go.id', '081234567891', '$2y$10$xX/zgR204Iq1On3mlJu4NuC/1TC12lzwVGhRf70XNASduaS5aYO02', NULL, 'admin', 't', '2025-08-12 01:46:07', '2025-08-28 12:05:05', '2025-08-12 01:46:07', '2025-08-28 12:05:05', NULL);
INSERT INTO "public"."users" VALUES (1, '27f125f9-b7d1-4c01-b883-e9bab0a12d82', 'Master Admin', 'master@kominfo-gunungkidul.go.id', '081234567890', '$2y$10$N45sMYRR9qXLYoMuFn4cve.cfxycAYvOpK.uP9r33FFITc6ZdO6Fe', NULL, 'master', 't', '2025-08-12 01:46:07', '2025-08-28 12:05:40', '2025-08-12 01:46:07', '2025-08-28 12:05:40', NULL);
INSERT INTO "public"."users" VALUES (11, 'ae95599d-a1e9-4271-9826-cf70ac3adf8c', 'john', 'john@gmail.com', '080889899898', '$2y$10$EvcesMzL87zoQx91pKagl.3jqM1fGXzH5DcRJmbFDYQDKkj7nuWBy', 17, 'master', 't', NULL, '2025-08-19 04:10:04', '2025-08-19 04:07:28', '2025-08-19 04:10:04', NULL);
INSERT INTO "public"."users" VALUES (4, '3626e00a-cf2e-4b66-aca9-f8d54da211af', 'John Doe', 'john.doe@gmail.com', '081234567893', '$2y$10$fN4/K96aaIS/s94O/MSqO.w00VomNicGXCE6dujCTmV0V2uBWQ7z2', 1, 'user', 't', '2025-08-12 01:46:07', '2025-08-28 02:26:36', '2025-08-12 01:46:07', '2025-08-28 02:26:36', NULL);
INSERT INTO "public"."users" VALUES (7, '19d1439d-3526-4d00-8b6d-e8f0107d6166', 'ega', 'egasurya04@gmail.com', '082257108680', '$2y$10$wDkJd.ay7ITrO5Kb9D3EyeALI4yPvG36GELOAROa/mkinj2byv0cW', 11, 'user', 't', NULL, '2025-08-19 02:55:29', '2025-08-19 02:44:05', '2025-08-19 02:56:10', NULL);
INSERT INTO "public"."users" VALUES (12, 'b6ec1993-3811-4e13-87b3-c8e54decd129', 'user', 'user1234@gmail.com', '0888889899', '$2y$10$oYAh7UOMWrJdovH5OH5dCeic4W9wnDKyUJOV0wxnpACCSlk1pKwbq', 3, 'user', 't', NULL, '2025-08-28 02:56:28', '2025-08-28 02:39:47', '2025-08-28 02:56:28', NULL);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."instansi_id_seq"
OWNED BY "public"."instansi"."id";
SELECT setval('"public"."instansi_id_seq"', 20, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."kategori_pengaduan_id_seq"
OWNED BY "public"."kategori_pengaduan"."id";
SELECT setval('"public"."kategori_pengaduan_id_seq"', 21, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."komentar_pengaduan_id_seq"
OWNED BY "public"."komentar_pengaduan"."id";
SELECT setval('"public"."komentar_pengaduan_id_seq"', 17, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."migrations_id_seq"
OWNED BY "public"."migrations"."id";
SELECT setval('"public"."migrations_id_seq"', 18, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."notifications_id_seq"
OWNED BY "public"."notifications"."id";
SELECT setval('"public"."notifications_id_seq"', 10, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."pengaduan_id_seq"
OWNED BY "public"."pengaduan"."id";
SELECT setval('"public"."pengaduan_id_seq"', 37, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."status_history_id_seq"
OWNED BY "public"."status_history"."id";
SELECT setval('"public"."status_history_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."users_id_seq"
OWNED BY "public"."users"."id";
SELECT setval('"public"."users_id_seq"', 12, true);

-- ----------------------------
-- Primary Key structure for table instansi
-- ----------------------------
ALTER TABLE "public"."instansi" ADD CONSTRAINT "pk_instansi" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table kategori_pengaduan
-- ----------------------------
ALTER TABLE "public"."kategori_pengaduan" ADD CONSTRAINT "pk_kategori_pengaduan" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table komentar_pengaduan
-- ----------------------------
CREATE INDEX "komentar_pengaduan_pengaduan_id" ON "public"."komentar_pengaduan" USING btree (
  "pengaduan_id" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table komentar_pengaduan
-- ----------------------------
ALTER TABLE "public"."komentar_pengaduan" ADD CONSTRAINT "pk_komentar_pengaduan" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table migrations
-- ----------------------------
ALTER TABLE "public"."migrations" ADD CONSTRAINT "pk_migrations" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table notifications
-- ----------------------------
CREATE INDEX "notifications_is_read" ON "public"."notifications" USING btree (
  "is_read" "pg_catalog"."bool_ops" ASC NULLS LAST
);
CREATE INDEX "notifications_user_id" ON "public"."notifications" USING btree (
  "user_id" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Checks structure for table notifications
-- ----------------------------
ALTER TABLE "public"."notifications" ADD CONSTRAINT "check_type" CHECK (type::text = ANY (ARRAY['info'::character varying, 'success'::character varying, 'warning'::character varying, 'error'::character varying]::text[]));

-- ----------------------------
-- Primary Key structure for table notifications
-- ----------------------------
ALTER TABLE "public"."notifications" ADD CONSTRAINT "pk_notifications" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table pengaduan
-- ----------------------------
CREATE INDEX "pengaduan_nomor_pengaduan" ON "public"."pengaduan" USING btree (
  "nomor_pengaduan" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);
CREATE INDEX "pengaduan_status" ON "public"."pengaduan" USING btree (
  "status" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);
CREATE INDEX "pengaduan_user_id" ON "public"."pengaduan" USING btree (
  "user_id" "pg_catalog"."int4_ops" ASC NULLS LAST
);
CREATE INDEX "pengaduan_uuid" ON "public"."pengaduan" USING btree (
  "uuid" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);

-- ----------------------------
-- Uniques structure for table pengaduan
-- ----------------------------
ALTER TABLE "public"."pengaduan" ADD CONSTRAINT "pengaduan_uuid_key" UNIQUE ("uuid");
ALTER TABLE "public"."pengaduan" ADD CONSTRAINT "pengaduan_nomor_pengaduan_key" UNIQUE ("nomor_pengaduan");

-- ----------------------------
-- Checks structure for table pengaduan
-- ----------------------------
ALTER TABLE "public"."pengaduan" ADD CONSTRAINT "check_status" CHECK (status::text = ANY (ARRAY['pending'::character varying, 'diproses'::character varying, 'selesai'::character varying, 'ditolak'::character varying]::text[]));

-- ----------------------------
-- Primary Key structure for table pengaduan
-- ----------------------------
ALTER TABLE "public"."pengaduan" ADD CONSTRAINT "pk_pengaduan" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table status_history
-- ----------------------------
CREATE INDEX "status_history_pengaduan_id" ON "public"."status_history" USING btree (
  "pengaduan_id" "pg_catalog"."int4_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table status_history
-- ----------------------------
ALTER TABLE "public"."status_history" ADD CONSTRAINT "pk_status_history" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table users
-- ----------------------------
CREATE INDEX "users_email" ON "public"."users" USING btree (
  "email" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);
CREATE INDEX "users_role" ON "public"."users" USING btree (
  "role" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);
CREATE INDEX "users_uuid" ON "public"."users" USING btree (
  "uuid" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);

-- ----------------------------
-- Uniques structure for table users
-- ----------------------------
ALTER TABLE "public"."users" ADD CONSTRAINT "users_uuid_key" UNIQUE ("uuid");
ALTER TABLE "public"."users" ADD CONSTRAINT "users_email_key" UNIQUE ("email");

-- ----------------------------
-- Checks structure for table users
-- ----------------------------
ALTER TABLE "public"."users" ADD CONSTRAINT "check_role" CHECK (role::text = ANY (ARRAY['master'::character varying, 'admin'::character varying, 'user'::character varying]::text[]));

-- ----------------------------
-- Primary Key structure for table users
-- ----------------------------
ALTER TABLE "public"."users" ADD CONSTRAINT "pk_users" PRIMARY KEY ("id");

-- ----------------------------
-- Foreign Keys structure for table komentar_pengaduan
-- ----------------------------
ALTER TABLE "public"."komentar_pengaduan" ADD CONSTRAINT "komentar_pengaduan_pengaduan_id_foreign" FOREIGN KEY ("pengaduan_id") REFERENCES "public"."pengaduan" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."komentar_pengaduan" ADD CONSTRAINT "komentar_pengaduan_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- ----------------------------
-- Foreign Keys structure for table notifications
-- ----------------------------
ALTER TABLE "public"."notifications" ADD CONSTRAINT "notifications_pengaduan_id_foreign" FOREIGN KEY ("pengaduan_id") REFERENCES "public"."pengaduan" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."notifications" ADD CONSTRAINT "notifications_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- ----------------------------
-- Foreign Keys structure for table pengaduan
-- ----------------------------
ALTER TABLE "public"."pengaduan" ADD CONSTRAINT "pengaduan_instansi_id_foreign" FOREIGN KEY ("instansi_id") REFERENCES "public"."instansi" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."pengaduan" ADD CONSTRAINT "pengaduan_kategori_id_foreign" FOREIGN KEY ("kategori_id") REFERENCES "public"."kategori_pengaduan" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."pengaduan" ADD CONSTRAINT "pengaduan_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "public"."users" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

-- ----------------------------
-- Foreign Keys structure for table status_history
-- ----------------------------
ALTER TABLE "public"."status_history" ADD CONSTRAINT "status_history_pengaduan_id_foreign" FOREIGN KEY ("pengaduan_id") REFERENCES "public"."pengaduan" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."status_history" ADD CONSTRAINT "status_history_updated_by_foreign" FOREIGN KEY ("updated_by") REFERENCES "public"."users" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
