<template>
  <div class="home-container">
    <aside class="sidebar">
      <div class="logo">
        <router-link to="/">
          <h2>PinjamTeman</h2>
        </router-link>
      </div>
      <nav class="nav-menu">
        <a href="#" class="nav-item active">Dashboard</a>
        <a href="#" class="nav-item">Pinjaman Saya</a>
        <a href="#" class="nav-item">Riwayat</a>
        <a href="#" class="nav-item">Pengaturan</a>
      </nav>
      <button class="btn-primary" @click="openModal">+ Pinjaman Baru</button>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <input type="text" placeholder="Cari transaksi..." class="search-input" />
        <div class="user-profile">
          <span class="notification-icon">🔔</span>
          <img src="https://via.placeholder.com/40" alt="Avatar" class="avatar" />
          <span>Budi</span>
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
          :key="item.id" 
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
            <span :class="['status-badge', item.status.toLowerCase().replace(' ', '-')]">
              {{ item.status }}
            </span>
            <button 
              v-if="item.status === 'Jatuh Tempo'" 
              class="btn-wa" 
              @click="remindWA(item.name)"
            >
              Kirim Tagihan WA
            </button>
            <button 
              v-else 
              class="btn-secondary" 
              @click="completeTransaction(item.id)"
            >
              Tandai Selesai
            </button>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
export default {
  name: 'HomeView',
  data() {
    return {
      totalPiutang: 1000000000,
      totalHutang: 50000000,
      currentTab: 'Semua',
      tabs: ['Semua', 'Saya Meminjam', 'Orang Lain Meminjam'],
      transactions: [
        {
          id: 1,
          name: 'Rian',
          description: 'Kamera DSLR',
          dueDate: '15 Mar 2026',
          status: 'Aktif',
          type: 'barang',
          category: 'Orang Lain Meminjam'
        },
        {
          id: 2,
          name: 'Siti',
          description: 'Rp 50.000 (Makan Siang)',
          dueDate: 'Hari Ini',
          status: 'Jatuh Tempo',
          type: 'uang',
          category: 'Orang Lain Meminjam'
        }
      ]
    };
  },
  computed: {
    filteredTransactions() {
      if (this.currentTab === 'Semua') return this.transactions;
      return this.transactions.filter(t => t.category === this.currentTab);
    }
  },
  methods: {
    formatRupiah(val) {
      return val.toLocaleString('id-ID');
    },
    openModal() {
      alert('Buka modal tambah pinjaman');
    },
    remindWA(name) {
      alert(`Mengirimkan pesan pengingat WhatsApp ke ${name}`);
    },
    completeTransaction(id) {
      this.transactions = this.transactions.filter(t => t.id !== id);
    }
  }
};
</script>

<style scoped>
/* Color Palette */
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

/* Sidebar */
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

/* Main Content */
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

/* Cards */
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

/* Tabs */
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

/* Transactions */
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

.status-badge.aktif { background: #e3f2fd; color: #1976d2; }
.status-badge.jatuh-tempo { background: #ffebee; color: #D62828; }

/* Buttons */
.btn-primary {
  background-color: #F77F00;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
}

.btn-secondary {
  background-color: #003049;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
}

.btn-wa {
  background-color: white;
  border: 1px solid #003049;
  color: #003049;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
}
</style>