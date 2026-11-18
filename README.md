# Tubes-Pemrograman-Lanjut

Pemrograman Lanjut x Basis Data: aplikasi penjualan UMKM DSM
(PHP Native OOP + MySQL).

## Menjalankan
1. Laragon: Start All
2. `mysql -u root < database/skema.sql`
3. `mysql -u root < database/seed.sql`
4. `php -S localhost:8000 -t public`
5. Buka http://localhost:8000

## Struktur
- `config/` konfigurasi database
- `database/` skema dan data uji
- `src/Core` Database (PDO), QueryBuilder
- `src/Exception` custom exception
- `src/Entity` BaseEntity, Validatable, Entity
- `src/Validation` Input, Validator
- `src/Repository` Repository dan kontraknya
- `public/` halaman web

## Tim
- Eplin: Database, QueryBuilder, exception
- Pay: Entity, Input, Validator
- Niph: Repository, halaman web