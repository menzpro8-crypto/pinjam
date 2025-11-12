# Backend Optimization TODO

## 1. Add Database Indexes
- Update init_db.php to add indexes on frequently queried columns (id, status, alat_id, etc.)
- [x] Added indexes to alat, anggota, peminjaman tables

## 2. Optimize Dashboard Queries
- Modify handleDashboard in api.php to combine multiple queries into fewer or single query
- [x] Combined dashboard stats into single query

## 3. Implement Pagination
- Add pagination parameters to GET endpoints for alat, anggota, peminjaman in api.php
- [x] Added pagination to alat, anggota, peminjaman GET endpoints

## 4. Add Caching
- Implement simple file-based caching for dashboard stats in config.php or new cache.php
- [x] Implemented SimpleCache class in cache.php
- [x] Added cache clearing in API endpoints when data changes (alat creation, peminjaman creation/return)

## 5. Test Optimizations
- Run tests to verify faster loading times
