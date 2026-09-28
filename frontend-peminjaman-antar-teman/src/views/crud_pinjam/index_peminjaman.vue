<template>
  <div class="add-container">
    <div class="form-card">
      <div class="form-header">
        <h2>Ajukan Pinjaman</h2>
        <p>Pilih teman dan barang/uang yang ingin kamu pinjam.</p>
      </div>

      <form @submit.prevent="saveTransaction" class="form-body">
        
        <div class="form-group">
          <label>Tipe Pinjaman</label>
          <select v-model="form.type" class="input-select">
            <option value="uang">💵 Uang</option>
            <option value="barang">📷 Barang</option>
          </select>
        </div>

        <div class="form-group">
          <label for="name">Pinjam Dari (Nama Teman)</label>
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
          <select
            id="barang"
            v-model="form.barangId"
            class="input-control"
            required
            :disabled="!form.temanId"
          >
            <option value="" disabled>
              {{ form.temanId ? 'Pilih barang' : 'Pilih teman dulu' }}
            </option>
            <option v-for="b in daftarBarang" :key="b.id" :value="b.id">
              {{ b.nama_barang }}
            </option>
          </select>
          <small v-if="form.temanId && daftarBarang.length === 0" class="hint">
            Teman ini belum punya barang yang tersedia.
          </small>
        </div>

        <div class="form-group">
          <label for="dueDate">Tanggal Harus Kembali / Lunas</label>
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
            {{ loading ? 'Menyimpan...' : 'Ajukan Pinjaman' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { getUsers } from '../../utils/user';
import { ajukanPinjamUang } from '../../utils/peminjamanUang';
import { ajukanPinjamBarang } from '../../utils/peminjaman';
import barangApi from '../../utils/barang';

export default {
  name: 'AddPinjaman',
  data() {
    return {
      form: {
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
  watch: {
    async 'form.temanId'(newVal) {
      this.form.barangId = '';
      this.daftarBarang = [];

      if (!newVal) return;

      try {
        const resBarang = await barangApi.getBarangTersedia(newVal);
        this.daftarBarang = resBarang.data.data || resBarang.data || [];
      } catch (e) {
        console.error('Gagal memuat barang milik teman ini', e);
      }
    }
  },
  async mounted() {
    try {
      const resUser = await getUsers();
      this.daftarUser = resUser.data.data || resUser.data || [];
    } catch (e) {
      console.error('Gagal memuat daftar user', e);
      if (this.$toast) this.$toast.error('Gagal memuat data teman');
    }
  },
  methods: {
    async saveTransaction() {
      if (!this.form.temanId) {
        if (this.$toast) this.$toast.error('Pilih teman terlebih dahulu.');
        return;
      }
      if (!this.form.dueDate) {
        if (this.$toast) this.$toast.error('Tanggal wajib diisi.');
        return;
      }

      this.loading = true;
      const hariIni = new Date().toISOString().split('T')[0];

      try {
        if (this.form.type === 'uang') {
          if (!this.form.nominal || this.form.nominal <= 0) {
            if (this.$toast) this.$toast.error('Nominal wajib diisi.');
            this.loading = false;
            return;
          }

          await ajukanPinjamUang({
            teman_id: this.form.temanId,
            arah: 'hutang',
            nominal: this.form.nominal,
            tgl_pinjam: hariIni,
            tgl_tenggat: this.form.dueDate,
          });
        } else {
          if (!this.form.barangId) {
            if (this.$toast) this.$toast.error('Pilih barang terlebih dahulu.');
            this.loading = false;
            return;
          }

          await ajukanPinjamBarang({
            barang_ids: [this.form.barangId],
            tgl_pinjam: hariIni,
            tgl_tenggat: this.form.dueDate,
          });
        }

        if (this.$toast) this.$toast.success('Berhasil mengajukan pinjaman!');
        this.$router.push('/dashboard');

      } catch (error) {
        const pesan = error.response?.data?.message || 'Gagal menyimpan transaksi.';
        if (this.$toast) this.$toast.error(pesan);
      } finally {
        this.loading = false;
      }
    },
    cancel() {
      this.$router.push('/pinjaman_saya');
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

.input-control, .input-select {
  padding: 10px 14px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  font-size: 13px;
  outline: none;
  width: 100%;
  box-sizing: border-box;
  font-family: inherit;
}

.input-control:focus, .input-select:focus {
  border-color: #003049;
}

.input-control:disabled {
  background-color: #F7FAFC;
  cursor: not-allowed;
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
  font-size: 13px;
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
  font-size: 13px;
}
</style>