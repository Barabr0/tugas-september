<template>
  <div class="admin-wrapper">
    <SidebarAdmin />

    <main class="main-content">
      <header class="topbar">
        <div class="topbar-left">
          <h1>Overview Sistem</h1>
          <p>Pantau statistik harian dan aktivitas pengguna PinjamTeman.</p>
        </div>

        <div class="topbar-right">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Cari user, barang, transaksi..." />
          </div>
        </div>
      </header>

      <!-- Summary Stat Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon users">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="stat-info">
            <span class="stat-title">Total Pengguna</span>
            <h3 class="stat-value">{{ users.length }}</h3>
            <span class="stat-trend neutral">Terdaftar di sistem</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon items">
            <i class="bi bi-box-seam-fill"></i>
          </div>
          <div class="stat-info">
            <span class="stat-title">Barang Terdaftar</span>
            <h3 class="stat-value">{{ barangs.length }}</h3>
            <span class="stat-trend neutral">Total barang aktif</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon active-loans">
            <i class="bi bi-life-preserver"></i>
          </div>
          <div class="stat-info">
            <span class="stat-title">Request Bantuan</span>
            <h3 class="stat-value">{{ pendingCount }}</h3>
            <span class="stat-trend neutral">Menunggu review</span>
          </div>
        </div>
      </div>

      <div class="dashboard-grid">
        <!-- Tabel Permintaan Bantuan -->
        <div class="card-section main-table-card">
          <div class="section-header">
            <div>
              <h3>Permintaan Bantuan User</h3>
              <p>Request bantuan antar user beserta alasan dan catatan responsnya.</p>
            </div>
            <select v-model="filterStatus" @change="fetchBantuan" class="filter-select">
              <option value="pending">Menunggu</option>
              <option value="disetujui">Disetujui</option>
              <option value="ditolak">Ditolak</option>
              <option value="">Semua</option>
            </select>
          </div>

          <div class="table-wrapper">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>Peminta</th>
                  <th>Target</th>
                  <th>Tipe</th>
                  <th>Alasan / Deskripsi</th>
                  <th>Status</th>
                  <th>Catatan Respons</th>
                  <th>Aksi Admin</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loadingBantuan">
                  <td colspan="7" class="empty-state">Memuat data bantuan...</td>
                </tr>
                <tr v-else-if="bantuanRequests.length === 0">
                  <td colspan="7" class="empty-state">Tidak ada permintaan bantuan.</td>
                </tr>
                <tr v-for="req in bantuanRequests" :key="req.id">
                  <td class="fw-bold">{{ req.peminta_nama || req.peminta?.name || 'Unknown' }}</td>
                  <td>{{ req.target_nama || req.target?.name || '-' }}</td>
                  <td>{{ req.tipe_request }}</td>
                  <td class="text-cell">{{ req.deskripsi || '-' }}</td>
                  <td>
                    <span class="tag-status" :class="req.status">{{ req.status }}</span>
                  </td>
                  <td class="text-cell">{{ req.alasan || '-' }}</td>
                  <td>
                    <div v-if="req.status === 'pending'" class="action-cell">
                      <button @click="handleRespondBantuan(req.id, 'disetujui')" class="btn-action btn-approve">
                        Setujui
                      </button>
                      <button @click="handleRespondBantuan(req.id, 'ditolak')" class="btn-action btn-reject">
                        Tolak
                      </button>
                    </div>
                    <span v-else class="done-text">Selesai</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Barang Sistem Terbaru -->
        <div class="card-section side-card">
          <div class="section-header">
            <h3>Barang Sistem Terbaru</h3>
          </div>

          <div class="user-list">
            <div v-if="loadingBarangs" class="empty-state">Memuat barang...</div>
            <div v-else-if="barangs.length === 0" class="empty-state">Belum ada barang.</div>
            <div v-for="barang in barangs.slice(0, 5)" :key="barang.id" class="user-item">
              <div class="user-avatar">
                <i class="bi bi-box"></i>
              </div>
              <div class="user-detail">
                <span class="user-name">{{ barang.nama_barang }}</span>
                <span class="user-email">Pemilik: {{ barang.pemilik?.name || '-' }}</span>
              </div>
              <span class="user-time">{{ barang.status }}</span>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import adminApi from '@/utils/admin';
import SidebarAdmin from '@/components/sidebarAdmin.vue';

