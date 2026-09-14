<template>
  <div class="add-container">
    <div class="form-card">
      <div class="form-header">
        <h2>Catat Pinjaman Baru</h2>
        <p>Isi formulir untuk mencatat pinjaman uang atau barang dengan teman.</p>
      </div>

      <form @submit.prevent="saveTransaction" class="form-body">
        <!-- Jenis Pinjaman -->
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

        <!-- Tipe Transaksi (Uang / Barang) -->
        <div class="form-group">
          <label>Tipe Pinjaman</label>
          <select v-model="form.type" class="input-select">
            <option value="uang">💵 Uang</option>
            <option value="barang">📷 Barang</option>
          </select>
        </div>

        <!-- Nama Teman -->
        <div class="form-group">
          <label for="name">Nama Teman</label>
          <input 
            id="name"
            v-model="form.name" 
            type="text" 
            placeholder="Contoh: Andi, Rian, Siti" 
            required 
            class="input-control"
          />
        </div>

        <!-- Deskripsi Pinjaman -->
        <div class="form-group">
          <label for="desc">{{ form.type === 'uang' ? 'Nominal (Rp)' : 'Deskripsi Barang' }}</label>
          <input 
            id="desc"
            v-model="form.description" 
            type="text" 
            :placeholder="form.type === 'uang' ? 'Contoh: 50.000 (Uang Makan)' : 'Contoh: Kamera DSLR Canon'" 
            required 
            class="input-control"
          />
        </div>

        <!-- Tanggal Jatuh Tempo -->
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

        <!-- Action Buttons -->
        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="cancel">Batal</button>
          <button type="submit" class="btn-save">Simpan Transaksi</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddPinjaman',
  data() {
    return {
      form: {
        category: 'Orang Lain Meminjam',
        type: 'uang',
        name: '',
        description: '',
        dueDate: ''
      }
    };
  },
  methods: {
    saveTransaction() {
      alert(`Berhasil menyimpan catatan pinjaman untuk ${this.form.name}!`);
      // Kembali ke halaman utama/dashboard setelah simpan
      this.$router.push('/');
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