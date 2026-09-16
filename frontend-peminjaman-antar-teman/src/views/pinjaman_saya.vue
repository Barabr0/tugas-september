<template>
  <div class="pinjam-page">
    <div class="main-card">
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
              <th style="width: 180px;" class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in pinjamanList" :key="item.key">
              <td class="id-col">#{{ index + 1 }}</td>
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
                <span :class="['tag-status', statusClass(item.status)]">
                  {{ labelStatus(item.status) }}
                </span>
              </td>
              <td class="action-col">
                <button
                  v-if="item.status === 'M' && item.isPenerimaAksi"
                  class="btn-icon setuju"
                  title="Setujui"
                  @click="aksi(item, 'setujui')"
                >
                  <i class="bi bi-check-lg"></i>
                </button>
                <button
                  v-if="item.status === 'M' && item.isPenerimaAksi"
                  class="btn-icon delete"
                  title="Tolak"
                  @click="aksi(item, 'tolak')"
                >
                  <i class="bi bi-x-lg"></i>
                </button>
                <button
                  v-if="item.status === 'M' && item.isPengaju"
                  class="btn-icon delete"
                  title="Batalkan"
                  @click="aksi(item, 'batalkan')"
                >
                  <i class="bi bi-trash-fill"></i>
                </button>
                <button
                  v-if="item.status === 'Ds' && item.isPenerimaAksi"
                  class="btn-icon edit"
                  title="Serahkan / Aktifkan"
                  @click="aksi(item, 'aktifkan')"
                >
                  <i class="bi bi-box-arrow-in-right"></i>
                </button>
                <button
                  v-if="item.status === 'A' && item.tipe === 'uang'"
                  class="btn-icon wa"
                  title="Tandai Lunas"
                  @click="aksi(item, 'lunas')"
                >
                  <i class="bi bi-check-circle"></i>
                </button>
              </td>
            </tr>

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
import { getPeminjaman, setujuiPeminjaman, tolakPeminjaman, batalkanPeminjaman, aktifkanPeminjaman } from '../utils/peminjaman';
import { getPeminjamanUang, setujuiPeminjamanUang, tolakPeminjamanUang, batalkanPeminjamanUang, aktifkanPeminjamanUang, lunasPeminjamanUang } from '../utils/peminjamanUang';

export default {
  name: 'PinjamanView',
  data() {
    return {
      pinjamanList: [],
      currentUserId: null
    };
  },
  async mounted() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    this.currentUserId = user.id;
    await this.muatData();
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

        this.pinjamanList = [...uangList, ...barangList].sort((a, b) => b.id - a.id);
      } catch (e) {
        console.error('Gagal memuat data pinjaman', e);
      }
    },
    mapBarang(item) {
      const namaBarang = item.barangs?.map(b => b.nama_barang).join(', ') || '-';
      const sayaPeminjam = item.peminjam_id === this.currentUserId;

      return {
        key: 'barang-' + item.id,
        id: item.id,
        tipe: 'barang',
        detail: namaBarang,
        nama_teman: sayaPeminjam ? item.pemilik.name : item.peminjam.name,
        kategori: sayaPeminjam ? 'Hutang' : 'Piutang',
        jatuh_tempo: this.formatTanggal(item.tgl_tenggat),
        status: item.status,
        isPengaju: item.peminjam_id === this.currentUserId,
        isPenerimaAksi: item.pemilik_id === this.currentUserId,
        raw: item
      };
    },
    mapUang(item) {
      const sayaPeminjam = item.peminjam_id === this.currentUserId;

      return {
        key: 'uang-' + item.id,
        id: item.id,
        tipe: 'uang',
        detail: 'Rp ' + Number(item.nominal).toLocaleString('id-ID'),
        nama_teman: sayaPeminjam ? item.pemberi.name : item.peminjam.name,
        kategori: sayaPeminjam ? 'Hutang' : 'Piutang',
        jatuh_tempo: this.formatTanggal(item.tgl_tenggat),
        status: item.status,
        isPengaju: item.peminjam_id === this.currentUserId,
        isPenerimaAksi: item.pemberi_id === this.currentUserId,
        raw: item
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
    async aksi(item, jenis) {
      try {
        if (item.tipe === 'barang') {
          if (jenis === 'setujui') await setujuiPeminjaman(item.id);
          if (jenis === 'tolak') await tolakPeminjaman(item.id);
          if (jenis === 'batalkan') await batalkanPeminjaman(item.id);
          if (jenis === 'aktifkan') await aktifkanPeminjaman(item.id);
        } else {
          if (jenis === 'setujui') await setujuiPeminjamanUang(item.id);
          if (jenis === 'tolak') await tolakPeminjamanUang(item.id);
          if (jenis === 'batalkan') await batalkanPeminjamanUang(item.id);
          if (jenis === 'aktifkan') await aktifkanPeminjamanUang(item.id);
          if (jenis === 'lunas') await lunasPeminjamanUang(item.id);
        }
        await this.muatData();
      } catch (e) {
        alert(e.response?.data?.message || 'Aksi gagal dilakukan');
      }
    }
  }
};
</script>

<style scoped>
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

.tag-status.menunggu { background-color: #FFF3E0; color: #F77F00; }
.tag-status.disetujui { background-color: #E3F2FD; color: #1565C0; }
.tag-status.ditolak { background-color: #FFEBEE; color: #D62828; }
.tag-status.aktif { background-color: #E8F5E9; color: #2E7D32; }
.tag-status.selesai { background-color: #F0F4F8; color: #4A5568; }
.tag-status.batal { background-color: #F0F0F0; color: #999; }

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

.btn-icon.wa { background-color: #E8F5E9; color: #2E7D32; }
.btn-icon.setuju { background-color: #E8F5E9; color: #2E7D32; }
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