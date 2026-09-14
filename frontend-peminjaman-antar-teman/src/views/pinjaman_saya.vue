<template>
  <div class="pinjam-page">
    <div class="main-card">
      <!-- Top Bar / Header Card -->
      <div class="card-top">
        <div class="header-left">
          <router-link to="/dashboard" class="btn-back" title="Kembali ke Beranda">
            <i class="bi bi-arrow-left"></i>
          </router-link>
          <div class="title-group">
            <span class="sub-title">Daftar Transaksi</span>
            <h2>Pinjaman Uang & Barang</h2>
          </div>
        </div>
        <router-link to="/peminjaman" class="btn-add">
          <i class="bi bi-plus-lg"></i> Tambah Pinjaman Baru
        </router-link>
      </div>

      <!-- Table Section -->
      <div class="table-wrapper">
        <table class="custom-table">
          <thead>
            <tr>
              <th style="width: 60px;">ID</th>
              <th style="width: 140px;">Nama Teman</th>
              <th>Detail Pinjaman</th>
              <th style="width: 120px;">Kategori</th>
              <th style="width: 140px;">Jatuh Tempo</th>
              <th style="width: 130px;">Status</th>
              <th style="width: 130px;" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in pinjamanList" :key="item.id">
              <td class="id-col">#{{ item.id }}</td>
              <td class="name-col">{{ item.nama_teman }}</td>
              <td class="detail-col">
                <span class="type-badge">{{ item.tipe === 'barang' ? '📷' : '💵' }}</span>
                <span>{{ item.detail }}</span>
              </td>
              <td>
                <span :class="['tag-kategori', item.kategori === 'Piutang' ? 'piutang' : 'hutang']">
                  {{ item.kategori }}
                </span>
              </td>
              <td class="date-col">{{ item.jatuh_tempo }}</td>
              <td>
                <span :class="['tag-status', item.status.toLowerCase().replace(' ', '-')]">
                  {{ item.status }}
                </span>
              </td>
              <td class="action-col">
                <button 
                  v-if="item.status === 'Jatuh Tempo'" 
                  class="btn-icon wa" 
                  title="Tagih via WhatsApp"
                  @click="remindWA(item.nama_teman)"
                >
                  <i class="bi bi-whatsapp"></i>
                </button>

                <button 
                  class="btn-icon edit" 
                  title="Edit Data"
                  @click="editPinjaman(item.id)"
                >
                  <i class="bi bi-pencil-fill"></i>
                </button>

                <button 
                  class="btn-icon delete" 
                  title="Hapus Data"
                  @click="confirmDelete(item.id)"
                >
                  <i class="bi bi-trash-fill"></i>
                </button>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="pinjamanList.length === 0">
              <td colspan="7" class="empty-state">
                <i class="bi bi-inbox"></i>
                <p>Belum ada catatan pinjaman aktif.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PinjamanView',
  data() {
    return {
      pinjamanList: [
        { 
          id: 1, 
          nama_teman: 'Rian', 
          detail: 'Kamera DSLR Canon EOS', 
          tipe: 'barang', 
          kategori: 'Piutang', 
          jatuh_tempo: '15 Mar 2026', 
          status: 'Aktif' 
        },
        { 
          id: 2, 
          nama_teman: 'Siti', 
          detail: 'Rp 50.000 (Makan Siang)', 
          tipe: 'uang', 
          kategori: 'Piutang', 
          jatuh_tempo: 'Hari Ini', 
          status: 'Jatuh Tempo' 
        },
        { 
          id: 3, 
          nama_teman: 'Andi', 
          detail: 'Rp 200.000 (Tiket Konser)', 
          tipe: 'uang', 
          kategori: 'Hutang', 
          jatuh_tempo: '20 Mar 2026', 
          status: 'Aktif' 
        }
      ]
    };
  },
  methods: {
    remindWA(name) {
      alert(`Mengirimkan pengingat WhatsApp ke ${name}`);
    },
    editPinjaman(id) {
      this.$router.push(`/pinjaman/${id}/edit`);
    },
    confirmDelete(id) {
      if (confirm('Yakin ingin menghapus catatan ini?')) {
        this.pinjamanList = this.pinjamanList.filter(item => item.id !== id);
      }
    }
  }
};
</script>

<style scoped>
/* Page Layout */
.pinjam-page {
  background-color: #FDFBF7;
  min-height: 100vh;
  padding: 40px 24px;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  box-sizing: border-box;
}

.main-card {
  background: #ffffff;
  max-width: 1100px;
  margin: 0 auto;
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 4px 20px rgba(0, 48, 73, 0.06);
  border: 1px solid #E2E8F0;
}

/* Card Top Bar */
.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #EDF2F7;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-back {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background-color: #F0F4F8;
  color: #003049;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  font-size: 18px;
  transition: all 0.2s;
}

.btn-back:hover {
  background-color: #003049;
  color: #ffffff;
}

.title-group .sub-title {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #F77F00;
  display: block;
}

.title-group h2 {
  margin: 2px 0 0 0;
  color: #003049;
  font-size: 22px;
  font-weight: 800;
}

.btn-add {
  background-color: #F77F00;
  color: #ffffff;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 13px;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.2s;
}

.btn-add:hover {
  background-color: #E07300;
}

/* Table Styling */
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
  padding: 16px 14px;
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

.name-col {
  font-weight: 700;
  color: #003049;
}

.detail-col {
  display: flex;
  align-items: center;
  gap: 10px;
}

.type-badge {
  background: #F0F4F8;
  padding: 6px 8px;
  border-radius: 8px;
  font-size: 14px;
}

/* Tags & Badges */
.tag-kategori {
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
}

.tag-kategori.piutang {
  background-color: #FFF3E0;
  color: #E65100;
}

.tag-kategori.hutang {
  background-color: #FFEBEE;
  color: #C62828;
}

.date-col {
  color: #718096;
  font-size: 13px;
}

.tag-status {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
}

.tag-status.aktif {
  background-color: #E3F2FD;
  color: #1565C0;
}

.tag-status.jatuh-tempo {
  background-color: #FFEBEE;
  color: #D62828;
}

/* Action Buttons */
.action-col {
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

.btn-icon.wa {
  background-color: #E8F5E9;
  color: #2E7D32;
}

.btn-icon.edit {
  background-color: #FFF8E1;
  color: #F57F17;
}

.btn-icon.delete {
  background-color: #FFEBEE;
  color: #C62828;
}

.text-center {
  text-align: center;
}

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