<template>
  <div class="pinjam-page">
    <div class="main-card">
      <div class="card-top">
        <div class="header-left">
          <router-link to="/teman" class="btn-back" title="Kembali">
            <i class="bi bi-arrow-left"></i>
          </router-link>
          <div class="title-group">
            <span class="sub-title">Profil Teman</span>
            <h2>{{ user?.name || 'Memuat...' }}</h2>
          </div>
        </div>
        <button
          v-if="user"
          :class="['btn-follow', isFollowing ? 'unfollow' : 'follow']"
          @click="toggleFollow"
        >
          <i :class="isFollowing ? 'bi bi-person-check-fill' : 'bi bi-person-plus-fill'"></i>
          {{ isFollowing ? 'Mengikuti' : 'Follow' }}
        </button>
      </div>

      <div v-if="loading" class="empty-state">
        <i class="bi bi-arrow-repeat spin"></i>
        <p>Memuat profil...</p>
      </div>

      <div v-else-if="user">
        <div class="profile-info">
          <p><strong>Email:</strong> {{ user.email }}</p>
          <p><strong>Skor Reputasi:</strong> {{ user.skor_reputasi }}</p>
        </div>

        <h3 class="section-title">Barang yang Bisa Dipinjam</h3>

        <div class="table-wrapper">
          <table class="custom-table">
            <thead>
              <tr>
                <th style="width: 50px;" class="text-center">No</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="barangs.length === 0">
                <td colspan="4" class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <p>Teman ini belum punya barang yang tersedia.</p>
                </td>
              </tr>
              <tr v-for="(b, index) in barangs" :key="b.id" v-else>
                <td class="text-center id-col">#{{ index + 1 }}</td>
                <td class="name-col">{{ b.nama_barang }}</td>
                <td>
                  <span class="tag-kategori-barang">{{ b.kategori?.nama_kategori || '-' }}</span>
                </td>
                <td class="desc-col">{{ b.deskripsi }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import followApi from '@/utils/follow';

export default {
  name: 'DetailTeman',
  data() {
    return {
      user: null,
      isFollowing: false,
      barangs: [],
      loading: false
    };
  },
  mounted() {
    this.fetchDetail();
  },
  methods: {
    async fetchDetail() {
      this.loading = true;
      try {
        const id = this.$route.params.id;
        const res = await followApi.getDetail(id);
        this.user = res.data.data.user;
        this.isFollowing = res.data.data.is_following;
        this.barangs = res.data.data.barangs;
      } catch (error) {
        console.error('Gagal memuat detail teman:', error);
        if (this.$toast) this.$toast.error('Gagal memuat profil teman.');
      } finally {
        this.loading = false;
      }
    },
    async toggleFollow() {
      try {
        if (this.isFollowing) {
          await followApi.unfollow(this.user.id);
          this.isFollowing = false;
          if (this.$toast) this.$toast.success(`Berhenti mengikuti ${this.user.name}`);
        } else {
          await followApi.follow(this.user.id);
          this.isFollowing = true;
          if (this.$toast) this.$toast.success(`Mulai mengikuti ${this.user.name}`);
        }
      } catch (error) {
        const pesan = error.response?.data?.message || 'Aksi gagal dilakukan.';
        if (this.$toast) this.$toast.error(pesan);
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
  max-width: 900px;
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
  transition: all 0.2s ease;
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

.btn-follow {
  padding: 10px 18px;
  border: none;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-follow.follow {
  background-color: #F77F00;
  color: white;
}

.btn-follow.unfollow {
  background-color: #E3F2FD;
  color: #1565C0;
}

.profile-info {
  background-color: #F8F9FA;
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 24px;
}

.profile-info p {
  margin: 4px 0;
  font-size: 13px;
  color: #4A5568;
}

.section-title {
  font-size: 16px;
  color: #003049;
  margin-bottom: 12px;
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

.desc-col {
  color: #4A5568;
  font-size: 13px;
}

.tag-kategori-barang {
  background-color: #F0F4F8;
  color: #003049;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
}

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