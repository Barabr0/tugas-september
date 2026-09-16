<template>
  <div class="add-container">
    <div class="form-card">
      <div class="form-header">
        <h2>Tambah Barang Baru</h2>
        <p>Catat barang Anda agar bisa dipinjamkan kepada teman.</p>
      </div>

      <form @submit.prevent="saveBarang" class="form-body">
        
        <div class="form-group">
          <label for="nama">Nama Barang</label>
          <input
            id="nama"
            v-model="form.nama_barang"
            type="text"
            placeholder="Contoh: Kamera DSLR Canon"
            required
            class="input-control"
          />
          <p v-if="errors.nama_barang" class="error-text">{{ errors.nama_barang[0] }}</p>
        </div>

        <div class="form-group">
          <label for="kategori">Kategori</label>
          <select id="kategori" v-model="form.kategori_id" required class="input-select">
            <option value="" disabled>Pilih Kategori</option>
            <option v-for="kat in kategoris" :key="kat.id" :value="kat.id">
              {{ kat.nama_kategori }}
            </option>
          </select>
          <p v-if="errors.kategori_id" class="error-text">{{ errors.kategori_id[0] }}</p>
        </div>

        <div class="form-group">
          <label for="deskripsi">Deskripsi</label>
          <textarea
            id="deskripsi"
            v-model="form.deskripsi"
            rows="4"
            placeholder="Masukkan deskripsi atau catatan barang..."
            required
            class="input-control"
          ></textarea>
          <p v-if="errors.deskripsi" class="error-text">{{ errors.deskripsi[0] }}</p>
        </div>

        <div class="form-group">
          <label>Kondisi Barang</label>
          <div class="radio-group">
            <label :class="['radio-btn', { active: form.kondisi === 'B' }]">
              <input type="radio" value="B" v-model="form.kondisi" />
              <span>👍 Baik</span>
            </label>
            <label :class="['radio-btn', { active: form.kondisi === 'R' }]">
              <input type="radio" value="R" v-model="form.kondisi" />
              <span>⚠️ Rusak Ringan</span>
            </label>
            <label :class="['radio-btn', { active: form.kondisi === 'P' }]">
              <input type="radio" value="P" v-model="form.kondisi" />
              <span>🔧 Diperbaiki</span>
            </label>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="cancel">Batal</button>
          <button type="submit" class="btn-save" :disabled="loading">
            {{ loading ? 'Menyimpan...' : 'Simpan Barang' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
import barangApi from '@/utils/barang';
import kategoriApi from '@/utils/kategori';

export default {
  name: 'AddBarang',
  data() {
    return {
      form: {
        nama_barang: '',
        kategori_id: '',
        deskripsi: '',
        kondisi: 'B', // Default Baik
      },
      kategoris: [],
      loading: false,
      errors: {}
    };
  },
  mounted() {
    this.fetchKategoris();
  },
  methods: {
    async fetchKategoris() {
      try {
        const response = await kategoriApi.getAll();
        this.kategoris = response.data?.data || response.data || [];
      } catch (error) {
        console.error('Gagal mengambil kategori:', error);
        alert('Gagal memuat data kategori. Pastikan kategori sudah ada.');
      }
    },
    async saveBarang() {
      this.loading = true;
      this.errors = {};
      
      try {
        // Panggil API store barang
        await barangApi.addBarang(this.form);
        
        alert('Barang berhasil ditambahkan!');
        this.$router.push('/barang'); // Redirect ke halaman list barang
        
      } catch (error) {
        if (error.response && error.response.status === 422) {
          // Tangkap error validasi dari Laravel
          this.errors = error.response.data.errors || {};
        } else {
          console.error('Gagal menyimpan barang:', error);
          alert('Terjadi kesalahan saat menyimpan barang.');
        }
      } finally {
        this.loading = false;
      }
    },
    cancel() {
      // Kembali ke halaman sebelumnya atau ke list barang
      this.$router.push('/barang');
    }
  }
};
</script>

<style scoped>
/* Tema yang sama persis dengan AddPinjaman.vue */
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
  display: flex;
  justify-content: center;
  align-items: center;
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
  font-family: inherit;
}

.input-control:focus, .input-select:focus {
  border-color: #003049;
}

.error-text {
  color: #b91c1c;
  font-size: 11px;
  margin: 0;
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