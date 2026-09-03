<template>
  <div class="landing-container">
    <!-- Navigation Bar -->
    <header class="navbar">
      <div class="nav-brand">
        <h2>JaMan</h2>
      </div>
      <nav class="nav-links">
        <a href="#hero">Beranda</a>
        <a href="#features">Fitur</a>
        <a href="#how-it-works">Cara Kerja</a>
      </nav>
      <div class="nav-actions">
        <button class="btn-primary" @click="login">
          <i class="bi bi-lock-fill"></i> Login
        </button>
      </div>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="hero-section">
      <div class="hero-content">
        <span class="hero-badge">🤝 Solusi Pinjam Meminjam Anti Canggung</span>
        <h1>Catat Hutang & Pinjaman Barang Antar Teman Lebih Rapi</h1>
        <p>
          Lupa siapa yang pinjam kamera atau belum bayar uang makan? 
          PinjamTeman bantu kamu mencatat piutang, hutang, dan pengingat otomatis via WhatsApp.
        </p>
        <div class="hero-buttons">
          <button class="btn-primary btn-large" @click="scrollToDashboard">
            Mulai Catat Sekarang
          </button>
          <a href="#features" class="btn-outline btn-large">Pelajari Fitur</a>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
      <div class="section-header">
        <h2>Kenapa Memakai PinjamTeman?</h2>
        <p>Semua fitur didesain agar hubungan pertemanan tetap aman dan keuangan teratur.</p>
      </div>
      <div class="features-grid">
        <div class="feature-card">
          <div class="feature-icon">💵</div>
          <h3>Pencatatan Uang</h3>
          <p>Catat pinjaman uang makan, tiket konser, atau dana darurat dengan tanggal jatuh tempo jelas.</p>
        </div>
        <div class="feature-card">
          <div class="feature-icon">📷</div>
          <h3>Peminjaman Barang</h3>
          <p>Lacak posisi barang kesayanganmu seperti kamera, charger, atau helm agar tidak lupa balik.</p>
        </div>
      </div>
    </section>

    <!-- Embedded Dashboard Section (Home Area) -->
    <section id="dashboard" class="dashboard-section">
      <div class="section-header">
        <h2>Dashboard Pencatatan Kamu</h2>
        <p>Kelola dan kelola seluruh transaksi aktif secara langsung di bawah ini.</p>
      </div>

      <div class="dashboard-box">
        <!-- Summary Cards -->
        <div class="summary-cards">
          <div class="card card-piutang">
            <p class="card-title">Total Piutang</p>
            <h3 class="card-value">Rp {{ formatRupiah(totalPiutang) }}</h3>
          </div>
          <div class="card card-hutang">
            <p class="card-title">Total Hutang</p>
            <h3 class="card-value">Rp {{ formatRupiah(totalHutang) }}</h3>
          </div>
        </div>

        <!-- Filter Tabs & Search -->
        <div class="dashboard-controls">
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
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Cari transaksi..." 
            class="search-input" 
          />
        </div>

        <!-- Transaction List -->
        <div class="transaction-list">
          <div 
            v-for="item in filteredTransactions" 
            :key="item.id" 
            class="transaction-card"
          >
            <div class="item-info">
              <div class="item-icon">{{ item.type === 'barang' ? '📷' : '💵' }}</div>
              <div>
                <div class="item-header">
                  <h4>{{ item.name }}</h4>
                  <span class="category-badge">{{ item.category }}</span>
                </div>
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
                Tagih WA
              </button>
              <button 
                v-else 
                class="btn-action-primary" 
                @click="completeTransaction(item.id)"
              >
                Selesai
              </button>
            </div>
          </div>

          <div v-if="filteredTransactions.length === 0" class="empty-state">
            <p>Tidak ada catatan transaksi.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <p>&copy; 2026 PinjamTeman. Dibuat untuk menjaga pertemanan tetap hangat.</p>
    </footer>
  </div>
