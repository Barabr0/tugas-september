<template>
  <div class="add-container">
    <div class="form-card">
      <div class="form-header">
        <h2>Catat Pinjaman Baru</h2>
        <p>Isi formulir untuk mencatat pinjaman uang atau barang dengan teman.</p>
      </div>

      <form @submit.prevent="saveTransaction" class="form-body">
        <div class="form-group">
          <label>Jenis Transaksi</label>
          <div class="radio-group">
            <label :class="['radio-btn', { active: form.category === 'Orang Lain Meminjam' }]">
              <input type="radio" value="Orang Lain Meminjam" v-model="form.category" />
              <span>📈 Teman Meminjam (Piutang)</span>
            </label>
            <label :class="['radio-btn', { active: form.category === 'Saya Meminjam' }]">
              <input type="radio" value="Saya Meminjam" v-model="form.category" />
              <span>📉 Saya Meminjam (Hutang)</span>
            </label>
          </div>
        </div>

        <div class="form-group">
          <label>Tipe Pinjaman</label>
          <select v-model="form.type" class="input-select">
            <option value="uang">💵 Uang</option>
            <option value="barang">📷 Barang</option>
          </select>
        </div>

        <div class="form-group">
          <label for="name">Nama Teman</label>
          <select id="name" v-model="form.temanId" required class="input-control">
            <option value="" disabled>Pilih teman</option>
            <option v-for="u in daftarUser" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>

        <div class="form-group" v-if="form.type === 'uang'">
          <label for="nominal">Nominal (Rp)</label>
          <input
            id="nominal"
            v-model.number="form.nominal"
            type="number"
            min="1"
            placeholder="Contoh: 50000"
            required
            class="input-control"
          />
        </div>
        <div class="form-group" v-else>
          <label for="barang">Pilih Barang</label>
          <select id="barang" v-model="form.barangId" class="input-control">
            <option value="" disabled>Pilih barang</option>
            <option v-for="b in daftarBarang" :key="b.id" :value="b.id">
              {{ b.nama_barang }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label for="dueDate">Tanggal Tanggung Jawab / Kembali</label>
          <input
            id="dueDate"
            v-model="form.dueDate"
            type="date"
            required
            class="input-control"
          />
        </div>

        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="cancel">Batal</button>
          <button type="submit" class="btn-save" :disabled="loading">
            {{ loading ? 'Menyimpan...' : 'Simpan Transaksi' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { getUsers } from '../../utils/user';
import { ajukanPinjamUang } from '../../utils/peminjamanUang';
import { ajukanPinjamBarang, getBarangTersedia } from '../../utils/peminjaman';

export default {
  name: 'AddPinjaman',
  data() {
    return {
      form: {
        category: 'Orang Lain Meminjam',
        type: 'uang',
        temanId: '',
        nominal: null,
        barangId: '',
        dueDate: ''
      },
      daftarUser: [],
      daftarBarang: [],
      loading: false
    };
  },
  async mounted() {
    try {
      const resUser = await getUsers();
      this.daftarUser = resUser.data.data;

      const resBarang = await getBarangTersedia();
      this.daftarBarang = resBarang.data.data;
    } catch (e) {
      console.error('Gagal memuat data awal', e);
    }
  },
  methods: {
    async saveTransaction() {
      if (!this.form.temanId) {
        alert('Pilih teman terlebih dahulu.');
        return;
      }
      if (!this.form.dueDate) {
        alert('Tanggal wajib diisi.');
        return;
      }

      this.loading = true;
      const hariIni = new Date().toISOString().split('T')[0];
      const arah = this.form.category === 'Orang Lain Meminjam' ? 'piutang' : 'hutang';

      try {
        if (this.form.type === 'uang') {
          if (!this.form.nominal || this.form.nominal <= 0) {
            alert('Nominal wajib diisi.');
            this.loading = false;
            return;
          }

          await ajukanPinjamUang({
            teman_id: this.form.temanId,
            arah: arah,
            nominal: this.form.nominal,
            tgl_pinjam: hariIni,
            tgl_tenggat: this.form.dueDate,
          });
        } else {
          if (!this.form.barangId) {
            alert('Pilih barang terlebih dahulu.');
            this.loading = false;
            return;
          }

          await ajukanPinjamBarang({
            barang_ids: [this.form.barangId],
            tgl_pinjam: hariIni,
            tgl_tenggat: this.form.dueDate,
          });
        }

        alert('Berhasil menyimpan catatan pinjaman!');
        this.$router.push('/dashboard');

      } catch (error) {
        const pesan = error.response?.data?.message || 'Gagal menyimpan transaksi.';
        alert(pesan);
      } finally {
        this.loading = false;
      }
    },
    cancel() {
      this.$router.push('/dashboard');
    }
  }
};
</script>

<style scoped>
.add-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #FDFBF7;
  padding: 24px;
  font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.form-card {
  background: #ffffff;
  width: 100%;
  max-width: 500px;
  padding: 32px;
  border-radius: 16px;
  border: 1px solid #E2E8F0;
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

.form-header h2 {
  color: #003049;
  margin: 0 0 6px 0;
  font-size: 22px;
}

.form-header p {
  color: #718096;
  font-size: 13px;
  margin-bottom: 24px;
}

.form-body {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 12px;
  font-weight: 700;
  color: #003049;
}

.radio-group {
  display: flex;
  gap: 10px;
}

.radio-btn {
  flex: 1;
  border: 1px solid #E2E8F0;
  padding: 10px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 600;
  text-align: center;
  background: #F8F9FA;
}

.radio-btn input {
  display: none;
}

.radio-btn.active {
  border-color: #F77F00;
  background-color: rgba(247, 127, 0, 0.1);
  color: #F77F00;
}

.input-control, .input-select {
  padding: 10px 14px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 13px;
  outline: none;
  width: 100%;
  box-sizing: border-box;
}

.input-control:focus, .input-select:focus {
  border-color: #003049;
}

.select-multi {
  height: 100px;
}

.hint {
  font-size: 11px;
  color: #A0AEC0;
  margin-top: 4px;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 10px;
}

.btn-save {
  flex: 1;
  background-color: #F77F00;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-cancel {
  background-color: transparent;
  color: #718096;
  border: 1px solid #CBD5E0;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}
</style>