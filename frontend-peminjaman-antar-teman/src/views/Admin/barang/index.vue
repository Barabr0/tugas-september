<template>
  <div class="admin-wrapper">
    <main class="main-content">
      <!-- Topbar Header -->
      <header class="topbar">
        <div class="topbar-left">
          <div class="header-title-wrapper">
            <router-link to="/admin/dashboard" class="btn-back" title="Kembali ke Dashboard">
              <i class="bi bi-arrow-left"></i>
            </router-link>
            <h1>Daftar Barang</h1>
          </div>
          <p>Kelola seluruh data barang milik pengguna yang terdaftar di sistem.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Cari nama barang, deskripsi..." 
            />
          </div>
        </div>
      </header>

      <!-- Main Card / Table Wrapper -->
      <div class="card-section">
        <div class="section-header">
          <div class="title-info">
            <h3>Tabel Data Barang</h3>
            <span class="badge-count">{{ filteredBarangs.length }} Barang</span>
          </div>
        </div>

        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th style="width: 70px;" class="text-center">ID</th>
                <!-- UBAH: Tampilkan Nama Pemilik & Kategori, bukan ID -->
                <th style="width: 160px;">Pemilik</th>
                <th style="width: 140px;">Kategori</th>
                <th style="width: 180px;">Nama Barang</th>
                <th style="width: 120px;" class="text-center">Kondisi</th>
                <th>Deskripsi</th>
                <th style="width: 120px;" class="text-center">Status</th>
                <th style="width: 110px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <!-- State Loading -->
              <tr v-if="loading">
                <td colspan="8" class="empty-state">
                  <i class="bi bi-arrow-repeat spin"></i>
                  <p>Memuat data barang...</p>
                </td>
              </tr>

              <!-- State Kosong -->
              <tr v-else-if="filteredBarangs.length === 0">
                <td colspan="8" class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <p>Tidak ada data barang yang ditemukan.</p>
                </td>
              </tr>

              <!-- Data Loop -->
              <tr v-for="barang in filteredBarangs" :key="barang.id" v-else>
                <td class="text-center id-col">#{{ barang.id }}</td>
                <td class="fw-bold">{{ barang.pemilik?.name || 'Unknown' }}</td>
                <td>{{ barang.kategori?.nama_kategori || '-' }}</td>
                <td class="fw-bold text-navy">{{ barang.nama_barang }}</td>
                <td class="text-center">
                  <span :class="['tag-status', kondisiClass(barang.kondisi)]">
                    {{ getKondisiText(barang.kondisi) }}
                  </span>
                </td>
                <td class="desc-col">{{ barang.deskripsi || '-' }}</td>
                <td class="text-center">
                  <span :class="['tag-status', statusClass(barang.status)]">
                    {{ getStatusText(barang.status) }}
                  </span>
                </td>
                <td class="action-col">
                  <div class="action-buttons">
                    <button class="btn-icon edit" title="Edit Barang" @click="editBarang(barang)">
                      <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn-icon delete" title="Hapus Paksa" @click="deleteBarang(barang.id)">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import adminApi from '@/utils/admin';

export default {
  name: 'AdminBarangIndexView',
  data() {
    return {
      searchQuery: '',
      loading: false,
      barangs: [] // Data akan diisi dari API
    };
  },
  computed: {
    filteredBarangs() {
      if (!this.searchQuery) return this.barangs;
      const query = this.searchQuery.toLowerCase();
      return this.barangs.filter(b => 
        b.nama_barang.toLowerCase().includes(query) ||
        (b.deskripsi && b.deskripsi.toLowerCase().includes(query)) ||
        (b.pemilik && b.pemilik.name.toLowerCase().includes(query))
      );
    }
  },
  mounted() {
    this.fetchBarangs();
  },
  methods: {
    async fetchBarangs() {
      this.loading = true;
      try {
        // Panggil API getAllBarangs dari utils/admin.js
        const response = await adminApi.getAllBarangs();
        this.barangs = response.data.data || response.data || [];
      } catch (error) {
        console.error('Gagal memuat data barang:', error);
        this.$toast.error('Gagal memuat data barang.');
      } finally {
        this.loading = false;
      }
    },
    getKondisiText(k) {
      const map = { B: 'Baik', R: 'Rusak Ringan', P: 'Diperbaiki' };
      return map[k] || k;
    },
    getStatusText(s) {
      const map = { T: 'Tersedia', D: 'Dipinjam', M: 'Maintenance' };
      return map[s] || s;
    },
    kondisiClass(k) {
      const map = { B: 'baik', R: 'rusak', P: 'diperbaiki' };
      return map[k] || '';
    },
    statusClass(s) {
      const map = { T: 'tersedia', D: 'dipinjam', M: 'maintenance' };
      return map[s] || '';
    },
    editBarang(barang) {
      this.$toast.info(`Fitur edit barang: ${barang.nama_barang} akan segera hadir.`);
    },
    async deleteBarang(id) {
      if (!confirm('Hapus barang ini secara permanen dari sistem?')) return;
      
      try {
        // Admin menggunakan fungsi forceDeleteBarang (hapus paksa)
        await adminApi.forceDeleteBarang(id);
        this.barangs = this.barangs.filter(b => b.id !== id);
        this.$toast.success('Barang berhasil dihapus paksa!');
      } catch (error) {
        console.error('Gagal menghapus barang:', error);
        this.$toast.error('Gagal menghapus barang.');
      }
    }
  }
};
</script>