export default {
  name: 'AdminDashboardView',
  components: {
    SidebarAdmin
  },
  data() {
    return {
      users: [],
      barangs: [],
      bantuanRequests: [],
      filterStatus: 'pending',
      loadingUsers: false,
      loadingBarangs: false,
      loadingBantuan: false
    };
  },
  computed: {
    pendingCount() {
      return this.bantuanRequests.filter(r => r.status === 'pending').length;
    }
  },
  mounted() {
    this.fetchAdminData();
  },
  methods: {
    async fetchAdminData() {
      await Promise.all([
        this.fetchUsers(),
        this.fetchBarangs(),
        this.fetchBantuan()
      ]);
    },

    async fetchUsers() {
      this.loadingUsers = true;
      try {
        const res = await adminApi.getAllUsers();
        this.users = res.data.data || res.data || [];
      } catch (error) {
        console.error('Gagal ambil users', error);
      } finally {
        this.loadingUsers = false;
      }
    },

    async fetchBarangs() {
      this.loadingBarangs = true;
      try {
        const res = await adminApi.getAllBarangs();
        this.barangs = res.data.data || res.data || [];
      } catch (error) {
        console.error('Gagal ambil barangs', error);
      } finally {
        this.loadingBarangs = false;
      }
    },

    async fetchBantuan() {
      this.loadingBantuan = true;
      try {
        const params = this.filterStatus ? { status: this.filterStatus } : {};
        const res = await adminApi.getBantuanRequests(params);
        this.bantuanRequests = res.data.data || [];
      } catch (error) {
        console.error('Gagal ambil bantuan', error);
      } finally {
        this.loadingBantuan = false;
      }
    },

    async handleRespondBantuan(id, status) {
      const label = status === 'disetujui' ? 'menyetujui' : 'menolak';
      const alasan = prompt(`Alasan ${label} request ini (opsional):`);
      if (alasan === null) return;

      try {
        await adminApi.respondBantuan(id, { status, alasan: alasan || null });
        this.$toast.success(`Request berhasil ${status}`);
        await this.fetchBantuan();
      } catch (error) {
        this.$toast.error(error.response?.data?.message || 'Gagal memproses request');
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
  min-width: 0;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
}

.topbar-left h1 {
  margin: 0 0 4px 0;
  font-size: 24px;
  color: #003049;
  font-weight: 800;
}

.topbar-left p {
  margin: 0;
  color: #718096;
  font-size: 13px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box i {
  position: absolute;
  left: 12px;
  color: #A0AEC0;
  font-size: 14px;
}

.search-box input {
  padding: 10px 14px 10px 36px;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  font-size: 13px;
  outline: none;
  width: 240px;
  background-color: #ffffff;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 28px;
}

.stat-card {
  background: #ffffff;
  padding: 20px;
  border-radius: 14px;
  border: 1px solid #E2E8F0;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  box-shadow: 0 2px 10px rgba(0, 48, 73, 0.03);
}

.stat-icon {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.stat-icon.users { background: rgba(0, 48, 73, 0.1); color: #003049; }
.stat-icon.active-loans { background: rgba(247, 127, 0, 0.15); color: #F77F00; }
.stat-icon.items { background: rgba(123, 31, 162, 0.15); color: #7B1FA2; }

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-title {
  font-size: 12px;
  color: #718096;
  font-weight: 600;
}

.stat-value {
  margin: 4px 0;
  font-size: 20px;
  color: #003049;
  font-weight: 800;
}

.stat-trend {
  font-size: 11px;
  font-weight: 700;
}

.stat-trend.neutral { color: #A0AEC0; }

.dashboard-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

.card-section {
  background: #ffffff;
  border-radius: 16px;
  padding: 24px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 2px 10px rgba(0, 48, 73, 0.03);
  min-width: 0;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  gap: 12px;
}

.section-header h3 {
  margin: 0 0 4px 0;
  font-size: 16px;
  color: #003049;
}

.section-header p {
  margin: 0;
  font-size: 12px;
  color: #718096;
}

.filter-select {
  padding: 6px 10px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 12px;
  background: #ffffff;
  color: #003049;
  outline: none;
}

.table-wrapper {
  overflow-x: auto;
}

.custom-table {
  width: 100%;
  border-collapse: collapse;
}

.custom-table th {
  padding: 10px 12px;
  font-size: 11px;
  color: #718096;
  text-transform: uppercase;
  border-bottom: 2px solid #EDF2F7;
  text-align: left;
  white-space: nowrap;
}

.custom-table td {
  padding: 12px;
  font-size: 13px;
  border-bottom: 1px solid #F7FAFC;
  vertical-align: top;
}

.text-cell {
  max-width: 200px;
  word-break: break-word;
}

.empty-state {
  text-align: center;
  color: #A0AEC0;
  padding: 20px;
  font-size: 13px;
}

.fw-bold { font-weight: 700; color: #003049; }

.tag-status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 700;
  text-transform: capitalize;
}

.tag-status.pending { background: #FFF3E0; color: #F77F00; }
.tag-status.disetujui { background: rgba(46, 125, 50, 0.12); color: #2E7D32; }
.tag-status.ditolak { background: rgba(214, 40, 40, 0.12); color: #D62828; }

.done-text {
  font-size: 11px;
  color: #A0AEC0;
}

.action-cell {
  display: flex;
  gap: 6px;
}

.btn-action {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.btn-approve { background: rgba(46, 125, 50, 0.12); color: #2E7D32; }
.btn-approve:hover { background: rgba(46, 125, 50, 0.22); }
.btn-reject { background: rgba(214, 40, 40, 0.12); color: #D62828; }
.btn-reject:hover { background: rgba(214, 40, 40, 0.22); }

.user-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.user-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background-color: #003049;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 13px;
}

.user-detail {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.user-name {
  font-size: 13px;
  font-weight: 700;
  color: #003049;
}

.user-email {
  font-size: 11px;
  color: #718096;
}

.user-time {
  font-size: 10px;
  color: #A0AEC0;
  background: #EDF2F7;
  padding: 4px 8px;
  border-radius: 12px;
}
</style>