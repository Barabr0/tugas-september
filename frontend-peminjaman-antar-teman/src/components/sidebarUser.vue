<template>
  <aside class="sidebar">
    <div class="sidebar-brand">
      <i class="bi bi-person-circle brand-icon"></i>
      <h2>JaMan <span class="user-badge">User</span></h2>
    </div>

    <nav class="sidebar-menu">
      <div class="menu-label">UTAMA</div>

      <router-link to="/dashboard" class="menu-item" active-class="active">
        <i class="bi bi-grid-1x2-fill"></i>
        <span>Dashboard</span>
      </router-link>

      <router-link to="/pinjaman_saya" class="menu-item" active-class="active">
        <i class="bi bi-receipt-cutoff"></i>
        <span>Pinjaman Saya</span>
      </router-link>

      <router-link to="/barang" class="menu-item" active-class="active">
        <i class="bi bi-box-seam-fill"></i>
        <span>Barang Saya</span>
      </router-link>

      <router-link to="/kategori" class="menu-item" active-class="active">
        <i class="bi bi-tags-fill"></i>
        <span>Kategori Barang</span>
      </router-link>

      <div class="menu-label">BANTUAN</div>

      <router-link to="/bantuan" class="menu-item" active-class="active">
        <i class="bi bi-life-preserver"></i>
        <span>Minta Bantuan</span>
      </router-link>
    </nav>
  </aside>
</template>

<script>
export default {
  name: 'sidebarUser'
};
</script>

<style scoped>
.sidebar {
  width: 260px;
  background-color: #003049;
  color: #ffffff;
  display: flex;
  flex-direction: column;
  padding: 24px 16px;
  flex-shrink: 0;
  min-height: 100vh;
  box-sizing: border-box;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 12px 24px 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand-icon {
  font-size: 24px;
  color: #FCBF49;
}

.sidebar-brand h2 {
  font-size: 20px;
  margin: 0;
  color: #ffffff;
  font-weight: 800;
}

.user-badge {
  font-size: 11px;
  color: #003049;
  font-weight: 700;
  background: #FCBF49;
  padding: 2px 8px;
  border-radius: 6px;
  margin-left: 2px;
}

.sidebar-menu {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-top: 20px;
  flex: 1;
}

.menu-label {
  font-size: 10px;
  font-weight: 700;
  color: #A0AEC0;
  letter-spacing: 1px;
  padding: 12px 12px 6px 12px;
}

.menu-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  color: #EAE2B7;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.menu-item i {
  font-size: 16px;
}

.menu-item:hover, .menu-item.active {
  background-color: #F77F00;
  color: #ffffff;
}
</style>