<style scoped>
.admin-wrapper {
  display: flex;
  min-height: 100vh;
  background-color: #F8F9FA;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #2D3748;
}

.main-content {
  flex: 1;
  padding: 32px;
  overflow-y: auto;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.header-title-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 4px;
}

.btn-back {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background-color: #ffffff;
  border: 1px solid #E2E8F0;
  color: #003049;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  font-size: 16px;
  transition: all 0.2s ease;
}

.btn-back:hover {
  background-color: #003049;
  color: #ffffff;
}

.topbar-left h1 {
  margin: 0;
  font-size: 24px;
  color: #003049;
  font-weight: 800;
}

.topbar-left p {
  margin: 0;
  color: #718096;
  font-size: 13px;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box i {
  position: absolute;
  left: 14px;
  color: #A0AEC0;
  font-size: 14px;
}

.search-box input {
  padding: 10px 14px 10px 38px;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  font-size: 13px;
  outline: none;
  width: 280px;
  background-color: #ffffff;
}

.search-box input:focus {
  border-color: #F77F00;
}

/* Card Section */
.card-section {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 10px rgba(0, 48, 73, 0.03);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid #EDF2F7;
}

.title-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.section-header h3 {
  margin: 0;
  font-size: 18px;
  color: #003049;
}

.badge-count {
  background-color: #F0F4F8;
  color: #003049;
  font-size: 12px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 20px;
}

.btn-primary-add {
  background-color: #F77F00;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.2s;
}

.btn-primary-add:hover {
  background-color: #E07300;
}

/* Table Design */
.table-wrapper {
  overflow-x: auto;
}

.custom-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
}

.custom-table th {
  padding: 12px 14px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  color: #718096;
  border-bottom: 2px solid #EDF2F7;
}

.custom-table td {
  padding: 14px;
  border-bottom: 1px solid #F7FAFC;
  font-size: 14px;
  color: #2D3748;
  vertical-align: middle;
}

.id-col {
  color: #A0AEC0;
  font-weight: 600;
  font-size: 13px;
}

.code-col {
  font-weight: 600;
  color: #718096;
  font-size: 12px;
}

.text-navy {
  color: #003049;
}

.fw-bold {
  font-weight: 700;
}

.desc-col {
  color: #4A5568;
  font-size: 13px;
}

/* Status Badges */
.tag-status {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
}

/* Kondisi */
.tag-status.baik { background-color: #E8F5E9; color: #2E7D32; }
.tag-status.rusak { background-color: #FFF3E0; color: #E65100; }
.tag-status.diperbaiki { background-color: #FFEBEE; color: #C62828; }

/* Status */
.tag-status.tersedia { background-color: #E3F2FD; color: #1565C0; }
.tag-status.dipinjam { background-color: #F3E5F5; color: #7B1FA2; }
.tag-status.maintenance { background-color: #ECEFF1; color: #455A64; }

/* Action Buttons */
.action-col {
  vertical-align: middle;
}

.action-buttons {
  display: flex;
  justify-content: center;
  gap: 8px;
}

.btn-icon {
  width: 34px;
  height: 34px;
  border: none;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 14px;
  transition: transform 0.1s ease;
}

.btn-icon:hover {
  transform: translateY(-1px);
}

.btn-icon.edit { background-color: #FFF8E1; color: #F57F17; }
.btn-icon.delete { background-color: #FFEBEE; color: #C62828; }

.text-center { text-align: center; }

.empty-state {
  text-align: center;
  padding: 40px;
  color: #A0AEC0;
}

.empty-state i {
  font-size: 32px;
  display: block;
  margin-bottom: 8px;
}
</style>