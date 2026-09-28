<template>
  <div class="pinjam-page">
    <div class="main-card">
      <div class="card-top">
        <div class="header-left">
          <router-link to="/dashboard" class="btn-back" title="Kembali ke Beranda">
            <i class="bi bi-arrow-left"></i>
          </router-link>
          <div class="title-group">
            <span class="sub-title">Jaringan Pertemanan</span>
            <h2>Cari & Ikuti Teman</h2>
          </div>
        </div>
      </div>

      <div v-if="loading" class="empty-state">
        <i class="bi bi-arrow-repeat spin"></i>
        <p>Memuat daftar user...</p>
      </div>

      <div v-else-if="users.length === 0" class="empty-state">
        <i class="bi bi-inbox"></i>
        <p>Belum ada user lain terdaftar.</p>
      </div>

      <div v-else class="user-grid">
        <div v-for="u in users" :key="u.id" class="user-card">
          <router-link :to="`/teman/${u.id}`" class="card-avatar-link">
            <div class="card-avatar">{{ getInitial(u.name) }}</div>
          </router-link>

          <router-link :to="`/teman/${u.id}`" class="card-name">{{ u.name }}</router-link>
          <p class="card-email">{{ u.email }}</p>

          <button
            v-if="!u.is_following"
            class="btn-follow follow"
            @click="toggleFollow(u)"
          >
            <i class="bi bi-person-plus-fill"></i> Follow
          </button>
          <button
            v-else
            class="btn-follow unfollow"
            @click="toggleFollow(u)"
          >
            <i class="bi bi-person-check-fill"></i> Mengikuti
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import followApi from '@/utils/follow';

export default {
  name: 'ListTeman',
  data() {
    return {
      users: [],
      loading: false
    };
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      this.loading = true;
      try {
        const res = await followApi.browseUsers();
        this.users = res.data.data || [];
      } catch (error) {
        console.error('Gagal memuat daftar user:', error);
        if (this.$toast) this.$toast.error('Gagal memuat daftar user.');
      } finally {
        this.loading = false;
      }
    },
    getInitial(name) {
      return name ? name.charAt(0).toUpperCase() : '?';
    },
    async toggleFollow(user) {
      try {
        if (user.is_following) {
          await followApi.unfollow(user.id);
          user.is_following = false;
          if (this.$toast) this.$toast.success(`Berhenti mengikuti ${user.name}`);
        } else {
          await followApi.follow(user.id);
          user.is_following = true;
          if (this.$toast) this.$toast.success(`Mulai mengikuti ${user.name}`);
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

/* Grid ala rekomendasi, dengan jarak antar kartu */
.user-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 20px;
}

.user-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 14px;
  padding: 20px 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  transition: box-shadow 0.2s ease, transform 0.15s ease;
}

.user-card:hover {
  box-shadow: 0 8px 20px rgba(0, 48, 73, 0.08);
  transform: translateY(-2px);
}

.card-avatar-link {
  text-decoration: none;
}

.card-avatar {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background-color: #F0F4F8;
  color: #003049;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 800;
  margin-bottom: 12px;
}

.card-name {
  font-size: 14px;
  font-weight: 700;
  color: #003049;
  text-decoration: none;
  margin-bottom: 2px;
}

.card-name:hover {
  text-decoration: underline;
}

.card-email {
  font-size: 12px;
  color: #A0AEC0;
  margin: 0 0 16px 0;
  word-break: break-all;
}

.btn-follow {
  width: 100%;
  padding: 8px 14px;
  border: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.btn-follow.follow {
  background-color: #F77F00;
  color: white;
}

.btn-follow.follow:hover {
  background-color: #E07300;
}

.btn-follow.unfollow {
  background-color: #E3F2FD;
  color: #1565C0;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #A0AEC0;
}

.empty-state i {
  font-size: 32px;
  display: block;
  margin-bottom: 8px;
}
</style>