<template>
  <div class="home-container">
    <aside class="sidebar">
      <div class="logo">
        <router-link to="/">
          <i class="bi bi-inbox-fill"></i>
          <h2>PinjamTeman</h2>
        </router-link>
      </div>
      <nav class="nav-menu">
        <a href="#" class="nav-item active">Dashboard</a>
        <router-link to="/pinjaman_saya" class="nav-item">Pinjaman Saya</router-link>
        <router-link to="/barang" class="nav-item">Barang</router-link>
        <router-link to="/kategori" class="nav-item">Kategori</router-link>
      </nav>
      
      
    </aside>

    <main class="main-content">
      <header class="topbar">
        <input type="text" placeholder="Cari transaksi..." class="search-input" />
        <div class="user-profile">
          <span class="notification-icon">🔔</span>

          <div class="profile-dropdown" ref="dropdownRef">
            <div class="profile-trigger" @click="isDropdownOpen = !isDropdownOpen">
              <img src="https://via.placeholder.com/40" alt="Avatar" class="avatar" />
              <span>{{ userName }}</span>
              <i class="bi bi-chevron-down dropdown-caret" :class="{ open: isDropdownOpen }"></i>
            </div>

            <div v-if="isDropdownOpen" class="dropdown-menu">
              <div class="dropdown-header">
                <p class="dropdown-name">{{ userName }}</p>
                <p class="dropdown-email">{{ userEmail }}</p>
              </div>
              <hr class="dropdown-divider" />
              <a href="#" class="dropdown-item">
                <i class="bi bi-person"></i> Profil Saya
              </a>
              <a href="#" class="dropdown-item">
                <i class="bi bi-gear"></i> Pengaturan
              </a>
              <hr class="dropdown-divider" />
              <button class="dropdown-item dropdown-logout" @click="handleLogout" :disabled="loggingOut">
                <i class="bi bi-box-arrow-right"></i>
                {{ loggingOut ? 'Keluar...' : 'Logout' }}
              </button>
            </div>
          </div>
        </div>
      </header>

      <section class="summary-cards">
        <div class="card card-piutang">
          <p class="card-title">Total Piutang</p>
          <h3 class="card-value">Rp {{ formatRupiah(totalPiutang) }}</h3>
        </div>
        <div class="card card-hutang">
          <p class="card-title">Total Hutang</p>
          <h3 class="card-value">Rp {{ formatRupiah(totalHutang) }}</h3>
        </div>
      </section>

      <div class="tabs">
        <button 
          v-for="tab in tabs" 
          :key="tab" 
          :class="['tab-btn', { active: currentTab === tab }]"
          @click="currentTab = tab"
        >
          {{ tab }}
        </button>
      </div>

      <section class="transaction-list">
        <div 
          v-for="item in filteredTransactions" 
          :key="item.key" 
          class="transaction-card"
        >
          <div class="item-info">
            <div class="item-icon">{{ item.type === 'barang' ? '📷' : '💵' }}</div>
            <div>
              <h4>{{ item.name }}</h4>
              <p class="item-detail">{{ item.description }}</p>
              <small class="item-date">Tgl Kembali: {{ item.dueDate }}</small>
            </div>
          </div>
          <div class="item-actions">
            <span :class="['status-badge', statusClass(item.status)]">
              {{ labelStatus(item.status) }}
            </span>
          </div>
        </div>

        <p v-if="filteredTransactions.length === 0" class="empty-msg">
          Belum ada transaksi.
        </p>
      </section>
    </main>
  </div>
</template>

<script>
import { logout } from '../utils/auth';
import { getPeminjaman } from '../utils/peminjaman';
import { getPeminjamanUang } from '../utils/peminjamanUang';