</template>
<script>
export default {
  name: 'LandingView',
  data() {
    return {
      totalPiutang: 1500000,
      totalHutang: 250000,
      searchQuery: '',
      currentTab: 'Semua',
      tabs: ['Semua', 'Orang Lain Meminjam', 'Saya Meminjam'],
      transactions: [
        {
          id: 1,
          name: 'Rian',
          description: 'Kamera DSLR Canon',
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
        },
        {
          id: 3,
          name: 'Andi',
          description: 'Rp 200.000 (Tiket Konser)',
          dueDate: '20 Mar 2026',
          status: 'Aktif',
          type: 'uang',
          category: 'Saya Meminjam'
        }
      ]
    };
  },
  computed: {
    filteredTransactions() {
      return this.transactions.filter(item => {
        const matchesTab = 
          this.currentTab === 'Semua' || item.category === this.currentTab;

        const matchesSearch = 
          item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          item.description.toLowerCase().includes(this.searchQuery.toLowerCase());

        return matchesTab && matchesSearch;
      });
    }
  },
  methods: {
    login() {
      console.log('Tombol diklik!');
      this.$router.push('/login');
    },
    formatRupiah(val) {
      return val.toLocaleString('id-ID');
    },
    scrollToDashboard() {
      const el = document.getElementById('dashboard');
      if (el) el.scrollIntoView({ behavior: 'smooth' });
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
.landing-container {
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #2D3748;
  background-color: #FDFBF7;
  margin: 0;
  padding: 0;
}

/* Navbar */
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 48px;
  background-color: #003049;
  color: white;
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-brand h2 {
  margin: 0;
  color: #F77F00;
  font-size: 22px;
}

.nav-links {
  display: flex;
  gap: 24px;
}

.nav-links a {
  color: #EAE2B7;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
}

.nav-links a:hover {
  color: #ffffff;
}

.nav-actions {
  display: flex;
  gap: 12px;
}

/* Hero Section */
.hero-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 64px 48px;
  gap: 32px;
}

.hero-content {
  max-width: 580px;
}

.hero-badge {
  background-color: rgba(247, 127, 0, 0.15);
  color: #D62828;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 700;
  display: inline-block;
  margin-bottom: 16px;
}

.hero-content h1 {
  font-size: 38px;
  color: #003049;
  line-height: 1.25;
  margin: 0 0 16px 0;
}

.hero-content p {
  font-size: 16px;
  color: #4A5568;
  line-height: 1.6;
  margin-bottom: 28px;
}

.hero-buttons {
  display: flex;
  gap: 16px;
}

/* Hero Preview Box */
.hero-preview {
  flex: 1;
  display: flex;
  justify-content: center;
}

.preview-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  border: 1px solid #E2E8F0;
  width: 100%;
  max-width: 360px;
}

.preview-header {
  font-weight: 700;
  font-size: 14px;
  margin-bottom: 16px;
  color: #003049;
}

.preview-stats {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-box {
  padding: 14px;
  border-radius: 10px;
  color: white;
}

.stat-box.piutang { background-color: #F77F00; }
.stat-box.hutang { background-color: #D62828; }

.stat-box small {
  display: block;
  font-size: 11px;
  opacity: 0.9;
}

.stat-box h3 {
  margin: 4px 0 0 0;
  font-size: 18px;
}

/* Features Section */
.features-section {
  padding: 74px 88px;
  background-color: #ffffff;
}

.section-header {
  text-align: center;
  max-width: 600px;
  margin: 0 auto 40px auto;
}

.section-header h2 {
  font-size: 28px;
  color: #003049;
  margin-bottom: 8px;
}

.section-header p {
  color: #718096;
  font-size: 14px;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 24px;
}

.feature-card {
  padding: 28px;
  background-color: #FDFBF7;
  border-radius: 12px;
  border: 1px solid #E2E8F0;
  text-align: center;
}

.feature-icon {
  font-size: 36px;
  margin-bottom: 12px;
}

.feature-card h3 {
  font-size: 18px;
  color: #003049;
  margin-bottom: 8px;
}

.feature-card p {
  font-size: 13px;
  color: #4A5568;
  line-height: 1.5;
}

/* Embedded Dashboard Section */
.dashboard-section {
  padding: 64px 48px;
  background-color: #F8F9FA;
}

.dashboard-box {
  background: white;
  padding: 28px;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid #E2E8F0;
}

.summary-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 24px;
}

.card {
  padding: 16px;
  border-radius: 10px;
  color: white;
}

.card-piutang { background-color: #F77F00; }
.card-hutang { background-color: #D62828; }

.card-title { margin: 0; font-size: 12px; opacity: 0.9; }
.card-value { margin: 4px 0 0 0; font-size: 20px; }

.dashboard-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.tabs { display: flex; gap: 8px; }

.tab-btn {
  background: none;
  border: none;
  padding: 8px 12px;
  cursor: pointer;
  font-weight: 700;
  font-size: 13px;
  color: #718096;
  border-bottom: 2px solid transparent;
}

.tab-btn.active {
  color: #003049;
  border-bottom-color: #003049;
}

.search-input {
  padding: 8px 12px;
  border: 1px solid #E2E8F0;
  border-radius: 6px;
  font-size: 13px;
  outline: none;
}

.transaction-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.transaction-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 16px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  background-color: #ffffff;
}

.item-info { display: flex; align-items: center; gap: 12px; }

.item-icon {
  font-size: 20px;
  background-color: #F0F4F8;
  padding: 10px;
  border-radius: 8px;
}

.item-header h4 { margin: 0; font-size: 14px; color: #003049; }

.category-badge {
  font-size: 10px;
  background: #EDF2F7;
  padding: 2px 6px;
  border-radius: 4px;
  margin-left: 6px;
}

.item-detail { margin: 2px 0; font-size: 12px; color: #4A5568; }
.item-date { font-size: 10px; color: #A0AEC0; }

.item-actions { display: flex; align-items: center; gap: 10px; }

.status-badge {
  padding: 4px 8px;
  border-radius: 10px;
  font-size: 10px;
  font-weight: 700;
}

.status-badge.aktif { background: #E3F2FD; color: #1976D2; }
.status-badge.jatuh-tempo { background: #FFEBEE; color: #D62828; }

/* Buttons */
.btn-primary {
  background-color: #F77F00;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
}

.btn-secondary {
  background-color: transparent;
  color: #EAE2B7;
  border: none;
  padding: 10px 14px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
}

.btn-outline {
  background-color: transparent;
  border: 1px solid #003049;
  color: #003049;
  padding: 10px 18px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  text-decoration: none;
}

.btn-large { padding: 12px 24px; font-size: 14px; }

.btn-action-primary {
  background-color: #003049;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}

.btn-wa {
  background-color: white;
  border: 1px solid #003049;
  color: #003049;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}

.footer {
  text-align: center;
  padding: 24px;
  background-color: #003049;
  color: #EAE2B7;
  font-size: 12px;
}

.empty-state {
  text-align: center;
  padding: 20px;
  color: #A0AEC0;
  font-size: 13px;
}
</style>