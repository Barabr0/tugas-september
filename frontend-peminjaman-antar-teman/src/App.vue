<template>
  <div id="app-wrapper">
    <Router-view />
    <!-- <RouterLink to="/login">Login</RouterLink> -->
  </div>
</template>

<script>
import HomeView from './views/Home.vue'
import Login from './views/Auth/login.vue'

export default {
  name: 'App', components: { HomeView, Login }
}
</script>

<style>
/* CSS Reset dasar */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: sans-serif;
  background-color: #FDFBF7;
}
</style>