export default {
  name: 'DashboardView',
  data() {
    return {
      totalPiutang: 0,
      totalHutang: 0,
      currentTab: 'Semua',
      tabs: ['Semua', 'Saya Meminjam', 'Orang Lain Meminjam'],
      loggingOut: false,
      isDropdownOpen: false,
      currentUserId: null,
      transactions: []
    };
  },
  computed: {
    filteredTransactions() {
      if (this.currentTab === 'Semua') return this.transactions;
      return this.transactions.filter(t => t.category === this.currentTab);
    },
    userName() {
      const user = localStorage.getItem('user');
      if (!user) return 'User';
      try {
        return JSON.parse(user).name || 'User';
      } catch {
        return 'User';
      }
    },
    userEmail() {
      const user = localStorage.getItem('user');
      if (!user) return '';
      try {
        return JSON.parse(user).email || '';
      } catch {
        return '';
      }
    }
  },
  async mounted() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    this.currentUserId = user.id;

    document.addEventListener('click', this.handleClickOutside);
    await this.muatData();
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    async muatData() {
      try {
        const [resBarang, resUang] = await Promise.all([
          getPeminjaman(),
          getPeminjamanUang()
        ]);

        const barangList = resBarang.data.data.map(item => this.mapBarang(item));
        const uangList = resUang.data.data.map(item => this.mapUang(item));

        this.transactions = [...uangList, ...barangList].sort((a, b) => b.id - a.id);

        this.totalPiutang = uangList
          .filter(t => t.category === 'Orang Lain Meminjam' && ['Ds', 'A'].includes(t.rawStatus))
          .reduce((sum, t) => sum + t.nominal, 0);

        this.totalHutang = uangList
          .filter(t => t.category === 'Saya Meminjam' && ['Ds', 'A'].includes(t.rawStatus))
          .reduce((sum, t) => sum + t.nominal, 0);

      } catch (e) {
        console.error('Gagal memuat data dashboard', e);
      }
    },
    mapBarang(item) {
      const sayaPeminjam = item.peminjam_id === this.currentUserId;
      const namaBarang = item.barangs?.map(b => b.nama_barang).join(', ') || '-';

      return {
        key: 'barang-' + item.id,
        id: item.id,
        name: sayaPeminjam ? item.pemilik.name : item.peminjam.name,
        description: namaBarang,
        dueDate: this.formatTanggal(item.tgl_tenggat),
        status: item.status,
        rawStatus: item.status,
        type: 'barang',
        category: sayaPeminjam ? 'Saya Meminjam' : 'Orang Lain Meminjam'
      };
    },
    mapUang(item) {
      const sayaPeminjam = item.peminjam_id === this.currentUserId;

      return {
        key: 'uang-' + item.id,
        id: item.id,
        name: sayaPeminjam ? item.pemberi.name : item.peminjam.name,
        description: 'Rp ' + Number(item.nominal).toLocaleString('id-ID'),
        dueDate: this.formatTanggal(item.tgl_tenggat),
        status: item.status,
        rawStatus: item.status,
        nominal: Number(item.nominal),
        type: 'uang',
        category: sayaPeminjam ? 'Saya Meminjam' : 'Orang Lain Meminjam'
      };
    },
    formatTanggal(tgl) {
      return new Date(tgl).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    },
    labelStatus(status) {
      const map = { M: 'Menunggu', Ds: 'Disetujui', Dt: 'Ditolak', A: 'Aktif', S: 'Selesai', B: 'Batal' };
      return map[status] || status;
    },
    statusClass(status) {
      const map = { M: 'menunggu', Ds: 'disetujui', Dt: 'ditolak', A: 'aktif', S: 'selesai', B: 'batal' };
      return map[status] || '';
    },
    formatRupiah(val) {
      return val.toLocaleString('id-ID');
    },
    handleClickOutside(event) {
      const dropdown = this.$refs.dropdownRef;
      if (dropdown && !dropdown.contains(event.target)) {
        this.isDropdownOpen = false;
      }
    },
    async handleLogout() {
      this.loggingOut = true;
      try {
        await logout();
      } catch (error) {
        console.error('Logout API gagal:', error);
      } finally {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        this.loggingOut = false;
        this.isDropdownOpen = false;
        this.$router.push('/login');
      }
    }
  }
};
</script>

<style scoped>
:root {
  --deep-blue: #003049;
  --blazing-orange: #F77F00;
  --flag-red: #D62828;
  --bg-light: #FDFBF7;
}

.home-container {
  display: flex;
  height: 100vh;
  background-color: #FDFBF7;
  font-family: sans-serif;
}

.sidebar {
  width: 240px;
  background-color: #003049;
  color: white;
  padding: 24px;
  display: flex;
  flex-direction: column;
}

.logo h2 {
  margin-bottom: 32px;
  color: #F77F00;
}

.nav-menu {
  display: flex;
  flex-direction: column;
  gap: 12px;
  flex: 1;
}

.nav-item {
  color: #cfd8dc;
  text-decoration: none;
  padding: 10px 14px;
  border-radius: 8px;
}

.nav-item.active, .nav-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
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
  margin-bottom: 24px;
}

.search-input {
  padding: 8px 16px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  width: 300px;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar {
  border-radius: 50%;
}

.summary-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 24px;
}

.card {
  padding: 20px;
  border-radius: 12px;
  color: white;
}

.card-piutang { background-color: #F77F00; }
.card-hutang { background-color: #D62828; }

.card-title { margin: 0 0 8px 0; opacity: 0.9; }
.card-value { margin: 0; font-size: 24px; }

.tabs {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  border-bottom: 2px solid #e0e0e0;
}

.tab-btn {
  background: none;
  border: none;
  padding: 10px 16px;
  cursor: pointer;
  font-weight: bold;
  color: #666;
}

.tab-btn.active {
  color: #003049;
  border-bottom: 3px solid #003049;
}

.transaction-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.transaction-card {
  background: white;
  padding: 16px;
  border-radius: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.item-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.item-icon {
  font-size: 24px;
  background: #f0f4f8;
  padding: 10px;
  border-radius: 8px;
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
}

.status-badge.menunggu { background: #FFF3E0; color: #F77F00; }
.status-badge.disetujui { background: #e3f2fd; color: #1976d2; }
.status-badge.ditolak { background: #ffebee; color: #D62828; }
.status-badge.aktif { background: #E8F5E9; color: #2E7D32; }
.status-badge.selesai { background: #F0F4F8; color: #4A5568; }
.status-badge.batal { background: #F0F0F0; color: #999; }

.btn-primary {
  background-color: #F77F00;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  width: 100%;
}

.empty-msg {
  text-align: center;
  color: #A0AEC0;
  padding: 24px;
}

.btn-logout {
  background-color: #D62828;
  color: white;
  border: none;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  margin-left: 8px;
}

.btn-logout:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.profile-dropdown {
  position: relative;
}

.profile-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 6px 10px;
  border-radius: 8px;
  transition: background-color 0.15s;
}

.profile-trigger:hover {
  background-color: #F0F4F8;
}

.dropdown-caret {
  font-size: 12px;
  color: #A0AEC0;
  transition: transform 0.15s;
}

.dropdown-caret.open {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  border: 1px solid #E2E8F0;
  width: 220px;
  padding: 8px;
  z-index: 50;
}

.dropdown-header {
  padding: 8px 12px;
}

.dropdown-name {
  margin: 0;
  font-weight: 700;
  font-size: 13px;
  color: #003049;
}

.dropdown-email {
  margin: 2px 0 0 0;
  font-size: 11px;
  color: #A0AEC0;
}

.dropdown-divider {
  border: none;
  border-top: 1px solid #EDF2F7;
  margin: 6px 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 9px 12px;
  border: none;
  background: none;
  text-align: left;
  font-size: 13px;
  color: #2D3748;
  text-decoration: none;
  border-radius: 8px;
  cursor: pointer;
}

.dropdown-item:hover {
  background-color: #F0F4F8;
}

.dropdown-logout {
  color: #D62828;
}

.dropdown-logout:hover {
  background-color: #FFEBEE;
}

.dropdown-logout:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>